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
                echo '<h3 class="title">Sửa email</h3>';
                ?>
            </div>
        </div><!-- Page Heading End -->

    </div><!-- Page Headings End -->
    <form action="#" method="POST">
        <div class="row">
            <div class="col-12 col-lg-12 mb-20">
                <div class="row mbn-20">

                    <div class="col-lg-2 form_title">Tiêu đề: </div>
                    <div class="col-lg-7 col-12 mb-20">
                        <?php echo $email_title; ?>
                        <input type="hidden" name="email_title" value="<?php echo $email_title; ?>">
                    </div>
                    <div class="col-lg-3"></div>

                    <div class="col-lg-2 form_title">Nội dung: </div>
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
                        <input type="submit" class="button button-primary" value="Hoàn thành">
                        <a href="javascript:history.go(-1)" class="button button-wikipedia">Huỷ bỏ</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="row justify-content-between align-items-center mb-10">
        <div class="col-12 col-lg-12 mb-20">
            <h4>Hướng dẫn mã code</h4>
            <p>Sử dụng mã code dưới đây để </p>
            <div class="order-details-customer-info">
                <ul>
                    <li><span>{code}</span> <span>Mã code để truy cập đầu việc</span></li>
                    <li><span>{link}</span> <span>Đường link truy cập đầu việc</span></li>
                    <li><span>{partner_company_name}</span> <span>Tên đối tác gửi việc</span></li>
                    <li><span>{partner_email}</span> <span>Email đối tác</span></li>
                    <li><span>{partner_name}</span> <span>Tên người liên hệ</span></li>
                    <li><span>{partner_ref}</span> <span>Số REF của đối tác</span></li>
                    <li><span>{our_ref}</span> <span>Số REF của mình</span></li>
                    <li><span>{customer_name}</span> <span>Tên khách hàng</span></li>
                    <li><span>{customer_address}</span> <span>Địa chỉ khách hàng</span></li>
                    <li><span>{application_number}</span> <span>Số đơn</span></li>
                    <li><span>{application_date}</span> <span>Ngày nộp đơn</span></li>
                    <li><span>{certificate_number}</span> <span>Số bằng</span></li>
                    <li><span>{certificate_date}</span> <span>Ngày cấp bằng</span></li>

                </ul>
            </div>
        </div>

    </div>
</div><!-- Content Body End -->
<?php
get_footer();
