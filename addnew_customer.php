  <?php 
/*
    Template Name: Thêm mới khách hàng
*/
    if (
            isset( $_POST['post_nonce_field'] ) && 
            wp_verify_nonce( $_POST['post_nonce_field'], 'post_nonce' ) 
        ) {

        # get data from the form
        $customer_name  = $_POST['customer_name'];
        $customer_company = $_POST['customer_company'];
        $user_email     = $_POST['user_email'];
        $phone_number   = $_POST['phone_number'];
        $address        = $_POST['address'];
        $country        = $_POST['country'];
        $note           = $_POST['note'];

        # add new user
        $args = array(
            'post_title'    => $customer_name,
            'post_content'  => $note,
            'post_status'   => 'publish',
            'post_type'     => 'customer',
        );

        $inserted = wp_insert_post( $args, $error );

        # if it's success create new user,
        # add more info throught custom fields
        if ( !$error ) {
            update_field('field_600d31f4060eb', $customer_company, $inserted ); # tên công ty
            update_field('field_600d3235060ed', $user_email, $inserted ); # email liên hệ
            update_field('field_600d3211060ec', $phone_number, $inserted ); # phone number
            update_field('field_600d323d060ee', $address, $inserted ); # address
            update_field('field_6037200ec98cc', $country, $inserted ); # country

            $thongbao = '<div class="alert alert-success" role="alert">
                            <i class="fa fa-check"></i> '. __('Đã tạo tài khoản thành công', 'qlcv') . '
                        </div>';
            # chuyển tới trang danh sách nhân sự
            wp_redirect( get_bloginfo('url') . '/danh-sach-nhan-su/?role=' . $role );
        } else {
            $thongbao = '<div class="alert alert-danger" role="alert">
                            <i class="zmdi zmdi-info"></i> '. __('Có lỗi xảy ra, xin vui lòng kiểm tra lại.', 'qlcv') . '
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
                                    <div class="col-lg-3 form_title lh45"><?php _e('Họ và tên', 'qlcv'); ?></div>
                                    <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="customer_name"></div>
                                    <div class="col-lg-3"></div>

                                    <div class="col-lg-3 form_title lh45"><?php _e('Công ty', 'qlcv'); ?></div>
                                    <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="customer_company"></div>
                                    <div class="col-lg-3"></div>

                                    <div class="col-lg-3 form_title lh45"><?php _e('Email', 'qlcv'); ?></div>
                                    <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="user_email"></div>
                                    <div class="col-lg-3"></div>

                                    <div class="col-lg-3 form_title lh45"><?php _e('Số điện thoại', 'qlcv'); ?></div>
                                    <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="phone_number"></div>
                                    <div class="col-lg-3"></div>

                                    <div class="col-lg-3 form_title lh45"><?php _e('Địa chỉ', 'qlcv'); ?></div>
                                    <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="address"></div>
                                    <div class="col-lg-3"></div>

                                    <div class="col-lg-3 form_title lh45"><?php _e('Quốc gia', 'qlcv'); ?></div>
                                    <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="country"></div>
                                    <div class="col-lg-3"></div>

                                    <div class="col-lg-3 form_title lh45"><?php _e('Ghi chú', 'qlcv'); ?></div>
                                    <div class="col-lg-6 col-12 mb-20"><textarea class="form-control" placeholder="<?php _e('Thông tin bổ sung', 'qlcv'); ?>" name="note"></textarea></div>
                                    <div class="col-lg-3"></div>

                                    <?php 
                                        wp_nonce_field( 'post_nonce', 'post_nonce_field' );
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