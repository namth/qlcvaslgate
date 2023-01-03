<?php 
/*
    Template Name: Đổi mật khẩu
*/
    if ( is_user_logged_in() ) {
        # get edit user data
        if ( $_GET['uid'] !="" ) {
            $current_user = get_user_by('ID', $_GET['uid']);
        } else {
            $current_user = wp_get_current_user();
        }

        # if it have edit action then update user info
        if ( isset( $_POST['post_nonce_field'] ) && 
            wp_verify_nonce( $_POST['post_nonce_field'], 'post_nonce' ) ) {

            # get data from the form
            $old_password       = $_POST['old_password'];
            $new_password       = $_POST['new_password'];
            $confirm_password   = $_POST['confirm_password'];

            if ( wp_check_password( $old_password, $current_user->user_pass, $current_user->ID ) && ($new_password == $confirm_password) ) {
                # change password
                $args = array(
                    'ID'        => $current_user->ID,
                    'user_pass' => $new_password,
                );

                $new_partner = wp_update_user( $args );

                # if it's success create new user,
                # add more info throught custom fields
                if ( !is_wp_error( $new_partner ) ) {
                    $thongbao = '<div class="alert alert-success" role="alert">
                                    <i class="fa fa-check"></i> ' . __('Đã đổi mật khẩu thành công', 'qlcv') . '
                                </div>';
                    $success = true;
                } else {
                    $thongbao = '<div class="alert alert-danger" role="alert">
                                    <i class="zmdi zmdi-info"></i> ' . __('Có lỗi xảy ra, xin vui lòng thông báo với admin để khắc phục.', 'qlcv') . '
                                </div>';
                }
                
            } else {
                    $thongbao = '<div class="alert alert-danger" role="alert">
                        <i class="zmdi zmdi-info"></i> ' . __('Mật khẩu cũ không đúng hoặc mật khẩu xác nhận không khớp.', 'qlcv') . '
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
                                if ( $success ) {
                                    wp_redirect( get_author_posts_url( $current_user->ID ) );
                                    exit;
                                }
                            ?>
                            <div>
                                <form action="#" method="POST" class="row">
                                    <div class="col-lg-3 form_title lh45"><?php _e('Mật khẩu cũ', 'qlcv'); ?></div>
                                    <div class="col-lg-6 col-12 mb-20"><input type="password" class="form-control" name="old_password" ></div>
                                    <div class="col-lg-3"></div>

                                    <div class="col-lg-3 form_title lh45"><?php _e('Mật khẩu mới', 'qlcv'); ?></div>
                                    <div class="col-lg-6 col-12 mb-20"><input type="password" class="form-control" name="new_password" ></div>
                                    <div class="col-lg-3"></div>

                                    <div class="col-lg-3 form_title lh45"><?php _e('Nhập lại mật khẩu mới', 'qlcv'); ?></div>
                                    <div class="col-lg-6 col-12 mb-20"><input type="password" class="form-control" name="confirm_password"></div>
                                    <div class="col-lg-3"></div>

                                    <?php 
                                        wp_nonce_field( 'post_nonce', 'post_nonce_field' );
                                    ?>

                                    <div class="col-lg-3"></div>
                                    <div class="col-lg-6 col-12 mb-20"><input type="submit" class="button button-primary" value="Đổi mật khẩu"> <a href="javascript:history.go(-1)" class="button button-wikipedia"><?php _e('Huỷ bỏ', 'qlcv'); ?></a></div>

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