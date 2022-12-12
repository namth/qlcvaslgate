<?php 
/*
    Template Name: Sửa thông tin nhân viên
*/
get_header();

get_sidebar();

if ( is_user_logged_in() ) {
    # get edit user data
    if ( $_GET['uid'] !="" ) {
        $this_user = get_user_by('ID', $_GET['uid']);
    } else {
        $this_user = wp_get_current_user();
    }

    # if it have edit action then update user info
    if ( isset( $_POST['post_nonce_field'] ) && 
        wp_verify_nonce( $_POST['post_nonce_field'], 'post_nonce' ) ) {

        # get data from the form
        $first_name     = $_POST['first_name'];
        $last_name      = $_POST['last_name'];
        $phone_number   = $_POST['phone_number'];
        $address        = $_POST['address'];
        $country        = $_POST['country'];
        $note           = $_POST['note'];
        $display_name   = $first_name . " " . $last_name;

        # add new user
        $args = array(
            'ID'            => $this_user->ID,
            'first_name'    => $first_name,
            'last_name'     => $last_name,
            'display_name'  => $display_name,
            'description'   => $note,
        );

        $new_partner = wp_update_user( $args );

        # if it's success create new user,
        # add more info throught custom fields
        if ( !is_wp_error( $new_partner ) ) {
            update_field('field_600d3211060ec', $phone_number, 'user_' . $new_partner ); # phone number
            update_field('field_600d323d060ee', $address, 'user_' . $new_partner ); # address
            update_field('field_6037200ec98cc', $country, 'user_' . $new_partner ); # country
               
            $thongbao = '<div class="alert alert-success" role="alert">
                            <i class="fa fa-check"></i> Đã sửa thông tin thành công
                        </div>';
        } else {
            $thongbao = '<div class="alert alert-danger" role="alert">
                            <i class="zmdi zmdi-info"></i> Có lỗi xảy ra, xin vui lòng kiểm tra lại.
                        </div>';
        }
    }
}
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

                                // print_r($this_user);
                                $so_dien_thoai  = get_field('so_dien_thoai' , 'user_' . $this_user->ID);
                                $dia_chi        = get_field('dia_chi' , 'user_' . $this_user->ID);
                                $quoc_gia       = get_field('quoc_gia' , 'user_' . $this_user->ID);
                            ?>
                            <div>
                                <form action="#" method="POST" class="row">
                                    <div class="col-lg-3 form_title lh45">Email</div>
                                    <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="user_email" disabled value="<?php echo $this_user->user_email; ?>"></div>
                                    <div class="col-lg-3"></div>

                                    <div class="col-lg-3 form_title lh45">Họ và tên</div>
                                    <div class="col-lg-3 col-12 mb-20"><input type="text" class="form-control" name="first_name" placeholder="Họ" value="<?php echo $this_user->user_firstname; ?>"></div>
                                    <div class="col-lg-3 col-12 mb-20"><input type="text" class="form-control" name="last_name" placeholder="Tên" value="<?php echo $this_user->user_lastname; ?>"></div>

                                    <div class="col-lg-3"></div>
                                    <div class="col-lg-3 form_title lh45">Số điện thoại</div>
                                    <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="phone_number" value="<?php echo $so_dien_thoai; ?>"></div>
                                    <div class="col-lg-3"></div>

                                    <div class="col-lg-3 form_title lh45">Địa chỉ</div>
                                    <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="address" value="<?php echo $dia_chi; ?>"></div>
                                    <div class="col-lg-3"></div>

                                    <div class="col-lg-3 form_title lh45">Quốc gia</div>
                                    <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="country" value="<?php echo $quoc_gia; ?>"></div>
                                    <div class="col-lg-3"></div>

                                    <div class="col-lg-3 form_title lh45">Ghi chú</div>
                                    <div class="col-lg-6 col-12 mb-20"><textarea class="form-control" placeholder="Thông tin bổ sung" name="note"><?php echo get_user_meta($this_user->ID, 'description', true); ?></textarea></div>
                                    <div class="col-lg-3"></div>

                                    <?php 
                                        wp_nonce_field( 'post_nonce', 'post_nonce_field' );
                                    ?>

                                    <div class="col-lg-3"></div>
                                    <div class="col-lg-6 col-12 mb-20"><input type="submit" class="button button-primary" value="Cập nhật"></div>

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