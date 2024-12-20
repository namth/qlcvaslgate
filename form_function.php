<?php
/* 
* All function for create form addnew, edit, delete partner, customer
*/

function form_addnew_partner($role=null){
    ?>
        <form action="#" method="POST" class="row">
            <?php 
            # if $role is not null, show title form
            if ($role) {
                echo '<div class="col-12 mb-20 notification">
                    <h4>' . __('Nhập thông tin đối tác mới', 'qlcv') . '</h4>
                </div>';
            }
            ?>
            <div class="col-lg-3 form_title text-left text-lg-right"><?php _e('Loại tài khoản', 'qlcv'); ?> <span class="text-danger">*</span></div>
            <div class="col-lg-6 col-12 mb-20">
                <div class="adomx-checkbox-radio-group inline">
                    <?php 
                        $options = [
                            0 => __('Cá nhân', 'qlcv'), 
                            1 => __('Tổ chức', 'qlcv')
                        ];
                        $default = (isset($_POST['phan_loai']))?$_POST['phan_loai']:0;
                        $style = $default?'display: block;':'display: none;';

                        foreach ($options as $key => $value) {
                            $checked = ($key==$default)?"checked":"";
                            echo '<label class="adomx-radio-2"><input type="radio" name="phan_loai" value="' . $key . '" ' . $checked . '> <i class="icon"></i> ' . $value . '</label>';
                        }
                    ?>
                </div>
            </div>
            <div class="col-lg-3"></div>

            <div class="col-lg-3 form_title text-left text-lg-right lh45 phanloai"><?php _e('Tên công ty/tổ chức', 'qlcv'); ?> <span class="text-danger">*</span></div>
            <div class="col-lg-6 col-12 mb-20 phanloai"><input type="text" class="form-control" name="user_company" value="<?php if(isset($_POST['user_company'])) echo $_POST['user_company']; ?>"></div>
            <div class="col-lg-3 phanloai"></div>

            <div class="col-lg-3 form_title text-lg-right phanloai"><?php _e('Đã có công ty tại Việt Nam?', 'qlcv'); ?> <span class="text-danger">*</span></div>
            <div class="col-lg-6 col-12 mb-20 phanloai">
                <div class="adomx-checkbox-radio-group">
                    <?php 
                        $checked = isset($_POST['vietnam_company']) && $_POST['vietnam_company']?"checked":"";
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
                        $default = (isset($_POST['worked']))?$_POST['worked']:0;
                        $style = $default?'display: block;':'display: none;';

                        foreach ($options as $key => $value) {
                            $checked = ($key==$default)?"checked":"";
                            echo '<label class="adomx-radio-2"><input type="radio" name="fdi" value="' . $key . '" ' . $checked . '> <i class="icon"></i> ' . $value . '</label>';
                        }
                    ?>
                </div>
                <div style="<?php echo $style; ?> margin-top: 15px;" id="fdi">
                    <small>Tại nước nào?</small>
                    <select class="form-control select2-tags mb-20" multiple="" name="fdi_countries[]">
                        <?php
                        $list_country = explode(PHP_EOL, get_field('list_country', 'option'));
                        $list_selected = $_POST['fdi_countries'];

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

            <div class="col-lg-3 form_title text-lg-right phanloai mt-20 lh45"><?php _e('Thành viên tổ chức', 'qlcv'); ?> </div>
            <div class="col-lg-6 col-12 mb-20 phanloai">
                <div style="<?php echo $style; ?> margin-top: 15px;" id="phanloai">
                    <small>Thêm danh sách thành viên công ty vào ô dưới đây</small>
                    <select class="form-control select2-tags mb-20" multiple="" name="staffs[]">
                        <?php
                        $list_selected = $_POST['staffs'];
                        $args   = array(
                            'role__in'      => array('partner', 'foreign_partner'),
                        );
                        $query = get_users($args);

                        if ($query) {
                            foreach ($query as $user) {
                                $selected = is_array($list_selected) && in_array( $user->ID, $list_selected )?"selected":"";
                                echo "<option value='" . $user->ID . "' " . $selected . ">" . $user->display_name . " (" . $user->user_email . ")</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-lg-3 phanloai"></div>

            <div class="col-lg-3 form_title lh45 text-lg-right phanloai"><?php _e('Trang web', 'qlcv'); ?></div>
            <div class="col-lg-6 col-12 mb-20 phanloai"><input type="text" class="form-control" name="user_website" value="<?php if(isset($_POST['user_website'])) echo $_POST['user_website']; ?>"></div>
            <div class="col-lg-3 phanloai"></div>

            <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Mã đối tác', 'qlcv'); ?> <span class="text-danger">*</span></div>
            <div class="col-lg-6 col-12 mb-20">
                <div class="adomx-checkbox-radio-group inline mb-10">
                    <label class="adomx-radio-2"><input type="radio" name="user_code_select" value="0" checked > <i class="icon"></i> <?php _e('Tạo mới', 'qlcv'); ?></label>
                    <label class="adomx-radio-2"><input type="radio" name="user_code_select" value="1"> <i class="icon"></i> <?php _e('Chọn trong danh sách', 'qlcv'); ?></label>
                </div>
                <input id="user_code_input" type="text" class="form-control" name="user_code" value="<?php if(isset($_POST['user_code'])) echo $_POST['user_code']; ?>">
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

            <div class="col-lg-3 form_title lh45 text-lg-right"><?php _e('Người liên hệ', 'qlcv'); ?> </div>
            <div class="col-lg-3 col-12 mb-20">
                <input type="text" class="form-control" name="first_name" placeholder="<?php _e('Họ', 'qlcv'); ?>" value="<?php if(isset($_POST['first_name'])) echo $_POST['first_name']; ?>">
            </div>
            <div class="col-lg-3 col-12 mb-20">
                <input type="text" class="form-control" name="last_name" placeholder="<?php _e('Tên', 'qlcv'); ?>" value="<?php if(isset($_POST['last_name'])) echo $_POST['last_name']; ?>">
            </div>
            <div class="col-lg-3"></div>

            <div class="col-lg-3 form_title lh45 text-lg-right">Email <span class="text-danger">*</span></div>
            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="user_email" value="<?php if(isset($_POST['user_email'])) echo $_POST['user_email']; ?>"></div>
            <div class="col-lg-3"></div>

            <div class="col-lg-3 form_title lh45 text-lg-right">Email CC</div>
            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="email_cc" value="<?php if(isset($_POST['email_cc'])) echo $_POST['email_cc']; ?>"></div>
            <div class="col-lg-3"></div>

            <div class="col-lg-3 form_title lh45 text-lg-right">Email BCC</div>
            <div class="col-lg-6 col-12 mb-20">
                <input type="text" class="form-control" name="email_bcc" value="<?php if(isset($_POST['email_bcc'])) echo $_POST['email_bcc']; ?>">
                <span class="form-help-text"><?php _e('Mỗi email cách nhau dấu ","', 'qlcv'); ?></span>
            </div>
            <div class="col-lg-3"></div>

            <div class="col-lg-3 form_title text-left text-lg-right mb-10 mt-10"><?php _e('Trạng thái', 'qlcv'); ?></div>
            <div class="col-lg-3 col-12 mb-20 mt-10">
                <div class="adomx-checkbox-radio-group inline">
                    <?php 
                        $options = [0 => __('Đã chốt', 'qlcv'), 1 => __('Tiềm năng', 'qlcv')];
                        $default = (isset($_POST['worked']))?$_POST['worked']:1;

                        foreach ($options as $key => $value) {
                            $checked = ($key==$default)?"checked":"";
                            echo '<label class="adomx-radio-2"><input type="radio" name="worked" value="' . $key . '" ' . $checked . '> <i class="icon"></i> ' . $value . '</label>';
                        }
                    ?>
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
                            $checked = isset($_POST['nguon_dau_viec']) && ($value->name==$_POST['nguon_dau_viec'])?"checked":"";
                            echo '<label class="adomx-radio-2"><input type="radio" name="nguon_dau_viec" value="' . $value->name . '" ' . $checked . '> <i class="icon"></i> ' . $value->name . '</label>';
                        }
                    ?>
                </div>
            </div>
            <div class="col-lg-6"></div>

            <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Phân loại chính', 'qlcv'); ?></div>
            <div class="col-lg-6 col-12 mb-20">
                <select class="form-control mb-20" name="type_of_client">
                <?php
                    $partner_list_type = explode(PHP_EOL, get_field('partner_list_type', 'option'));

                    foreach ($partner_list_type as $value) {
                        $value = trim($value);
                        $selected = ($value == $_POST['type_of_client'])?"selected":"";
                        if ($value) {
                            echo '<option value="' . $value . '" ' . $selected . '>' . $value . '</option>';
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
                        $list_selected = $_POST['detail_client_type'];

                        foreach ($list_other_jobs as $jobid) {
                            $term = get_term($jobid, 'group');
                            $selected = is_array($list_selected) && in_array($term->name, $list_selected)?"selected":"";
                            echo "<option value='" . $term->name . "' " . $selected . ">" . $term->name . "</option>";
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
                        $selected = ($value==$_POST['partner_vip'])?"selected":"";
                        if ($value) {
                            echo '<option value="' . $value . '" ' . $selected . '>' . $value . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="col-lg-3"></div>

            <?php 
            # if $role is null, show checkbox role, else add a hidden input with value is $role
            if ($role) {
                echo '<input type="hidden" name="role" value="' . $role . '">';
            } else {
                ?>
                <div class="col-lg-3 form_title text-lg-right"><?php _e('Vai trò', 'qlcv'); ?></div>
                <div class="col-lg-6 col-12 mb-20">
                    <!-- <select name="role" class="form-control select2-tags mb-20"> -->
                        <?php 
                            $options = [
                                'partner' => __('Đối tác gửi việc', 'qlcv'), 
                                'foreign_partner' => __('Đối tác nhận việc', 'qlcv')
                            ];
                            $list_selected = isset($_POST['role'])?$_POST['role']:["partner"];

                            foreach ($options as $key => $value) {
                                $checked = is_array($list_selected) && in_array($key, $list_selected)?"checked":"";
                                echo '<label for="roles" class="inline">
                                    <input type="checkbox" name="role[]" value="' . $key . '" ' . $checked . '> ' . $value . '
                                </label>';
                            }
                        ?>
                    <!-- </select> -->
                </div>
                <div class="col-lg-3"></div>
                <?php
            }
            ?>

            <div class="col-lg-3 form_title lh45 text-lg-right"><?php _e('Số điện thoại', 'qlcv'); ?></div>
            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="phone_number" value="<?php if(isset($_POST['phone_number'])) echo $_POST['phone_number']; ?>"></div>
            <div class="col-lg-3"></div>

            <div class="col-lg-3 form_title lh45 text-lg-right"><?php _e('Địa chỉ', 'qlcv'); ?></div>
            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="address" value="<?php if(isset($_POST['address'])) echo $_POST['address']; ?>"></div>
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
                                $selected = ($country == $_POST['country'])?"selected":"";
                                echo "<option value='" . $country . "' " . $selected . ">" . $country . "</option>";
                            }
                        }
                    ?>
                </select>
            </div>
            <div class="col-lg-3"></div>

            <div class="col-lg-3 form_title lh45 text-lg-right"><?php _e('Thành phố', 'qlcv'); ?> <span class="text-danger">*</span></div>
            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="city" value="<?php if(isset($_POST['city'])) echo $_POST['city']; ?>"></div>
            <div class="col-lg-3"></div>

            <div class="col-lg-3 form_title lh45 text-lg-right"><?php _e('Ngôn ngữ giao tiếp', 'qlcv'); ?></div>
            <div class="col-lg-6 col-12 mb-20">
                <select class="form-control select2-tags mb-20" multiple="" name="languages[]">
                    <?php
                        $languages = explode(PHP_EOL, get_field('languages', 'option'));
                        $list_selected = $_POST['languages'];
                        
                        if ($languages) {
                            foreach ($languages as $language) {
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
            <div class="col-lg-6 col-12 mb-20"><textarea class="form-control" placeholder="Thông tin bổ sung" name="note"><?php if(isset($_POST['note'])) echo $_POST['note']; ?></textarea></div>
            <div class="col-lg-3"></div>

            <?php
            wp_nonce_field('post_nonce', 'post_nonce_field');
            ?>

            <div class="col-lg-3"></div>
            <div class="col-lg-6 col-12 mb-20"><input type="submit" class="button button-primary" value="<?php _e('Tạo mới', 'qlcv'); ?>"></div>

        </form>
    <?php
}

/*  Process add new partner 
*  @param $args: array of data from form with following keys:
*  company_name
*  company_website
*  user_code
*  first_name
*  last_name
*  user_email
*  email_cc
*  email_bcc
*  phone_number
*  address
*  country
*  city
*  vietnam_company
*  languages
*  note
*  phan_loai
*  detail_client_type
*  fdi
*  fdi_countries
*  role
*  worked
*  nguon_dau_viec
*  staffs
*  type_of_client
*  partner_vip
*/
function process_addnew_partner($input) {
    # initialize variables
    $error_partner_code = false;

    # get data from form
    $company_name   = $input['user_company'];
    $company_website= $input['user_website'];
    $user_code      = $input['user_code'];
    $user_code_select= $input['user_code_select'];
    $user_code_exists= $input['user_code_exists'];
    $first_name     = $input['first_name'];
    $last_name      = $input['last_name'];
    $user_email     = $input['user_email'];
    $phone_number   = $input['phone_number'];
    $address        = $input['address'];
    $country        = $input['country'];
    $note           = $input['note'];
    $link_onedrive  = $input['link_onedrive'];
    $role           = $input['role'];
    $type_of_client = $input['type_of_client'];
    $display_name   = trim($first_name . " " . $last_name);
    $user_pass      = 'd1412@pass';

    $worked         = $input['worked'];
    $nguon_dau_viec = $input['nguon_dau_viec'];
    $partner_vip    = $input['partner_vip'];
    $email_cc       = $input['email_cc'];
    $email_bcc      = $input['email_bcc'];
    $city           = $input['city']; #
    $vietnam_company= $input['vietnam_company'];

    $phan_loai      = $input['phan_loai'];
    $fdi            = $input['fdi'];

    # if user_code_select is 1, get from user_code_exists
    if ($user_code_select) {
        $user_code = get_field('partner_code', 'user_' . $user_code_exists);
    } else {
        if (search_partner($user_code)) {
            $error_partner_code = true;
            $error_message = __("<b>Trùng mã đối tác</b>", 'qlcv');
        }    
    }

    # if have not $role, then set $error_partner_code to true with message: "Chưa chọn vai trò"
    if (!$role) {
        $error_partner_code = true;
        $error_message = __("<b>Chưa chọn vai trò</b>", 'qlcv');
    } else {
        # pop one role name from array
        if (is_array($role)) {
            $role_name = array_pop($role);
        } else {
            $role_name = $role;
        }
    }

    # data processing before insert to database
    if ($input['languages']) {
        $languages      = implode(", ", $input['languages']);
    }
    if ($input['detail_client_type']) {
        $detail_client_type = implode(", ", $input['detail_client_type']);
    }
    if ($input['fdi_countries']) {
        $fdi_countries  = implode(", ", $input['fdi_countries']);
    }

    if ($phan_loai) {
        if ($input['staffs']) {
            $staffs     = implode("|", $input['staffs']);
        }
    }

    # if haven't display name, use email as display name
    if (!$display_name) {
        $display_name = $user_email;
    }
    # add new user
    $args = array(
        'user_login'    => $user_email,
        'user_email'    => $user_email,
        'user_pass'     => $user_pass,
        'first_name'    => $first_name,
        'last_name'     => $last_name,
        'display_name'  => $display_name,
        'website'       => $company_website,
        'description'   => $note,
        'role'          => $role_name,
    );

    $new_partner = wp_insert_user($args);

    # if have role, then add role to user
    if (is_array($role) && !empty($role)) {
        # add role to user
        foreach ($role as $role_name) {
            $user = new WP_User($new_partner);
            $user->add_role($role_name);
        }
    }

    $data = array();
    # if it's success create new user,
    # add more info throught custom fields
    if (!is_wp_error($new_partner) && !$error_partner_code) {
        update_field('field_600d31f4060eb', $company_name, 'user_' . $new_partner); # company_name
        update_field('field_607a4fb37b7e0', $user_code, 'user_' . $new_partner); # user_code
        update_field('field_600d3211060ec', $phone_number, 'user_' . $new_partner); # phone number
        update_field('field_600d323d060ee', $address, 'user_' . $new_partner); # address
        update_field('field_6037200ec98cc', $country, 'user_' . $new_partner); # country
        update_field('field_6010f85bfcf55', $link_onedrive, 'user_' . $new_partner); # link_onedrive
        update_field('field_60a3cbacb1330', $type_of_client, 'user_' . $new_partner); # type_of_client
        update_field('field_61cd79951653e', $partner_vip, 'user_' . $new_partner); # partner_vip
        update_field('field_65a5625b5eb0e', $city, 'user_' . $new_partner); # city
        update_field('field_65a562035eb0c', $vietnam_company, 'user_' . $new_partner); # $vietnam_company
        update_field('field_65a4acb5db9c6', $phan_loai, 'user_' . $new_partner); # $có phải là cty hay không
        update_field('field_65a4acebdb9c7', $staffs, 'user_' . $new_partner); # $update người trong công ty
        # if "phan_loai" is 0 and "user_code_select" is 1, then update $new_partner to staffs of $user_code_exists
        if ($phan_loai == 0 && $user_code_select == 1) {
            # get "phan_loai" of $user_code_exists, if it's 1, then update staffs
            $phan_loai = get_field('field_65a4acb5db9c6', 'user_' . $user_code_exists);
            if ($phan_loai == 1) {
                $staffs = get_field('field_65a4acebdb9c7', 'user_' . $user_code_exists);
                $staffs = $staffs . "|" . $new_partner;
                update_field('field_65a4acebdb9c7', $staffs, 'user_' . $user_code_exists);
            }
        }

        update_field('field_65a5622f5eb0d', $languages, 'user_' . $new_partner); # $languages
        update_field('field_6039b28e2ba07', $email_cc, 'user_' . $new_partner); # email_cc
        update_field('field_609a038489e8c', $email_bcc, 'user_' . $new_partner); # email_bcc
        update_field('field_61cd79bf1653f', $worked, 'user_' . $new_partner); # đã chốt hoặc tiềm năng
        update_field('field_65de936686343', $nguon_dau_viec, 'user_' . $new_partner); # nguồn đến từ đâu
        update_field('field_65dcc97fa77b9', $detail_client_type, 'user_' . $new_partner); # chuyên ngành đối tác
        update_field('field_65ddcd2141e6f', $fdi, 'user_' . $new_partner); # có vốn fdi không
        if ($fdi && $fdi_countries) {
            update_field('field_65ddcd7941e70', $fdi_countries, 'user_' . $new_partner); # quốc gia đầu tư
        }

        $data['status'] = 'success';
        $data['user_id'] = $new_partner;
        $data['content'] = "<option value='" . $new_partner . "' selected>" . $display_name . " (" . $user_email . ")</option>";
        $data['notification'] = '<div class="alert alert-success" role="alert">
                                    <i class="fa fa-check"></i> ' . __('Đã tạo tài khoản thành công', 'qlcv') . '
                                  </div>';
                                  
        # create log, add new partner
        asl_create_log(__('Đã tạo mới đối tác', 'qlcv'), null, $new_partner);

    } else {
        $data['status'] = 'error';
        $data['notification'] = '<div class="alert alert-danger" role="alert">
                                    <i class="zmdi zmdi-info"></i> ' . __('Có lỗi xảy ra, xin vui lòng kiểm tra lại.', 'qlcv') . ' ' . $error_message . '
                                  </div>';
    }

    switch ($role) {
        case 'partner':
            $data['hide_form']          = '#create_partner';
            $data['div_notification']   = '#create_partner .notification';
            $data['select_element']     = 'select[name="partner"]';
            break;

        case 'foreign_partner':
            $data['hide_form']          = '#create_foreign_partner';
            $data['div_notification']   = '#create_foreign_partner .notification';
            $data['select_element']     = 'select[name="foreign_partner"]';
            break;

        default:
            break;
    }

    return $data;
}