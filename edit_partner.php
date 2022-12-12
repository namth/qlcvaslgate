<?php
/*
    Template Name: Sửa thông tin đối tác
*/
$history_link   = $_SERVER['HTTP_REFERER'];

if (is_user_logged_in()) {
    # get edit user data
    if ($_GET['uid'] != "") {
        $this_user = get_user_by('ID', $_GET['uid']);
    } else {
        $this_user = wp_get_current_user();
    }

    # if it have edit action then update user info
    if (
        isset($_POST['post_nonce_field']) &&
        wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')
    ) {

        # get data from the form
        $user_company   = $_POST['user_company'];
        $partner_code   = $_POST['partner_code'];
        $type_of_client = $_POST['type_of_client'];
        $partner_vip    = $_POST['partner_vip'];
        $first_name     = $_POST['first_name'];
        $last_name      = $_POST['last_name'];
        $user_email     = $_POST['user_email'];
        $phone_number   = $_POST['phone_number'];
        $address        = $_POST['address'];
        $country        = $_POST['country'];
        $note           = $_POST['note'];
        $email_cc       = $_POST['email_cc'];
        $email_bcc      = $_POST['email_bcc'];
        $roles          = $_POST['roles'];
        $display_name   = $first_name . " " . $last_name;
        $history_link   = $_POST['history_link'];
        $worked         = $_POST['worked'];

        # check partner code
        $current_partner_code = get_field('partner_code' , 'user_' . $this_user->ID);
        if ( ($current_partner_code != $partner_code) && search_partner($partner_code) ) {
            $error_partner_code = true;
            $error_message = "<b>Trùng mã đối tác</b>";
        }

        # edit user
        $args = array(
            'ID'            => $this_user->ID,
            'user_email'    => $user_email,
            'first_name'    => $first_name,
            'last_name'     => $last_name,
            'display_name'  => $display_name,
            'description'   => $note,
        );

        $new_partner = wp_update_user($args);

        # if it's success create new user,
        # add more info throught custom fields
        if (!is_wp_error($new_partner) && !$error_partner_code ) {
            
            $theUser = new WP_User($new_partner);
            
            # add new roles if itn't exist.
            foreach ($roles as $key => $value) {
                if ( !in_array($value, $this_user->roles) ) {
                    $theUser->add_role( $value );
                }
            }
            # remove roles 
            foreach ($this_user->roles as $key => $value) {
                if ( !in_array($value, $roles) ) {
                    $theUser->remove_role( $value );
                }
            }
            
            update_field('field_607a4fb37b7e0', $partner_code, 'user_' . $new_partner); # partner code
            update_field('field_600d31f4060eb', $user_company, 'user_' . $new_partner); # user_company
            update_field('field_60a3cbacb1330', $type_of_client, 'user_' . $new_partner); # type_of_client
            update_field('field_61cd79951653e', $partner_vip, 'user_' . $new_partner); # partner_vip
            update_field('field_600d3211060ec', $phone_number, 'user_' . $new_partner); # phone number
            update_field('field_600d323d060ee', $address, 'user_' . $new_partner); # address
            update_field('field_6037200ec98cc', $country, 'user_' . $new_partner); # country
            update_field('field_6039b28e2ba07', $email_cc, 'user_' . $new_partner); # email_cc
            update_field('field_609a038489e8c', $email_bcc, 'user_' . $new_partner); # email_bcc
            update_field('field_61cd79bf1653f', $worked, 'user_' . $new_partner); # đã chốt hoặc tiềm năng

            $thongbao = '<div class="alert alert-success" role="alert">
                                <i class="fa fa-check"></i> Đã sửa thông tin thành công
                            </div>';
            
            wp_redirect( $history_link );
            exit;
        } else {
            $thongbao = '<div class="alert alert-danger" role="alert">
                                <i class="zmdi zmdi-info"></i> Có lỗi xảy ra, xin vui lòng kiểm tra lại. '. $error_message .'
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

                    // print_r($this_user);
                    $ten_cong_ty    = get_field('ten_cong_ty', 'user_' . $this_user->ID);
                    $so_dien_thoai  = get_field('so_dien_thoai', 'user_' . $this_user->ID);
                    $dia_chi        = get_field('dia_chi', 'user_' . $this_user->ID);
                    $quoc_gia       = get_field('quoc_gia', 'user_' . $this_user->ID);
                    $document       = get_field('document', 'user_' . $this_user->ID);
                    $partner_code   = get_field('partner_code', 'user_' . $this_user->ID);
                    $email_cc       = get_field('email_cc', 'user_' . $this_user->ID);
                    $email_bcc      = get_field('email_bcc', 'user_' . $this_user->ID);
                    $type_of_client = get_field('type_of_client', 'user_' . $this_user->ID);
                    $partner_vip    = get_field('vip', 'user_' . $this_user->ID);
                    $worked         = get_field('worked', 'user_' . $this_user->ID);

                    $role_list      = array(
                        'partner' => 'Đối tác',
                        'foreign_partner' => 'Đối tác nước ngoài',
                    );

                    ?>
                    <div>
                        <form action="#" method="POST" class="row">
                            <div class="col-lg-3 form_title lh45">Email <span class="text-danger">*</span></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="user_email" value="<?php echo $this_user->user_email; ?>"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45">Tên công ty/tổ chức <span class="text-danger">*</span></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="user_company" value="<?php echo $ten_cong_ty; ?>"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45">Mã đối tác <span class="text-danger">*</span></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="partner_code" value="<?php echo $partner_code; ?>"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45">Vai trò <span class="text-danger">*</span></div>
                            <div class="col-lg-9 col-12 mb-20">
                                <?php
                                foreach ($role_list as $key => $value) {
                                    if (in_array($key, $this_user->roles)) {
                                        $checked = "checked";
                                    } else $checked = "";

                                    echo '<label for="roles" class="inline">
                                                <input type="checkbox" name="roles[]" value="' . $key . '" ' . $checked . '> ' . $value . '
                                            </label>';
                                }
                                ?>

                            </div>

                            <div class="col-lg-3 form_title lh45">Họ và tên <span class="text-danger">*</span></div>
                            <div class="col-lg-3 col-12 mb-20"><input type="text" class="form-control" name="first_name" placeholder="Họ" value="<?php echo $this_user->user_firstname; ?>"></div>
                            <div class="col-lg-3 col-12 mb-20"><input type="text" class="form-control" name="last_name" placeholder="Tên" value="<?php echo $this_user->user_lastname; ?>"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title mb-10 mt-10">Trạng thái</div>
                            <div class="col-lg-3 col-12 mb-20 mt-10">
                                <div class="adomx-checkbox-radio-group inline">
                                    <label class="adomx-radio-2"><input type="radio" name="worked" value="1" <?php if ($worked) echo "checked"; ?>> <i class="icon"></i> Đã chốt</label>
                                    <label class="adomx-radio-2"><input type="radio" name="worked" value="0" <?php if (!$worked) echo "checked"; ?>> <i class="icon"></i> Tiềm năng</label>
                                </div>
                            </div>
                            <div class="col-lg-6"></div>

                            <div class="col-lg-3 form_title lh45">Phân loại</div>
                            <div class="col-lg-6 col-12 mb-20">
                                <select class="form-control mb-20" name="type_of_client">
                                    <?php
                                    if ($type_of_client) {
                                        echo '<option value="' . $type_of_client . '">' . $type_of_client . '</option>';
                                    } else {
                                        echo '<option value="">-- Phân loại --</option>';
                                    }

                                    $partner_list_type = explode(PHP_EOL, get_field('partner_list_type', 'option'));
                                    // print_r($partner_list_type);

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

                            <div class="col-lg-3 form_title lh45">Cấp độ</div>
                            <div class="col-lg-6 col-12 mb-20">
                                <select class="form-control mb-20" name="partner_vip">
                                    <?php
                                    if ($partner_vip) {
                                        echo '<option value="' . $partner_vip . '">' . $partner_vip . '</option>';
                                    } else {
                                        echo '<option value="">-- Phân loại VIP --</option>';
                                    }

                                    $partner_vip_type = explode(PHP_EOL, get_field('partner_vip_type', 'option'));
                                    // print_r($partner_vip_type);

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

                            <div class="col-lg-3 form_title lh45">Số điện thoại</div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="phone_number" value="<?php echo $so_dien_thoai; ?>"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45">Địa chỉ</div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="address" value="<?php echo $dia_chi; ?>"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45">Quốc gia</div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="country" value="<?php echo $quoc_gia; ?>"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45">Email CC</div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="email_cc" value="<?php echo $email_cc; ?>"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45">Email BCC</div>
                            <div class="col-lg-6 col-12 mb-20">
                                <input type="text" class="form-control" name="email_bcc" value="<?php echo $email_bcc; ?>">
                                <span class="form-help-text">Mỗi email cách nhau dấu ","</span>
                            </div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45">Ghi chú</div>
                            <div class="col-lg-6 col-12 mb-20"><textarea class="form-control" placeholder="Thông tin bổ sung" name="note"><?php echo get_user_meta($this_user->ID, 'description', true); ?></textarea></div>
                            <div class="col-lg-3"></div>

                            <?php
                            echo '<input type="hidden" name="history_link" value="' . $history_link . '">';
                            wp_nonce_field('post_nonce', 'post_nonce_field');
                            ?>

                            <div class="col-lg-3"></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="submit" class="button button-primary" value="Cập nhật"> <a href="javascript:history.go(-1)" class="button button-wikipedia">Huỷ bỏ</a></div>

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