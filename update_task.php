<?php
/*
    Template Name: Cập nhật nội dung nhiệm vụ
*/
$history_link   = $_SERVER['HTTP_REFERER'];

if (
    isset($_GET['taskid'])  && ($_GET['taskid'] != "") &&
    isset($_POST['content']) && ($_POST['content'] != "")
) {
    if (is_user_logged_in()) {
        $postid = $_GET['taskid'];
        $content = $_POST['content'];
        $current_user = wp_get_current_user();
        $current_time = current_time('timestamp', 0);

        $history_link   = $_POST['history_link'];

        $update = wp_update_post(array(
            'ID'            => $postid,
            'post_content'  => $content,
        ));

        #nếu update thành công thì update history
        if ($update) {
            $noi_dung = "đã cập nhật thông tin nhiệm vụ.";
            $row_update = array(
                'nguoi_thuc_hien'   => $current_user,
                'noi_dung'          => $noi_dung,
                'thoi_gian'         => $current_time,
            );

            $logs = get_field('history', $update);
            if ($logs) {
                array_unshift($logs, $row_update);
                update_field('field_6010e02533119', $logs, $update);
            } else add_row('field_6010e02533119', $row_update, $update);

            # push notification
            $id_job = get_field('job', $postid);
            $content_notif = $current_user->display_name . " " . $noi_dung . " <b>" . get_the_title($postid) . "</b>";
            if ($id_job) {
                $content_notif .= " cho " . get_the_title($id_job);
            }

            $receiver = get_field('receiver', 'user_' . $current_user->ID);
            $manager = get_field('manager', $postid);
            create_notification($postid, $content_notif, $manager['ID'], $receiver);

            # đợi 3 giây và chuyển trang
            sleep(3);
            wp_redirect($history_link);
        }
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
                $postid = $_GET['taskid'];
                echo '<h4>' . get_the_title() . '</h4>';
                echo '<h3 class="title">' . get_the_title($postid) . '</h3>';
                ?>
            </div>
        </div><!-- Page Heading End -->

    </div><!-- Page Headings End -->
    <form action="" method="POST">
        <div class="row">
            <div class="col-12 col-lg-12 mb-20">
                <?php
                if (isset($update)) {
                    if ($update) {
                        # nếu thành công thì thông báo thành công, 3 giây sau thì chuyển trang
                        echo '<div class="alert alert-success" role="alert">
                                            <i class="fa fa-check"></i> Bài viết đã được cập nhật.
                                          </div>';
                    } else {
                        echo '<div class="alert alert-danger" role="alert">
                                            <i class="zmdi zmdi-info"></i> Xảy ra lỗi, không thể cập nhật.
                                          </div>';
                    }
                } else {
                    $content_post = get_post($_GET['taskid']);

                    echo '<textarea class="summernote" name="content">' . $content_post->post_content . '</textarea>';
                    echo '<input type="hidden" name="history_link" value="' . $history_link . '">';
                    echo '<div class="" style="margin-top: 20px; display:block;"><input type="submit" class="button button-primary" value="Cập nhật"> <a href="javascript:history.go(-1)" class="button button-wikipedia">Huỷ bỏ</a></div>';
                }
                ?>
            </div>
        </div>
    </form>

</div><!-- Content Body End -->

<?php
get_footer();
?>