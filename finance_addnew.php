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
            $total_wallet = get_field('total_usd', 'option');
            $wallet = 'field_60bb2f7cf9156';
        } else {
            $total_wallet = get_field('total_vnd', 'option');
            $wallet = 'field_60bb2f98f9157';
        }

        # cập nhật số dư mới
        if ($finance_type == "Thu") {
            # tính tổng trong ví
            $total_value = $total_wallet + $finance_value;

            # tính toán trong job đó
            $job_paid       = get_field('paid', $finance_job) + $finance_value;
            $job_remainning = get_field('remainning', $finance_job) - $finance_value;

            # update 
            update_field('field_60a231d395f2e', $job_paid, $finance_job);
            update_field('field_60a231d3961b0', $job_remainning, $finance_job);

            # nếu chưa set currency thì cài đặt luôn
            if (!$currency) {
                update_field('field_60a231d39602e', $finance_currency, $finance_job);
            }
        } else if ($finance_type == "Chi") {
            # tính tổng trong ví
            $total_value = $total_wallet - $finance_value;

            # tính toán trong job đó
            $job_advance    = get_field('advance_money', $finance_job) + $finance_value;
            $job_debt       = get_field('debt', $finance_job) - $finance_value;

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
                <h3 class="title">Tạo phiếu thu/chi</h3>
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
                                            <i class="fa fa-check"></i> Bài viết đã được cập nhật.
                                          </div>';
                    } else {
                        echo '<div class="alert alert-danger" role="alert">
                                            <i class="zmdi zmdi-info"></i> Xảy ra lỗi, không thể cập nhật.
                                          </div>';
                    }
                } else {
                    if ($error) {
                        echo "Có lỗi xảy ra " . $error_detail;
                    }

                ?>
                    <div class="row mbn-20">
                        <div class="col-lg-3 form_title lh45">Ngày thu/chi <span class="text-danger">*</span></div>
                        <div class="col-lg-3 col-12 mb-20">
                            <input type="text" class="form-control input-date-single" value="" name="finance_date" data-mask="99/99/9999">
                            <span class="form-help-text">"dd/mm/yyyy"</span>
                        </div>
                        <div class="col-lg-6"></div>

                        <div class="col-lg-3 form_title lh45">Phân loại <span class="text-danger">*</span></div>
                        <div class="col-lg-6 col-12 mb-20">
                            <div class="form-group">
                                <label class="inline lh45">
                                    <input type="radio" name="finance_type" value="Thu" checked>Thu</label>
                                <label class="inline lh45">
                                    <input type="radio" name="finance_type" value="Chi">Chi</label>
                            </div>
                        </div>
                        <div class="col-lg-3"></div>

                        <div class="col-lg-3 form_title lh45">Công việc</div>
                        <div class="col-lg-6 col-12 mb-20">
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
                                    echo '<option value="">-- Chọn công việc liên quan --</option>';
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
                        </div>
                        <div class="col-lg-3"></div>

                        <div class="col-lg-3 form_title lh45">Người nhận phiếu <span class="text-danger">*</span></div>
                        <div class="col-lg-6 col-12 mb-20">
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
                                        echo "<option value='" . $partner_1['ID'] . "'>" . $partner_1['display_name'] . " (" . $cty_1 . ")</option>";
                                    }
                                    if ($partner_2["ID"] && ($partner_1["ID"] != $partner_2["ID"])) {
                                        echo "<option value='" . $partner_2['ID'] . "'>" . $partner_2['display_name'] . " (" . $cty_2 . ")</option>";
                                    }
                                    if ($foreign_partner["ID"]) {
                                        echo "<option value='" . $foreign_partner['ID'] . "'>" . $foreign_partner['display_name'] . " (" . $cty_nn . ")</option>";
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

                        </div>
                        <div class="col-lg-3"></div>

                        <div class="col-lg-3 form_title lh45">Loại tiền <span class="text-danger">*</span></div>
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

                        <div class="col-lg-3 form_title lh45">Số tiền <span class="text-danger">*</span></div>
                        <div class="col-lg-6 col-12 mb-20">
                            <input type="number" placeholder="0" class="form-control" name="finance_value">
                        </div>
                        <div class="col-lg-3"></div>

                        <div class="col-lg-3 form_title lh45">Lý do <span class="text-danger">*</span></div>
                        <div class="col-lg-6 col-12 mb-20">
                            <input type="text" class="form-control" name="finance_title">
                        </div>
                        <div class="col-lg-3"></div>

                        <div class="col-lg-3 form_title lh45">Ghi chú</div>
                        <div class="col-lg-8 col-12 mb-20">
                            <textarea class="summernote" name="finance_content"></textarea>
                        </div>
                        <div class="col-lg-1"></div>

                        <?php
                        echo '<input type="hidden" name="history_link" value="' . $history_link . '">';
                        wp_nonce_field('post_nonce', 'post_nonce_field');
                        ?>

                        <div class="col-lg-3"></div>
                        <div class="col-lg-6 col-12 mb-20"><input type="submit" class="button button-primary" value="Cập nhật"> <a href="javascript:history.go(-1)" class="button button-wikipedia">Huỷ bỏ</a></div>
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