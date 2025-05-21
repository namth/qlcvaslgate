<?php
/*
    Template Name: Thêm mới nhân viên
*/
$history_link   = $_SERVER['HTTP_REFERER'];

if (
    isset($_POST['post_nonce_field']) &&
    wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')
) {
    global $wpdb;
    
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
        
        # Update aslmember table
        $aslTable = $wpdb->prefix . 'aslmember';
        
        # Initialize role values
        $role_admin = $role_manager = $role_member = $role_law_manager = $role_ip_manager = 0;
        
        # Set the appropriate role flag
        switch ($role) {
            case 'administrator':
                $role_admin = 1;
                break;
            case 'contributor':
                $role_manager = 1;
                break;
            case 'member':
                $role_member = 1;
                break;
            case 'law_manager':
                $role_law_manager = 1;
                break;
            case 'ip_manager':
                $role_ip_manager = 1;
                break;
        }
        
        # Get agency and work group information if exists
        $agency_hn = $agency_hcm = 0;
        $group_trademark = $group_patent = $group_design = $group_franchise = $group_copyright = $group_others = $group_potential = 0;
        
        # Get agency information if set
        $chi_nhanh = get_field('chi_nhanh', 'user_' . $new_partner);
        if ($chi_nhanh) {
            $brand = array();
            foreach ($chi_nhanh as $id_chi_nhanh) {
                $term = get_term($id_chi_nhanh);
                $brand[] = $term->slug;
            }
            
            if (is_array($brand)) {
                $agency_hn = in_array('ha-noi', $brand) ? 1 : 0;
                $agency_hcm = in_array('ho-chi-minh', $brand) ? 1 : 0;
            }
        }
        
        # Get work group information if set
        $nhom_cong_viec = get_field('nhom_cong_viec', 'user_' . $new_partner);
        if ($nhom_cong_viec) {
            $work_group = array();
            foreach ($nhom_cong_viec as $id_cong_viec) {
                $term = get_term($id_cong_viec);
                $work_group[] = $term->slug;
            }
            
            if (is_array($work_group)) {
                $group_trademark = in_array('nhan-hieu', $work_group) ? 1 : 0;
                $group_patent = in_array('sang-che', $work_group) ? 1 : 0;
                $group_design = in_array('kieu-dang', $work_group) ? 1 : 0;
                $group_franchise = in_array('franchise', $work_group) ? 1 : 0;
                $group_copyright = in_array('ban-quyen', $work_group) ? 1 : 0;
                $group_others = in_array('viec-khac', $work_group) ? 1 : 0;
                $group_potential = in_array('tiem-nang', $work_group) ? 1 : 0;
            }
        }
        
        # Insert into aslmember table
        $wpdb->insert(
            $aslTable,
            array(
                'memberid'      => $new_partner,
                'name'          => $display_name,
                'address'       => $address,
                'phone'         => $phone_number,
                'email'         => $user_email,
                'date'          => current_time('mysql', 1),
                'agency_hn'     => $agency_hn,
                'agency_hcm'    => $agency_hcm,
                'group_trademark'   => $group_trademark,
                'group_patent'      => $group_patent,
                'group_design'      => $group_design,
                'group_franchise'   => $group_franchise,
                'group_copyright'   => $group_copyright,
                'group_others'      => $group_others,
                'group_potential'   => $group_potential,
                'role_admin'        => $role_admin,
                'role_manager'      => $role_manager,
                'role_member'       => $role_member,
                'role_law_manager'  => $role_law_manager,
                'role_ip_manager'   => $role_ip_manager,
            )
        );

        $thongbao = '<div class="alert alert-success" role="alert">
                            <i class="fa fa-check"></i> ' . __('Đã tạo tài khoản thành công', 'qlcv') . '
                        </div>';
        # chuyển tới trang danh sách nhân sự
        wp_redirect($history_link);
        exit;
    } else {
        $thongbao = '<div class="alert alert-danger" role="alert">
                            <i class="zmdi zmdi-info"></i> ' . __('Có lỗi xảy ra, xin vui lòng kiểm tra lại.', 'qlcv') . '
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
                            <div class="col-lg-3 col-12 mb-20"><input type="text" class="form-control" name="first_name" placeholder="Họ"></div>
                            <div class="col-lg-3 col-12 mb-20"><input type="text" class="form-control" name="last_name" placeholder="Tên"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45"><?php _e('Tên đăng nhập', 'qlcv'); ?></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="user_login"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45"><?php _e('Mật khẩu', 'qlcv'); ?></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="password" class="form-control" name="user_pass"></div>
                            <div class="col-lg-3"></div>

                            <?php
                            if (in_array('administrator', $current_user->roles)) {
                            ?>
                                <div class="col-lg-3 form_title lh45"><?php _e('Chức vụ', 'qlcv'); ?></div>
                                <div class="col-lg-6 col-12 mb-20">
                                    <select name="role" class="form-control select2-tags mb-20">
                                        <option value="member"><?php _e('Nhân viên', 'qlcv'); ?></option>
                                        <option value="contributor"><?php _e('Cấp quản lý', 'qlcv'); ?></option>
                                    </select>
                                </div>
                                <div class="col-lg-3"></div>
                            <?php
                            } else {
                                echo "<input type='hidden' value='member'>";
                            }
                            ?>
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