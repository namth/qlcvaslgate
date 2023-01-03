<?php
/*
    Template Name: Cập nhật trạng thái hoàn thành nhiệm vụ
*/
if (isset($_GET['taskid'])  && ($_GET['taskid'] != "")) {
    # lấy dữ liệu bài viết
    $postid         = $_GET['taskid'];
    $current_post   = get_post($postid);
    // print_r($current_post);
    $deadline       = get_field('deadline', $postid);
    //$trang_thai 	= get_field('trang_thai');
    $job            = get_field('job', $postid);
    $post_title     = $current_post->post_title;

    if (
        is_user_logged_in() &&
        isset($_POST['post_nonce_field']) &&
        wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')
    ) {

        $current_user = wp_get_current_user();
        $current_time = current_time('timestamp', 7);

        # Lấy dữ liệu từ form
        $post_date      = $_POST['deadline'];
        $deadline_arr   = explode('/', $post_date);
        $temp_date      = array_reverse($deadline_arr);
        $deadline       = implode('', $temp_date);
        $thong_bao      = $_POST['thong_bao'];
        $phe_duyet      = $_POST['phe_duyet'];
        $so_don         = $_POST['so_don'];
        $so_bang        = $_POST['so_bang'];

        # cập nhật số đơn, số bằng nếu được set
        if ($so_don) {
            update_field('field_606ec70f02a63', $so_don, $job);
            update_field('field_606ec71f02a64', $deadline, $job);
        }
        if ($so_bang) {
            update_field('field_606ec7aa02a65', $so_bang, $job);
            update_field('field_606ec7b602a66', $deadline, $job);
        }
        
        # if status is "send to partner", it's must be change checkbox in data
        if ($thong_bao) {
            update_field('field_607d38c56ee32', true, $postid);
        }
        # nếu "cần phê duyệt" thì làm tiếp
        if ($phe_duyet) {
            #cập nhật trạng thái là chờ phê duyệt
            update_field('field_600fde92f9be9', 'Chờ phê duyệt', $postid);
            $noi_dung = "đã gửi cho cấp trên để phê duyệt";

            # update history
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
            $content_notif = $current_user->display_name . " " . $noi_dung . " về việc <b>" . get_the_title($postid) . "</b>";
            if ($job) {
                $content_notif .= " của " . get_the_title($job);
            }

            $receiver = get_field('receiver', 'user_' . $current_user->ID);
            $manager = get_field('manager', $postid);
            create_notification($postid, $content_notif, $manager['ID'], $receiver);
            # đợi 3 giây và chuyển trang
            // sleep(3);
            wp_redirect(get_permalink($postid));
        } else {
            # đợi 3 giây và chuyển trang
            // sleep(3);
            wp_redirect(get_bloginfo('url') . '/approval/?taskid=' . $postid);
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
                echo '<h3 class="title">' . get_the_title() . '</h3>';
                ?>
            </div>
        </div><!-- Page Heading End -->

    </div><!-- Page Headings End -->
    <form action="#" method="POST">
        <div class="row">
            <div class="col-12 col-lg-12 mb-20">
                <?php
                if (isset($update)) {
                    if ($update) {
                        # nếu thành công thì thông báo thành công, 3 giây sau thì chuyển trang
                        echo '<div class="alert alert-success" role="alert">
                                            <i class="fa fa-check"></i> ' . __('Bài viết đã được cập nhật.', 'qlcv') . '
                                          </div>';
                    } else {
                        echo '<div class="alert alert-danger" role="alert">
                                            <i class="zmdi zmdi-info"></i> ' . __('Xảy ra lỗi, không thể cập nhật.', 'qlcv') . '
                                          </div>';
                    }
                } else {

                ?>
                    <div class="row mbn-20">

                        <?php
                        if ($job) {
                        ?>
                            <div class="col-lg-3 form_title lh45" style="color: lightgray;"><?php _e('Công việc lớn', 'qlcv'); ?></div>
                            <div class="col-lg-6 col-12 mb-20">
                                <input type="text" value="<?php echo get_the_title($job); ?>" disabled class="form-control">
                            </div>
                            <div class="col-lg-3"></div>
                        <?php
                        }
                        ?>

                        <div class="col-lg-3 form_title lh45" style="color: lightgray;"><?php _e('Tên nhiệm vụ', 'qlcv'); ?></div>
                        <div class="col-lg-6 col-12 mb-20"><input type="text" value="<?php echo $post_title; ?>" disabled class="form-control"></div>
                        <div class="col-lg-3"></div>

                        <?php
                        $phan_loai = get_field('phan_loai', $job);
                        
                        switch ($post_title) {
                            case 'Nộp đơn':
                                echo '  
                                        <div class="col-lg-3 form_title">Ngày ' . lcfirst($post_title) . '</div>
                                        <div class="col-lg-3 col-12 mb-20">
                                            <input type="text" class="form-control" name="deadline" data-mask="99/99/9999">
                                            <span class="form-help-text">"dd/mm/yyyy"</span>
                                        </div>
                                        <div class="col-lg-6"></div>

                                        <div class="col-lg-3 form_title lh45">' . __('Số đơn', 'qlcv') . '</div>
                                        <div class="col-lg-3 col-12 mb-20">
                                            <input type="text" class="form-control" name="so_don">
                                        </div>
                                        <div class="col-lg-6"></div>';
                                break;

                            case 'Cấp bằng':
                                echo '  
                                        <div class="col-lg-3 form_title">Ngày ' . lcfirst($post_title) . '</div>
                                        <div class="col-lg-3 col-12 mb-20">
                                            <input type="text" class="form-control" name="deadline" data-mask="99/99/9999">
                                            <span class="form-help-text">"dd/mm/yyyy"</span>
                                        </div>
                                        <div class="col-lg-6"></div>
                                        
                                        <div class="col-lg-3 form_title lh45">' . __('Số bằng', 'qlcv') . '</div>
                                        <div class="col-lg-3 col-12 mb-20">
                                            <input type="text" class="form-control" name="so_bang">
                                        </div>
                                        <div class="col-lg-6"></div>';
                                break;
                        }
                        ?>

                        <div class="col-lg-3 form_title lh45"><?php _e('Thông báo cho khách hàng', 'qlcv'); ?></div>
                        <div class="col-lg-3 col-12 mb-20">
                            <label class="adomx-checkbox" style="margin-top: 14px;">
                                <input type="checkbox" name="thong_bao"> <i class="icon"></i>
                            </label>
                        </div>
                        <div class="col-lg-6"></div>

                        <div class="col-lg-3 form_title lh45"><?php _e('Cần phê duyệt', 'qlcv'); ?></div>
                        <div class="col-lg-3 col-12 mb-20">
                            <label class="adomx-checkbox" style="margin-top: 14px;">
                                <input type="checkbox" name="phe_duyet"> <i class="icon"></i>
                            </label>
                        </div>
                        <div class="col-lg-6"></div>

                        <?php
                        wp_nonce_field('post_nonce', 'post_nonce_field');
                        ?>

                        <div class="col-lg-3"></div>
                        <div class="col-lg-6 col-12 mb-20">
                            <input type="submit" class="button button-primary" value="<?php _e('Hoàn thành', 'qlcv'); ?>">
                            <a href="javascript:history.go(-1)" class="button button-wikipedia"><?php _e('Huỷ bỏ', 'qlcv'); ?></a>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    </form>

</div><!-- Content Body End -->

<?php
get_footer();
?>