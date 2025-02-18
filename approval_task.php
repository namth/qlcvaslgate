<?php
/*
    Template Name: Phê duyệt nhiệm vụ (task)
*/
if (isset($_GET['taskid'])  && ($_GET['taskid'] != "")) {
    # lấy dữ liệu bài viết
    $postid     = $_GET['taskid'];
    $id_email   = get_field('email', $postid);
    $thong_bao  = get_field('thong_bao', $postid);
    $job        = get_field('job', $postid);
    if (!$job) {
        # nếu không lấy được id của job thì có nghĩa đây là công việc nhỏ, gán id task cho job
        $job    = $postid;
    }
    $phan_loai  = get_field('phan_loai', $job);
    $post_title = get_the_title($postid);

    $current_user = wp_get_current_user();
    $current_time = current_time('timestamp', 7);
    # nếu không có thông báo thì hoàn thành luôn
    if (!$thong_bao) {
        # chuyển trạng thái là Hoàn thành
        update_field('field_600fde92f9be9', 'Hoàn thành', $postid);
        $noi_dung = "nhiệm vụ đã được phê duyệt";
        # lưu lịch sử 
        $row_update = array(
            'nguoi_thuc_hien'   => $current_user,
            'noi_dung'          => $noi_dung,
            'thoi_gian'         => $current_time,
        );

        $logs = get_field('history', $postid);
        if ($logs) {
            array_unshift($logs, $row_update);
            update_field('field_6010e02533119', $logs, $postid);
        } else add_row('field_6010e02533119', $row_update, $postid);

        # push notification
        $content_notif = $noi_dung . " về việc <b>" . get_the_title($postid) . "</b>";
        if ($job) {
            $content_notif .= " của " . get_the_title($job);
        }

        $receiver = get_field('receiver', 'user_' . $current_user->ID);
        $manager = get_field('manager', $postid);
        create_notification($postid, $content_notif, $manager['ID'], $receiver);

        # quay về trang single_task
        wp_redirect(get_permalink($postid));
        exit;
    } else if (
        isset($_POST['post_nonce_field']) &&
        wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')
    ) {

        $to = $_POST['email_to'];
        $email_title = $_POST['email_title'];
        $email_content = stripslashes($_POST['email_content']);
        $email_cc = explode(',', $_POST['email_cc']);
        $email_bcc = explode(',', $_POST['email_bcc']);
        $action = $_POST['action'];
        $current_attachments = explode(PHP_EOL, get_field('attachments', $id_email));

        // Xử lý file đính kèm
        $attachments = $current_attachments;
        // print_r($_FILES);
        if (!empty($_FILES['attachments']['name'][0])) {  // Kiểm tra xem có file nào được tải lên không
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';

            $files = $_FILES['attachments'];
            $upload_overrides = array( 'test_form' => false );  // Bỏ qua kiểm tra form (quan trọng!)

            foreach ($files['name'] as $key => $value) {
                if ($files['error'][$key] == UPLOAD_ERR_OK) { // Kiểm tra lỗi upload

                    $file = array(
                        'name' => $files['name'][$key],
                        'type' => $files['type'][$key],
                        'tmp_name' => $files['tmp_name'][$key],
                        'error' => $files['error'][$key],
                        'size' => $files['size'][$key]
                    );

                    $upload_results = wp_handle_upload($file, $upload_overrides);

                    if ( $upload_results && ! isset( $upload_results['error'] ) ) {
                        // File đã được tải lên thành công
                        $attachments[] = $upload_results['file']; // Đường dẫn đến file đã tải lên
                    } else {
                        // Xử lý lỗi tải lên
                        echo 'Lỗi tải lên tệp: ' . $upload_results['error'];
                        exit; // Dừng nếu có lỗi
                    }
                } else {
                    // Xử lý các lỗi upload khác (ví dụ: dung lượng quá lớn)
                    echo '<p style=\'color:red;\'>Lỗi tải lên tệp: Mã lỗi ' . $files['error'][$key] . '</p>';
                    exit; // Dừng nếu có lỗi
                }
            }
        }

        # if action = 1 then send email directly to partner, else save email template, and wait for manager approval
        if ($action) {
            $headers[] = 'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>';
            foreach ($email_cc as $email) {
                $headers[] = 'Cc: ' . $email;
            }
            foreach ($email_bcc as $email) {
                $headers[] = 'Bcc: ' . $email;
            }

            #send email to partner
            $sent = wp_mail($to, $email_title, $email_content, $headers, $attachments);

            // 5. Xử lý kết quả và hiển thị thông báo
            if ($sent) {
                // Xóa các file đã tải lên sau khi gửi thành công
                foreach ($attachments as $file_path) {
                    unlink($file_path); // Xóa file khỏi server
                }
            }

            # chuyển trạng thái là Hoàn thành
            update_field('field_600fde92f9be9', 'Hoàn thành', $postid);
        } else {
            # kiểm tra xem đã có mail mẫu chưa
            $email = get_field('email', $postid);

            # cập nhật vào mail 
            if ($email) {
                # nếu có mẫu mail thì cập nhật
                $email_id = wp_update_post(array(
                    'ID'            => $email,
                    'post_title'    => $email_title,
                    'post_content'  => $email_content,
                ));

            } else {
                # nếu không có mẫu mail thì tạo mới
                $args = array(
                    'post_title'    => $email_title,
                    'post_content'  => $email_content,
                    'post_status'   => 'publish',
                    'post_type'     => 'email',
                );

                $email_id = wp_insert_post($args, $error);

                # update vào task
                update_field('field_60f8d9ec31144', $email_id, $postid);
            }

            update_field('field_60f8d97d1b0f8', $to, $email_id); # email_to
            if ($email_cc) {
                update_field('field_60f8d98a1b0f9', implode(',', $email_cc), $email_id); # email_cc
            }
            if ($email_bcc) {
                update_field('field_60f8d9911b0fa', implode(',', $email_bcc), $email_id); # email_bcc
            }
            # if have attachments, imploded them with PHP_EOL and save to ACF field: field_67add7781ca67
            if ($attachments) {
                update_field('field_67add7781ca67', implode(PHP_EOL, $attachments), $email_id);
            }
            # chuyển trạng thái là Hoàn thành
            update_field('field_600fde92f9be9', 'Quản lý đã phê duyệt', $postid);
        }

        $noi_dung = "nhiệm vụ đã được phê duyệt.";
        # lưu lịch sử 
        $row_update = array(
            'nguoi_thuc_hien'   => $current_user,
            'noi_dung'          => $noi_dung,
            'thoi_gian'         => $current_time,
        );

        $logs = get_field('history', $postid);
        if ($logs) {
            array_unshift($logs, $row_update);
            update_field('field_6010e02533119', $logs, $postid);
        } else add_row('field_6010e02533119', $row_update, $postid);


        wp_redirect(get_permalink($postid));
        exit;
    }
}
get_header();

get_sidebar();

# xử lý khi cần thông báo cho khách hàng
$code = get_field('ma_code', $job);
if (!$code) {
    # Tạo mã code
    $code = base64_encode($job);
    update_field('field_606fe68f81af2', $code, $job);
}

# lấy thông tin người cần gửi
$customer       = get_field('customer', $job);
$customer_name  = get_field('ten_cong_ty', $customer->ID);
$customer_address = get_field('dia_chi', $customer->ID);
$so_don         = get_field('so_don', $job);
$ngay_nop_don   = get_field('ngay_nop_don', $job);
$so_bang        = get_field('so_bang', $job);
$ngay_cap_bang  = get_field('ngay_cap_bang', $job);
$partner        = get_field('partner_2', $job);
$partner_ref    = get_field('partner_ref', $job);
$our_ref        = get_field('our_ref', $job);
$to             = $partner['user_email'];
$email_cc       = get_field('email_cc', 'user_' . $partner['ID']);
$email_bcc      = get_field('email_bcc', 'user_' . $partner['ID']);
$partner_company_name = get_field('ten_cong_ty', 'user_' . $partner['ID']);
$partner_company_name = get_field('ten_cong_ty', 'user_' . $partner['ID']);

# get email template or email content (if it's exists)
if ($id_email) {
    # get email content
    $email_title = get_the_title($id_email);
    $email_post = get_post($id_email);
    $email_content = $email_post->post_content;
    $to         = get_field('email_to', $id_email);
    $email_cc   = get_field('email_cc', $id_email);
    $email_bcc  = get_field('email_bcc', $id_email);
    $attachments = explode(PHP_EOL, get_field('attachments', $id_email));
} else {
    $email_post = get_email_template($post_title, $phan_loai, 'email');
    if ($email_post) {
        $replace_arr = array(
            '{code}'                    => $code,
            '{link}'                    => get_permalink($job),
            '{partner_company_name}'    => $partner_company_name,
            '{partner_email}'           => $to,
            '{partner_name}'            => $partner['display_name'],
            '{client_name}'             => $customer->post_title,
            '{partner_ref}'             => $partner_ref,
            '{our_ref}'                 => $our_ref,
            '{customer_name}'           => $customer_name,
            '{customer_address}'        => $customer_address,
            '{application_number}'      => $so_don,
            '{application_date}'        => $ngay_nop_don,
            '{certificate_number}'      => $so_bang,
            '{certificate_date}'        => $ngay_cap_bang,
        );
        $email_content  = replace_content($replace_arr, $email_post->post_content);
    }
    $email_title    = "Thông báo cập nhật mới cho công việc: " . get_the_title($job);
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
                echo '<h3 class="title">Phê duyệt và gửi mail cho đối tác</h3>';
                ?>
            </div>
        </div><!-- Page Heading End -->

    </div><!-- Page Headings End -->
    <form action="#" method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-12 col-lg-12 mb-20">
                <div class="row mbn-20">
                    <div class="col-lg-2 form_title lh45">Người nhận: </div>
                    <div class="col-lg-7 col-12 mb-20">
                        <input type="text" value="<?php echo $to; ?>" class="form-control" name="email_to">
                    </div>
                    <div class="col-lg-3"></div>

                    <div class="col-lg-2 form_title lh45">Email CC: </div>
                    <div class="col-lg-7 col-12 mb-20"><input type="text" class="form-control" name="email_cc" value="<?php echo $email_cc; ?>"></div>
                    <div class="col-lg-3"></div>

                    <div class="col-lg-2 form_title">Email BCC: </div>
                    <div class="col-lg-7 col-12 mb-20">
                        <input type="text" class="form-control" name="email_bcc" value="<?php echo $email_bcc; ?>">
                        <span class="form-help-text">Các email cách nhau bởi dấu ",".</span>
                    </div>
                    <div class="col-lg-3"></div>

                    <div class="col-lg-2 form_title">Tiêu đề: </div>
                    <div class="col-lg-7 col-12 mb-20">
                        <input type="text" class="form-control" name="email_title" value="<?php echo $email_title; ?>">
                    </div>
                    <div class="col-lg-3"></div>

                    <div class="col-lg-2 form_title">File đính kèm: </div>
                    <div class="col-lg-7 col-12 mb-20">
                        <input type="file" id="attachments" class="form-control" name="attachments[]" multiple>
                        <?php 
                        if (is_array($attachments) && count($attachments) > 0) {
                            echo '<ul class="list-attachments">';
                            foreach ($attachments as $file) {
                                echo '<li>
                                        <a href="' . $file . '">
                                            <i class="zmdi zmdi-file-text"></i>
                                            ' . basename($file) . '
                                        </a>
                                        <i class="zmdi zmdi-close-circle-o remove-attachment"></i>
                                    </li>';
                            }
                            echo '</ul>';

                            $attach_string = implode('|', $attachments);
                        } else {
                            $attach_string = '';
                        }
                        
                        echo '<input type="hidden" name="current_attachments" value="' . $attach_string . '">';
                        ?>
                        <input type="hidden" name="id_email" value="<?php echo $id_email; ?>">
                    </div>
                    <div class="col-lg-3"></div>

                    <div class="col-lg-2 form_title">Nội dung: </div>
                    <div class="col-lg-9 col-12 mb-20">
                        <?php //wp_editor($email_content, 'email_content');  ?>
                        <textarea class="summernote" name="email_content"><?php echo $email_content; ?></textarea>
                    </div>
                    <div class="col-lg-1 mb-20"></div>

                    <?php
                    wp_nonce_field('post_nonce', 'post_nonce_field');

                    if (in_array('contributor', $current_user->roles)) {
                    ?>
                        <div class="col-lg-2 form_title">Hành động </div>
                        <div class="col-lg-7 col-12 mb-20">
                            <label class="inline"><input type="radio" name="action" value="0" checked="">Gửi giám đốc duyệt</label>
                            <label class="inline"><input type="radio" name="action" value="1">Gửi trực tiếp cho khách</label>
                        </div>
                        <div class="col-lg-3"></div>
                    <?php
                    } else {
                        echo '<input type="hidden" name="action" value="1">';
                    }
                    ?>

                    <div class="col-lg-2"></div>
                    <div class="col-lg-7 col-12 mb-20">
                        <input type="submit" class="button button-primary" value="Hoàn thành">
                        <a href="javascript:history.go(-1)" class="button button-wikipedia">Huỷ bỏ</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

</div><!-- Content Body End -->

<?php
get_footer();
