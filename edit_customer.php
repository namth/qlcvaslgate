<?php
/*
    Template Name: Sửa thông tin khách hàng
*/
$history_link   = $_SERVER['HTTP_REFERER'];

if (is_user_logged_in()) {
    # get edit user data
    if ($_GET['uid'] != "") {
        $customer_id = $_GET['uid'];
    } else {
        # thoát
    }

    # if it have edit action then update user info
    if (
        isset($_POST['post_nonce_field']) &&
        wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')
    ) {

        # get data from the form
        $user_email     = $_POST['user_email'];
        $customer_name  = $_POST['customer_name'];
        $customer_company = $_POST['customer_company'];
        $phone_number   = $_POST['phone_number'];
        $address        = $_POST['address'];
        $country        = $_POST['country'];
        $note           = $_POST['note'];
        $history_link   = $_POST['history_link'];

        $new_partner = wp_update_post(array(
            'ID'            => $customer_id,
            'post_title'    => $customer_name,
            'post_content'  => $note,
        ));

        # if it's success create new user,
        # add more info throught custom fields
        if (!is_wp_error($new_partner)) {
            update_field('field_600d31f4060eb', $customer_company, $new_partner); # tên công ty
            update_field('field_600d3211060ec', $phone_number, $new_partner); # phone number
            update_field('field_600d323d060ee', $address, $new_partner); # address
            update_field('field_600d3235060ed', $user_email, $inserted); # email liên hệ
            update_field('field_6037200ec98cc', $country, $new_partner); # country
            update_field('field_6010f85bfcf55', $link_onedrive, $new_partner); # link_onedrive

            $thongbao = '<div class="alert alert-success" role="alert">
                                <i class="fa fa-check"></i> ' . __('Đã sửa thông tin thành công', 'qlcv') . '
                            </div>';

            wp_redirect($history_link);
            exit;
        } else {
            $thongbao = '<div class="alert alert-danger" role="alert">
                                <i class="zmdi zmdi-info"></i> ' . __('Có lỗi xảy ra, xin vui lòng kiểm tra lại.', 'qlcv') . '
                            </div>';
        }
    }
}

get_header();

get_sidebar();
?>

<!-- Content Body Start -->
<div class="content-body">

    <!-- Page Headings Start -->
    <div class="row justify-content-between align-items-center mb-10">

        <!-- Page Heading Start -->
        <div class="col-12 col-lg-12 mb-20">
            <div class="page-heading">
                <?php
                echo '<h3 class="title">' . get_the_title() . '</h3>';
                ?>
            </div>
        </div><!-- Page Heading End -->

        <div class="col-12 mb-30">
            <div class="box">
                <div class="box-body">
                    <?php
                    if ($thongbao) {
                        echo $thongbao;
                    }

                    // print_r($current_user);
                    $user_email = get_field('field_600d3235060ed', $customer_id);
                    $so_dien_thoai = get_field('so_dien_thoai', $customer_id);
                    $customer_company = get_field('ten_cong_ty', $customer_id);
                    $dia_chi = get_field('dia_chi', $customer_id);
                    $quoc_gia = get_field('quoc_gia', $customer_id);
                    $customer_name = get_the_title($customer_id);
                    ?>
                    <div>
                        <form action="#" method="POST" class="row">
                            <div class="col-lg-3 form_title lh45">Email</div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="user_email" value="<?php echo $user_email; ?>"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45"><?php _e('Họ và tên', 'qlcv'); ?></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="customer_name" value="<?php echo $customer_name; ?>"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45"><?php _e('Công ty', 'qlcv'); ?></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="customer_company" value="<?php echo $customer_company; ?>"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45"><?php _e('Số điện thoại', 'qlcv'); ?></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="phone_number" value="<?php echo $so_dien_thoai; ?>"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45"><?php _e('Địa chỉ', 'qlcv'); ?></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="address" value="<?php echo $dia_chi; ?>"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45"><?php _e('Quốc gia', 'qlcv'); ?></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="country" value="<?php echo $quoc_gia; ?>"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45"><?php _e('Ghi chú', 'qlcv'); ?></div>
                            <div class="col-lg-6 col-12 mb-20"><textarea class="form-control" placeholder="<?php _e('Thông tin bổ sung', 'qlcv'); ?>" name="note"><?php the_content(); ?></textarea></div>
                            <div class="col-lg-3"></div>

                            <?php
                            echo '<input type="hidden" name="history_link" value="' . $history_link . '">';
                            wp_nonce_field('post_nonce', 'post_nonce_field');
                            ?>

                            <div class="col-lg-3"></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="submit" class="button button-primary" value="<?php _e('Cập nhật', 'qlcv'); ?>"> <a href="javascript:history.go(-1)" class="button button-wikipedia">Huỷ bỏ</a></div>

                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- Page Headings End -->

</div><!-- Content Body End -->

<?php
get_footer();
?>