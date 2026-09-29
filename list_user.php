<?php
/*
    Template Name: Danh sách nhân sự (user)
*/
if (isset($_GET['role']) && ($_GET['role'] != '')) {
    $role = sanitize_text_field($_GET['role']);
} else {
    $role = '';
}

// Xử lý xuất CSV (UTF-8 with BOM) cho Admin (phải chạy trước khi get_header() gửi bất kỳ output nào)
if ((isset($_GET['export_csv']) && $_GET['export_csv'] == '1') || (isset($_GET['export_excel']) && $_GET['export_excel'] == '1')) {
    $current_user = wp_get_current_user();
    if (!is_user_logged_in() || (!current_user_can('manage_options') && !in_array('administrator', (array)$current_user->roles))) {
        wp_die(__('Bạn không có quyền xuất dữ liệu này.', 'qlcv'));
    }

    $args = array(
        'role'   => $role,
        'number' => 999999,
    );
    $query = new WP_User_Query($args);
    $users = $query->get_results();

    global $wp_roles;

    $is_partner_view = ($role == 'partner' || $role == 'foreign_partner');

    if ($is_partner_view) {
        $headers = array(
            'STT',
            'Ngày tạo',
            'Mã đối tác',
            'Tên người liên hệ',
            'Tên công ty/tổ chức',
            'Loại đối tác',
            'Số điện thoại',
            'Email',
            'Địa chỉ',
            'Quốc gia',
            'Vai trò'
        );
        $filename_prefix = ($role == 'foreign_partner') ? 'danh_sach_doi_tac_nhan_viec' : 'danh_sach_doi_tac';
    } elseif (!empty($role)) {
        $headers = array(
            'STT',
            'Ngày tạo',
            'Tên nhân sự',
            'Tên đăng nhập',
            'Số điện thoại',
            'Email',
            'Chi nhánh',
            'Nhóm công việc',
            'Địa chỉ',
            'Quốc gia',
            'Vai trò'
        );
        $filename_prefix = 'danh_sach_' . $role;
    } else {
        $headers = array(
            'STT',
            'Ngày tạo',
            'Họ và tên',
            'Tên đăng nhập',
            'Mã đối tác',
            'Tên công ty/tổ chức',
            'Số điện thoại',
            'Email',
            'Chi nhánh',
            'Nhóm công việc',
            'Địa chỉ',
            'Quốc gia',
            'Vai trò'
        );
        $filename_prefix = 'danh_sach_tat_ca_nhan_su';
    }

    $filename = $filename_prefix . '_' . date('Ymd_His') . '.csv';

    // Xóa toàn bộ output buffer trước khi gửi headers
    while (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

    $output = fopen('php://output', 'w');

    // Ghi UTF-8 BOM để Excel / các trình đọc CSV nhận diện chuẩn UTF-8 tiếng Việt
    fwrite($output, "\xEF\xBB\xBF");

    // Ghi dòng tiêu đề
    fputcsv($output, $headers);

    $stt = 1;
    if (!empty($users)) {
        foreach ($users as $user) {
            $so_dien_thoai  = get_field('so_dien_thoai', 'user_' . $user->ID);
            $partner_code   = get_field('partner_code', 'user_' . $user->ID);
            $ten_cong_ty    = get_field('ten_cong_ty', 'user_' . $user->ID);
            $is_company     = get_field('is_company', 'user_' . $user->ID);
            $quoc_gia       = get_field('quoc_gia', 'user_' . $user->ID);
            $dia_chi        = get_field('dia_chi', 'user_' . $user->ID);
            $chi_nhanh      = get_field('chi_nhanh', 'user_' . $user->ID);
            $nhom_cong_viec = get_field('nhom_cong_viec', 'user_' . $user->ID);
            $registered     = $user->user_registered ? date('d/m/Y', strtotime($user->user_registered)) : '';

            // Lấy tên chi nhánh
            $branch_names = array();
            if (!empty($chi_nhanh) && is_array($chi_nhanh)) {
                foreach ($chi_nhanh as $id_chi_nhanh) {
                    $term = get_term($id_chi_nhanh);
                    if ($term && !is_wp_error($term)) {
                        $branch_names[] = $term->name;
                    }
                }
            }
            $branch_str = implode(', ', $branch_names);

            // Lấy tên nhóm công việc
            $work_group_names = array();
            if (!empty($nhom_cong_viec) && is_array($nhom_cong_viec)) {
                foreach ($nhom_cong_viec as $id_cong_viec) {
                    $term = get_term($id_cong_viec);
                    if ($term && !is_wp_error($term)) {
                        $work_group_names[] = $term->name;
                    }
                }
            }
            $work_group_str = implode(', ', $work_group_names);

            // Vai trò
            $role_names = array();
            if (!empty($user->roles) && is_array($user->roles)) {
                foreach ($user->roles as $user_role) {
                    if (isset($wp_roles->roles[$user_role]['name'])) {
                        $role_names[] = translate_user_role($wp_roles->roles[$user_role]['name']);
                    } else {
                        $role_names[] = $user_role;
                    }
                }
            }
            $roles_str = implode(', ', $role_names);

            if ($is_partner_view) {
                $partner_type_str = $is_company ? __('Doanh nghiệp', 'qlcv') : __('Cá nhân', 'qlcv');
                $row = array(
                    $stt,
                    $registered,
                    (string)$partner_code,
                    (string)$user->display_name,
                    (string)$ten_cong_ty,
                    $partner_type_str,
                    (string)$so_dien_thoai,
                    (string)$user->user_email,
                    (string)$dia_chi,
                    (string)$quoc_gia,
                    $roles_str
                );
            } elseif (!empty($role)) {
                $row = array(
                    $stt,
                    $registered,
                    (string)$user->display_name,
                    (string)$user->user_login,
                    (string)$so_dien_thoai,
                    (string)$user->user_email,
                    $branch_str,
                    $work_group_str,
                    (string)$dia_chi,
                    (string)$quoc_gia,
                    $roles_str
                );
            } else {
                $row = array(
                    $stt,
                    $registered,
                    (string)$user->display_name,
                    (string)$user->user_login,
                    (string)$partner_code,
                    (string)$ten_cong_ty,
                    (string)$so_dien_thoai,
                    (string)$user->user_email,
                    $branch_str,
                    $work_group_str,
                    (string)$dia_chi,
                    (string)$quoc_gia,
                    $roles_str
                );
            }

            fputcsv($output, $row);
            $stt++;
        }
    }

    fclose($output);
    exit;
}

get_header();

get_sidebar();

// Store original role for pagination to avoid variable override in loops
$original_role = $role;

// Handle member export
if (isset($_GET['export_member']) && !empty($_GET['export_member'])) {
    $user_id = intval($_GET['export_member']);
    $export_result = export_single_member_to_table($user_id);
    
    if ($export_result) {
        echo '<div class="alert alert-success" role="alert">';
        echo '<i class="fa fa-check"></i> ' . __('Đã export thành công user vào bảng wp_aslmember', 'qlcv');
        echo '</div>';
    } else {
        echo '<div class="alert alert-danger" role="alert">';
        echo '<i class="fa fa-exclamation-triangle"></i> ' . __('Có lỗi xảy ra khi export user (kiểm tra vai trò nhân sự)', 'qlcv');
        echo '</div>';
    }
}

$current_user = wp_get_current_user();
?>

<!-- Content Body Start -->
<div class="content-body">

    <!-- Page Headings Start -->
    <div class="row justify-content-between align-items-center mb-10">

        <!-- Page Heading Start -->
        <div class="col-12 col-lg-12 mb-20">
            <!--Basic Start-->
            <div class="col-12 mb-30">

                <?php
                    // pagination
                    $paged = (get_query_var('paged')) ? absint(get_query_var('paged')) : 1;
                    $count_args  = array(
                        'role'      => $role,
                        'number'    => 999999,
                    );
                    $user_count_query = new WP_User_Query($count_args);
                    $user_count = $user_count_query->get_results();

                    // count the number of users found in the query
                    $total_users = $user_count ? count($user_count) : 1;

                    // how many users to show per page
                    $users_per_page = 20;

                    // calculate the total number of pages.
                    $total_pages = 1;
                    $offset = $users_per_page * ($paged - 1);
                    $total_pages = ceil($total_users / $users_per_page);


                    $args   = array(
                        'role'      => $role, /*partner, member, subscriber, contributor, author*/
                        'number'    => $users_per_page,
                        'paged'     => $paged,
                        'offset'    => $offset,
                    );
                    $query = new WP_User_Query($args);
                    $users = $query->get_results();

                    $total_user_args = $args;
                    $total_user_args['number'] = 99999;
                    $total_user_args['paged'] = 1;
                    $total_user_args['offset'] = 0;
                    $total_user_query = new WP_User_Query($total_user_args);
                ?>
                <div class="row justify-content-between">
                    <div class="col-lg-auto mb-10">
                        <?php 
                            switch ($role) {
                                case 'partner':
                                    $role_name = __('đối tác', 'qlcv');
                                    $_create_link = '/them-doi-tac-moi/';
                                    break;
                                    
                                case 'foreign_partner':
                                    $role_name = __('đối tác nhận việc', 'qlcv');
                                    $_create_link = '/them-doi-tac-moi/';
                                    break;
                                    
                                case 'contributor':
                                    $role_name = __('quản lý', 'qlcv');
                                    $_create_link = '/them-nhan-su-moi/';
                                    break;

                                default:
                                    $role_name = __('nhân sự', 'qlcv');
                                    $_create_link = '/them-nhan-su-moi/';
                                    break;
                            }
                        ?>
                        <p><?php _e('Có tổng cộng', 'qlcv'); ?> <?php echo sizeof($total_user_query->get_results()) . " " . $role_name; ?> <?php _e('tìm thấy', 'qlcv'); ?></p>
                        <h2><?php
                            echo __('Danh sách', 'qlcv') . ' ' . $role_name;
                        ?></h2>
                    </div>
                    <div class="col-lg-auto mb-10 right_button">
                        <?php 
                        $is_admin = current_user_can('manage_options') || in_array('administrator', (array)$current_user->roles);
                        if ($is_admin) : 
                            $csv_export_url = add_query_arg('export_csv', '1');
                            $csv_export_url = remove_query_arg('paged', $csv_export_url);
                        ?>
                            <a href="<?php echo esc_url($csv_export_url); ?>" class="button button-success" style="margin-right: 10px;" title="<?php _e('Xuất danh sách ra file CSV (UTF-8 with BOM)', 'qlcv'); ?>">
                                <span><i class="fa fa-file-excel-o"></i><?php _e('Xuất CSV', 'qlcv'); ?></span>
                            </a>
                        <?php endif; ?>
                        <a href="<?php echo get_bloginfo('url') . $_create_link; ?>" class="button button-primary"><span><i class="fa fa-plus"></i><?php _e('Tạo mới', 'qlcv'); ?></span></a>
                    </div>
                    <div class="col-12 box mb-20">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <?php
                                    if (($role == 'partner') || ($role == 'foreign_partner')) {
                                        echo "<th>" . __("Mã đối tác", 'qlcv') . "</th>";
                                        echo "<th>" . __("Tên người liên hệ", 'qlcv') . "</th>";
                                        echo "<th>" . __("Tên công ty/tổ chức", 'qlcv') . "</th>";
                                    } else {
                                        echo "<th>" . __("Tên nhân sự", 'qlcv') . "</th>";
                                    }

                                    echo "<th>" . __("Số điện thoại", 'qlcv') . "</th>
                                                <th>Email</th>";
                                    echo "<th>" . __("Quốc gia", 'qlcv') . "</th>";

                                    if (in_array('contributor', $current_user->roles)) {
                                        echo "<th>" . __("Sửa", 'qlcv') . "</th>";
                                        echo "<th>" . __("Thao tác", 'qlcv') . "</th>";
                                    }
                                    ?>

                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = $offset;
                                if (!empty($users)) {
                                    foreach ($users as $user) {
                                        $i++;
                                        $roles = array();
                                        $so_dien_thoai  = get_field('so_dien_thoai', 'user_' . $user->ID);
                                        $partner_code   = get_field('partner_code', 'user_' . $user->ID);
                                        $ten_cong_ty    = get_field('ten_cong_ty', 'user_' . $user->ID);
                                        $is_company     = get_field('is_company' , 'user_' . $user->ID);
                                        $quoc_gia       = get_field('quoc_gia' , 'user_' . $user->ID);
                                        $author_link    = get_author_posts_url($user->ID);

                                        echo "<tr>";
                                        echo "<td>" . $i . "</td>";

                                        if (($role == 'partner') || ($role == 'foreign_partner')) {
                                            $icon_company = $is_company?"<i class='fa fa-building'></i>":"";
                                            echo "<td>" . $partner_code . "</td>";
                                            echo "<td><a href='" . $author_link . "'>" . $user->display_name . "</a></td>";
                                            echo "<td><a href='" . $author_link . "'>" . $icon_company . " " . $ten_cong_ty . "</a></td>";
                                        } else {
                                            echo "<td><a href='" . $author_link . "'>" . $user->display_name . "</a></td>";
                                        }

                                        if ($so_dien_thoai) {
                                            echo "<td>" . $so_dien_thoai . "</td>";
                                        } else echo "<td>" . __("Chưa có", 'qlcv') . "</td>";

                                        echo "<td>" . $user->user_email . "</td>";
                                        echo "<td>" . $quoc_gia . "</td>";

                                        # display user role name
                                        if (!empty($user->roles) && is_array($user->roles)) {
                                            foreach ($user->roles as $user_role)
                                                $roles[] = translate_user_role($wp_roles->roles[$user_role]['name']);
                                        }

                                        if (in_array('contributor', $current_user->roles)) {
                                            echo '<td><a href="' . get_bloginfo('url') . '/sua-thong-tin-doi-tac/?uid=' . $user->ID . '"><i class="fa fa-edit"></i></a></td>';
                                            echo '<td>';
                                            // Post to renewal system button (for all roles)
                                            echo '<a href="' . get_bloginfo('url') . '/post-to-renewal-system/?uid=' . $user->ID . '"><i class="fa fa-telegram"></i></a>';
                                            // Export button for member, contributor, law_manager, ip_manager, administrator roles
                                            $is_member_user = !empty($user->roles) && !empty(array_intersect($user->roles, ['member', 'contributor', 'law_manager', 'ip_manager', 'administrator']));
                                            if ($is_member_user) {
                                                echo '<span style="margin-left: 20px;"></span>';
                                                $export_url = add_query_arg('export_member', $user->ID);
                                                echo '<a href="' . esc_url($export_url) . '" title="' . __('Export to member table', 'qlcv') . '" onclick="return confirm(\'' . __('Bạn có chắc muốn export user này vào bảng wp_aslmember?', 'qlcv') . '\')"><i class="fa fa-share text-success"></i></a>';
                                            }
                                            echo '</td>';
                                        }
                                        echo "</tr>";
                                    }
                                } else {
                                    $colspan = 6;
                                    if (in_array('contributor', $current_user->roles)) {
                                        $colspan += 2; // Edit column + Actions column
                                    }
                                    echo "<tr><td colspan='{$colspan}' class='text-center'>" . __("Không có dữ liệu.", 'qlcv') . "</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-12">
                        <div class="pagination justify-content-center">
                            <?php
                            $big = 999999999; // need an unlikely integer
                            
                            // Get current URL for Polylang compatibility
                            $current_url = get_pagenum_link(1);
                            $current_url = remove_query_arg('paged', $current_url);
                            
                            // Add role parameter if exists
                            if (!empty($original_role)) {
                                $current_url = add_query_arg('role', $original_role, $current_url);
                            }

                            echo paginate_links(array(
                                'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                                'format'    => '?paged=%#%',
                                'current'   => max(1, get_query_var('paged')),
                                'total'     => $total_pages,
                                'type'      => 'list',
                                'prev_text' => __('« Trang trước', 'qlcv'),
                                'next_text' => __('Trang sau »', 'qlcv'),
                                'add_args'  => array('role' => $original_role), // Preserve role parameter
                            ));
                            ?>
                        </div>
                    </div>
                </div>

            </div>
            <!--Basic End-->


        </div><!-- Page Heading End -->

    </div><!-- Page Headings End -->

</div><!-- Content Body End -->

<style>
.alert {
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: 4px;
    position: relative;
}

.alert-success {
    color: #3c763d;
    background-color: #dff0d8;
    border-color: #d6e9c6;
}

.alert-danger {
    color: #a94442;
    background-color: #f2dede;
    border-color: #ebccd1;
}

.text-success {
    color: #28a745 !important;
}
</style>

<?php
get_footer();
?>