<?php
/*
    Template Name: Thêm mới partner (đối tác)
*/
$history_link   = $_SERVER['HTTP_REFERER'];
$thongbao = $selected = "";

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
    $email_cc       = $_POST['email_cc'];
    $email_bcc      = $_POST['email_bcc'];
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
    if ($_POST['detail_client_type']) {
        $detail_client_type = implode(", ", $_POST['detail_client_type']);
    }
    $fdi            = $_POST['fdi'];
    if ($_POST['fdi_countries']) {
        $fdi_countries  = implode(", ", $_POST['fdi_countries']);
    }
    
    if ($phan_loai) {
        if ($_POST['staffs']) {
            $staffs     = implode("|", $_POST['staffs']);
        }
    }
    
    $history_link   = $_POST['history_link'];
    $roles          = $_POST['role'];
    $worked         = $_POST['worked'];
    $nguon_dau_viec = $_POST['nguon_dau_viec'];
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
        );

        $new_partner = wp_insert_user($args);
        
        $theUser = new WP_User($new_partner);
        # add new roles if itn't exist.
        foreach ($roles as $key => $value) {
            $theUser->add_role( $value );
        }

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
            update_field('field_65a5625b5eb0e', $city, 'user_' . $new_partner); # city
            update_field('field_65a562035eb0c', $vietnam_company, 'user_' . $new_partner); # $vietnam_company
            update_field('field_65a4acb5db9c6', $phan_loai, 'user_' . $new_partner); # $có phải là cty hay không
            update_field('field_65a4acebdb9c7', $staffs, 'user_' . $new_partner); # $update người trong công ty
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
                        echo $thongbao;
                    }
                    ?>
                    <div>
                        <form action="#" method="POST" class="row">
                            <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Tên công ty/tổ chức', 'qlcv'); ?> <span class="text-danger">*</span></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="user_company" value="<?php if(isset($_POST['user_company'])) echo $_POST['user_company']; ?>"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Mã đối tác', 'qlcv'); ?> <span class="text-danger">*</span></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="user_code" value="<?php if(isset($_POST['user_code'])) echo $_POST['user_code']; ?>"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45 text-lg-right"><?php _e('Họ và tên', 'qlcv'); ?> </div>
                            <div class="col-lg-3 col-12 mb-20">
                                <input type="text" class="form-control" name="first_name" placeholder="<?php _e('Họ', 'qlcv'); ?>" value="<?php if(isset($_POST['first_name'])) echo $_POST['first_name']; ?>">
                            </div>
                            <div class="col-lg-3 col-12 mb-20">
                                <input type="text" class="form-control" name="last_name" placeholder="<?php _e('Tên', 'qlcv'); ?>" value="<?php if(isset($_POST['last_name'])) echo $_POST['last_name']; ?>">
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

                            <div class="col-lg-3 form_title text-lg-right"><?php _e('Vai trò', 'qlcv'); ?></div>
                            <div class="col-lg-6 col-12 mb-20">
                                <!-- <select name="role" class="form-control select2-tags mb-20"> -->
                                    <?php 
                                        $options = [
                                            'partner' => __('Đối tác gửi việc', 'qlcv'), 
                                            'foreign_partner' => __('Đối tác nhận việc', 'qlcv')
                                        ];
                                        $list_selected = isset($_POST['role'])?$_POST['role']:"";

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

                            <div class="col-lg-3 form_title text-lg-right"><?php _e('Đã có công ty tại Việt Nam?', 'qlcv'); ?> <span class="text-danger">*</span></div>
                            <div class="col-lg-6 col-12 mb-20">
                                <div class="adomx-checkbox-radio-group">
                                    <?php 
                                        $checked = isset($_POST['vietnam_company']) && $_POST['vietnam_company']?"checked":"";
                                    ?>
                                    <label class="adomx-switch"><input type="checkbox" name="vietnam_company" <?php echo $checked; ?>> <i class="lever"></i></label>
                                </div>
                            </div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title text-lg-right"><?php _e('Phân loại đầu tư', 'qlcv'); ?> <span class="text-danger">*</span></div>
                            <div class="col-lg-6 col-12 mb-20">
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

                            <div class="col-lg-3 form_title text-lg-right"><?php _e('Loại tài khoản', 'qlcv'); ?> <span class="text-danger">*</span></div>
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