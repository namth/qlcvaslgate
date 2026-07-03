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
                                                            <?php echo asl_get_partner_select_options_step0(); ?>
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

                                            <div class="col-lg-3 form_title lh45 text-left text-lg-right"><?php _e('Số Invoice', 'qlcv'); ?></div>
                                            <div class="col-lg-6 col-12 mb-20"><input type="text" placeholder="<?php _e('Số Invoice', 'qlcv'); ?>" class="form-control" name="invoice_number"></div>
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
                                                            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#vieckhac" data-group="Việc luật"><?php _e('Việc luật', 'qlcv'); ?></a></li>
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

                                                        case 'Việc luật':
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
                                                            <?php
                                                            $list_color_text = get_field('list_color', 'option');
                                                            $colors = [];
                                                            if (!empty($list_color_text)) {
                                                                $colors = array_filter(array_map('trim', explode("\n", $list_color_text)));
                                                            }
                                                            ?>
                                                            <select name="trademark_color" id="trademark_color_select" class="form-control mb-10">
                                                                <option value="">-- <?php _e('Chọn màu sắc', 'qlcv'); ?> --</option>
                                                                <?php foreach ($colors as $color): ?>
                                                                    <option value="<?php echo esc_attr($color); ?>"><?php echo esc_html($color); ?></option>
                                                                <?php endforeach; ?>
                                                                <option value="custom"><?php _e('Khác (Tự nhập)', 'qlcv'); ?></option>
                                                            </select>
                                                            <input type="text" name="trademark_color_custom" id="trademark_color_custom" placeholder="<?php _e('Nhập màu sắc mới', 'qlcv'); ?>" class="form-control mb-10" style="display: none;">
                                                            <input type="text" placeholder="<?php _e('Danh mục sản phẩm dịch vụ', 'qlcv'); ?>" class="form-control mb-10" name="service_category">
                                                            <input class="dropify" type="file" name="file_upload">
                                                            <script>
                                                                document.addEventListener('DOMContentLoaded', function() {
                                                                    var select = document.getElementById('trademark_color_select');
                                                                    var customInput = document.getElementById('trademark_color_custom');
                                                                    if (select && customInput) {
                                                                        select.addEventListener('change', function() {
                                                                            if (this.value === 'custom') {
                                                                                customInput.style.display = 'block';
                                                                            } else {
                                                                                customInput.style.display = 'none';
                                                                                customInput.value = '';
                                                                            }
                                                                        });
                                                                    }
                                                                });
                                                            </script>
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
                                                            <input type="text" class="form-control" value="" name="deadline" placeholder="<?php _e('Deadline: dd/mm/yyyy', 'qlcv'); ?>" data-mask="99/99/9999">
                                                            <span class="form-help-text text-danger"><?php _e('Lưu ý: nếu là đầu việc lớn có nhiều nhiệm vụ con thì bỏ qua trường thông tin này.', 'qlcv'); ?></span>
                                                        </div>
                                                        <div class="tab-pane fade <?php echo $class_viec_khac; ?>" id="vieckhac">
                                                            <select class="form-control select2-tags mb-20" name="other_job">
                                                                <option value=""> -- <?php _e('Chọn phân loại công việc', 'qlcv'); ?> -- </option>
                                                                <?php 
                                                                    $list_other_jobs = get_term_children(10, 'group');
                                                                    foreach ($list_other_jobs as $jobid) {
                                                                        $term = get_term($jobid, 'group');
                                                                        echo "<option value='" . $term->name . "'>" . $term->name . "</option>";
                                                                    }
                                                                ?>
                                                            </select>
                                                            <span class="form-help-text"><?php _e('Nhập deadline cho công việc này', 'qlcv'); ?></span>
                                                            <input type="text" class="form-control" value="" name="deadline" placeholder="<?php _e('Deadline: dd/mm/yyyy', 'qlcv'); ?>" data-mask="99/99/9999">
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
                                            <div class="col-lg-8 col-12 mb-20"><textarea class="form-control summernote" placeholder="<?php _e('Lưu ý', 'qlcv'); ?>" name="mindful"></textarea></div>
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

                                            <div class="col-lg-3 form_title lh45 text-left text-lg-right"><?php _e('Độ khó', 'qlcv'); ?></div>
                                            <div class="col-lg-6 col-12 mb-20">
                                                <select class="form-control select2-tags mb-20" name="level">
                                                    <option value="Đơn giản" selected><?php _e('Đơn giản', 'qlcv'); ?></option>
                                                    <option value="Trung Bình"><?php _e('Trung Bình', 'qlcv'); ?></option>
                                                    <option value="Khó"><?php _e('Khó', 'qlcv'); ?></option>
                                                    <option value="Rất khó"><?php _e('Rất khó', 'qlcv'); ?></option>
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
                                            <?php echo asl_get_partner_select_options_step1(); ?>
                                        </select>
                                    </div>
                                    <div class="col-12 mb-20">
                                        <button class="button button-primary create_new_button" data-div="#create_partner"><span><i class="fa fa-user-plus"></i><?php _e('Tạo đối tác mới', 'qlcv'); ?></span></button>
                                        <div id="create_partner" style="display: none;">
                                            <?php 
                                                # add new partner form
                                                form_addnew_partner('partner');
                                            ?>
                                        </div>
                                    </div>
                                    <div class="foreign_partner">
                                        <div class="col-12 mb-20">
                                            <h4><?php _e('Chọn đối tác nhận việc từ trong danh sách', 'qlcv'); ?></h4>
                                            <select class="form-control select2-tags mb-20" name="foreign_partner">
                                                <option value="">-- <?php _e('Chọn đối tác nhận việc', 'qlcv'); ?> --</option>
                                                <?php echo asl_get_foreign_partner_select_options(); ?>
                                            </select>
                                        </div>
                                        <div class="col-12 mb-20">
                                            <button class="button button-primary create_new_button" data-div="#create_foreign_partner"><span><i class="fa fa-user-plus"></i><?php _e('Tạo đối tác nước ngoài mới', 'qlcv'); ?></span></button>
                                            <div id="create_foreign_partner" style="display: none;">
                                                <?php 
                                                    # add new foreign partner form
                                                    form_addnew_partner('foreign_partner');
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-20">
                                        <h4><?php _e('Chọn khách hàng từ trong danh sách', 'qlcv'); ?> <span class="text-danger">*</span></h4>
                                        <select class="form-control select2-tags mb-20" name="customer">
                                            <option value="">-- <?php _e('Chọn khách hàng', 'qlcv'); ?> --</option>
                                            <?php echo asl_get_customer_select_options(); ?>
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
                                        <h4><?php _e('Chọn người quản lý (A)', 'qlcv'); ?></h4>
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

                                        <!-- Chọn người được tham vấn (C), có thể chọn nhiều -->
                                        <h4 style="margin-top: 30px;"><?php _e('Chọn người được tham vấn (C)', 'qlcv'); ?></h4>
                                        <select class="form-control select2-tags mb-20" multiple="" name="co_manager">
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

                                        <h4 style="margin-top: 30px;"><?php _e('Chọn người thực hiện (R)', 'qlcv'); ?></h4>
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

                                        <!-- Chọn người cùng thực hiện, có thể chọn nhiều -->
                                        <h4 style="margin-top: 30px;"><?php _e('Chọn người cùng thực hiện (R1)', 'qlcv'); ?></h4>
                                        <select class="form-control select2-tags mb-20" multiple="" name="co_member">
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
                                        <h4 style="margin-top: 30px;"><?php _e('Chọn người giám sát (I)', 'qlcv'); ?></h4>
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