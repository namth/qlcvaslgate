<?php
/*
    Template Name: Tạo job mới
*/
get_header();

get_sidebar();

$type = "";
if (isset($_GET['type'])) {
    $type = $_GET['type'];
}
?>

<!-- Content Body Start -->
<div class="content-body">

    <!-- Page Headings Start -->
    <div class="row justify-content-between align-items-center mb-10">

        <div class="col-12 mb-30">
            <div class="box">
                <div class="box-head">
                    <h3 class="title"><?php the_title(); ?></h3>
                </div>
                <div class="box-body">

                    <div class="smart-wizard" id="create_new_job">
                        <ul>
                            <li><a href="#step-0">1. <?php _e('Thông tin công việc', 'qlcv'); ?></a></li>
                            <li><a href="#step-1">2. <?php _e('Đối tác, khách hàng', 'qlcv'); ?></a></li>
                            <li><a href="#step-2">3. <?php _e('Tài chính', 'qlcv'); ?></a></li>
                            <li><a href="#step-3">4. <?php _e('Nhân sự thực hiện', 'qlcv'); ?></a></li>
                        </ul>

                        <div>
                            <div id="step-0">
                                <div class="row mbn-20">
                                    <div class="col-12 mb-20">
                                        <h4><?php _e('Nhập thông tin công việc', 'qlcv'); ?></h4>
                                    </div>
                                    <div class="col-12">
                                        <form action="" method="POST" id="new_job" class="row">
                                            <div class="col-lg-3 form_title text-left text-lg-right"><?php _e('Nguồn đầu việc', 'qlcv'); ?> <span class="text-danger">*</span></div>
                                            <div class="col-lg-6 col-12 mb-20">
                                                <div class="form-group">
                                                    <?php
                                                    $terms = get_terms(array(
                                                        'taxonomy' => 'post_tag',
                                                        'hide_empty' => false,
                                                    ));
                                                    foreach ($terms as $value) {
                                                        echo '<label class="inline"><input type="radio" name="nguon_dau_viec" value="' . $value->name . '">' . $value->name . '</label>';
                                                    }
                                                    ?>
                                                    <div id="select_partner_1" style="display: none;">
                                                        <select class="form-control select2-tags mb-20" name="partner_1">
                                                            <option value="">-- <?php _e('Chọn đối tác giới thiệu', 'qlcv'); ?> --</option>
                                                            <?php
                                                            $args   = array(
                                                                'role'      => 'partner', /*subscriber, contributor, author*/
                                                            );
                                                            $query = get_users($args);

                                                            if ($query) {
                                                                foreach ($query as $user) {
                                                                    $ten_cong_ty    = get_field('ten_cong_ty', 'user_' . $user->ID);
                                                                    echo "<option value='" . $user->ID . "'>" . $ten_cong_ty . " (" . $user->user_email . ")</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-3"></div>

                                            <div class="col-lg-3 form_title text-left text-lg-right"><?php _e('Phân loại', 'qlcv'); ?> <span class="text-danger">*</span></div>
                                            <div class="col-lg-6 col-12 mb-20">
                                                <div class="form-group">
                                                    <label class="inline"><input type="radio" name="tiem_nang" value="0" checked=""><?php _e('Đã chốt', 'qlcv'); ?></label>
                                                    <label class="inline"><input type="radio" name="tiem_nang" value="1"><?php _e('Tiềm năng', 'qlcv'); ?></label>
                                                </div>
                                            </div>
                                            <div class="col-lg-3"></div>

                                            <div class="col-lg-3 form_title lh45 text-left text-lg-right"><?php _e('Tên công việc', 'qlcv'); ?> <span class="text-danger">*</span></div>
                                            <div class="col-lg-6 col-12 mb-20"><input type="text" placeholder="<?php _e('VD: Nhãn hiệu 9OUTFIT', 'qlcv'); ?>" class="form-control" name="job_name"></div>
                                            <div class="col-lg-3"></div>

                                            <div class="col-lg-3 form_title lh45 text-left text-lg-right"><?php _e('Số REF của đối tác', 'qlcv'); ?></div>
                                            <div class="col-lg-6 col-12 mb-20"><input type="text" placeholder="<?php _e('Số REF của đối tác', 'qlcv'); ?>" class="form-control" name="partner_ref"></div>
                                            <div class="col-lg-3"></div>

                                            <div class="col-lg-3 form_title lh45 text-left text-lg-right"><?php _e('Số REF của mình', 'qlcv'); ?></div>
                                            <div class="col-lg-6 col-12 mb-20"><input type="text" placeholder="<?php _e('Để trống sẽ tự sinh số REF', 'qlcv'); ?>" class="form-control" name="our_ref"></div>
                                            <div class="col-lg-3"></div>

                                            <div class="col-lg-3 form_title text-left text-lg-right"><?php _e('Thông tin', 'qlcv'); ?> <span class="text-danger">*</span></div>
                                            <div class="col-lg-8 col-12 mb-20">
                                                <div class="form-group">
                                                    <?php
                                                    if (!$type) {
                                                        $type = "Nhãn hiệu";
                                                    ?>
                                                        <ul class="nav nav-pills mb-15" id="choose_group">
                                                            <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#nhanhieu" data-group="Nhãn hiệu"><?php _e('Nhãn hiệu', 'qlcv'); ?></a></li>
                                                            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#kieudang" data-group="Kiểu dáng"><?php _e('Kiểu dáng', 'qlcv'); ?></a></li>
                                                            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#sangche" data-group="Sáng chế"><?php _e('Sáng chế', 'qlcv'); ?></a></li>
                                                            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#otherip" data-group="Bản quyền"><?php _e('Bản quyền', 'qlcv'); ?></a></li>
                                                            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#otherip" data-group="Franchise"><?php _e('Franchise', 'qlcv'); ?></a></li>
                                                            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#vieckhac" data-group="Việc khác"><?php _e('Việc khác', 'qlcv'); ?></a></li>
                                                        </ul>
                                                    <?php
                                                    }

                                                    switch ($type) {
                                                        case 'Nhãn hiệu':
                                                            $class_nhan_hieu    = "active show";
                                                            $class_kieu_dang = $class_sang_che = $class_otherip = "";
                                                            break;

                                                        case 'Kiểu dáng':
                                                            $class_nhan_hieu = $class_sang_che = $class_otherip = "";
                                                            $class_kieu_dang    = "active show";
                                                            break;

                                                        case 'Sáng chế':
                                                            $class_nhan_hieu = $class_kieu_dang = $class_otherip = "";
                                                            $class_sang_che     = "active show";
                                                            break;

                                                        case 'Việc khác':
                                                            $class_nhan_hieu = $class_kieu_dang = $class_sang_che = $class_otherip = "";
                                                            $class_viec_khac    = "active show";
                                                            break;

                                                        default:
                                                            $class_nhan_hieu = $class_kieu_dang = $class_sang_che = $class_viec_khac = "";
                                                            $class_otherip    = "active show";
                                                            break;
                                                    }
                                                    ?>
                                                    <input type="hidden" name="danh_muc" value="<?php echo $type; ?>">
                                                    <div class="tab-content">
                                                        <div class="tab-pane fade <?php echo $class_nhan_hieu; ?>" id="nhanhieu">
                                                            <input type="text" placeholder="<?php _e('Tên nhãn hiệu', 'qlcv'); ?>" class="form-control mb-10" name="brand_name">
                                                            <input type="text" placeholder="<?php _e('Nhóm', 'qlcv'); ?>" class="form-control mb-10" name="brand_group">
                                                            <input type="text" placeholder="<?php _e('Số lượng nhóm', 'qlcv'); ?>" class="form-control mb-10" name="brand_number_group">
                                                            <input class="dropify" type="file" name="file_upload">
                                                        </div>
                                                        <div class="tab-pane fade <?php echo $class_kieu_dang; ?>" id="kieudang">
                                                            <input type="text" placeholder="<?php _e('Link tới bộ ảnh', 'qlcv'); ?>" class="form-control mb-10" name="kdang_pic">
                                                            <input type="text" placeholder="<?php _e('Link tới bản mô tả của bộ ảnh', 'qlcv'); ?>" class="form-control mb-10" name="kdang_info">
                                                            <input type="text" placeholder="<?php _e('Số lượng phương án', 'qlcv'); ?>" class="form-control mb-10" name="kdang_phuongan">
                                                        </div>
                                                        <div class="tab-pane fade <?php echo $class_sang_che; ?>" id="sangche">
                                                            <input type="text" placeholder="<?php _e('Link tới bản mô tả sáng chế', 'qlcv'); ?>" class="form-control mb-10" name="sche_info">
                                                            <input type="text" placeholder="<?php _e('Số lượng yêu cầu bảo hộ', 'qlcv'); ?>" class="form-control mb-10" name="sche_request_1">
                                                            <input type="text" placeholder="<?php _e('Số lượng yêu cầu bảo hộ độc lập', 'qlcv'); ?>" class="form-control mb-10" name="sche_request_2">
                                                        </div>
                                                        <div class="tab-pane fade <?php echo $class_otherip; ?>" id="otherip">
                                                            <span class="form-help-text"><?php _e('Nhập deadline cho công việc này', 'qlcv'); ?></span>
                                                            <input type="text" class="form-control" value="" name="deadline" placeholder="Deadline: dd/mm/yyyy" data-mask="99/99/9999">
                                                            <span class="form-help-text text-danger"><?php _e('Lưu ý: nếu là đầu việc lớn có nhiều nhiệm vụ con thì bỏ qua trường thông tin này.', 'qlcv'); ?></span>
                                                        </div>
                                                        <div class="tab-pane fade <?php echo $class_viec_khac; ?>" id="vieckhac">
                                                            <select class="form-control select2-tags mb-20" name="other_job">
                                                                <option value=""> -- Chọn phân loại công việc -- </option>
                                                                <?php 
                                                                    $list_other_jobs = get_term_children(10, 'group');
                                                                    foreach ($list_other_jobs as $jobid) {
                                                                        $term = get_term($jobid, 'group');
                                                                        echo "<option value='" . $term->name . "'>" . $term->name . "</option>";
                                                                    }
                                                                ?>
                                                            </select>
                                                            <span class="form-help-text"><?php _e('Nhập deadline cho công việc này', 'qlcv'); ?></span>
                                                            <input type="text" class="form-control" value="" name="deadline" placeholder="Deadline: dd/mm/yyyy" data-mask="99/99/9999">
                                                            <span class="form-help-text text-danger"><?php _e('Lưu ý: nếu là đầu việc lớn có nhiều nhiệm vụ con thì bỏ qua trường thông tin này.', 'qlcv'); ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-1"></div>

                                            <div class="col-lg-3 form_title lh45 text-left text-lg-right"><?php _e('Nội dung chi tiết', 'qlcv'); ?></div>
                                            <div class="col-lg-8 col-12 mb-20"><textarea class="form-control summernote" placeholder="<?php _e('Thông tin bổ sung', 'qlcv'); ?>" name="note"></textarea></div>
                                            <div class="col-lg-1"></div>

                                            <div class="col-lg-3 form_title lh45 text-left text-lg-right"><?php _e('Lưu ý công việc', 'qlcv'); ?></div>
                                            <div class="col-lg-8 col-12 mb-20"><textarea class="form-control summernote" placeholder="Lưu ý" name="mindful"></textarea></div>
                                            <div class="col-lg-1"></div>

                                            <div class="col-lg-3 form_title lh45 text-left text-lg-right"><?php _e('Link file hồ sơ', 'qlcv'); ?></div>
                                            <div class="col-lg-6 col-12 mb-20"><textarea class="form-control" placeholder="<?php _e('Nhúng link từ one drive', 'qlcv'); ?>" name="link_onedrive"></textarea></div>
                                            <div class="col-lg-3"></div>

                                            <div class="col-lg-3 form_title lh45 text-left text-lg-right"><?php _e('Quốc gia nộp', 'qlcv'); ?> <span class="text-danger">*</span></div>
                                            <div class="col-lg-6 col-12 mb-20">
                                                <select class="form-control select2-tags mb-20" multiple="" name="country[]">
                                                    <?php
                                                        $list_country = explode(PHP_EOL, get_field('list_country', 'option'));
                    
                                                        if ($list_country) {
                                                            foreach ($list_country as $country) {
                                                                echo "<option value='" . $country . "'>" . $country . "</option>";
                                                            }
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-lg-3"></div>

                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div id="step-1">
                                <div class="row mbn-20">
                                    <div class="col-12 mb-20">
                                        <h4><?php _e('Chọn đối tác gửi việc từ trong danh sách', 'qlcv'); ?> <span class="text-danger">*</span></h4>
                                        <select class="form-control select2-tags mb-20" name="partner">
                                            <option value="">-- <?php _e('Chọn đối tác gửi việc', 'qlcv'); ?> --</option>
                                            <?php
                                            $args   = array(
                                                'role'      => 'partner', /*subscriber, contributor, author*/
                                            );
                                            $query = get_users($args);

                                            if ($query) {
                                                foreach ($query as $user) {
                                                    $ten_cong_ty    = get_field('ten_cong_ty', 'user_' . $user->ID);
                                                    $partner_code   = get_field('partner_code', 'user_' . $user->ID);
                                                    echo "<option value='" . $user->ID . "'>" . $partner_code . " - " . $ten_cong_ty . " (" . $user->user_email . ")</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-12 mb-20">
                                        <button class="button button-primary create_new_button" data-div="#create_partner"><span><i class="fa fa-user-plus"></i><?php _e('Tạo đối tác mới', 'qlcv'); ?></span></button>
                                        <div id="create_partner" style="display: none;">
                                            <form action="#" method="POST" class="row">
                                                <div class="col-12 mb-20 notification">
                                                    <h4><?php _e('Nhập thông tin đối tác mới', 'qlcv'); ?></h4>
                                                </div>
                                                <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Tên công ty/tổ chức', 'qlcv'); ?></div>
                                                <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="company_name"></div>
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Mã đối tác', 'qlcv'); ?> <span class="text-danger">*</span></div>
                                                <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="user_code"></div>
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Họ và tên', 'qlcv'); ?></div>
                                                <div class="col-lg-3 col-12 mb-20"><input type="text" class="form-control" name="first_name" placeholder="<?php _e('Họ', 'qlcv'); ?>"></div>
                                                <div class="col-lg-3 col-12 mb-20"><input type="text" class="form-control" name="last_name" placeholder="<?php _e('Tên', 'qlcv'); ?>"></div>
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-3 form_title text-left text-lg-right mb-10 mt-10"><?php _e('Trạng thái', 'qlcv'); ?></div>
                                                <div class="col-lg-3 col-12 mb-20 mt-10">
                                                    <div class="adomx-checkbox-radio-group inline">
                                                        <?php 
                                                            $options = [0 => __('Đã chốt', 'qlcv'), 1 => __('Tiềm năng', 'qlcv')];
                                                            $default = 1;

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
                                                                echo '<label class="adomx-radio-2"><input type="radio" name="nguon_dau_viec" value="' . $value->name . '"> <i class="icon"></i> ' . $value->name . '</label>';
                                                            }
                                                        ?>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6"></div>

                                                <div class="col-lg-3 form_title text-left text-lg-right lh45">Email <span class="text-danger">*</span></div>
                                                <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="user_email"></div>
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
                                                            
                                                            foreach ($list_other_jobs as $jobid) {
                                                                $term = get_term($jobid, 'group');
                                                                echo "<option value='" . $term->name . "'>" . $term->name . "</option>";
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

                                                <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Số điện thoại', 'qlcv'); ?></div>
                                                <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="phone_number"></div>
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Địa chỉ', 'qlcv'); ?></div>
                                                <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="address"></div>
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Quốc gia', 'qlcv'); ?></div>
                                                <div class="col-lg-6 col-12 mb-20">
                                                    <select class="form-control select2-tags mb-20" name="country">
                                                        <option value="">-- <?php _e('Chọn quốc gia') ?> --</option>
                                                        <?php
                                                            $list_country = explode(PHP_EOL, get_field('list_country', 'option'));
                        
                                                            if ($list_country) {
                                                                foreach ($list_country as $country) {
                                                                    $country = trim($country);
                                                                    echo "<option value='" . $country . "'>" . $country . "</option>";
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
                                                        <label class="adomx-switch"><input type="checkbox" name="vietnam_company"> <i class="lever"></i></label>
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
                                                            
                                                            foreach ($options as $key => $value) {
                                                                echo '<label class="adomx-radio-2"><input type="radio" name="fdi" value="' . $key . '"> <i class="icon"></i> ' . $value . '</label>';
                                                            }
                                                        ?>
                                                    </div>
                                                    <div style="display: none; margin-top: 15px;" id="fdi">
                                                        <small>Tại nước nào?</small>
                                                        <select class="form-control select2-tags mb-20" multiple="" name="fdi_countries[]">
                                                            <?php
                                                            $list_country = explode(PHP_EOL, get_field('list_country', 'option'));
                                                            
                                                            if ($list_country) {
                                                                foreach ($list_country as $country) {
                                                                    $country = trim($country);
                                                                    echo "<option value='" . $country . "'>" . $country . "</option>";
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
                                                            
                                                            if ($languages) {
                                                                foreach ($languages as $language) {
                                                                    $language = trim($language);
                                                                    echo "<option value='" . $language . "'>" . $language . "</option>";
                                                                }
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Ghi chú', 'qlcv'); ?></div>
                                                <div class="col-lg-6 col-12 mb-20"><textarea class="form-control" placeholder="<?php _e('Thông tin bổ sung', 'qlcv'); ?>" name="note"></textarea></div>
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-3 form_title text-lg-right"><?php _e('Loại tài khoản', 'qlcv'); ?> <span class="text-danger">*</span></div>
                                                <div class="col-lg-6 col-12 mb-20">
                                                    <div class="adomx-checkbox-radio-group inline">
                                                        <?php 
                                                            $options = [
                                                                0 => __('Cá nhân', 'qlcv'), 
                                                                1 => __('Tổ chức', 'qlcv')
                                                            ];
                                                            
                                                            foreach ($options as $key => $value) {
                                                                echo '<label class="adomx-radio-2"><input type="radio" name="phan_loai" value="' . $key . '"> <i class="icon"></i> ' . $value . '</label>';
                                                            }
                                                        ?>
                                                    </div>
                                                    <div style="display: none; margin-top: 15px;" id="phanloai">
                                                        <small>Thêm danh sách thành viên công ty vào ô dưới đây</small>
                                                        <select class="form-control select2-tags mb-20" multiple="" name="staffs[]">
                                                            <?php
                                                            $args   = array(
                                                                'role__in'      => array('partner', 'foreign_partner'),
                                                            );
                                                            $query = get_users($args);
                        
                                                            if ($query) {
                                                                foreach ($query as $user) {
                                                                    echo "<option value='" . $user->ID . "'>" . $user->display_name . " (" . $user->user_email . ")</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3"></div>

                                                <?php
                                                wp_nonce_field('post_nonce', 'post_nonce_field');
                                                ?>
                                                <input type="hidden" name="role" value="partner">

                                                <div class="col-lg-3"></div>
                                                <div class="col-lg-6 col-12 mb-20"><input type="submit" class="button button-primary" value="<?php _e('Tạo mới', 'qlcv'); ?>"></div>


                                            </form>
                                        </div>
                                    </div>
                                    <div class="foreign_partner">
                                        <div class="col-12 mb-20">
                                            <h4><?php _e('Chọn đối tác nhận việc từ trong danh sách', 'qlcv'); ?></h4>
                                            <select class="form-control select2-tags mb-20" name="foreign_partner">
                                                <option value="">-- <?php _e('Chọn đối tác nhận việc', 'qlcv'); ?> --</option>
                                                <?php
                                                $args   = array(
                                                    'role'      => 'foreign_partner', /*subscriber, contributor, author*/
                                                );
                                                $query = get_users($args);

                                                if ($query) {
                                                    foreach ($query as $user) {
                                                        $ten_cong_ty    = get_field('ten_cong_ty', 'user_' . $user->ID);
                                                        $partner_code   = get_field('partner_code', 'user_' . $user->ID);
                                                        echo "<option value='" . $user->ID . "'>" . $partner_code . " - " . $ten_cong_ty . " (" . $user->user_email . ")</option>";
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-12 mb-20">
                                            <button class="button button-primary create_new_button" data-div="#create_foreign_partner"><span><i class="fa fa-user-plus"></i><?php _e('Tạo đối tác nước ngoài mới', 'qlcv'); ?></span></button>
                                            <div id="create_foreign_partner" style="display: none;">
                                                <form action="#" method="POST" class="row">
                                                    <div class="col-12 mb-20 notification">
                                                        <h4><?php _e('Nhập thông tin đối tác', 'qlcv'); ?></h4>
                                                    </div>
                                                    <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Tên công ty/tổ chức', 'qlcv'); ?></div>
                                                    <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="company_name"></div>
                                                    <div class="col-lg-3"></div>

                                                    <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Mã đối tác', 'qlcv'); ?></div>
                                                    <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="user_code"></div>
                                                    <div class="col-lg-3"></div>

                                                    <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Họ và tên', 'qlcv'); ?></div>
                                                    <div class="col-lg-3 col-12 mb-20"><input type="text" class="form-control" name="first_name" placeholder="<?php _e('Họ', 'qlcv'); ?>"></div>
                                                    <div class="col-lg-3 col-12 mb-20"><input type="text" class="form-control" name="last_name" placeholder="<?php _e('Tên', 'qlcv'); ?>"></div>
                                                    <div class="col-lg-3"></div>

                                                    <div class="col-lg-3 form_title text-left text-lg-right mb-10 mt-10"><?php _e('Trạng thái', 'qlcv'); ?></div>
                                                    <div class="col-lg-3 col-12 mb-20 mt-10">
                                                        <div class="adomx-checkbox-radio-group inline">
                                                            <?php 
                                                                $options = [0 => __('Đã chốt', 'qlcv'), 1 => __('Tiềm năng', 'qlcv')];
                                                                $default = 1;

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
                                                                    echo '<label class="adomx-radio-2"><input type="radio" name="nguon_dau_viec" value="' . $value->name . '"> <i class="icon"></i> ' . $value->name . '</label>';
                                                                }
                                                            ?>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6"></div>

                                                    <div class="col-lg-3 form_title text-left text-lg-right lh45">Email <span class="text-danger">*</span></div>
                                                    <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="user_email"></div>
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
                                                                
                                                                foreach ($list_other_jobs as $jobid) {
                                                                    $term = get_term($jobid, 'group');
                                                                    echo "<option value='" . $term->name . "'>" . $term->name . "</option>";
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

                                                    <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Số điện thoại', 'qlcv'); ?></div>
                                                    <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="phone_number"></div>
                                                    <div class="col-lg-3"></div>

                                                    <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Địa chỉ', 'qlcv'); ?></div>
                                                    <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="address"></div>
                                                    <div class="col-lg-3"></div>

                                                    <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Quốc gia', 'qlcv'); ?></div>
                                                    <div class="col-lg-6 col-12 mb-20">
                                                        <select class="form-control select2-tags mb-20" name="country">
                                                            <option value="">-- <?php _e('Chọn quốc gia') ?> --</option>
                                                            <?php
                                                                $list_country = explode(PHP_EOL, get_field('list_country', 'option'));
                            
                                                                if ($list_country) {
                                                                    foreach ($list_country as $country) {
                                                                        $country = trim($country);
                                                                        echo "<option value='" . $country . "'>" . $country . "</option>";
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
                                                            <label class="adomx-switch"><input type="checkbox" name="vietnam_company"> <i class="lever"></i></label>
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
                                                                
                                                                foreach ($options as $key => $value) {
                                                                    echo '<label class="adomx-radio-2"><input type="radio" name="fdi" value="' . $key . '"> <i class="icon"></i> ' . $value . '</label>';
                                                                }
                                                            ?>
                                                        </div>
                                                        <div style="display: none; margin-top: 15px;" id="fdi">
                                                            <small>Tại nước nào?</small>
                                                            <select class="form-control select2-tags mb-20" multiple="" name="fdi_countries[]">
                                                                <?php
                                                                $list_country = explode(PHP_EOL, get_field('list_country', 'option'));
                                                                
                                                                if ($list_country) {
                                                                    foreach ($list_country as $country) {
                                                                        $country = trim($country);
                                                                        echo "<option value='" . $country . "'>" . $country . "</option>";
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
                                                                
                                                                if ($languages) {
                                                                    foreach ($languages as $language) {
                                                                        $language = trim($language);
                                                                        echo "<option value='" . $language . "'>" . $language . "</option>";
                                                                    }
                                                                }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-lg-3"></div>

                                                    <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Ghi chú', 'qlcv'); ?></div>
                                                    <div class="col-lg-6 col-12 mb-20"><textarea class="form-control" placeholder="<?php _e('Thông tin bổ sung', 'qlcv'); ?>" name="note"></textarea></div>
                                                    <div class="col-lg-3"></div>

                                                    <div class="col-lg-3 form_title text-lg-right"><?php _e('Loại tài khoản', 'qlcv'); ?> <span class="text-danger">*</span></div>
                                                    <div class="col-lg-6 col-12 mb-20">
                                                        <div class="adomx-checkbox-radio-group inline">
                                                            <?php 
                                                                $options = [
                                                                    0 => __('Cá nhân', 'qlcv'), 
                                                                    1 => __('Tổ chức', 'qlcv')
                                                                ];
                                                                
                                                                foreach ($options as $key => $value) {
                                                                    echo '<label class="adomx-radio-2"><input type="radio" name="phan_loai" value="' . $key . '"> <i class="icon"></i> ' . $value . '</label>';
                                                                }
                                                            ?>
                                                        </div>
                                                        <div style="display: none; margin-top: 15px;" id="phanloai">
                                                            <small>Thêm danh sách thành viên công ty vào ô dưới đây</small>
                                                            <select class="form-control select2-tags mb-20" multiple="" name="staffs[]">
                                                                <?php
                                                                $args   = array(
                                                                    'role__in'      => array('partner', 'foreign_partner'),
                                                                );
                                                                $query = get_users($args);
                            
                                                                if ($query) {
                                                                    foreach ($query as $user) {
                                                                        echo "<option value='" . $user->ID . "'>" . $user->display_name . " (" . $user->user_email . ")</option>";
                                                                    }
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3"></div>

                                                    <?php
                                                    wp_nonce_field('post_nonce', 'post_nonce_field');
                                                    ?>
                                                    <input type="hidden" name="role" value="foreign_partner">

                                                    <div class="col-lg-3"></div>
                                                    <div class="col-lg-6 col-12 mb-20"><input type="submit" class="button button-primary" value="<?php _e('Tạo mới', 'qlcv'); ?>"></div>

                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-20">
                                        <h4><?php _e('Chọn khách hàng từ trong danh sách', 'qlcv'); ?> <span class="text-danger">*</span></h4>
                                        <select class="form-control select2-tags mb-20" name="customer">
                                            <option value="">-- <?php _e('Chọn khách hàng', 'qlcv'); ?> --</option>
                                            <?php
                                            $args   = array(
                                                'post_type'     => 'customer',
                                                'posts_per_page' => -1,
                                            );
                                            $query = new WP_Query($args);

                                            if ($query->have_posts()) {
                                                while ($query->have_posts()) {
                                                    $query->the_post();

                                                    $cty = get_field('ten_cong_ty');
                                                    $email = get_field('email');

                                                    echo "<option value='" . get_the_ID() . "'>" . $cty;
                                                    if ($email) {
                                                        echo " (" . $email . ")";
                                                    }
                                                    echo "</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-12 mb-20">
                                        <div id="customer_function">
                                            <button class="button button-primary create_customer"><span><i class="fa fa-user-plus"></i><?php _e('Tạo khách hàng mới', 'qlcv'); ?></span></button>
                                            <button class="button button-primary copy_customer"><span><i class="fa fa-user-plus"></i><?php _e('Copy dữ liệu đối tác', 'qlcv'); ?></span></button>
                                        </div>
                                        <div id="create_customer" style="display: none;">
                                            <form action="#" method="POST" class="row">
                                                <div class="col-12 mb-20 notification">
                                                    <h4><?php _e('Nhập thông tin khách hàng mới', 'qlcv'); ?></h4>
                                                </div>
                                                <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Tên công ty/Tên khách', 'qlcv'); ?></div>
                                                <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="customer_name"></div>
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Số điện thoại', 'qlcv'); ?></div>
                                                <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="phone_number"></div>
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-3 form_title text-left text-lg-right lh45">Email</div>
                                                <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="user_email"></div>
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Địa chỉ', 'qlcv'); ?></div>
                                                <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="address"></div>
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Quốc gia', 'qlcv'); ?></div>
                                                <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="country"></div>
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Ghi chú', 'qlcv'); ?></div>
                                                <div class="col-lg-6 col-12 mb-20"><textarea class="form-control" placeholder="<?php _e('Thông tin bổ sung', 'qlcv'); ?>" name="note"></textarea></div>
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Link file hồ sơ', 'qlcv'); ?></div>
                                                <div class="col-lg-6 col-12 mb-20"><textarea class="form-control" placeholder="<?php _e('Nhúng link từ one drive', 'qlcv'); ?>" name="link_onedrive"></textarea></div>
                                                <div class="col-lg-3"></div>

                                                <?php
                                                wp_nonce_field('post_nonce', 'post_nonce_field');
                                                ?>

                                                <div class="col-lg-3"></div>
                                                <div class="col-lg-6 col-12 mb-20"><input type="submit" class="button button-primary" value="<?php _e('Tạo mới', 'qlcv'); ?>"></div>

                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="step-2">
                                <div class="row mbn-20">
                                    <form action="" method="POST" id="finance" class="row">
                                        <div class="col-lg-3 form_title text-left text-lg-right"><?php _e('Loại tiền', 'qlcv'); ?></div>
                                        <div class="col-lg-6 col-12 mb-20">
                                            <div class="form-group">
                                                <label class="inline"><input type="radio" name="currency" value="USD" checked>USD</label>
                                                <label class="inline"><input type="radio" name="currency" value="VND">VND</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-3"></div>

                                        <div class="col-lg-3 form_title lh45 text-left text-lg-right"><?php _e('Tổng số tiền', 'qlcv'); ?></div>
                                        <div class="col-lg-6 col-12 mb-20"><input type="number" placeholder="0" class="form-control" name="total_value"></div>
                                        <div class="col-lg-3"></div>

                                        <div class="col-lg-3 form_title lh45 text-left text-lg-right"><?php _e('Đã thanh toán', 'qlcv'); ?></div>
                                        <div class="col-lg-6 col-12 mb-20"><input type="number" placeholder="0" class="form-control" name="paid"></div>
                                        <div class="col-lg-3"></div>
                                    </form>
                                </div>
                            </div>
                            <div id="step-3">
                                <div class="row mbn-20">
                                    <div class="col-12 mb-20">
                                        <h4><?php _e('Chọn người quản lý', 'qlcv'); ?></h4>
                                        <select class="form-control select2-tags mb-20" name="manager">
                                            <?php
                                            $args   = array(
                                                'role'      => 'contributor', /*subscriber, contributor, author*/
                                            );
                                            $query = get_users($args);

                                            if ($query) {
                                                foreach ($query as $user) {
                                                    echo "<option value='" . $user->ID . "'>" . $user->display_name . " (" . $user->user_email . ")</option>";
                                                }
                                            }
                                            ?>
                                        </select>

                                        <h4 style="margin-top: 30px;"><?php _e('Chọn người thực hiện', 'qlcv'); ?></h4>
                                        <select class="form-control select2-tags mb-20" name="member">
                                            <?php
                                            $args   = array(
                                                'role__in'      => array('member', 'contributor'), /*subscriber, contributor, author*/
                                            );
                                            $query = get_users($args);

                                            if ($query) {
                                                foreach ($query as $user) {
                                                    echo "<option value='" . $user->ID . "'>" . $user->display_name . " (" . $user->user_email . ")</option>";
                                                }
                                            }
                                            ?>
                                        </select>

                                        <!-- Chọn người giám sát -->
                                        <h4 style="margin-top: 30px;"><?php _e('Chọn người giám sát', 'qlcv'); ?></h4>
                                        <select class="form-control select2-tags mb-20" multiple="" name="supervisor" >
                                            <?php
                                            $args   = array(
                                                'role__in'      => array('administrator', 'editor', 'contributor'), /*subscriber, contributor, author*/
                                            );
                                            $query = get_users($args);

                                            if ($query) {
                                                foreach ($query as $user) {
                                                    echo "<option value='" . $user->ID . "'>" . $user->display_name . " (" . $user->user_email . ")</option>";
                                                }
                                            }
                                            ?>
                                        </select>

                                        <?php
                                            $terms = get_terms(array(
                                                'taxonomy' => 'agency',
                                                'hide_empty' => false,
                                            ));
                                            if($terms){
                                                echo '<h4 style="margin-top: 30px;">' . __('Chọn chi nhánh thực hiện', 'qlcv') . '</h4>
                                                      <select class="form-control select2-tags mb-20" name="agency">';
                                                      
                                                foreach ($terms as $value) {
                                                    echo "<option value='" . $value->name . "'>" . $value->name . "</option>";
                                                }
                                                echo '</select>';
                                            }
                                        ?>
                                        
                                    </div>
                                    <div class="col-12 mb-20">
                                        <button class="button button-primary finish_newjob"><span><i class="fa fa-user-plus"></i><?php _e('Hoàn tất', 'qlcv'); ?></span></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- Page Headings End -->

</div><!-- Content Body End -->

<?php
get_footer();
?>