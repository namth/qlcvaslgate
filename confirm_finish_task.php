<?php
/*
    Template Name: Xác nhận trạng thái hoàn thành nhiệm vụ
*/
if (isset($_GET['taskid'])  && ($_GET['taskid'] != "")) {
    # lấy dữ liệu bài viết
    $postid         = $_GET['taskid'];
    $current_user = wp_get_current_user();

    if (
        is_user_logged_in() &&
        isset($_POST['post_nonce_field']) &&
        wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')
    ) {

        $current_time = current_time('timestamp', 7);

        # Lấy dữ liệu từ form
        $action   = $_POST['action'];
        if ($action) {
            # TH soạn email gửi cho khách
            # set "thong_bao" = true
            update_field('field_607d38c56ee32', true, $postid);
        } else {
            # TH hoàn thành luôn
            update_field('field_607d38c56ee32', false, $postid);
        }

        wp_redirect(get_bloginfo('url') . '/approval/?taskid=' . $postid);
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

    </div><!-- Page Headings End -->
    <form action="#" method="POST">
        <div class="row">
            <div class="col-12 col-lg-12 mb-20">
                <div class="row mbn-20">

                    <?php
                    $phan_loai = get_field('phan_loai', $job);
                    ?>

                    <div class="col-lg-3 form_title text-left text-lg-right">Hành động</div>
                    <div class="col-lg-9 col-12 mb-20">
                        <label class="inline"><input type="radio" name="action" value="0" checked="">Hoàn thành luôn</label>
                        <label class="inline"><input type="radio" name="action" value="1">Gửi email thông báo cho khách</label>
                    </div>

                    <?php
                    wp_nonce_field('post_nonce', 'post_nonce_field');
                    ?>

                    <div class="col-lg-3"></div>
                    <div class="col-lg-6 col-12 mb-20">
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
?>