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

        global $wpdb;

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
            
            # Update record in aslmember table
            $aslTable = 'wp_aslmember';
            
            # Get user work groups and branches
            $chi_nhanh = get_field('chi_nhanh', 'user_' . $new_partner);
            $nhom_cong_viec = get_field('nhom_cong_viec', 'user_' . $new_partner);
            
            $work_group = array();
            $brand = array();

            # Process work groups
            if (!empty($nhom_cong_viec)) {
                foreach ($nhom_cong_viec as $id_cong_viec) {
                    $term = get_term($id_cong_viec);
                    $work_group[] = $term->slug;
                }
            }

            # Process branches
            if (!empty($chi_nhanh)) {
                foreach ($chi_nhanh as $id_chi_nhanh) {
                    $term = get_term($id_chi_nhanh);
                    $brand[] = $term->slug;
                }
            }

            # Set group flags
            $group_trademark = in_array('nhan-hieu', $work_group) ? 1 : 0;
            $group_patent = in_array('sang-che', $work_group) ? 1 : 0;
            $group_design = in_array('kieu-dang', $work_group) ? 1 : 0;
            $group_franchise = in_array('franchise', $work_group) ? 1 : 0;
            $group_copyright = in_array('ban-quyen', $work_group) ? 1 : 0;
            $group_others = in_array('viec-khac', $work_group) ? 1 : 0;
            $group_potential = in_array('tiem-nang', $work_group) ? 1 : 0;

            # Set agency flags
            $agency_hn = in_array('ha-noi', $brand) ? 1 : 0;
            $agency_hcm = in_array('ho-chi-minh', $brand) ? 1 : 0;

            # Set role flags
            $theUser = new WP_User($new_partner);
            $role_admin = in_array('administrator', $theUser->roles) ? 1 : 0;
            $role_manager = in_array('contributor', $theUser->roles) ? 1 : 0;
            $role_member = in_array('member', $theUser->roles) ? 1 : 0;
            $role_law_manager = in_array('law_manager', $theUser->roles) ? 1 : 0;
            $role_ip_manager = in_array('ip_manager', $theUser->roles) ? 1 : 0;

            # Update the member record in the database
            $wpdb->update(
                $aslTable,
                array(
                    'name' => $display_name,
                    'address' => $address,
                    'phone' => $phone_number,
                    'email' => $this_user->user_email,
                    'agency_hn' => $agency_hn,
                    'agency_hcm' => $agency_hcm,
                    'group_trademark' => $group_trademark,
                    'group_patent' => $group_patent,
                    'group_design' => $group_design,
                    'group_franchise' => $group_franchise,
                    'group_copyright' => $group_copyright,
                    'group_others' => $group_others,
                    'group_potential' => $group_potential,
                    'role_admin' => $role_admin,
                    'role_manager' => $role_manager,
                    'role_member' => $role_member,
                    'role_law_manager' => $role_law_manager,
                    'role_ip_manager' => $role_ip_manager
                ),
                array('memberid' => $this_user->ID)
            );
               
            $thongbao = '<div class="alert alert-success" role="alert">
                            <i class="fa fa-check"></i> ' . __('Đã sửa thông tin thành công', 'qlcv') . '
                        </div>';
        } else {
            $thongbao = '<div class="alert alert-danger" role="alert">
                            <i class="zmdi zmdi-info"></i> ' . __('Có lỗi xảy ra, xin vui lòng kiểm tra lại.','qlcv') . '
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

                                    <div class="col-lg-3 form_title lh45"><?php _e('Họ và tên', 'qlcv'); ?></div>
                                    <div class="col-lg-3 col-12 mb-20"><input type="text" class="form-control" name="first_name" placeholder="<?php _e('Họ', 'qlcv'); ?>" value="<?php echo $this_user->user_firstname; ?>"></div>
                                    <div class="col-lg-3 col-12 mb-20"><input type="text" class="form-control" name="last_name" placeholder="<?php _e('Tên', 'qlcv'); ?>" value="<?php echo $this_user->user_lastname; ?>"></div>

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
                                    <div class="col-lg-6 col-12 mb-20"><textarea class="form-control" placeholder="<?php _e('Thông tin bổ sung', 'qlcv'); ?>" name="note"><?php echo get_user_meta($this_user->ID, 'description', true); ?></textarea></div>
                                    <div class="col-lg-3"></div>

                                    <?php 
                                        wp_nonce_field( 'post_nonce', 'post_nonce_field' );
                                    ?>

                                    <div class="col-lg-3"></div>
                                    <div class="col-lg-6 col-12 mb-20"><input type="submit" class="button button-primary" value="<?php _e('Cập nhật', 'qlcv'); ?>"></div>

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