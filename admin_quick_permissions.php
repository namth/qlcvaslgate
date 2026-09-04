<?php
/**
 * Tính năng Phân quyền nhanh hàng loạt (Bulk Quick Permissions)
 * Quản lý và phân quyền Chuyên mục công việc (Taxonomy group) cho nhân sự.
 */

if (!defined('ABSPATH')) {
    exit;
}

// 1. Đăng ký trang quản trị dưới menu "Thành viên" (Users)
add_action('admin_menu', 'asl_register_quick_permissions_page');
function asl_register_quick_permissions_page() {
    add_submenu_page(
        'users.php',
        __('Phân quyền nhanh', 'qlcv'),
        __('Phân quyền nhanh', 'qlcv'),
        'manage_options',
        'qlcv-quick-permissions',
        'asl_render_quick_permissions_page'
    );
}

// 2. Giao diện và Xử lý phân quyền
function asl_render_quick_permissions_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Bạn không có quyền truy cập trang này.', 'qlcv'));
    }

    $notice = null;

    // Xử lý khi submit Form
    if (isset($_POST['asl_quick_permissions_submit'])) {
        check_admin_referer('asl_quick_permissions_action', 'asl_quick_permissions_nonce');

        $selected_users = isset($_POST['selected_users']) ? array_map('intval', (array)$_POST['selected_users']) : array();
        $selected_terms = isset($_POST['selected_terms']) ? array_map('intval', (array)$_POST['selected_terms']) : array();
        $action_mode    = isset($_POST['action_mode']) ? sanitize_text_field($_POST['action_mode']) : 'add';

        if (empty($selected_users)) {
            $notice = array(
                'type'    => 'error',
                'message' => __('Vui lòng chọn ít nhất một nhân sự để thực hiện phân quyền.', 'qlcv')
            );
        } elseif (empty($selected_terms) && $action_mode !== 'sync') {
            $notice = array(
                'type'    => 'error',
                'message' => __('Vui lòng chọn ít nhất một chuyên mục công việc.', 'qlcv')
            );
        } else {
            $updated_count = 0;

            foreach ($selected_users as $user_id) {
                // Lấy quyền hiện tại của user
                $current_terms = get_field('nhom_cong_viec', 'user_' . $user_id);
                if (!is_array($current_terms)) {
                    $current_terms = array();
                }
                $current_terms = array_map('intval', array_filter($current_terms));

                if ($action_mode === 'add') {
                    // Thêm bổ sung (Giữ nguyên quyền cũ)
                    $new_terms = array_values(array_unique(array_merge($current_terms, $selected_terms)));
                } elseif ($action_mode === 'sync') {
                    // Đồng bộ / Ghi đè danh sách chọn
                    $new_terms = array_values(array_unique($selected_terms));
                } elseif ($action_mode === 'remove') {
                    // Gỡ bỏ các quyền được chọn
                    $new_terms = array_values(array_diff($current_terms, $selected_terms));
                } else {
                    continue;
                }

                // Cập nhật trường ACF và usermeta
                update_field('field_652d1a5e15e85', $new_terms, 'user_' . $user_id);
                update_user_meta($user_id, 'nhom_cong_viec', $new_terms);

                // Đồng bộ bảng wp_aslmember
                if (function_exists('export_single_member_to_table')) {
                    export_single_member_to_table($user_id);
                }

                $updated_count++;
            }

            $mode_labels = array(
                'add'    => __('Thêm bổ sung', 'qlcv'),
                'sync'   => __('Đồng bộ / Ghi đè', 'qlcv'),
                'remove' => __('Gỡ bỏ', 'qlcv'),
            );
            $mode_text = isset($mode_labels[$action_mode]) ? $mode_labels[$action_mode] : $action_mode;

            $notice = array(
                'type'    => 'success',
                'message' => sprintf(
                    __('Đã cập nhật phân quyền thành công theo chế độ "%s" cho %d nhân sự!', 'qlcv'),
                    $mode_text,
                    $updated_count
                )
            );
        }
    }

    // Các vai trò đối tác / bên ngoài cần loại trừ khỏi phân quyền nội bộ
    $excluded_roles = array('partner', 'foreign_partner', 'subscriber');

    // Lấy danh sách Users (loại trừ partner, foreign_partner, subscriber)
    $all_users = get_users(array(
        'role__not_in' => $excluded_roles,
        'orderby'      => 'display_name',
        'order'        => 'ASC',
    ));

    // Lọc lại chắc chắn để loại bỏ bất kỳ user nào có chứa vai trò bị loại trừ
    $all_users = array_filter($all_users, function($u) use ($excluded_roles) {
        $roles = (array) $u->roles;
        foreach ($roles as $r) {
            if (in_array($r, $excluded_roles, true)) {
                return false;
            }
        }
        return true;
    });

    // Thu thập danh sách vai trò hiện có
    $all_roles = array();
    foreach ($all_users as $u) {
        if (!empty($u->roles) && is_array($u->roles)) {
            foreach ($u->roles as $r) {
                if (in_array($r, $excluded_roles, true)) {
                    continue;
                }
                if (!isset($all_roles[$r])) {
                    $all_roles[$r] = ucfirst(str_replace('_', ' ', $r));
                }
            }
        }
    }
    asort($all_roles);

    // Lấy danh sách Taxonomy `group`
    $all_terms = get_terms(array(
        'taxonomy'   => 'group',
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ));

    // Xây dựng cây phân cấp (cha - con)
    $term_roots = array();
    $term_children = array();
    if (!is_wp_error($all_terms) && !empty($all_terms)) {
        foreach ($all_terms as $term_item) {
            if ($term_item->parent == 0) {
                $term_roots[$term_item->term_id] = $term_item;
            } else {
                $term_children[$term_item->parent][] = $term_item;
            }
        }
    }

    // Map tên term để hiển thị preview
    $term_names_map = array();
    if (!is_wp_error($all_terms) && !empty($all_terms)) {
        foreach ($all_terms as $t) {
            $term_names_map[$t->term_id] = $t->name;
        }
    }
    ?>

    <div class="wrap asl-quick-permissions-wrap">
        <h1 class="wp-heading-inline">
            <span class="dashicons dashicons-shield-alt" style="font-size: 30px; width: 30px; height: 30px; vertical-align: middle; margin-right: 8px; color: #2271b1;"></span>
            <?php _e('Phân quyền nhanh Chuyên mục công việc cho Nhân sự', 'qlcv'); ?>
        </h1>
        <p class="description" style="margin-top: 5px; margin-bottom: 20px; font-size: 14px;">
            <?php _e('Công cụ hỗ trợ thêm quyền xem các chuyên mục mới tạo (như Thực thi bản quyền phần mềm) cho nhiều nhân viên cùng lúc mà không lo mất quyền cũ, hoặc đồng bộ lại toàn bộ quyền.', 'qlcv'); ?>
        </p>

        <?php if ($notice): ?>
            <div class="notice notice-<?php echo esc_attr($notice['type']); ?> is-dismissible" style="padding: 12px 15px; margin-bottom: 20px; border-left-width: 4px;">
                <p style="font-size: 14px; font-weight: 500; margin: 0;"><?php echo esc_html($notice['message']); ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="asl_quick_permissions_form">
            <?php wp_nonce_field('asl_quick_permissions_action', 'asl_quick_permissions_nonce'); ?>

            <div class="asl-qp-layout">
                <!-- CỘT BÊN TRÁI: 1. CHẾ ĐỘ & 2. CHUYÊN MỤC -->
                <div class="asl-qp-col-left">
                    <!-- 1. CHỌN CHẾ ĐỘ PHÂN QUYỀN -->
                    <div class="asl-qp-card asl-qp-card-mode">
                        <div class="asl-qp-card-header">
                            <div class="asl-qp-card-title">
                                <span class="dashicons dashicons-admin-settings"></span>
                                <?php _e('1. Chọn Chế độ Phân quyền', 'qlcv'); ?>
                            </div>
                        </div>
                        <div class="asl-qp-card-body">
                            <div class="asl-qp-mode-options">
                                <label class="asl-qp-mode-label is-selected">
                                    <input type="radio" name="action_mode" value="add" checked>
                                    <div class="asl-qp-mode-info">
                                        <div class="asl-qp-mode-title">
                                            <span class="dashicons dashicons-plus-alt2" style="color: #2e7d32;"></span>
                                            <strong><?php _e('Thêm bổ sung (Giữ nguyên quyền cũ)', 'qlcv'); ?></strong>
                                            <span class="asl-qp-recommend-badge"><?php _e('Khuyên dùng', 'qlcv'); ?></span>
                                        </div>
                                        <div class="asl-qp-mode-desc">
                                            <?php _e('Chỉ thêm các chuyên mục được chọn vào quyền hiện tại của nhân sự. Không làm mất bất kỳ quyền nào họ đang có.', 'qlcv'); ?>
                                        </div>
                                    </div>
                                </label>

                                <label class="asl-qp-mode-label">
                                    <input type="radio" name="action_mode" value="sync">
                                    <div class="asl-qp-mode-info">
                                        <div class="asl-qp-mode-title">
                                            <span class="dashicons dashicons-update" style="color: #d32f2f;"></span>
                                            <strong><?php _e('Đồng bộ / Ghi đè toàn bộ', 'qlcv'); ?></strong>
                                        </div>
                                        <div class="asl-qp-mode-desc">
                                            <?php _e('Đặt lại chính xác danh sách quyền của các nhân sự được chọn thành các chuyên mục được tích chọn bên dưới.', 'qlcv'); ?>
                                        </div>
                                    </div>
                                </label>

                                <label class="asl-qp-mode-label">
                                    <input type="radio" name="action_mode" value="remove">
                                    <div class="asl-qp-mode-info">
                                        <div class="asl-qp-mode-title">
                                            <span class="dashicons dashicons-dismiss" style="color: #c2185b;"></span>
                                            <strong><?php _e('Gỡ bỏ quyền đã chọn', 'qlcv'); ?></strong>
                                        </div>
                                        <div class="asl-qp-mode-desc">
                                            <?php _e('Gỡ bỏ các chuyên mục được chọn khỏi danh sách quyền hiện có của các nhân sự đã chọn.', 'qlcv'); ?>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- 2. CHỌN CHUYÊN MỤC CÔNG VIỆC -->
                    <div class="asl-qp-card asl-qp-card-terms">
                        <div class="asl-qp-card-header">
                            <div class="asl-qp-card-title">
                                <span class="dashicons dashicons-category"></span>
                                <?php _e('2. Chọn Chuyên mục công việc', 'qlcv'); ?>
                                <span class="asl-qp-badge" id="selected_terms_count">0 <?php _e('đã chọn', 'qlcv'); ?></span>
                            </div>
                        </div>

                        <div class="asl-qp-filters">
                            <div class="asl-qp-filter-row">
                                <div class="asl-qp-search-box" style="width: 100%;">
                                    <span class="dashicons dashicons-search"></span>
                                    <input type="text" id="term_search_input" placeholder="<?php _e('Tìm chuyên mục (ví dụ: Thực thi, Bản quyền, Nhãn hiệu...)...', 'qlcv'); ?>">
                                </div>
                            </div>
                            <div class="asl-qp-action-links">
                                <button type="button" class="button button-small" id="select_all_terms"><?php _e('Chọn tất cả', 'qlcv'); ?></button>
                                <button type="button" class="button button-small" id="deselect_all_terms"><?php _e('Bỏ chọn tất cả', 'qlcv'); ?></button>
                                <span class="asl-qp-total-text" id="visible_terms_count"><?php echo sprintf(__('Tổng số: %d chuyên mục', 'qlcv'), count($all_terms)); ?></span>
                            </div>
                        </div>

                        <div class="asl-qp-term-tree custom-scroll" id="asl_term_tree_container">
                            <?php if (!empty($term_roots)): ?>
                                <?php foreach ($term_roots as $root_term):
                                    $has_children = !empty($term_children[$root_term->term_id]);
                                    $is_highlight = ($root_term->slug === 'thuc-thi-ban-quyen-phan-mem');
                                ?>
                                    <div class="asl-qp-term-item is-root <?php echo $is_highlight ? 'is-highlight' : ''; ?>" data-name="<?php echo esc_attr(mb_strtolower($root_term->name, 'UTF-8')); ?>" data-slug="<?php echo esc_attr($root_term->slug); ?>">
                                        <label class="asl-qp-term-label">
                                            <input type="checkbox" name="selected_terms[]" value="<?php echo esc_attr($root_term->term_id); ?>" class="asl-qp-term-checkbox">
                                            <span class="asl-qp-term-name">
                                                <strong><?php echo esc_html($root_term->name); ?></strong>
                                                <?php if ($root_term->slug === 'thuc-thi-ban-quyen-phan-mem'): ?>
                                                    <span class="asl-qp-new-badge"><?php _e('Mới tạo', 'qlcv'); ?></span>
                                                <?php endif; ?>
                                            </span>
                                        </label>

                                        <?php if ($has_children): ?>
                                            <div class="asl-qp-term-children">
                                                <?php foreach ($term_children[$root_term->term_id] as $child_term):
                                                    $is_child_highlight = ($child_term->slug === 'thuc-thi-ban-quyen-phan-mem');
                                                ?>
                                                    <div class="asl-qp-term-item is-child <?php echo $is_child_highlight ? 'is-highlight' : ''; ?>" data-name="<?php echo esc_attr(mb_strtolower($child_term->name, 'UTF-8')); ?>" data-slug="<?php echo esc_attr($child_term->slug); ?>">
                                                        <label class="asl-qp-term-label">
                                                            <input type="checkbox" name="selected_terms[]" value="<?php echo esc_attr($child_term->term_id); ?>" class="asl-qp-term-checkbox">
                                                            <span class="asl-qp-term-name">
                                                                <?php echo esc_html($child_term->name); ?>
                                                                <?php if ($is_child_highlight): ?>
                                                                    <span class="asl-qp-new-badge"><?php _e('Mới tạo', 'qlcv'); ?></span>
                                                                <?php endif; ?>
                                                            </span>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- CỘT BÊN PHẢI: 3. CHỌN NHÂN SỰ ÁP DỤNG & NÚT LƯU -->
                <div class="asl-qp-col-right">
                    <div class="asl-qp-card asl-qp-card-users">
                        <div class="asl-qp-card-header">
                            <div class="asl-qp-card-title">
                                <span class="dashicons dashicons-admin-users"></span>
                                <?php _e('3. Chọn Nhân sự áp dụng', 'qlcv'); ?>
                                <span class="asl-qp-badge" id="selected_users_count">0 <?php _e('đã chọn', 'qlcv'); ?></span>
                            </div>
                        </div>

                        <div class="asl-qp-filters">
                            <div class="asl-qp-filter-row">
                                <div class="asl-qp-search-box">
                                    <span class="dashicons dashicons-search"></span>
                                    <input type="text" id="user_search_input" placeholder="<?php _e('Tìm tên, email...', 'qlcv'); ?>">
                                </div>
                                <select id="user_role_filter" class="asl-qp-select">
                                    <option value=""><?php _e('-- Tất cả vai trò --', 'qlcv'); ?></option>
                                    <?php foreach ($all_roles as $role_key => $role_name): ?>
                                        <option value="<?php echo esc_attr($role_key); ?>"><?php echo esc_html($role_name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="asl-qp-action-links">
                                <button type="button" class="button button-small" id="select_all_users"><?php _e('Chọn tất cả', 'qlcv'); ?></button>
                                <button type="button" class="button button-small" id="deselect_all_users"><?php _e('Bỏ chọn tất cả', 'qlcv'); ?></button>
                                <span class="asl-qp-total-text" id="visible_users_count"><?php echo sprintf(__('Hiển thị: %d nhân sự', 'qlcv'), count($all_users)); ?></span>
                            </div>
                        </div>

                        <div class="asl-qp-user-list custom-scroll" id="asl_user_list_container">
                            <?php foreach ($all_users as $u):
                                $u_roles = !empty($u->roles) ? implode(',', $u->roles) : '';
                                $u_terms = get_field('nhom_cong_viec', 'user_' . $u->ID);
                                if (!is_array($u_terms)) {
                                    $u_terms = array();
                                }
                                $u_term_count = count($u_terms);
                                
                                $term_preview_names = array();
                                foreach ($u_terms as $tid) {
                                    if (isset($term_names_map[$tid])) {
                                        $term_preview_names[] = $term_names_map[$tid];
                                    }
                                }
                                $term_preview_str = !empty($term_preview_names) ? implode(', ', array_slice($term_preview_names, 0, 4)) . ($u_term_count > 4 ? '...' : '') : __('Chưa có quyền nhóm', 'qlcv');
                            ?>
                                <div class="asl-qp-user-item" data-name="<?php echo esc_attr(mb_strtolower($u->display_name . ' ' . $u->user_login, 'UTF-8')); ?>" data-email="<?php echo esc_attr(strtolower($u->user_email)); ?>" data-roles="<?php echo esc_attr($u_roles); ?>">
                                    <label class="asl-qp-user-label">
                                        <input type="checkbox" name="selected_users[]" value="<?php echo esc_attr($u->ID); ?>" class="asl-qp-user-checkbox">
                                        <span class="asl-qp-user-avatar">
                                            <?php echo get_avatar($u->ID, 36); ?>
                                        </span>
                                        <div class="asl-qp-user-info">
                                            <div class="asl-qp-user-name">
                                                <strong><?php echo esc_html($u->display_name); ?></strong>
                                                <span class="asl-qp-role-tag"><?php echo esc_html(!empty($u->roles[0]) ? $u->roles[0] : ''); ?></span>
                                            </div>
                                            <div class="asl-qp-user-meta">
                                                <span class="asl-qp-email"><?php echo esc_html($u->user_email); ?></span>
                                                <span class="asl-qp-sep">•</span>
                                                <span class="asl-qp-current-perms" title="<?php echo esc_attr(implode(', ', $term_preview_names)); ?>">
                                                    <span class="dashicons dashicons-category" style="font-size: 13px; width: 13px; height: 13px; vertical-align: middle;"></span>
                                                    <?php echo esc_html($u_term_count); ?> <?php _e('nhóm', 'qlcv'); ?>: <em><?php echo esc_html($term_preview_str); ?></em>
                                                </span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- NÚT LƯU PHÂN QUYỀN (NẰM DƯỚI MỤC SỐ 3) -->
                        <div class="asl-qp-footer-actions">
                            <button type="submit" name="asl_quick_permissions_submit" id="btn_submit_quick_perms" class="button button-primary button-hero">
                                <span class="dashicons dashicons-saved"></span>
                                <?php _e('Lưu phân quyền', 'qlcv'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- STYLES -->
    <style>
        .asl-quick-permissions-wrap {
            margin-top: 20px;
            max-width: 1400px;
        }
        .asl-qp-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 15px;
            align-items: stretch;
        }
        @media (max-width: 1024px) {
            .asl-qp-layout {
                grid-template-columns: 1fr;
            }
        }
        .asl-qp-col-left, .asl-qp-col-right {
            display: flex;
            flex-direction: column;
            min-height: 0;
        }
        .asl-qp-col-left {
            gap: 20px;
        }
        .asl-qp-card {
            background: #fff;
            border: 1px solid #c3c4c7;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            display: flex;
            flex-direction: column;
        }
        .asl-qp-card-users {
            height: 100%;
            min-height: 0;
            display: flex;
            flex-direction: column;
        }
        .asl-qp-card-header {
            padding: 14px 18px;
            border-bottom: 1px solid #e0e0e0;
            background: #f8f9fa;
            border-radius: 6px 6px 0 0;
        }
        .asl-qp-card-title {
            font-size: 15px;
            font-weight: 600;
            color: #1d2327;
            display: flex;
            align-items: center;
        }
        .asl-qp-card-title .dashicons {
            margin-right: 8px;
            color: #2271b1;
            font-size: 20px;
        }
        .asl-qp-badge {
            margin-left: auto;
            background: #2271b1;
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 9px;
            border-radius: 12px;
        }
        .asl-qp-filters {
            padding: 12px 16px;
            border-bottom: 1px solid #f0f0f1;
            background: #fafafa;
        }
        .asl-qp-filter-row {
            display: flex;
            gap: 10px;
            margin-bottom: 8px;
        }
        .asl-qp-search-box {
            position: relative;
            flex: 1;
        }
        .asl-qp-search-box .dashicons {
            position: absolute;
            left: 8px;
            top: 50%;
            transform: translateY(-50%);
            color: #8c8f94;
            font-size: 17px;
        }
        .asl-qp-search-box input {
            width: 100%;
            padding-left: 30px !important;
            height: 34px !important;
            border-radius: 4px;
        }
        .asl-qp-select {
            height: 34px !important;
            border-radius: 4px;
            max-width: 170px;
        }
        .asl-qp-action-links {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
        }
        .asl-qp-total-text {
            margin-left: auto;
            color: #646970;
            font-weight: 500;
        }
        .asl-qp-user-item {
            padding: 8px 10px;
            border-bottom: 1px solid #f0f0f1;
            border-radius: 4px;
            transition: background 0.15s ease;
        }
        .asl-qp-user-item:hover {
            background: #f0f6fc;
        }
        .asl-qp-user-label {
            display: flex;
            align-items: center;
            cursor: pointer;
            width: 100%;
        }
        .asl-qp-user-checkbox, .asl-qp-term-checkbox {
            margin-right: 10px !important;
            flex-shrink: 0;
        }
        .asl-qp-user-avatar {
            margin-right: 12px;
            flex-shrink: 0;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
        }
        .asl-qp-user-avatar img {
            border-radius: 50%;
        }
        .asl-qp-user-info {
            flex: 1;
            min-width: 0;
        }
        .asl-qp-user-name {
            font-size: 13px;
            color: #1d2327;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .asl-qp-role-tag {
            font-size: 10px;
            background: #e5e5e5;
            color: #444;
            padding: 1px 6px;
            border-radius: 3px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .asl-qp-user-meta {
            font-size: 12px;
            color: #646970;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .asl-qp-sep {
            margin: 0 4px;
        }
        .asl-qp-current-perms em {
            color: #2271b1;
            font-style: normal;
        }

        /* Chế độ thực hiện */
        .asl-qp-card-mode {
            margin-bottom: 0;
        }
        .asl-qp-card-body {
            padding: 14px 16px;
        }
        .asl-qp-mode-options {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .asl-qp-mode-label {
            display: flex;
            align-items: flex-start;
            padding: 10px 14px;
            border: 1px solid #dcdcde;
            border-radius: 5px;
            cursor: pointer;
            background: #fff;
            transition: all 0.2s ease;
        }
        .asl-qp-mode-label:hover {
            border-color: #2271b1;
            background: #f8fafc;
        }
        .asl-qp-mode-label.is-selected {
            border-color: #2271b1;
            background: #f0f6fc;
            box-shadow: 0 0 0 1px #2271b1;
        }
        .asl-qp-mode-label input[type="radio"] {
            margin-top: 3px !important;
            margin-right: 10px !important;
        }
        .asl-qp-mode-info {
            flex: 1;
        }
        .asl-qp-mode-title {
            font-size: 13px;
            color: #1d2327;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .asl-qp-mode-desc {
            font-size: 12px;
            color: #646970;
            margin-top: 3px;
            line-height: 1.4;
        }
        .asl-qp-recommend-badge {
            background: #2e7d32;
            color: #fff;
            font-size: 10px;
            font-weight: 600;
            padding: 1px 6px;
            border-radius: 10px;
            margin-left: 6px;
        }
        .asl-qp-new-badge {
            background: #d32f2f;
            color: #fff;
            font-size: 10px;
            font-weight: 600;
            padding: 1px 6px;
            border-radius: 10px;
            margin-left: 6px;
        }

        /* Tree Taxonomy */
        .asl-qp-term-tree {
            max-height: 400px;
            overflow-y: auto;
            padding: 8px 12px;
        }
        .asl-qp-user-list {
            max-height: 520px;
            overflow-y: auto;
            padding: 8px 12px;
        }
        .asl-qp-term-item {
            padding: 6px 8px;
            border-radius: 4px;
            transition: background 0.15s;
        }
        .asl-qp-term-item:hover {
            background: #f0f6fc;
        }
        .asl-qp-term-item.is-highlight {
            background: #fff8e1;
            border-left: 3px solid #ffb300;
        }
        .asl-qp-term-label {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        .asl-qp-term-name {
            font-size: 13px;
            color: #1d2327;
        }
        .asl-qp-slug-code {
            font-size: 11px;
            background: #f0f0f1;
            padding: 1px 5px;
            border-radius: 3px;
            color: #50575e;
            margin-left: 6px;
        }
        .asl-qp-term-children {
            margin-left: 26px;
            border-left: 2px solid #e0e0e0;
            padding-left: 8px;
            margin-top: 4px;
            margin-bottom: 4px;
        }
        .asl-qp-footer-actions {
            padding: 12px 18px;
            background: #fafafa;
            border-top: 1px solid #e0e0e0;
            border-radius: 0 0 6px 6px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }
        .asl-qp-footer-actions .button-hero {
            font-size: 14px !important;
            height: 38px !important;
            line-height: 36px !important;
            padding: 0 18px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px;
            box-shadow: 0 2px 4px rgba(34, 113, 177, 0.2);
            border-radius: 4px !important;
        }
        .asl-qp-footer-actions .button-hero .dashicons {
            margin: 0 !important;
            font-size: 18px !important;
            width: 18px !important;
            height: 18px !important;
            line-height: 18px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
    </style>

    <!-- JAVASCRIPT -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var userSearchInput = document.getElementById('user_search_input');
            var userRoleFilter = document.getElementById('user_role_filter');
            var userItems = document.querySelectorAll('.asl-qp-user-item');
            var selectAllUsersBtn = document.getElementById('select_all_users');
            var deselectAllUsersBtn = document.getElementById('deselect_all_users');
            var selectedUsersCountBadge = document.getElementById('selected_users_count');
            var visibleUsersCountText = document.getElementById('visible_users_count');

            var termSearchInput = document.getElementById('term_search_input');
            var termItems = document.querySelectorAll('.asl-qp-term-item');
            var selectAllTermsBtn = document.getElementById('select_all_terms');
            var deselectAllTermsBtn = document.getElementById('deselect_all_terms');
            var selectedTermsCountBadge = document.getElementById('selected_terms_count');
            var visibleTermsCountText = document.getElementById('visible_terms_count');

            var modeRadios = document.querySelectorAll('input[name="action_mode"]');
            var modeLabels = document.querySelectorAll('.asl-qp-mode-label');
            var form = document.getElementById('asl_quick_permissions_form');

            // 1. Cập nhật số lượng user đã chọn
            function updateSelectedUsersCount() {
                var checked = document.querySelectorAll('.asl-qp-user-checkbox:checked').length;
                selectedUsersCountBadge.textContent = checked + ' <?php _e('đã chọn', 'qlcv'); ?>';
            }

            // 2. Cập nhật số lượng term đã chọn
            function updateSelectedTermsCount() {
                var checked = document.querySelectorAll('.asl-qp-term-checkbox:checked').length;
                selectedTermsCountBadge.textContent = checked + ' <?php _e('đã chọn', 'qlcv'); ?>';
            }

            // 3. Lọc danh sách nhân sự
            function filterUsers() {
                var query = userSearchInput.value.trim().toLowerCase();
                var role = userRoleFilter.value.toLowerCase();
                var visibleCount = 0;

                userItems.forEach(function(item) {
                    var name = item.getAttribute('data-name') || '';
                    var email = item.getAttribute('data-email') || '';
                    var roles = (item.getAttribute('data-roles') || '').split(',');

                    var matchQuery = !query || name.indexOf(query) !== -1 || email.indexOf(query) !== -1;
                    var matchRole = !role || roles.indexOf(role) !== -1;

                    if (matchQuery && matchRole) {
                        item.style.display = '';
                        visibleCount++;
                    } else {
                        item.style.display = 'none';
                    }
                });

                visibleUsersCountText.textContent = '<?php _e('Hiển thị:', 'qlcv'); ?> ' + visibleCount + ' <?php _e('nhân sự', 'qlcv'); ?>';
            }

            userSearchInput.addEventListener('input', filterUsers);
            userRoleFilter.addEventListener('change', filterUsers);

            // 4. Chọn tất cả / Bỏ chọn nhân sự
            selectAllUsersBtn.addEventListener('click', function() {
                userItems.forEach(function(item) {
                    if (item.style.display !== 'none') {
                        var cb = item.querySelector('.asl-qp-user-checkbox');
                        if (cb) cb.checked = true;
                    }
                });
                updateSelectedUsersCount();
            });

            deselectAllUsersBtn.addEventListener('click', function() {
                userItems.forEach(function(item) {
                    var cb = item.querySelector('.asl-qp-user-checkbox');
                    if (cb) cb.checked = false;
                });
                updateSelectedUsersCount();
            });

            document.querySelectorAll('.asl-qp-user-checkbox').forEach(function(cb) {
                cb.addEventListener('change', updateSelectedUsersCount);
            });

            // 5. Lọc danh sách Chuyên mục (Taxonomy)
            function filterTerms() {
                var query = termSearchInput.value.trim().toLowerCase();
                var visibleCount = 0;

                termItems.forEach(function(item) {
                    var name = item.getAttribute('data-name') || '';
                    var slug = item.getAttribute('data-slug') || '';

                    var match = !query || name.indexOf(query) !== -1 || slug.indexOf(query) !== -1;
                    if (match) {
                        item.style.display = '';
                        visibleCount++;
                        // Nếu là con khớp, hiển thị cả cha
                        var parentContainer = item.closest('.asl-qp-term-children');
                        if (parentContainer) {
                            var parentItem = parentContainer.closest('.asl-qp-term-item');
                            if (parentItem) parentItem.style.display = '';
                        }
                    } else {
                        // Nếu item này có con đang hiển thị thì không ẩn
                        var hasVisibleChild = false;
                        var children = item.querySelectorAll('.asl-qp-term-item');
                        children.forEach(function(child) {
                            var cName = child.getAttribute('data-name') || '';
                            var cSlug = child.getAttribute('data-slug') || '';
                            if (cName.indexOf(query) !== -1 || cSlug.indexOf(query) !== -1) {
                                hasVisibleChild = true;
                            }
                        });

                        if (hasVisibleChild) {
                            item.style.display = '';
                        } else {
                            item.style.display = 'none';
                        }
                    }
                });

                visibleTermsCountText.textContent = '<?php _e('Hiển thị:', 'qlcv'); ?> ' + visibleCount + ' <?php _e('chuyên mục', 'qlcv'); ?>';
            }

            termSearchInput.addEventListener('input', filterTerms);

            // 6. Chọn tất cả / Bỏ chọn Chuyên mục
            selectAllTermsBtn.addEventListener('click', function() {
                termItems.forEach(function(item) {
                    if (item.style.display !== 'none') {
                        var cb = item.querySelector('.asl-qp-term-checkbox');
                        if (cb) cb.checked = true;
                    }
                });
                updateSelectedTermsCount();
            });

            deselectAllTermsBtn.addEventListener('click', function() {
                termItems.forEach(function(item) {
                    var cb = item.querySelector('.asl-qp-term-checkbox');
                    if (cb) cb.checked = false;
                });
                updateSelectedTermsCount();
            });

            document.querySelectorAll('.asl-qp-term-checkbox').forEach(function(cb) {
                cb.addEventListener('change', updateSelectedTermsCount);
            });

            // 7. Chuyển đổi style chế độ thực hiện
            modeRadios.forEach(function(radio) {
                radio.addEventListener('change', function() {
                    modeLabels.forEach(function(l) { l.classList.remove('is-selected'); });
                    if (this.checked) {
                        this.closest('.asl-qp-mode-label').classList.add('is-selected');
                    }
                });
            });

            // 8. Xác nhận trước khi Submit
            form.addEventListener('submit', function(e) {
                var userCount = document.querySelectorAll('.asl-qp-user-checkbox:checked').length;
                var termCount = document.querySelectorAll('.asl-qp-term-checkbox:checked').length;
                var mode = document.querySelector('input[name="action_mode"]:checked').value;

                if (userCount === 0) {
                    alert('<?php _e('Vui lòng chọn ít nhất một nhân sự!', 'qlcv'); ?>');
                    e.preventDefault();
                    return false;
                }

                if (termCount === 0 && mode !== 'sync') {
                    alert('<?php _e('Vui lòng chọn ít nhất một chuyên mục!', 'qlcv'); ?>');
                    e.preventDefault();
                    return false;
                }

                var modeMsg = '';
                if (mode === 'add') {
                    modeMsg = 'THÊM BỔ SUNG ' + termCount + ' chuyên mục (GIỮ NGUYÊN quyền cũ) cho ' + userCount + ' nhân sự';
                } else if (mode === 'sync') {
                    modeMsg = 'ĐỒNG BỘ / GHI ĐÈ đúng ' + termCount + ' chuyên mục cho ' + userCount + ' nhân sự';
                } else if (mode === 'remove') {
                    modeMsg = 'GỠ BỎ ' + termCount + ' chuyên mục khỏi ' + userCount + ' nhân sự';
                }

                if (!confirm('Bạn có chắc chắn muốn thực hiện:\n\n' + modeMsg + ' ?')) {
                    e.preventDefault();
                    return false;
                }
            });

            // 9. Tự động đồng bộ chiều cao box Nhân sự bằng đúng tổng 2 box bên cạnh
            function syncBoxHeights() {
                var userList = document.getElementById('asl_user_list_container');
                if (!userList) return;

                if (window.innerWidth <= 1024) {
                    userList.style.height = '';
                    userList.style.maxHeight = '520px';
                    return;
                }

                var colLeft = document.querySelector('.asl-qp-col-left');
                var cardUsers = document.querySelector('.asl-qp-card-users');
                if (!colLeft || !cardUsers) return;

                var leftHeight = colLeft.offsetHeight;
                var cardHeader = cardUsers.querySelector('.asl-qp-card-header');
                var filters = cardUsers.querySelector('.asl-qp-filters');
                var footer = cardUsers.querySelector('.asl-qp-footer-actions');

                var otherHeight = (cardHeader ? cardHeader.offsetHeight : 0) +
                                  (filters ? filters.offsetHeight : 0) +
                                  (footer ? footer.offsetHeight : 0);

                var targetListHeight = leftHeight - otherHeight - 2;
                if (targetListHeight > 200) {
                    userList.style.height = targetListHeight + 'px';
                    userList.style.maxHeight = targetListHeight + 'px';
                }
            }

            syncBoxHeights();
            window.addEventListener('resize', syncBoxHeights);
            setTimeout(syncBoxHeights, 150);
        });
    </script>
    <?php
}
