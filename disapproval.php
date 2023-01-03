<?php
/*
    Template Name: Không phê duyệt nhiệm vụ (task)
*/
$history_link   = $_SERVER['HTTP_REFERER'];

if (isset($_GET['taskid'])  && ($_GET['taskid'] != "")) {
    # lấy dữ liệu bài viết
    $postid        = $_GET['taskid'];
    $thong_bao    = get_field('thong_bao', $postid);
    $job        = get_field('job', $postid);
    if (!$job) {
        # nếu không lấy được id của job thì có nghĩa đây là công việc nhỏ, gán id task cho job
        $job    = $postid;
    }
    $phan_loai     = get_field('phan_loai', $job);
    $post_title = get_the_title($postid);

    $current_user = wp_get_current_user();
    $current_time = current_time('timestamp', 7);
    # nếu không có thông báo thì hoàn thành luôn
    if (
        isset($_POST['post_nonce_field']) &&
        wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')
    ) {

        $to = $_POST['email_to'];
        $email_title = $_POST['email_title'];
        $email_content = auto_url($_POST['email_content']);
        $email_cc = explode(',', $_POST['email_cc']);
        $email_bcc = explode(',', $_POST['email_bcc']);
        $link_file = $_POST['link_file'];
        $email_content .= "<br>Link tham khảo: " . $link_file;
        $history_link   = $_POST['history_link'];

        $headers[] = 'From: <admin@qlcv.aslgate.com>';
        foreach ($email_cc as $email) {
            $headers[] = 'Cc: ' . $email;
        }
        foreach ($email_bcc as $email) {
            $headers[] = 'Bcc: ' . $email;
        }
        #send email to partner
        wp_mail($to, $email_title, $email_content, $headers);

        # chuyển trạng thái là Đang thực hiện
        update_field('field_600fde92f9be9', 'Đang thực hiện', $postid);
        $noi_dung = __("nhiệm vụ không được phê duyệt.", 'qlcv') . "<br>";

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


        wp_redirect($history_link);
        exit;
    }
}
get_header();

get_sidebar();

# lấy thông tin người cần gửi
$user_arr       = get_field('user', $postid);
$our_ref        = get_field('our_ref', $job);
$email_title    = __("Từ chối phê duyệt đầu việc: ", 'qlcv') . get_the_title($job) . " (" . __("Số REF: ", 'qlcv') . $our_ref . ")";
$email_content  = "";

?>

<!-- Content Body Start -->
<div class="content-body">

    <!-- Page Headings Start -->
    <div class="row justify-content-between align-items-center mb-10">

        <!-- Page Heading Start -->
        <div class="col-12 col-lg-12 mb-20">
            <div class="page-heading">
                <?php
                echo '<h3 class="title">' . __('Từ chối phê duyệt nhiệm vụ', 'qlcv') . '</h3>';
                ?>
            </div>
        </div><!-- Page Heading End -->

    </div><!-- Page Headings End -->
    <form action="#" method="POST">
        <div class="row">
            <div class="col-12 col-lg-12 mb-20">
                <div class="row mbn-20">
                    <div class="col-lg-3 form_title lh45"><?php _e('Người nhận:', 'qlcv'); ?> </div>
                    <div class="col-lg-6 col-12 mb-20">
                        <input type="text" value="<?php echo $user_arr['user_email']; ?>" class="form-control" name="email_to">
                    </div>
                    <div class="col-lg-3"></div>

                    <div class="col-lg-3 form_title lh45">Email CC: </div>
                    <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="email_cc" value=""></div>
                    <div class="col-lg-3"></div>

                    <div class="col-lg-3 form_title">Email BCC: </div>
                    <div class="col-lg-6 col-12 mb-20">
                        <input type="text" class="form-control" name="email_bcc" value="">
                        <span class="form-help-text"><?php _e('Các email cách nhau bởi dấu ",".', 'qlcv'); ?></span>
                    </div>
                    <div class="col-lg-3"></div>

                    <div class="col-lg-3 form_title"><?php _e('Tiêu đề:', 'qlcv'); ?> </div>
                    <div class="col-lg-6 col-12 mb-20">
                        <input type="text" class="form-control" name="email_title" value="<?php echo $email_title; ?>">
                    </div>
                    <div class="col-lg-3"></div>

                    <div class="col-lg-3 form_title"><?php _e('Nội dung:', 'qlcv'); ?> </div>
                    <div class="col-lg-8 col-12 mb-20">
                        <?php wp_editor($email_content, 'email_content');  ?>
                    </div>
                    <div class="col-lg-1 mb-20"></div>

                    <div class="col-lg-3 form_title">Link file: </div>
                    <div class="col-lg-6 col-12 mb-20">
                        <input type="text" class="form-control" name="link_file" value="">
                    </div>
                    <div class="col-lg-3"></div>

                    <?php
                    echo '<input type="hidden" name="history_link" value="' . $history_link . '">';
                    wp_nonce_field('post_nonce', 'post_nonce_field');
                    ?>

                    <div class="col-lg-3"></div>
                    <div class="col-lg-6 col-12 mb-20">
                        <input type="submit" class="button button-primary" value="<?php _e('Gửi', 'qlcv'); ?>">
                        <a href="javascript:history.go(-1)" class="button button-wikipedia"><?php _e('Huỷ bỏ', 'qlcv'); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </form>

</div><!-- Content Body End -->

<?php
get_footer();
