<?php
/*
    Template Name: Thêm mới partner (đối tác)
*/
$history_link   = $_SERVER['HTTP_REFERER'];

if (
    is_user_logged_in() &&
    isset($_POST['post_nonce_field']) &&
    wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')
) {

    # get data from the form
    $user_code      = $_POST['user_code'];
    $user_company   = $_POST['user_company'];
    $type_of_client = $_POST['type_of_client'];
    $partner_vip    = $_POST['partner_vip'];
    $first_name     = $_POST['first_name'];
    $last_name      = $_POST['last_name'];
    $user_email     = $_POST['user_email'];
    $phone_number   = $_POST['phone_number'];
    $address        = $_POST['address'];
    $country        = $_POST['country'];
    $note           = $_POST['note'];
    $history_link   = $_POST['history_link'];
    $role           = $_POST['role'];
    $worked         = $_POST['worked'];
    $display_name   = $first_name . " " . $last_name;
    $user_pass      = 'd1412@pass';

    # check partner-code, if it's not exists then add new user
    if (!search_partner($user_code)) {
        $args = array(
            'user_login'    => $user_code,
            'user_email'    => $user_email,
            'user_pass'     => $user_pass,
            'first_name'    => $first_name,
            'last_name'     => $last_name,
            'display_name'  => $display_name,
            'description'   => $note,
            'role'          => $role,
        );

        $new_partner = wp_insert_user($args);

        # if it's success create new user,
        # add more info throught custom fields
        if (!is_wp_error($new_partner)) {
            wp_update_user(array('user_nicename' => $new_partner));
            update_field('field_607a4fb37b7e0', $user_code, 'user_' . $new_partner); # user_code
            update_field('field_600d31f4060eb', $user_company, 'user_' . $new_partner); # user_company
            update_field('field_60a3cbacb1330', $type_of_client, 'user_' . $new_partner); # type_of_client
            update_field('field_61cd79951653e', $partner_vip, 'user_' . $new_partner); # partner_vip
            update_field('field_600d3211060ec', $phone_number, 'user_' . $new_partner); # phone number
            update_field('field_600d3235060ed', $user_email, 'user_' . $new_partner); # user_email
            update_field('field_600d323d060ee', $address, 'user_' . $new_partner); # address
            update_field('field_6037200ec98cc', $country, 'user_' . $new_partner); # country
            update_field('field_61cd79bf1653f', $worked, 'user_' . $new_partner); # đã chốt hoặc tiềm năng

            $thongbao = '<div class="alert alert-success" role="alert">
                                <i class="fa fa-check"></i> ' . __('Đã tạo tài khoản thành công', 'qlcv') . '
                            </div>';

            if ($history_link) {
                wp_redirect($history_link);
                exit;
            }
        } else {
            $thongbao = '<div class="alert alert-danger" role="alert">
                                <i class="zmdi zmdi-info"></i> Có lỗi xảy ra, xin vui lòng kiểm tra lại.
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
                    ?>
                    <div>
                        <form action="#" method="POST" class="row">
                            <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Tên công ty/tổ chức', 'qlcv'); ?> <span class="text-danger">*</span></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="user_company" value="<?php echo $_POST['user_company']; ?>"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Mã đối tác', 'qlcv'); ?> <span class="text-danger">*</span></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="user_code" value="<?php echo $_POST['user_code']; ?>"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45 text-lg-right"><?php _e('Họ và tên', 'qlcv'); ?> <span class="text-danger">*</span></div>
                            <div class="col-lg-3 col-12 mb-20">
                                <input type="text" class="form-control" name="first_name" placeholder="<?php _e('Họ', 'qlcv'); ?>" value="<?php echo $_POST['first_name']; ?>">
                            </div>
                            <div class="col-lg-3 col-12 mb-20">
                                <input type="text" class="form-control" name="last_name" placeholder="<?php _e('Tên', 'qlcv'); ?>" value="<?php echo $_POST['last_name']; ?>">
                            </div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title text-left text-lg-right mb-10 mt-10"><?php _e('Trạng thái', 'qlcv'); ?></div>
                            <div class="col-lg-3 col-12 mb-20 mt-10">
                                <div class="adomx-checkbox-radio-group inline">
                                    <label class="adomx-radio-2"><input type="radio" name="worked" value="1"> <i class="icon"></i> <?php _e('Đã chốt', 'qlcv'); ?></label>
                                    <label class="adomx-radio-2"><input type="radio" name="worked" value="0" checked> <i class="icon"></i> <?php _e('Tiềm năng', 'qlcv'); ?></label>
                                </div>
                            </div>
                            <div class="col-lg-6"></div>

                            <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Phân loại', 'qlcv'); ?></div>
                            <div class="col-lg-6 col-12 mb-20">
                                <select class="form-control mb-20" name="type_of_client">
                                <?php
                                    $partner_list_type = explode(PHP_EOL, get_field('partner_list_type', 'option'));

                                    foreach ($partner_list_type as $value) {
                                        $value = trim($value);
                                        if ($value) {
                                            echo '<option value="' . $value . '">' . $value . '</option>';
                                        }
                                    }
                                ?>
                                </select>
                            </div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Cấp độ', 'qlcv'); ?></div>
                            <div class="col-lg-6 col-12 mb-20">
                                <select class="form-control mb-20" name="partner_vip">
                                    <?php
                                    $partner_vip_type = explode(PHP_EOL, get_field('partner_vip_type', 'option'));

                                    foreach ($partner_vip_type as $value) {
                                        $value = trim($value);
                                        if ($value) {
                                            echo '<option value="' . $value . '">' . $value . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45 text-lg-right"><?php _e('Chức năng', 'qlcv'); ?></div>
                            <div class="col-lg-6 col-12 mb-20">
                                <select name="role" class="form-control select2-tags mb-20">
                                    <option value="partner"><?php _e('Đối tác gửi việc', 'qlcv'); ?></option>
                                    <option value="foreign_partner"><?php _e('Đối tác nhận việc', 'qlcv'); ?></option>
                                </select>
                            </div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45 text-lg-right">Email <span class="text-danger">*</span></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="user_email" value="<?php echo $_POST['user_email']; ?>"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45 text-lg-right"><?php _e('Số điện thoại', 'qlcv'); ?></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="phone_number" value="<?php echo $_POST['phone_number']; ?>"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45 text-lg-right"><?php _e('Địa chỉ', 'qlcv'); ?></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="address" value="<?php echo $_POST['address']; ?>"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45 text-lg-right"><?php _e('Quốc gia', 'qlcv'); ?></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="country" value="<?php echo $_POST['country']; ?>"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45 text-lg-right"><?php _e('Ghi chú', 'qlcv'); ?></div>
                            <div class="col-lg-6 col-12 mb-20"><textarea class="form-control" placeholder="Thông tin bổ sung" name="note"></textarea></div>
                            <div class="col-lg-3"></div>

                            <?php
                            wp_nonce_field('post_nonce', 'post_nonce_field');
                            echo '<input type="hidden" name="history_link" value="' . $history_link . '">';
                            ?>

                            <div class="col-lg-3"></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="submit" class="button button-primary" value="<?php _e('Tạo mới', 'qlcv'); ?>"></div>

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