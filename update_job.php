<?php
/*
    Template Name: Sửa công việc
*/
$history_link   = $_SERVER['HTTP_REFERER'];

if (isset($_GET['jobid'])  && ($_GET['jobid'] != "")) {
    # lấy dữ liệu bài viết
    $current_post = get_post($postid);
    $postid = $_GET['jobid'];
    $current_user = wp_get_current_user();
    $current_time = current_time('timestamp', 7);
    $phan_loai = get_field('phan_loai', $postid);

    if (
        is_user_logged_in() &&
        isset($_POST['post_nonce_field']) &&
        wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')
    ) {

        # Lấy dữ liệu từ form
        #partner
        $partner        = $_POST['partner'];
        $foreign_partner= $_POST['foreign_partner'];
        $member         = $_POST['member'];
        $manager        = $_POST['manager'];
        $agency         = $_POST['agency'];
        # job
        $jobname        = $_POST['jobname'];
        $so_don         = $_POST['so_don'];
        $so_bang        = $_POST['so_bang'];
        $mindful        = $_POST['mindful'];
        $note           = $_POST['note'];
        $link_onedrive  = $_POST['link_onedrive'];
        # work process
        $work_process   = $_POST['work_process'];
        $work_date      = $_POST['work_date'];
        $other_work_process = $_POST['other_work_process'];
        $other_work_date    = $_POST['other_work_date'];
        # get form cash in
        $total_value    = $_POST['total_value'];
        $paid           = $_POST['paid'];
        $remainning     = $_POST['remainning'];
        $currency       = $_POST['currency'];
        # get form cash out
        $total_cost     = $_POST['total_cost'];
        $advance_money  = $_POST['advance_money'];
        $debt           = $_POST['debt'];
        $currency_out   = $_POST['currency_out'];
        $history_link   = $_POST['history_link'];


        # nếu jobname có thay đổi thì mới update
        if (($jobname != "") && ($current_post->post_title != $jobname)) {
            $update = wp_update_post(array(
                'ID'            => $postid,
                'post_title'    => $jobname,
                'post_content'  => $note,
            ));
        }

        if ($so_don) {
            update_field('field_606ec70f02a63', $so_don, $postid);
            $post_date      = $_POST['ngay_nop_don'];
            $date_tmp_arr   = explode('/', $post_date);
            $temp_date      = array_reverse($date_tmp_arr);
            $ngay_nop_don   = implode('', $temp_date);

            update_field('field_606ec71f02a64', $ngay_nop_don, $postid);
        }
        if ($so_bang) {
            update_field('field_606ec7aa02a65', $so_bang, $postid);
            $post_date      = $_POST['ngay_cap_bang'];
            $date_tmp_arr   = explode('/', $post_date);
            $temp_date      = array_reverse($date_tmp_arr);
            $ngay_cap_bang  = implode('', $temp_date);

            update_field('field_606ec7b602a66', $ngay_cap_bang, $postid);
        }

        update_field('field_60a38cc126a5f', $link_onedrive, $postid); # Link tài liệu
        update_field('field_60fceb18a736d', $mindful, $postid); # Lưu ý công việc
        # partner
        update_field('field_602f7923c59bb', $partner, $postid); # save to partner_2 field
        update_field('field_609bf99f726ef', $foreign_partner, $postid); 
        update_field('field_603627f913b2c', $member, $postid); # save to member, manager field
        update_field('field_603629217fe93', $manager, $postid); 
        # work process 
        if (is_array($work_process)) {
            for ($i=0; $i < count($work_process); $i++) { 
                if ($work_process[$i] && $work_date[$i]) {
                    # cập nhật lịch sử
                    update_job_history($work_process[$i], $work_date[$i], $postid);
                    print_r('update: ' . $work_process[$i] . '<br>');
                }
            }
        }
        if ($other_work_process && $other_work_date) {
            update_job_history($other_work_process, $other_work_date, $postid);
        }
        #finance of this job
        update_field('field_60a231d395dd8', $total_value, $postid);
        update_field('field_60a231d395f2e', $paid, $postid);
        update_field('field_60a231d3961b0', $remainning, $postid);
        update_field('field_60a231d39602e', $currency, $postid);

        update_field('field_60afae64cfd69', $total_cost, $postid);
        update_field('field_60afaeb8cfd6a', $advance_money, $postid);
        update_field('field_60afaf50cfd6b', $debt, $postid);
        update_field('field_60afafbccfd6c', $currency_out, $postid);

        #agency
        if($agency){
            wp_set_object_terms($postid, $agency, 'agency');
        }

        switch ($phan_loai) {
            case 'Nhãn hiệu':
                # job infomation
                $ten_nhan_hieu  = $_POST['ten_nhan_hieu'];
                $nhom           = $_POST['nhom'];
                $so_luong_nhom  = $_POST['so_luong_nhom'];
                
                
                if (isset($_FILES['file_upload'])) {
                    echo "Upload: <br>";
                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                    $uploadedfile = $_FILES['file_upload'];
                    # check neu file upload khong co loi, tuc la khong empty
                    if ($uploadedfile["error"] == 0) {
                        $movefile = wp_handle_upload($uploadedfile, array('test_form' => false));
                        //On sauvegarde la photo dans le média library
                        if ($movefile) {
                            $wp_upload_dir = wp_upload_dir();
                            $attachment = array(
                                'guid' => $wp_upload_dir['url'] . '/' . basename($movefile['file']),
                                'post_mime_type' => $movefile['type'],
                                'post_title' => preg_replace('/\.[^.]+$/', '', basename($movefile['file'])),
                                'post_content' => '',
                                'post_status' => 'inherit',
                            );
                            $attach_id = wp_insert_attachment($attachment, $movefile['file']);
    
                            update_field('field_600fdca20269f', $attach_id, $postid);
                        }
                    }
                }
                update_field('field_600fd7db6154d', $ten_nhan_hieu, $postid);
                update_field('field_600fd7ec6154e', $nhom, $postid);
                update_field('field_600fd7f46154f', $so_luong_nhom, $postid);

                break;

            case 'Kiểu dáng':
                $kdang_pic      = $_POST['bo_anh'];
                $kdang_info     = $_POST['ban_mo_ta_bo_anh'];
                $kdang_phuongan = $_POST['so_luong_phuong_an'];
                
                update_field('field_600fd8b88a1c1', $kdang_pic, $postid);
                update_field('field_600fd8f38a1c2', $kdang_info, $postid);
                update_field('field_600fd9048a1c3', $kdang_phuongan, $postid);
                break;
                
            case 'Sáng chế':
                $ban_mota_sangche   = $_POST['ban_mota_sangche'];
                $slyc_baoho         = $_POST['slyc_baoho'];
                $slyc_baoho_doclap  = $_POST['slyc_baoho_doclap'];

                update_field('field_600fd84dcbfa2', $ban_mota_sangche, $postid);
                update_field('field_600fd874cbfa3', $slyc_baoho, $postid);
                update_field('field_600fd895cbfa5', $slyc_baoho_doclap, $postid);
                break;
        }


        wp_redirect($history_link);
    }
}
get_header();

get_sidebar();

$terms      = get_the_terms($postid, 'group');
$term_names = wp_list_pluck($terms, 'name');
$agency     = get_the_terms($postid, 'agency');
// print_r($agency);
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

    </div><!-- Page Headings End -->
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-12 col-lg-12 mb-20">
                <?php
                if (isset($update)) {
                    if ($update) {
                        echo '<div class="alert alert-success" role="alert">
                                            <i class="fa fa-check"></i> ' . __('Bài viết đã được cập nhật.', 'qlcv') . '
                                          </div>';
                    } else {
                        echo '<div class="alert alert-danger" role="alert">
                                            <i class="zmdi zmdi-info"></i> ' . __('Xảy ra lỗi, không thể cập nhật.', 'qlcv') . '
                                          </div>';
                    }
                } else {
                    $content_post   = get_post($_GET['jobid']);
                    $partner        = get_field('partner_2', $postid);
                    $foreign_partner= get_field('foreign_partner', $postid);
                    $manager        = get_field('manager', $postid);
                    $member         = get_field('member', $postid);
                    // $currency         = get_field('currency', $postid);

                    // print_r($currency);
                ?>
                    <div class="box">
                        <div class="box-head">
                            <h3 class="title"><?php _e('Nhân sự tham gia', 'qlcv'); ?></h3>
                        </div>
                        <div class="box-body">
                            <div class="row mbn-20">
                                <div class="col-6 mb-20">
                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Công việc', 'qlcv'); ?></b></label>
                                        <input type="text" value="<?php echo $content_post->post_title; ?>" name="jobname" class="form-control">
                                    </div>

                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Đối tác gửi việc', 'qlcv'); ?></b></label>
                                        <select class="form-control select2-tags mb-20" name="partner">
                                            <?php
                                            if ($partner["ID"]) {
                                                echo "<option value='" . $partner['ID'] . "'>" . $partner['display_name'] . " (" . $partner['user_email'] . ")</option>";
                                            } else {
                                                echo '<option value="">-- ' . __('Chọn đối tác gửi việc', 'qlcv') . ' --</option>';
                                            }
                                            $args   = array(
                                                'role'    => 'partner', /*subscriber, contributor, author*/
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

                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Đối tác nhận việc', 'qlcv'); ?></b></label>
                                        <select class="form-control select2-tags mb-20" name="foreign_partner">
                                            <?php
                                            if ($foreign_partner["ID"]) {
                                                echo "<option value='" . $foreign_partner['ID'] . "'>" . $foreign_partner['display_name'] . " (" . $foreign_partner['user_email'] . ")</option>";
                                            } else {
                                                echo '<option value="">-- ' . __('Chọn đối tác nhận việc', 'qlcv') . ' --</option>';
                                            }
                                            $args   = array(
                                                'role'    => 'foreign_partner', /*subscriber, contributor, author*/
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

                                <div class="col-6 mb-20">
                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Người quản lý', 'qlcv'); ?></b></label>
                                        <select class="form-control select2-tags mb-20" name="manager">
                                            <?php
                                            if ($manager["ID"]) {
                                                echo "<option value='" . $manager['ID'] . "'>" . $manager['display_name'] . " (" . $manager['user_email'] . ")</option>";
                                            } else {
                                                echo '<option value="">-- ' . __('Chọn người quản lý', 'qlcv') . ' --</option>';
                                            }
                                            $args   = array(
                                                'role'    => 'contributor', /*subscriber, contributor, author*/
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

                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Người thực hiện', 'qlcv'); ?></b></label>
                                        <select class="form-control select2-tags mb-20" name='member'>
                                            <?php
                                            if ($member["ID"]) {
                                                echo "<option value='" . $member['ID'] . "'>" . $member['display_name'] . " (" . $member['user_email'] . ")</option>";
                                            } else {
                                                echo '<option value="">-- ' . __('Chọn người thực hiện', 'qlcv') . ' --</option>';
                                            }
                                            $args   = array(
                                                'role__in'      => array('member', 'contributor'),
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

                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Chi nhánh', 'qlcv'); ?></b></label>
                                        <select class="form-control select2-tags mb-20" name='agency'>
                                            <option value="">-- <?php _e('Chi nhánh', 'qlcv'); ?> --</option>
                                            <?php
                                            $terms = get_terms(array(
                                                'taxonomy' => 'agency',
                                                'hide_empty' => false,
                                            ));
                                            if($terms){
                                                foreach ($terms as $value) {
                                                    $selected = ($agency[0]->slug == $value->slug) ? "selected" : "";
                                                    echo "<option value='" . $value->slug . "' " . $selected . ">" . $value->name . "</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="box">
                        <?php
                        if ($phan_loai == "Nhãn hiệu") {
                            $ten_nhan_hieu  = get_field('ten_nhan_hieu', $postid);
                            $nhom           = get_field('nhom', $postid);
                            $so_luong_nhom  = get_field('so_luong_nhom', $postid);
                            $logo           = get_field('logo', $postid);
                        ?>
                        <div class="box-head">
                            <h2 class="title"><?php _e('Thông tin nhãn hiệu', 'qlcv'); ?></h2>
                        </div>
                        <div class="box-body">
                            <div class="row mbn-20">
                                <div class="col-lg-6 col-12 mb-20">
                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Tên nhãn hiệu', 'qlcv'); ?> </b></label>
                                        <input type="text" placeholder="<?php _e('Tên nhãn hiệu', 'qlcv'); ?>" class="form-control mb-10" name="ten_nhan_hieu" value="<?php echo $ten_nhan_hieu; ?>">
                                    </div>
                                    
                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Nhóm', 'qlcv'); ?></b></label>
                                        <input type="text" placeholder="<?php _e('Nhóm', 'qlcv'); ?>" class="form-control mb-10" name="nhom" value="<?php echo $nhom; ?>">
                                    </div>
                                    
                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Số lượng nhóm', 'qlcv'); ?></b></label>
                                        <input type="text" placeholder="<?php _e('Số lượng nhóm', 'qlcv'); ?>" class="form-control mb-10" name="so_luong_nhom" value="<?php echo $so_luong_nhom; ?>">
                                    </div>
                                    
                                </div>
                                <div class="col-lg-6 col-12 mb-20">
                                    <div class="mb-20">
                                        <label for=""><b><?php _e('File ảnh', 'qlcv'); ?></b></label>
                                        <input class="dropify" type="file" name="file_upload">
                                        <img src="<?php echo $logo; ?>" alt="">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php 
                        } else if ($phan_loai == "Kiểu dáng") {
                            $bo_anh                 = get_field('bo_anh', $postid);
                            $ban_mo_ta_cua_bo_anh   = get_field('ban_mo_ta_cua_bo_anh', $postid);
                            $so_luong_phuong_an     = get_field('so_luong_phuong_an', $postid);
                        ?>
                        <div class="box-head">
                            <h2 class="title"><?php _e('Thông tin kiểu dáng', 'qlcv'); ?></h2>
                        </div>
                        <div class="box-body">
                            <div class="row mbn-20">
                                <div class="col-lg-8 col-12 mb-20">
                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Link tới bộ ảnh', 'qlcv'); ?> </b></label>
                                        <input type="text" placeholder="<?php _e('Link tới bộ ảnh', 'qlcv'); ?>" class="form-control mb-10" name="bo_anh" value="<?php echo $ten_nhan_hieu; ?>">
                                    </div>
                                    
                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Link tới bản mô tả bộ ảnh', 'qlcv'); ?></b></label>
                                        <input type="text" placeholder="<?php _e('Bản mô tả bộ ảnh', 'qlcv'); ?>" class="form-control mb-10" name="ban_mo_ta_bo_anh" value="<?php echo $nhom; ?>">
                                    </div>
                                    
                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Số lượng phương án', 'qlcv'); ?></b></label>
                                        <input type="text" placeholder="<?php _e('Số lượng phương án', 'qlcv'); ?>" class="form-control mb-10" name="so_luong_phuong_an" value="<?php echo $so_luong_nhom; ?>">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-12 mb-20">
                                    
                                </div>
                            </div>
                        </div>
                        <?php 
                        } else if ($phan_loai == "Sáng chế") {
                            $ban_mo_ta_sang_che     = get_field('ban_mo_ta_sang_che', $postid);
                            $so_luong_yeu_cau_bao_ho    = get_field('so_luong_yeu_cau_bao_ho', $postid);
                            $so_luong_yeu_cau_bao_ho_doc_lap = get_field('so_luong_yeu_cau_bao_ho_doc_lap', $postid);
                        ?>
                        <div class="box-head">
                            <h2 class="title"><?php _e('Thông tin kiểu dáng', 'qlcv'); ?></h2>
                        </div>
                        <div class="box-body">
                            <div class="row mbn-20">
                                <div class="col-lg-8 col-12 mb-20">
                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Bản mô tả sáng chế', 'qlcv'); ?> </b></label>
                                        <input type="text" placeholder="<?php _e('Bản mô tả sáng chế', 'qlcv'); ?>" class="form-control mb-10" name="ban_mota_sangche" value="<?php echo $ban_mo_ta_sang_che; ?>">
                                    </div>
                                    
                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Số lượng yêu cầu bảo hộ', 'qlcv'); ?></b></label>
                                        <input type="text" placeholder="<?php _e('Số lượng yêu cầu bảo hộ', 'qlcv'); ?>" class="form-control mb-10" name="slyc_baoho" value="<?php echo $so_luong_yeu_cau_bao_ho; ?>">
                                    </div>
                                    
                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Số lượng yêu cầu bảo hộ độc lập', 'qlcv'); ?></b></label>
                                        <input type="text" placeholder="<?php _e('Số lượng yêu cầu bảo hộ độc lập', 'qlcv'); ?>" class="form-control mb-10" name="slyc_baoho_doclap" value="<?php echo $so_luong_yeu_cau_bao_ho_doc_lap; ?>">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-12 mb-20">
                                    
                                </div>
                            </div>
                        </div>
                        <?php 
                        }
                        ?>
                    </div>

                    <?php
                    if ($phan_loai != "Việc khác") {
                        $so_don         = get_field('so_don', $postid);
                        $ngay_nop_don   = get_field('ngay_nop_don', $postid);
                        $so_bang        = get_field('so_bang', $postid);
                        $ngay_cap_bang  = get_field('ngay_cap_bang', $postid);
                        $mindful        = get_field('mindful', $postid);
                        $link_onedrive  = get_field('link_onedrive', $postid);

                        $content_post = get_post($postid);

                    ?>
                    <div class="box">
                        <div class="box-head">
                            <h2 class="title"><?php _e('Nội dung công việc', 'qlcv'); ?></h2>
                        </div>
                        <div class="box-body">
                            <div class="row mbn-20">
                                <div class="col-lg-6 col-12 mb-20">
                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Số đơn', 'qlcv'); ?></b></label>
                                        <input type="text" value="<?php echo $so_don; ?>" name="so_don" class="form-control">
                                    </div>

                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Ngày nộp đơn', 'qlcv'); ?></b></label>
                                        <input type="text" value="<?php echo $ngay_nop_don; ?>" name="ngay_nop_don" class="form-control" data-mask="99/99/9999">
                                    </div>

                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Số bằng', 'qlcv'); ?></b></label>
                                        <input type="text" value="<?php echo $so_bang; ?>" name="so_bang" class="form-control">
                                    </div>

                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Ngày cấp bằng', 'qlcv'); ?></b></label>
                                        <input type="text" value="<?php echo $ngay_cap_bang; ?>" name="ngay_cap_bang" class="form-control" data-mask="99/99/9999">
                                    </div>

                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Tài liệu đi kèm', 'qlcv'); ?></b></label>
                                        <textarea class="form-control summernote" placeholder="<?php _e('Link tới tài liệu', 'qlcv'); ?>" name="link_onedrive"><?php echo $link_onedrive; ?></textarea>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-12 mb-20">
                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Nội dung chi tiết', 'qlcv'); ?></b></label>
                                        <textarea class="form-control summernote" placeholder="<?php _e('Thông tin bổ sung', 'qlcv'); ?>" name="note"><?php echo $content_post->post_content; ?></textarea>
                                    </div>

                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Lưu ý công việc', 'qlcv'); ?></b></label>
                                        <textarea class="form-control summernote" placeholder="<?php _e('Lưu ý', 'qlcv'); ?>" name="mindful"><?php echo $mindful; ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php 
                    }
                    ?>

                    <div class="box">
                        <div class="box-head">
                            <h2 class="title"><?php _e('Lịch sử thực hiện', 'qlcv'); ?></h2>
                        </div>
                        <div class="box-body">
                            <div class="row mbn-20">
                                <div class="col-lg-6 col-12 mb-20">
                                    <div class="adomx-checkbox-radio-group">
                                        <?php 
                                            # get all default mile stone
                                            if (in_array("Tiềm năng", $term_names)) {
                                                $term       = get_term_by('name', 'Tiềm năng', 'group');
                                            } else {
                                                $term       = get_term_by('name', $phan_loai, 'group');
                                            }

                                            $work_list  = get_field('work_process', 'term_' . $term->term_id);
                                            $work_arr   = explode(PHP_EOL, $work_list);
                                            $work_arr   = array_map('trim', $work_arr);

                                            # get history of work that is current process
                                            $work_list  = get_field('lich_su_cong_viec', $postid);
                                            $work_history = array();
                                            foreach ($work_list as $key => $value) {
                                                $work_history[] = $value['mo_ta'];
                                                $work_date[] = $value['ngay_thang'];
                                            }

                                            $work_not_done = array_diff($work_arr, $work_history);

                                            // print_r($work_list);
                                            echo "<table>";
                                            for ($i=0; $i < count($work_history); $i++) {
                                                echo "<tr>";
                                                echo '<td><label class="adomx-checkbox">
                                                        <input type="checkbox" value="' . $work_history[$i] . '" checked disabled> <i class="icon"></i> <span class="text">' . $work_history[$i] . '</span>
                                                     </label></td>';
                                                echo "<td>" . $work_date[$i] . "</td>";
                                                echo "</tr>";
                                            }
                                            
                                            foreach ($work_not_done as $process) {
                                                if ($process) {
                                                    echo "<tr>";
                                                    echo '<td><label class="adomx-checkbox">
                                                            <input type="checkbox" name="work_process[]" value="' . $process . '"> <i class="icon"></i> <span class="text">' . $process . '</span>
                                                            </label></td>';
                                                            
                                                    echo '<td><input type="text" value="" name="work_date[]" class="form-control" data-mask="99/99/9999"></td>';
                                                    echo "</tr>";
                                                }
                                            }
                                            echo "<tr>";
                                            echo '<td>' . __('Nội dung khác', 'qlcv') . ' <input type="text" value="" name="other_work_process" class="form-control"></td>';
                                            echo '<td>' . __('Ngày cập nhật', 'qlcv') . ' <input type="text" value="" name="other_work_date" class="form-control" data-mask="99/99/9999"></td>';
                                            echo "</tr>";

                                            echo "</table>";
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                    # get field cash in
                    $total_value    = get_field('total_value', $postid);
                    $paid           = get_field('paid', $postid);
                    $remainning     = get_field('remainning', $postid);
                    $currency       = get_field('currency', $postid);
                    # get field cash out
                    $total_cost     = get_field('total_cost', $postid);
                    $advance_money  = get_field('advance_money', $postid);
                    $debt           = get_field('debt', $postid);
                    $currency_out   = get_field('currency_out', $postid);
                    $currency_list  = array('USD', 'VND');

                    ?>
                    <div class="box">
                        <div class="box-head">
                            <h2 class="title"><?php _e('Tài chính công việc', 'qlcv'); ?></h2>
                        </div>
                        <div class="box-body finance">
                            <div class="row mbn-20">
                                <div class="col-lg-6 col-12 mb-20">
                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Tổng tiền cần thanh toán', 'qlcv'); ?></b></label>
                                        <input type="text" value="<?php echo $total_value; ?>" name="total_value" class="form-control">
                                    </div>

                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Đã thanh toán', 'qlcv'); ?></b></label>
                                        <input type="text" value="<?php echo $paid; ?>" name="paid" class="form-control">
                                    </div>

                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Số tiền còn lại', 'qlcv'); ?></b></label>
                                        <input type="text" value="<?php echo $remainning; ?>" name="remainning" class="form-control">
                                    </div>

                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Loại tiền tệ', 'qlcv'); ?></b></label>
                                        <div class="form-group">
                                            <?php
                                            foreach ($currency_list as $crcy) {
                                                $checked = ($crcy == $currency) ? 'checked' : '';
                                                echo '<label class="inline lh45">
                                                    <input type="radio" name="currency" value="' . $crcy . '" ' . $checked . '>' . $crcy . '</label>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6 col-12 mb-20">
                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Chi phí cho đối tác nước ngoài', 'qlcv'); ?></b></label>
                                        <input type="text" value="<?php echo $total_cost; ?>" name="total_cost" class="form-control">
                                    </div>

                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Đã tạm ứng cho đối tác nước ngoài', 'qlcv'); ?></b></label>
                                        <input type="text" value="<?php echo $advance_money; ?>" name="advance_money" class="form-control">
                                    </div>

                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Tiền còn nợ', 'qlcv'); ?></b></label>
                                        <input type="text" value="<?php echo $debt; ?>" name="debt" class="form-control">
                                    </div>

                                    <div class="mb-20">
                                        <label for=""><b><?php _e('Loại tiền tệ', 'qlcv'); ?></b></label>
                                        <div class="form-group">
                                            <?php
                                            foreach ($currency_list as $crcy) {
                                                $checked = ($crcy == $currency_out) ? 'checked' : '';
                                                echo '<label class="inline lh45">
                                                    <input type="radio" name="currency_out" value="' . $crcy . '" ' . $checked . '>' . $crcy . '</label>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="box">
                        <div class="box-body">
                            <div class="row mbn-20">

                                <?php
                                echo '<input type="hidden" name="history_link" value="' . $history_link . '">';
                                wp_nonce_field('post_nonce', 'post_nonce_field');
                                ?>

                                <div class="col-lg-3"></div>
                                <div class="col-lg-6 col-12 mb-20"><input type="submit" class="button button-primary" value="<?php _e('Cập nhật', 'qlcv'); ?>"> <a href="javascript:history.go(-1)" class="button button-wikipedia"><?php _e('Huỷ bỏ', 'qlcv'); ?></a></div>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    </form>

</div><!-- Content Body End -->
<script src="<?php echo get_template_directory_uri(); ?>/assets/js/update_job.js"></script>

<?php
get_footer();
?>