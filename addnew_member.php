<?php
/*
    Template Name: Thêm mới nhân viên
*/
$history_link   = $_SERVER['HTTP_REFERER'];

if (
    isset($_POST['post_nonce_field']) &&
    wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')
) {

    # get data from the form
    $first_name     = $_POST['first_name'];
    $last_name      = $_POST['last_name'];
    $user_login     = $_POST['user_login'];
    $user_email     = $_POST['user_email'];
    $phone_number   = $_POST['phone_number'];
    $address        = $_POST['address'];
    $country        = $_POST['country'];
    $note           = $_POST['note'];
    $role           = $_POST['role'];
    $display_name   = $first_name . " " . $last_name;
    $user_pass      = $_POST['user_pass'];
    $history_link   = $_POST['history_link'];

    # add new user
    $args = array(
        'user_login'    => $user_login,
        'user_email'    => $user_email,
        'user_pass'     => $user_pass,
        'first_name'    => $first_name,
        'last_name'     => $last_name,
        'display_name'  => $display_name,
        'description'   => $note,
    );

    if ($role) {
        $args['role'] = $role;
    } else {
        $args['role'] = 'member';
    }

    $new_partner = wp_insert_user($args);

    # if it's success create new user,
    # add more info throught custom fields
    if (!is_wp_error($new_partner)) {
        wp_update_user(array('user_nicename' => $new_partner));
        update_field('field_600d3211060ec', $phone_number, 'user_' . $new_partner); # phone number
        update_field('field_600d323d060ee', $address, 'user_' . $new_partner); # address
        update_field('field_6037200ec98cc', $country, 'user_' . $new_partner); # country

        $thongbao = '<div class="alert alert-success" role="alert">
                            <i class="fa fa-check"></i> Đã tạo tài khoản thành công
                        </div>';
        # chuyển tới trang danh sách nhân sự
        wp_redirect($history_link);
        exit;
    } else {
        $thongbao = '<div class="alert alert-danger" role="alert">
                            <i class="zmdi zmdi-info"></i> Có lỗi xảy ra, xin vui lòng kiểm tra lại.
                        </div>';
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
                        echo '<div class="alert alert-success" role="alert">
                                            <i class="fa fa-check"></i> ' . $thongbao . '
                                          </div>';
                    }

                    $current_user = wp_get_current_user();

                    // print_r($current_user->roles);
                    ?>
                    <div>
                        <form action="#" method="POST" class="row">
                            <div class="col-lg-3 form_title lh45">Họ và tên</div>
                            <div class="col-lg-3 col-12 mb-20"><input type="text" class="form-control" name="first_name" placeholder="Họ"></div>
                            <div class="col-lg-3 col-12 mb-20"><input type="text" class="form-control" name="last_name" placeholder="Tên"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45">Tên đăng nhập</div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="user_login"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45">Mật khẩu</div>
                            <div class="col-lg-6 col-12 mb-20"><input type="password" class="form-control" name="user_pass"></div>
                            <div class="col-lg-3"></div>

                            <?php
                            if (in_array('administrator', $current_user->roles)) {
                            ?>
                                <div class="col-lg-3 form_title lh45">Chức vụ</div>
                                <div class="col-lg-6 col-12 mb-20">
                                    <select name="role" class="form-control select2-tags mb-20">
                                        <option value="member">Nhân viên</option>
                                        <option value="contributor">Cấp quản lý</option>
                                    </select>
                                </div>
                                <div class="col-lg-3"></div>
                            <?php
                            } else {
                                echo "<input type='hidden' value='member'>";
                            }
                            ?>
                            <div class="col-lg-3 form_title lh45">Email</div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="user_email"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45">Số điện thoại</div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="phone_number"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45">Địa chỉ</div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="address"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45">Quốc gia</div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="country"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45">Ghi chú</div>
                            <div class="col-lg-6 col-12 mb-20"><textarea class="form-control" placeholder="Thông tin bổ sung" name="note"></textarea></div>
                            <div class="col-lg-3"></div>

                            <?php
                            wp_nonce_field('post_nonce', 'post_nonce_field');
                            echo '<input type="hidden" name="history_link" value="' . $history_link . '">';
                            ?>

                            <div class="col-lg-3"></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="submit" class="button button-primary" value="Tạo mới"></div>

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