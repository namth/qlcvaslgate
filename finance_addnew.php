<?php
/*
    Template Name: Thêm mới thu chi
*/
$history_link   = $_SERVER['HTTP_REFERER'];

if (
    isset($_POST['post_nonce_field']) &&
    wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')
) {

    $current_user = wp_get_current_user();
    $current_time = current_time('timestamp', 7);

    # Lấy dữ liệu từ form
    $fi_tmp_date        = $_POST['finance_date'];
    $finance_user       = $_POST['finance_user'];
    $finance_currency   = $_POST['finance_currency'];
    $finance_value      = $_POST['finance_value'];
    $finance_title      = $_POST['finance_title'];
    $finance_content    = $_POST['finance_content'];
    $finance_type       = $_POST['finance_type'];
    $finance_job        = $_POST['finance_job'];
    $currency           = get_field('currency', $finance_job);
    $currency_out       = get_field('currency_out', $finance_job);
    $history_link       = $_POST['history_link'];

    if ($fi_tmp_date && $finance_user && $finance_currency && $finance_value && $finance_type) {

        $fi_tmp_date   = explode('/', $fi_tmp_date);
        $temp_date      = array_reverse($fi_tmp_date);
        $finance_date   = implode('', $temp_date);
        $error = false;
    } else {
        $error = true;
    }

    # nếu $currency được sử dụng không đúng với currency của job thì sẽ báo lỗi
    if ((($finance_type == "Thu") && ($currency != $finance_currency) && $currency)
        || (($finance_type == "Chi") && ($currency_out != $finance_currency) && $currency_out)
    ) {
        $error = true;
        $error_detail = "không đúng loại tiền";
    }

    if (!$error && isset($finance_title) && ($finance_title != "")) {

        # lấy tiền ở trong ví ra và lưu lại địa chỉ ví
        if ($finance_currency == "USD") {
            $total_wallet = floatval(get_field('total_usd', 'option'));
            $wallet = 'field_60bb2f7cf9156';
        } else {
            $total_wallet = floatval(get_field('total_vnd', 'option'));
            $wallet = 'field_60bb2f98f9157';
        }

        # cập nhật số dư mới
        if ($finance_type == "Thu") {
            # tính tổng trong ví
            $total_value = floatval($total_wallet) + floatval($finance_value);

            # tính toán trong job đó
            $job_paid       = floatval(get_field('paid', $finance_job)) + floatval($finance_value);
            $job_remainning = floatval(get_field('remainning', $finance_job)) - floatval($finance_value);

            # update 
            update_field('field_60a231d395f2e', $job_paid, $finance_job);
            update_field('field_60a231d3961b0', $job_remainning, $finance_job);

            # nếu chưa set currency thì cài đặt luôn
            if (!$currency) {
                update_field('field_60a231d39602e', $finance_currency, $finance_job);
            }
        } else if ($finance_type == "Chi") {
            # tính tổng trong ví
            $total_value = floatval($total_wallet) - floatval($finance_value);

            # tính toán trong job đó
            $job_advance    = floatval(get_field('advance_money', $finance_job)) + floatval($finance_value);
            $job_debt       = floatval(get_field('debt', $finance_job)) - floatval($finance_value);

            # update 
            update_field('field_60afaeb8cfd6a', $job_advance, $finance_job);
            update_field('field_60afaf50cfd6b', $job_debt, $finance_job);

            # nếu chưa set currency thì cài đặt luôn
            if (!$currency_out) {
                update_field('field_60afafbccfd6c', $finance_currency, $finance_job);
            }
        }
        $finance_content .= "<br>Số dư hiện tại là: " . ($total_value) . $finance_currency . ".";

        # tạo phiếu thu / chi 
        $inserted = wp_insert_post(array(
            'post_title'    => $finance_title,
            'post_content'  => $finance_content,
            'post_status'   => 'publish',
            'post_type'     => 'finance',
        ));

        # nếu update thành công thì update history
        if ($inserted) {
            # cập nhật ví
            update_field($wallet, $total_value, 'option');
            # cập nhật phiếu thu chi
            update_field('field_60bb0c67ad592', $finance_date, $inserted); # date
            update_field('field_60bb0c7aad593', $finance_type, $inserted); # type
            update_field('field_60bb0d37ad594', $finance_user, $inserted); # user
            update_field('field_60bb0da4ad595', $finance_job, $inserted); # job
            update_field('field_60bb0e2fad596', $finance_value, $inserted); # value
            update_field('field_60bb0e38ad597', $finance_currency, $inserted); # currency

            # update vào job 

            # đợi 3 giây và chuyển trang
            # sleep(3);
            wp_redirect($history_link);
        }
    }
}
get_header();

get_sidebar();

// Lấy parameters từ GET
$get_jobid = isset($_GET['jobid']) ? intval($_GET['jobid']) : '';
$get_type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';

// Validate type parameter
if ($get_type && !in_array($get_type, ['Thu', 'Chi'])) {
    $get_type = '';
}

if (isset($_GET['jobid'])  && ($_GET['jobid'] != "")) {
    # lấy dữ liệu bài viết
    $jobid      = $_GET['jobid'];
    $currency   = get_field('currency', $jobid);
}
?>

<!-- Content Body Start -->
<div class="content-body">

    <!-- Page Headings Start -->
    <div class="row justify-content-between align-items-center mb-10">

        <!-- Page Heading Start -->
        <div class="col-12 col-lg-12 mb-20">
            <div class="page-heading">
                <h3 class="title"><?php _e('Tạo phiếu thu/chi', 'qlcv'); ?></h3>
            </div>
        </div><!-- Page Heading End -->

    </div><!-- Page Headings End -->
    <form action="#" method="POST">
        <div class="row">
            <div class="col-12 col-lg-12 mb-20">
                <?php
                if (isset($update)) {
                    if ($update) {
                        # nếu thành công thì thông báo thành công, 3 giây sau thì chuyển trang
                        echo '<div class="alert alert-success" role="alert">
                                            <i class="fa fa-check"></i> ' . __('Bài viết đã được cập nhật.', 'qlcv') . '
                                          </div>';
                    } else {
                        echo '<div class="alert alert-danger" role="alert">
                                            <i class="zmdi zmdi-info"></i> ' . __('Xảy ra lỗi, không thể cập nhật.', 'qlcv') . '
                                          </div>';
                    }
                } else {
                    if ($error) {
                        echo __("Có lỗi xảy ra ", 'qlcv') . $error_detail;
                    }

                ?>
                    <div class="row mbn-20">
                        <div class="col-lg-3 form_title lh45"><?php _e('Ngày thu/chi', 'qlcv'); ?> <span class="text-danger">*</span></div>
                        <div class="col-lg-3 col-12 mb-20">
                            <input type="text" class="form-control input-date-single" value="" name="finance_date" data-mask="99/99/9999">
                            <span class="form-help-text">"dd/mm/yyyy"</span>
                        </div>
                        <div class="col-lg-6"></div>

                        <div class="col-lg-3 form_title lh45"><?php _e('Phân loại', 'qlcv'); ?> <span class="text-danger">*</span></div>
                        <div class="col-lg-6 col-12 mb-20">
                            <div class="form-group">
                                <?php if ($get_type): ?>
                                    <!-- Type được cố định từ GET parameter -->
                                    <input type="hidden" name="finance_type" value="<?php echo esc_attr($get_type); ?>">
                                    <div class="form-control-static" style="padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;">
                                        <strong><?php echo esc_html($get_type); ?></strong>
                                        <small class="text-muted">(<?php _e('Được thiết lập tự động', 'qlcv'); ?>)</small>
                                    </div>
                                <?php else: ?>
                                    <!-- Cho phép chọn type -->
                                    <label class="inline lh45">
                                        <input type="radio" name="finance_type" value="Thu" checked><?php _e('Thu', 'qlcv'); ?></label>
                                    <label class="inline lh45">
                                        <input type="radio" name="finance_type" value="Chi"><?php _e('Chi', 'qlcv'); ?></label>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-lg-3"></div>

                        <div class="col-lg-3 form_title lh45"><?php _e('Công việc', 'qlcv'); ?></div>
                        <div class="col-lg-6 col-12 mb-20">
                            <?php if ($get_jobid): ?>
                                <!-- Job được cố định từ GET parameter -->
                                <input type="hidden" name="finance_job" value="<?php echo esc_attr($get_jobid); ?>">
                                <div class="form-control-static" style="padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;">
                                    <strong><?php echo esc_html(get_the_title($get_jobid)); ?></strong>
                                    <?php 
                                    $job_email = get_field('email', $get_jobid);
                                    if ($job_email): ?>
                                        <small class="text-muted"> (<?php echo esc_html($job_email); ?>)</small>
                                    <?php endif; ?>
                                    <br><small class="text-muted">(<?php _e('Được thiết lập tự động', 'qlcv'); ?>)</small>
                                </div>
                            <?php else: ?>
                                <!-- Cho phép chọn job -->
                                <select class="form-control select2-tags mb-20" name="finance_job">
                                    <?php
                                    if ($jobid) {
                                        $email = get_field('email', $jobid);

                                        echo "<option value='" . $jobid . "'>" . get_the_title($jobid);
                                        if ($email) {
                                            echo " (" . $email . ")";
                                        }
                                        echo "</option>";
                                    } else {
                                        echo '<option value="">-- ' . __('Chọn công việc liên quan', 'qlcv') . ' --</option>';
                                    }

                                    $args   = array(
                                        'post_type'     => 'job',
                                    );
                                    $query = new WP_Query($args);

                                    if ($query->have_posts()) {
                                        while ($query->have_posts()) {
                                            $query->the_post();

                                            $cty = get_field('ten_cong_ty');
                                            $email = get_field('email');

                                            echo "<option value='" . get_the_ID() . "'>" . get_the_title();
                                            if ($email) {
                                                echo " (" . $email . ")";
                                            }
                                            echo "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            <?php endif; ?>
                        </div>
                        <div class="col-lg-3"></div>

                        <div class="col-lg-3 form_title lh45"><?php _e('Người nhận phiếu', 'qlcv'); ?> <span class="text-danger">*</span></div>
                        <div class="col-lg-6 col-12 mb-20">
                            <?php if ($get_jobid): ?>
                                <!-- Cho phép chọn giữa các đối tác của job với partner_2 là mặc định -->
                                <select class="form-control select2-tags mb-20" name="finance_user">
                                    <?php 
                                    $job_partner_1 = get_field('partner_1', $get_jobid);
                                    $job_partner_2 = get_field('partner_2', $get_jobid);
                                    $job_foreign_partner = get_field('foreign_partner', $get_jobid);
                                    
                                    // Lấy thông tin công ty cho từng đối tác
                                    $company_1 = $job_partner_1 ? get_field('ten_cong_ty', 'user_' . $job_partner_1['ID']) : '';
                                    $company_2 = $job_partner_2 ? get_field('ten_cong_ty', 'user_' . $job_partner_2['ID']) : '';
                                    $company_foreign = $job_foreign_partner ? get_field('ten_cong_ty', 'user_' . $job_foreign_partner['ID']) : '';
                                    
                                    // Partner 1
                                    if ($job_partner_1 && isset($job_partner_1['ID'])): ?>
                                        <option value="<?php echo esc_attr($job_partner_1['ID']); ?>">
                                            <?php echo esc_html($job_partner_1['display_name']); ?>
                                            <?php if ($company_1): ?> (<?php echo esc_html($company_1); ?>)<?php endif; ?>
                                            - <?php _e('Đối tác gửi việc', 'qlcv'); ?>
                                        </option>
                                    <?php endif;
                                    
                                    // Partner 2 (default selected)
                                    if ($job_partner_2 && isset($job_partner_2['ID']) && $job_partner_2['ID'] != $job_partner_1['ID']): ?>
                                        <option value="<?php echo esc_attr($job_partner_2['ID']); ?>" selected>
                                            <?php echo esc_html($job_partner_2['display_name']); ?>
                                            <?php if ($company_2): ?> (<?php echo esc_html($company_2); ?>)<?php endif; ?>
                                            - <?php _e('Đối tác nhận việc', 'qlcv'); ?>
                                        </option>
                                    <?php endif;
                                    
                                    // Foreign Partner
                                    if ($job_foreign_partner && isset($job_foreign_partner['ID'])): ?>
                                        <option value="<?php echo esc_attr($job_foreign_partner['ID']); ?>">
                                            <?php echo esc_html($job_foreign_partner['display_name']); ?>
                                            <?php if ($company_foreign): ?> (<?php echo esc_html($company_foreign); ?>)<?php endif; ?>
                                            - <?php _e('Đối tác nước ngoài', 'qlcv'); ?>
                                        </option>
                                    <?php endif; ?>
                                    
                                    <?php if (!$job_partner_1 && !$job_partner_2 && !$job_foreign_partner): ?>
                                        <option value=""><?php _e('Không có đối tác nào được thiết lập', 'qlcv'); ?></option>
                                    <?php endif; ?>
                                </select>
                            <?php else: ?>
                                <!-- Form bình thường khi không có GET jobid -->
                                <select class="form-control select2-tags mb-20" name="finance_user">
                                    <?php
                                    if ($jobid) {
                                        $partner_1      = get_field('partner_1', $jobid);
                                        $partner_2      = get_field('partner_2', $jobid);
                                        $foreign_partner = get_field('foreign_partner', $jobid);

                                        $cty_1  = get_field('ten_cong_ty', 'user_' . $partner_1['ID']);
                                        $cty_2  = get_field('ten_cong_ty', 'user_' . $partner_2['ID']);
                                        $cty_nn = get_field('ten_cong_ty', 'user_' . $foreign_partner['ID']);

                                        if ($partner_1["ID"]) {
                                            echo "<option value='" . $partner_1['ID'] . "'>" . $partner_1['display_name'] . " (" . $cty_1 . ") - " . __('Đối tác gửi việc', 'qlcv') . "</option>";
                                        }
                                        if ($partner_2["ID"] && ($partner_1["ID"] != $partner_2["ID"])) {
                                            echo "<option value='" . $partner_2['ID'] . "' selected>" . $partner_2['display_name'] . " (" . $cty_2 . ") - " . __('Đối tác nhận việc', 'qlcv') . "</option>";
                                        }
                                        if ($foreign_partner["ID"]) {
                                            echo "<option value='" . $foreign_partner['ID'] . "'>" . $foreign_partner['display_name'] . " (" . $cty_nn . ") - " . __('Đối tác nước ngoài', 'qlcv') . "</option>";
                                        }
                                    } else {
                                        echo "<option value='" . $current_user->ID . "'>-- Chọn người nhận phiếu --</option>";

                                        $query = get_users(
                                            array(
                                                'role__in' => array('partner', 'foreign_partner', 'subscriber'),
                                            )
                                        );

                                        if ($query) {
                                            foreach ($query as $user) {
                                                $ten_cong_ty    = get_field('ten_cong_ty', 'user_' . $user->ID);
                                                echo "<option value='" . $user->ID . "'>" . $user->display_name . " (" . $ten_cong_ty . ")</option>";
                                            }
                                        }
                                    }
                                    ?>
                                </select>
                            <?php endif; ?>
                        </div>
                        <div class="col-lg-3"></div>

                        <div class="col-lg-3 form_title lh45"><?php _e('Loại tiền', 'qlcv'); ?> <span class="text-danger">*</span></div>
                        <div class="col-lg-6 col-12 mb-20">
                            <div class="form-group">
                                <?php
                                if ($currency) {
                                    echo '<label class="inline lh45"><input type="radio" name="finance_currency" value="' . $currency . '" checked>' . $currency . '</label>';
                                } else {
                                    $crcy_arr = array("USD", "VND");

                                    foreach ($crcy_arr as $value) {
                                        echo '<label class="inline lh45"><input type="radio" name="finance_currency" value="' . $value . '">' . $value . '</label>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <div class="col-lg-3"></div>

                        <div class="col-lg-3 form_title lh45"><?php _e('Số tiền', 'qlcv'); ?> <span class="text-danger">*</span></div>
                        <div class="col-lg-6 col-12 mb-20">
                            <input type="number" placeholder="0" class="form-control" name="finance_value">
                        </div>
                        <div class="col-lg-3"></div>

                        <div class="col-lg-3 form_title lh45"><?php _e('Lý do', 'qlcv'); ?> <span class="text-danger">*</span></div>
                        <div class="col-lg-6 col-12 mb-20">
                            <input type="text" class="form-control" name="finance_title">
                        </div>
                        <div class="col-lg-3"></div>

                        <div class="col-lg-3 form_title lh45"><?php _e('Ghi chú', 'qlcv'); ?></div>
                        <div class="col-lg-8 col-12 mb-20">
                            <textarea class="summernote" name="finance_content"></textarea>
                        </div>
                        <div class="col-lg-1"></div>

                        <?php
                        echo '<input type="hidden" name="history_link" value="' . $history_link . '">';
                        wp_nonce_field('post_nonce', 'post_nonce_field');
                        ?>

                        <div class="col-lg-3"></div>
                        <div class="col-lg-6 col-12 mb-20"><input type="submit" class="button button-primary" value="<?php _e('Cập nhật', 'qlcv'); ?>"> <a href="javascript:history.go(-1)" class="button button-wikipedia"><?php _e('Huỷ bỏ', 'qlcv'); ?></a></div>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    </form>

</div><!-- Content Body End -->

<?php
get_footer();
?>