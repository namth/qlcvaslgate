<?php
/*
    Template Name: Sửa thông tin đối tác
*/
$history_link   = $_SERVER['HTTP_REFERER'];
$thongbao = "";

if (is_user_logged_in()) {
    # get edit user data
    if ($_GET['uid'] != "") {
        $this_user = get_user_by('ID', $_GET['uid']);
    } else {
        $this_user = wp_get_current_user();
    }
    # get all data from $this_user
    $_ten_cong_ty    = get_field('ten_cong_ty', 'user_' . $this_user->ID);
    $_so_dien_thoai  = get_field('so_dien_thoai', 'user_' . $this_user->ID);
    $_dia_chi        = get_field('dia_chi', 'user_' . $this_user->ID);
    $_quoc_gia       = get_field('quoc_gia', 'user_' . $this_user->ID);
    $_city           = get_field('city' , 'user_' . $this_user->ID);
    $_is_company     = get_field('is_company' , 'user_' . $this_user->ID);
    $_staffs         = get_field('staffs' , 'user_' . $this_user->ID);
    $_vietnam_company= get_field('vietnam_company' , 'user_' . $this_user->ID);
    $_languages      = get_field('languages' , 'user_' . $this_user->ID);
    $_document       = get_field('document', 'user_' . $this_user->ID);
    $_partner_code   = get_field('partner_code', 'user_' . $this_user->ID);
    $_email_cc       = get_field('email_cc', 'user_' . $this_user->ID);
    $_email_bcc      = get_field('email_bcc', 'user_' . $this_user->ID);
    $_type_of_client = get_field('type_of_client', 'user_' . $this_user->ID);
    $_detail_client_type = get_field('detail_client_type', 'user_' . $this_user->ID);
    $_partner_vip    = get_field('vip', 'user_' . $this_user->ID);
    $_worked         = get_field('worked', 'user_' . $this_user->ID);
    $_fdi            = get_field('fdi', 'user_' . $this_user->ID);
    $_fdi_countries  = get_field('fdi_countries', 'user_' . $this_user->ID);
    $_source         = get_field('source', 'user_' . $this_user->ID);

    # get user website from user meta
    $_website = get_user_meta($this_user->ID, 'website', true);

    # if it have edit action then update user info
    if (
        isset($_POST['post_nonce_field']) &&
        wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')
    ) {

        # init changed label array
        $changed_label = array();
        # get data from the form
        $user_company   = $_POST['user_company'];
        $partner_code   = $_POST['partner_code'];
        $user_code_select = $_POST['user_code_select'];
        $user_code_exists = $_POST['user_code_exists'];
        $type_of_client = $_POST['type_of_client'];
        $partner_vip    = $_POST['partner_vip'];
        $first_name     = $_POST['first_name'];
        $last_name      = $_POST['last_name'];
        $user_email     = $_POST['user_email'];
        $user_website   = $_POST['user_website'];
        $phone_number   = $_POST['phone_number'];
        $address        = $_POST['address'];
        $country        = $_POST['country'];
        $city           = $_POST['city']; #
        $vietnam_company= $_POST['vietnam_company'];
        if ($_POST['languages']) {
            $languages      = implode(", ", $_POST['languages']);
        }
        $note           = $_POST['note'];
        $phan_loai      = $_POST['phan_loai'];
        if ($phan_loai) {
            if (isset($_POST['staffs'])) {
                $staffs     = implode("|", $_POST['staffs']);
            }
        }
        
        if ($_POST['detail_client_type']) {
            $detail_client_type = implode(", ", $_POST['detail_client_type']);
        }
        $fdi            = $_POST['fdi'];
        if ($_POST['fdi_countries']) {
            $fdi_countries  = implode(", ", $_POST['fdi_countries']);
        }
        $email_cc       = $_POST['email_cc'];
        $email_bcc      = $_POST['email_bcc'];
        $roles          = $_POST['roles'];
        $display_name   = $first_name . " " . $last_name;
        $history_link   = $_POST['history_link'];
        $worked         = $_POST['worked'];
        $nguon_dau_viec = $_POST['nguon_dau_viec'];

        # check partner code
        $current_partner_code = get_field('partner_code' , 'user_' . $this_user->ID);
        if ( ($current_partner_code != $partner_code) ) {
            # if user_code_select is 1, get from user_code_exists
            if ($user_code_select) {
                # if "phan loai" is 0, then get from user_code_exists, else set error_partner_code = true with message "Tổ chức không được dùng chung mã đối tác"
                if ($phan_loai == 0) {
                    $partner_code = get_field('partner_code', 'user_' . $user_code_exists);
                } else {
                    $error_partner_code = true;
                    $error_message = __("<b>Tổ chức không được dùng chung mã đối tác</b>", 'qlcv');
                }
            } else {
                if (search_partner($partner_code)) {
                    $duplicateID = search_partner($partner_code);
                    $error_partner_code = true;
                    $error_message = __("<b>Trùng mã đối tác</b>", 'qlcv') . $duplicateID;
                }    
            }
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
            # check if website is changed, then update new website to this user
            if ($_website != $user_website) {
                update_user_meta($new_partner, 'website', $user_website);
                $changed_label[] = __('website', 'qlcv');
            }
            # check if partner code is changed, then update new partner code to this user
            if ($_partner_code != $partner_code) {
                update_field('field_607a4fb37b7e0', $partner_code, 'user_' . $new_partner); # partner code
                $changed_label[] = __('mã đối tác', 'qlcv');

                # if "phan loai" is 1, then update partner code to all staffs
                if ($phan_loai == 1) {
                    $staffs_arr = explode("|", $staffs);
                    if ($staffs_arr) {
                        foreach ($staffs_arr as $staff) {
                            # get partner code of staffs, if it's the same with old partner code, then update new partner code
                            $staff_partner_code = trim(get_field('partner_code', 'user_' . $staff));
                            if ( !$staff_partner_code || ($staff_partner_code == $_partner_code)) {
                                update_field('field_607a4fb37b7e0', $partner_code, 'user_' . $staff); # partner code
                            }
                        }
                    }
                }
            }
            # check if user company is changed, then update new user company to this user
            if ($_ten_cong_ty != $user_company) {
                update_field('field_600d31f4060eb', $user_company, 'user_' . $new_partner); # user_company
                $changed_label[] = __('tên công ty', 'qlcv');
            }
            # check if type of client is changed, then update new type of client to this user
            if ($_type_of_client != $type_of_client) {
                update_field('field_60a3cbacb1330', $type_of_client, 'user_' . $new_partner); # type_of_client
                $changed_label[] = __('phân loại chính', 'qlcv');
            }
            # check if partner vip is changed, then update new partner vip to this user
            if ($_partner_vip != $partner_vip) {
                update_field('field_61cd79951653e', $partner_vip, 'user_' . $new_partner); # partner_vip
                $changed_label[] = __('thành cấp độ ', 'qlcv') . '"' . $partner_vip . '"';
            }
            # check if phone number is changed, then update new phone number to this user
            if ($_so_dien_thoai != $phone_number) {
                update_field('field_600d3211060ec', $phone_number, 'user_' . $new_partner); # phone number
                $changed_label[] = __('số điện thoại', 'qlcv');
            }
            # check if address is changed, then update new address to this user
            if ($_dia_chi != $address) {
                update_field('field_600d323d060ee', $address, 'user_' . $new_partner); # address
                $changed_label[] = __('địa chỉ', 'qlcv');
            }
            # check if country is changed, then update new country to this user
            if ($_quoc_gia != $country) {
                update_field('field_6037200ec98cc', $country, 'user_' . $new_partner); # country
                $changed_label[] = __('quốc gia', 'qlcv');
            }
            # check if city is changed, then update new city to this user
            if ($_city != $city) {
                update_field('field_65a5625b5eb0e', $city, 'user_' . $new_partner); # city
                $changed_label[] = __('thành phố', 'qlcv');
            }
            # check if vietnam company is changed, then update new vietnam company to this user
            if ($_vietnam_company != $vietnam_company) {
                update_field('field_65a562035eb0c', $vietnam_company, 'user_' . $new_partner); # $vietnam_company
                $changed_label[] = __('có công ty tại Việt Nam', 'qlcv');
            }
            # check if languages is changed, then update new languages to this user
            if ($languages && ($_languages != $languages)) {
                update_field('field_65a5622f5eb0d', $languages, 'user_' . $new_partner); # $languages
                $changed_label[] = __('ngôn ngữ giao tiếp', 'qlcv');
            }
            # check if email cc is changed, then update new email cc to this user
            if ($_email_cc != $email_cc) {
                update_field('field_6039b28e2ba07', $email_cc, 'user_' . $new_partner); # email_cc
                $changed_label[] = __('email CC', 'qlcv');
            }
            # check if email bcc is changed, then update new email bcc to this user
            if ($_email_bcc != $email_bcc) {
                update_field('field_609a038489e8c', $email_bcc, 'user_' . $new_partner); # email_bcc
                $changed_label[] = __('email BCC', 'qlcv');
            }
            # check if worked is changed, then update new worked to this user
            if ($_worked != $worked) {
                update_field('field_61cd79bf1653f', $worked, 'user_' . $new_partner); # đã chốt hoặc tiềm năng
                $changed_label[] = __('trạng thái', 'qlcv');
            }
            # check if nguon dau viec is changed, then update new nguon dau viec to this user
            if ($_source != $nguon_dau_viec) {
                update_field('field_65de936686343', $nguon_dau_viec, 'user_' . $new_partner); # nguồn đến từ đâu
                $changed_label[] = __('nguồn đầu việc', 'qlcv');
            }
            # check if detail client type is changed, then update new detail client type to this user
            if ($_detail_client_type != $detail_client_type) {
                update_field('field_65dcc97fa77b9', $detail_client_type, 'user_' . $new_partner); # chuyên ngành đối tác
                $changed_label[] = __('chuyên ngành đối tác', 'qlcv');
            }
            # check if fdi is changed, then update new fdi to this user
            if (($_fdi != $fdi)) {
                update_field('field_65ddcd2141e6f', $fdi, 'user_' . $new_partner); # có vốn fdi không
                $changed_label[] = __('vốn fdi', 'qlcv');
            }
            if ($fdi && $fdi_countries) {
                update_field('field_65ddcd7941e70', $fdi_countries, 'user_' . $new_partner); # quốc gia đầu tư
                $changed_label[] = __('quốc gia đầu tư', 'qlcv');
            }
            # check if phan loai is changed, then update new phan loai to this user
            if ($_is_company != $phan_loai) {
                update_field('field_65a4acb5db9c6', $phan_loai, 'user_' . $new_partner); # phân loại
                $changed_label[] = __('loại tài khoản', 'qlcv');
            }
            # check if staffs is changed, then update new staffs to this user
            if ($_staffs != $staffs) {
                update_field('field_65a4acebdb9c7', $staffs, 'user_' . $new_partner); # staffs
                $changed_label[] = __('danh sách thành viên công ty', 'qlcv');
            }

            # if $change_label is not empty, create log, change partner info
            if (!empty($changed_label)) {
                $content_log = __('Đã sửa thông tin ', 'qlcv') . implode(", ", $changed_label);
                asl_create_log($content_log, null, $new_partner);
            }
            
            $thongbao = '<div class="alert alert-success" role="alert">
                                <i class="fa fa-check"></i> ' . __('Đã sửa thông tin thành công', 'qlcv') . '
                            </div>';
            
            wp_redirect( $history_link );
            exit;
        } else {
            $thongbao = '<div class="alert alert-danger" role="alert">
                                <i class="zmdi zmdi-info"></i> ' . __('Có lỗi xảy ra, xin vui lòng kiểm tra lại.','qlcv') . ' '. $error_message .'
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
                    

                    $role_list      = array(
                        'partner' => 'Đối tác',
                        'foreign_partner' => 'Đối tác nước ngoài',
                    );

                    ?>
                    <div>
                        <form action="#" method="POST" class="row">
                            <div class="col-lg-3 form_title text-left text-lg-right"><?php _e('Loại tài khoản', 'qlcv'); ?> <span class="text-danger">*</span></div>
                            <div class="col-lg-6 col-12 mb-20">
                                <div class="adomx-checkbox-radio-group inline">
                                    <?php 
                                        $options = [
                                            0 => __('Cá nhân', 'qlcv'), 
                                            1 => __('Tổ chức', 'qlcv')
                                        ];
                                        $style = $_is_company?'display: block;':'display: none;';

                                        foreach ($options as $key => $value) {
                                            $checked = ($key==$_is_company)?"checked":"";
                                            echo '<label class="adomx-radio-2"><input type="radio" name="phan_loai" value="' . $key . '" ' . $checked . '> <i class="icon"></i> ' . $value . '</label>';
                                        }
                                    ?>
                                </div>
                            </div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45 text-lg-right phanloai"><?php _e('Tên công ty/tổ chức', 'qlcv'); ?> <span class="text-danger">*</span></div>
                            <div class="col-lg-6 col-12 mb-20 phanloai"><input type="text" class="form-control" name="user_company" value="<?php echo $_ten_cong_ty; ?>"></div>
                            <div class="col-lg-3 phanloai"></div>

                            <div class="col-lg-3 form_title text-lg-right phanloai"><?php _e('Đã có công ty tại Việt Nam?', 'qlcv'); ?> <span class="text-danger">*</span></div>
                            <div class="col-lg-6 col-12 mb-20 phanloai">
                                <div class="adomx-checkbox-radio-group">
                                    <?php 
                                        $checked = $_vietnam_company?"checked":"";
                                    ?>
                                    <label class="adomx-switch"><input type="checkbox" name="vietnam_company" <?php echo $checked; ?>> <i class="lever"></i></label>
                                </div>
                            </div>
                            <div class="col-lg-3 phanloai"></div>

                            <div class="col-lg-3 form_title text-lg-right phanloai"><?php _e('Phân loại đầu tư', 'qlcv'); ?> <span class="text-danger">*</span></div>
                            <div class="col-lg-6 col-12 mb-20 phanloai">
                                <div class="adomx-checkbox-radio-group inline">
                                    <?php 
                                        $options = [
                                            0 => __('100% Việt Nam', 'qlcv'), 
                                            1 => __('Có vốn đầu tư nước ngoài (FDI)', 'qlcv')
                                        ];
                                        $style = $_fdi?'display: block;':'display: none;';

                                        foreach ($options as $key => $value) {
                                            $checked = ($key==$_fdi)?"checked":"";
                                            echo '<label class="adomx-radio-2"><input type="radio" name="fdi" value="' . $key . '" ' . $checked . '> <i class="icon"></i> ' . $value . '</label>';
                                        }
                                    ?>
                                </div>
                                <div style="<?php echo $style; ?> margin-top: 15px;" id="fdi">
                                    <small>Tại nước nào?</small>
                                    <select class="form-control select2-tags mb-20" multiple="" name="fdi_countries[]">
                                        <?php
                                        $list_country = explode(PHP_EOL, get_field('list_country', 'option'));
                                        $list_selected = explode(", ", $_fdi_countries);
    
                                        if ($list_country) {
                                            foreach ($list_country as $country) {
                                                $country = trim($country);
                                                $selected = is_array($list_selected) && in_array($country, $list_selected)?"selected":"";
                                                echo "<option value='" . $country . "' " . $selected . ">" . $country . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 phanloai"></div>

                            <div class="col-lg-3 form_title text-lg-right phanloai mt-20 lh45"><?php _e('Thành viên tổ chức', 'qlcv'); ?> <span class="text-danger">*</span></div>
                            <div class="col-lg-6 col-12 mb-20 phanloai">
                                <div style="<?php echo $_is_company?"":"display: none;"; ?> margin-top: 15px;" id="phanloai">
                                    <small>Thêm danh sách thành viên công ty vào ô dưới đây</small>
                                    <select class="form-control select2-tags mb-20" multiple="" name="staffs[]">
                                        <?php
                                        $args   = array(
                                            'role__in'      => array('partner', 'foreign_partner'),
                                        );
                                        $query = get_users($args);
    
                                        if ($query) {
                                            $staff_arr = explode("|", $_staffs);
                                            foreach ($query as $user) {
                                                $selected = in_array($user->ID, $staff_arr)?"selected":"";
                                                echo "<option value='" . $user->ID . "' " . $selected . ">" . $user->display_name . " (" . $user->user_email . ")</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 phanloai"></div>

                            <div class="col-lg-3 form_title lh45 text-lg-right phanloai"><?php _e('Trang web', 'qlcv'); ?></div>
                            <div class="col-lg-6 col-12 mb-20 phanloai"><input type="text" class="form-control" name="user_website" value="<?php echo $_website; ?>"></div>
                            <div class="col-lg-3 phanloai"></div>

                            <div class="col-lg-3 form_title lh45 text-lg-right"><?php _e('Mã đối tác', 'qlcv'); ?> <span class="text-danger">*</span></div>
                            <div class="col-lg-6 col-12 mb-20">
                                <div class="adomx-checkbox-radio-group inline mb-10">
                                    <label class="adomx-radio-2"><input type="radio" name="user_code_select" value="0" checked > <i class="icon"></i> <?php _e('Mã hiện tại', 'qlcv'); ?></label>
                                    <label class="adomx-radio-2"><input type="radio" name="user_code_select" value="1"> <i class="icon"></i> <?php _e('Chọn trong danh sách', 'qlcv'); ?></label>
                                </div>
                                <input id="user_code_input" type="text" class="form-control" name="partner_code" value="<?php echo $_partner_code; ?>">
                                <div id="user_code_select" style="display: none;">
                                    <select class="form-control select2-tags mb-20" name="user_code_exists">
                                        <option value="">-- <?php _e('Chọn mã đối tác trong hệ thống') ?> --</option>
                                        <?php
                                        $list_selected = $_POST['user_code_select'];
                                        $args   = array(
                                            'role__in'      => array('partner', 'foreign_partner'),
                                        );
                                        $query = get_users($args);
                    
                                        if ($query) {
                                            foreach ($query as $user) {
                                                # get user_code
                                                $user_code = get_field('partner_code', 'user_' . $user->ID);
                                                # get company name
                                                $company_name = get_field('ten_cong_ty', 'user_' . $user->ID);
                                                # if have not company name, use display name
                                                $display_name = $company_name?$company_name:$user->display_name;
                                                $selected = ( $user->ID == $list_selected )?"selected":"";
                                                echo "<option value='" . $user->ID . "' " . $selected . ">" . $display_name . " (" . $user_code . ")</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title text-lg-right"><?php _e('Vai trò', 'qlcv'); ?> <span class="text-danger">*</span></div>
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

                            <div class="col-lg-3 form_title lh45 text-lg-right"><?php _e('Người liên hệ', 'qlcv'); ?> <span class="text-danger">*</span></div>
                            <div class="col-lg-3 col-12 mb-20"><input type="text" class="form-control" name="first_name" placeholder="<?php _e('Họ', 'qlcv'); ?>" value="<?php echo $this_user->user_firstname; ?>"></div>
                            <div class="col-lg-3 col-12 mb-20"><input type="text" class="form-control" name="last_name" placeholder="<?php _e('Tên', 'qlcv'); ?>" value="<?php echo $this_user->user_lastname; ?>"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45 text-lg-right">Email <span class="text-danger">*</span></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="user_email" value="<?php echo $this_user->user_email; ?>"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45 text-lg-right">Email CC</div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="email_cc" value="<?php echo $_email_cc; ?>"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45 text-lg-right">Email BCC</div>
                            <div class="col-lg-6 col-12 mb-20">
                                <input type="text" class="form-control" name="email_bcc" value="<?php echo $_email_bcc; ?>">
                                <span class="form-help-text"><?php _e('Mỗi email cách nhau dấu ","', 'qlcv'); ?></span>
                            </div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title mb-10 mt-10 text-lg-right"><?php _e('Trạng thái', 'qlcv'); ?></div>
                            <div class="col-lg-3 col-12 mb-20 mt-10">
                                <div class="adomx-checkbox-radio-group inline">
                                    <label class="adomx-radio-2"><input type="radio" name="worked" value="1" <?php if ($_worked) echo "checked"; ?>> <i class="icon"></i> <?php _e('Đã chốt', 'qlcv'); ?></label>
                                    <label class="adomx-radio-2"><input type="radio" name="worked" value="0" <?php if (!$_worked) echo "checked"; ?>> <i class="icon"></i> <?php _e('Tiềm năng', 'qlcv'); ?></label>
                                </div>
                            </div>
                            <div class="col-lg-6"></div>

                            <div class="col-lg-3 form_title text-left text-lg-right mb-10 mt-10"><?php _e('Nguồn', 'qlcv'); ?></div>
                            <div class="col-lg-3 col-12 mb-20 mt-10">
                                <div class="adomx-checkbox-radio-group inline">
                                    <?php 
                                        $terms = get_terms(array(
                                            'taxonomy' => 'post_tag',
                                            'hide_empty' => false,
                                        ));
                                        
                                        foreach ($terms as $value) {
                                            $checked = ($value->name==$_source)?"checked":"";
                                            echo '<label class="adomx-radio-2"><input type="radio" name="nguon_dau_viec" value="' . $value->name . '" ' . $checked . '> <i class="icon"></i> ' . $value->name . '</label>';
                                        }
                                    ?>
                                </div>
                            </div>
                            <div class="col-lg-6"></div>

                            <div class="col-lg-3 form_title lh45 text-lg-right"><?php _e('Phân loại chính', 'qlcv'); ?></div>
                            <div class="col-lg-6 col-12 mb-20">
                                <select class="form-control mb-20" name="type_of_client">
                                    <?php
                                    if ($_type_of_client) {
                                        echo '<option value="' . $_type_of_client . '">' . $_type_of_client . '</option>';
                                    } else {
                                        echo '<option value="">-- ' . __('Phân loại', 'qlcv') . ' --</option>';
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

                            <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Chuyên ngành', 'qlcv'); ?></div>
                            <div class="col-lg-6 col-12 mb-20">
                                <select class="form-control select2-tags mb-20" multiple="" name="detail_client_type[]">
                                    <?php 
                                        $list_other_jobs = get_term_children(10, 'group');
                                        $list_selected = explode(", ", get_field('detail_client_type', 'user_' . $this_user->ID));

                                        foreach ($list_other_jobs as $jobid) {
                                            $term = get_term($jobid, 'group');
                                            $selected = is_array($list_selected) && in_array($term->name, $list_selected)?"selected":"";
                                            echo "<option value='" . $term->name . "' " . $selected . ">" . $term->name . "</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45 text-lg-right"><?php _e('Cấp độ', 'qlcv'); ?></div>
                            <div class="col-lg-6 col-12 mb-20">
                                <select class="form-control mb-20" name="partner_vip">
                                    <?php
                                    if ($_partner_vip) {
                                        echo '<option value="' . $_partner_vip . '">' . $_partner_vip . '</option>';
                                    } else {
                                        echo '<option value="">-- ' . __('Phân loại VIP', 'qlcv') . ' --</option>';
                                    }

                                    $partner_vip_type = explode(PHP_EOL, get_field('partner_vip_type', 'option'));
                                    // print_r($partner_vip_type);

                                    foreach ($partner_vip_type as $value) {
                                        $value = trim($value);
                                        if ($value && $value != $_partner_vip) {
                                            echo '<option value="' . $value . '">' . $value . '</option>';
                                        }
                                    }
                                    ?>
                                    
                                </select>
                            </div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45 text-lg-right"><?php _e('Số điện thoại', 'qlcv'); ?></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="phone_number" value="<?php echo $_so_dien_thoai; ?>"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45 text-lg-right"><?php _e('Địa chỉ', 'qlcv'); ?></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="address" value="<?php echo $_dia_chi; ?>"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45 text-lg-right"><?php _e('Quốc gia', 'qlcv'); ?> <span class="text-danger">*</span></div>
                            <div class="col-lg-6 col-12 mb-20">
                                <select class="form-control select2-tags mb-20" name="country">
                                    <option value="">-- <?php _e('Chọn quốc gia') ?> --</option>
                                    <?php
                                        $list_country = explode(PHP_EOL, get_field('list_country', 'option'));
    
                                        if ($list_country) {
                                            foreach ($list_country as $country) {
                                                $country = trim($country);
                                                $selected = ($country == $_quoc_gia)?"selected":"";
                                                echo "<option value='" . $country . "' " . $selected . ">" . $country . "</option>";
                                            }
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45 text-lg-right"><?php _e('Thành phố', 'qlcv'); ?> <span class="text-danger">*</span></div>
                            <div class="col-lg-6 col-12 mb-20">
                                <input type="text" class="form-control" name="city" value="<?php echo $_city; ?>">
                            </div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45 text-lg-right"><?php _e('Ngôn ngữ giao tiếp', 'qlcv'); ?></div>
                            <div class="col-lg-6 col-12 mb-20">
                                <select class="form-control select2-tags mb-20" multiple="" name="languages[]">
                                    <?php
                                        $list_languages = explode(PHP_EOL, get_field('languages', 'option'));
                                        $list_selected = explode(", ", $_languages);
                                        
                                        if ($list_languages) {
                                            foreach ($list_languages as $language) {
                                                $language = trim($language);
                                                $selected = is_array($list_selected) && in_array( $language, $list_selected )?"selected":"";
                                                
                                                echo "<option value='" . $language . "' " . $selected . ">" . $language . "</option>";
                                            }
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45 text-lg-right"><?php _e('Ghi chú', 'qlcv'); ?></div>
                            <div class="col-lg-6 col-12 mb-20"><textarea class="form-control" placeholder="<?php _e('Thông tin bổ sung', 'qlcv'); ?>" name="note"><?php echo get_user_meta($this_user->ID, 'description', true); ?></textarea></div>
                            <div class="col-lg-3"></div>

                            <?php
                            echo '<input type="hidden" name="history_link" value="' . $history_link . '">';
                            wp_nonce_field('post_nonce', 'post_nonce_field');
                            ?>

                            <div class="col-lg-3"></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="submit" class="button button-primary" value="<?php _e('Cập nhật', 'qlcv'); ?>"> <a href="javascript:history.go(-1)" class="button button-wikipedia"><?php _e('Huỷ bỏ', 'qlcv'); ?></a></div>

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