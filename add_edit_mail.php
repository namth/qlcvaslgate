<?php
/*
    Template Name: Tạo mới và sửa mẫu mail
*/
$history_link   = $_SERVER['HTTP_REFERER'];

if (isset($_GET['code'])  && ($_GET['code'] != "")) {
    # lấy dữ liệu được truyền vào
    $code = explode('/', base64_decode($_GET['code']));
    $group_name     = $code[0];
    $email_title    = $code[1];
}
if (isset($_GET['mail'])  && ($_GET['mail'] != "")) {
    $email_id = $_GET['mail'];
    $email_post = get_post($email_id);

    $email_title = get_the_title($email_id);
    $email_content = $email_post->post_content;
}

if (
    isset($_POST['post_nonce_field']) &&
    wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')
) {

    $email_title = $_POST['email_title'];
    $email_content = $_POST['email_content'];
    $email = $_POST['mail'];
    $history_link   = $_POST['history_link'];

    # cập nhật vào mail 
    if ($email) {
        # nếu có mẫu mail thì cập nhật
        $update_email = wp_update_post(array(
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

        $inserted = wp_insert_post($args, $error);
        wp_set_object_terms($inserted, $group_name, 'group');
    }

    wp_redirect($history_link);
    exit;
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
                echo '<h3 class="title">' . __('Sửa email', 'qlcv') . '</h3>';
                ?>
            </div>
        </div><!-- Page Heading End -->

    </div><!-- Page Headings End -->
    <form action="#" method="POST">
        <div class="row">
            <div class="col-12 col-lg-12 mb-20">
                <div class="row mbn-20">

                    <div class="col-lg-2 form_title"><?php _e('Tiêu đề: ', 'qlcv'); ?></div>
                    <div class="col-lg-7 col-12 mb-20">
                        <?php echo $email_title; ?>
                        <input type="hidden" name="email_title" value="<?php echo $email_title; ?>">
                    </div>
                    <div class="col-lg-3"></div>

                    <div class="col-lg-2 form_title"><?php _e('Nội dung: ', 'qlcv'); ?></div>
                    <div class="col-lg-9 col-12 mb-20">
                        <textarea class="summernote" name="email_content"><?php echo $email_content;  ?></textarea>
                    </div>
                    <div class="col-lg-1 mb-20"></div>

                    <?php
                    if ($email_id) {
                        echo '<input type="hidden" name="mail" value="' . $email_id . '">';
                    }
                    echo '<input type="hidden" name="history_link" value="' . $history_link . '">';
                    wp_nonce_field('post_nonce', 'post_nonce_field');
                    ?>

                    <div class="col-lg-2"></div>
                    <div class="col-lg-7 col-12 mb-20">
                        <input type="submit" class="button button-primary" value="<?php _e('Hoàn thành', 'qlcv'); ?>">
                        <a href="javascript:history.go(-1)" class="button button-wikipedia"><?php _e('Huỷ bỏ', 'qlcv'); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="row justify-content-between align-items-center mb-10">
        <div class="col-12 col-lg-12 mb-20">
            <h4><?php _e('Hướng dẫn mã code', 'qlcv'); ?></h4>
            <p><?php _e('Sử dụng mã code dưới đây để soạn nội dung email', 'qlcv'); ?></p>
            <div class="order-details-customer-info">
                <ul>
                    <li><span>{code}</span> <span><?php _e('Mã code để truy cập đầu việc', 'qlcv'); ?></span></li>
                    <li><span>{link}</span> <span><?php _e('Đường link truy cập đầu việc', 'qlcv'); ?></span></li>
                    <li><span>{partner_company_name}</span> <span><?php _e('Tên đối tác gửi việc', 'qlcv'); ?></span></li>
                    <li><span>{partner_email}</span> <span><?php _e('Email đối tác', 'qlcv'); ?></span></li>
                    <li><span>{partner_name}</span> <span><?php _e('Tên người liên hệ', 'qlcv'); ?></span></li>
                    <li><span>{partner_ref}</span> <span><?php _e('Số REF của đối tác', 'qlcv'); ?></span></li>
                    <li><span>{our_ref}</span> <span><?php _e('Số REF của mình', 'qlcv'); ?></span></li>
                    <li><span>{customer_name}</span> <span><?php _e('Tên khách hàng', 'qlcv'); ?></span></li>
                    <li><span>{customer_address}</span> <span><?php _e('Địa chỉ khách hàng', 'qlcv'); ?></span></li>
                    <li><span>{application_number}</span> <span><?php _e('Số đơn', 'qlcv'); ?></span></li>
                    <li><span>{application_date}</span> <span><?php _e('Ngày nộp đơn', 'qlcv'); ?></span></li>
                    <li><span>{certificate_number}</span> <span><?php _e('Số bằng', 'qlcv'); ?></span></li>
                    <li><span>{certificate_date}</span> <span><?php _e('Ngày cấp bằng', 'qlcv'); ?></span></li>

                </ul>
            </div>
        </div>

    </div>
</div><!-- Content Body End -->
<?php
get_footer();
