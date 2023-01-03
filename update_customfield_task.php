<?php
/*
    Template Name: Cập nhật các trường tuỳ biến của nhiệm vụ
*/
$history_link   = $_SERVER['HTTP_REFERER'];

if (isset($_GET['taskid'])  && ($_GET['taskid'] != "")) {
    # lấy dữ liệu bài viết
    $postid         = $_GET['taskid'];
    $current_post   = get_post($postid);
    $trang_thai     = get_field('trang_thai', $postid);
    $deadline       = get_field('deadline', $postid);
    $respone        = get_field('time_to_response', $postid);
    $member         = get_field('user', $postid);
    $manager        = get_field('manager', $postid);
    $job    = get_field('job', $postid);
    if (
        is_user_logged_in() &&
        isset($_POST['post_nonce_field']) &&
        wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')
    ) {

        $current_user = wp_get_current_user();
        $current_time = current_time('timestamp', 7);

        # Lấy dữ liệu từ form
        $taskname = $_POST['taskname'];
        $post_member = $_POST['user'];
        $post_manager = $_POST['manager'];
        $history_link   = $_POST['history_link'];

        # update đối tác luôn không cần thông báo
        $foreign_partner = $_POST['foreign_partner'];
        update_field('field_60c2432b28405', $foreign_partner, $inserted); # đối tác nhận việc

        # nếu người thực hiện có thay đổi thì thông báo
        if ($member['ID'] != $post_member) {
            $tmp_user = get_user_by('ID', $post_member);
            $tmp_manager = get_user_by('ID', $post_manager);
            #update vào csdl
            update_field('field_600fded7b438f', $post_member, $postid);
            $notif_item[]   = __('người thực hiện từ', 'qlcv') . ' <b>' . $tmp_user->display_name . '</b> '. __('sang', 'qlcv') . ' <b>' . $member['display_name'] . '</b>';
            $update = true;
            $update_user = true;

            $email_title = __("Thông báo về việc thay đổi nhân sự", 'qlcv');

            # set notification for old member & manager 
            $content = $tmp_user->display_name . " " . __("không còn là người xử lý việc", 'qlcv') . " <b>" . get_the_title($postid) . "</b>. " . __("Xem chi tiết việc", 'qlcv') . " <b>" . get_the_title($job) . "</b>";
            create_notification($postid, $content, $post_manager, $post_member);
            # send email notif
            $email_content = $content;
            $email_content .= "<br>" . __("Link tới công việc:", 'qlcv') . " " . get_the_permalink($postid);
            $email_content = auto_url($email_content);
            $email_content .= "<br><br>" . __("Trân trọng, ", 'qlcv');

            $headers[] = 'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>';
            $headers[] = 'Cc: ' . $email_admin;
            $headers[] = 'Cc: ' . $tmp_manager->user_email;
            $headers[] = 'Cc: ' . $tmp_user->user_email;

            $sent = wp_mail($to, $email_title, $email_content, $headers);

            # set notification for new member & manager
            $content = $member['display_name'] . " " . __("đã được giao là người xử lý việc", 'qlcv') . " <b>" . get_the_title($postid) . "</b>. " . __("Xem chi tiết việc", 'qlcv') . " <b>" . get_the_title($job) . "</b>";
            create_notification($postid, $content, $post_manager, $member['ID']);
            # send email notif
            $email_content = $content;
            $email_content .= "<br>" . __("Link tới công việc:", 'qlcv') . " " . get_the_permalink($postid);
            $email_content = auto_url($email_content);
            $email_content .= "<br><br>" . __("Trân trọng, ", 'qlcv');

            $headers[] = 'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>';
            $headers[] = 'Cc: ' . $email_admin;
            $headers[] = 'Cc: ' . $tmp_manager->user_email;
            $headers[] = 'Cc: ' . $member['user_email'];

            $sent = wp_mail($to, $email_title, $email_content, $headers);

        }

        # nếu người thực hiện có thay đổi thì thông báo
        if ($manager['ID'] != $post_manager) {
            $tmp_user = get_user_by('ID', $post_member);
            $tmp_manager = get_user_by('ID', $post_manager);
            #update vào csdl
            update_field('field_60fd46973dd42', $post_manager, $postid);
            $notif_item[]   = __('người quản lý từ', 'qlcv') . ' <b>' . $tmp_manager->display_name . '</b> '. __('sang', 'qlcv') . ' <b>' . $manager['display_name'] . '</b>';
            $update = true;
            $update_user = true;

            $email_title = __("Thông báo về việc thay đổi nhân sự", 'qlcv');

            # set notification for old member & manager 
            $content = $tmp_user->display_name . " " . __("không còn là người quản lý việc", 'qlcv') . "  <b>" . get_the_title($postid) . "</b>. " . __("Xem chi tiết việc", 'qlcv') . "  <b>" . get_the_title($job) . "</b>";
            create_notification($postid, $content, $post_manager, $post_member);
            # send email notif
            $email_content = $content;
            $email_content .= "<br>" . __("Link tới công việc:", 'qlcv') . " " . get_the_permalink($postid);
            $email_content = auto_url($email_content);
            $email_content .= "<br><br>" . __("Trân trọng, ", 'qlcv');

            $headers[] = 'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>';
            $headers[] = 'Cc: ' . $email_admin;
            $headers[] = 'Cc: ' . $tmp_manager->user_email;
            $headers[] = 'Cc: ' . $tmp_user->user_email;

            $sent = wp_mail($to, $email_title, $email_content, $headers);

            # set notification for new member & manager
            $content = $member['display_name'] . " " . __("đã được giao là người quản lý việc", 'qlcv') . "  <b>" . get_the_title($postid) . "</b>. ". __("Xem chi tiết việc", 'qlcv') . "  <b>" . get_the_title($job) . "</b>";
            create_notification($postid, $content, $post_manager, $member['ID']);
            # send email notif
            $email_content = $content;
            $email_content .= "<br>" . __("Link tới công việc:", 'qlcv') . " " . get_the_permalink($postid);
            $email_content = auto_url($email_content);
            $email_content .= "<br><br>" . __("Trân trọng, ", 'qlcv');

            $headers[] = 'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>';
            $headers[] = 'Cc: ' . $email_admin;
            $headers[] = 'Cc: ' . $manager['user_email'];
            $headers[] = 'Cc: ' . $tmp_user->user_email;

            $sent = wp_mail($to, $email_title, $email_content, $headers);

        }

        /* if ($update_user) {
            $content = "Có sự thay đổi của công việc: <b>" . get_the_title($postid) . "</b>. Hãy xem chi tiết!";
            create_notification($postid, $content, $post_manager, $post_member);
        } */

        # nếu ngày tháng deadline thay đổi thì xử lý
        if (($_POST['deadline'] != $deadline) && ($_POST['deadline'] != "")) {
            # xử lý chuỗi ngày tháng từ dạng DD/MM/YYYY sang YYYYMMDD để phù hợp với format của ACF custom field
            $deadline_arr   = explode('/', $_POST['deadline']);
            $temp_date      = array_reverse($deadline_arr);
            $new_deadline   = implode('', $temp_date);

            #update vào csdl
            update_field('field_600fde50f9be7', $new_deadline, $postid);
            $notif_item[]   = 'deadline';
            $update = true;
        }
        # nếu ngày tháng respone thay đổi thì xử lý
        if (($_POST['respone'] != $respone) && ($_POST['respone'] != "")) {
            # xử lý chuỗi ngày tháng từ dạng DD/MM/YYYY sang YYYYMMDD để phù hợp với format của ACF custom field
            $respone_arr    = explode('/', $_POST['respone']);
            $temp_date      = array_reverse($respone_arr);
            $new_respone    = implode('', $temp_date);

            #update vào csdl
            update_field('field_600fde70f9be8', $new_respone, $postid);
            $notif_item[]   = __('deadline chờ phản hồi','qlcv');
            $update = true;
        }

        # nếu taskname có thay đổi thì mới update
        if ($current_post->post_title != $taskname) {
            $update = wp_update_post(array(
                'ID'            => $postid,
                'post_title'    => $taskname,
            ));

            $notif_item[]   = __('tên nhiệm vụ', 'qlcv');
        }

        # nếu update thành công thì update history & push notification
        if ($update) {
            $noi_dung = __("đã cập nhật", 'qlcv') . " " . implode(', ', $notif_item);
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
            $id_job = get_field('job', $postid);
            $content_notif = $current_user->display_name . " " . $noi_dung . " <b>" . get_the_title($postid) . "</b>";
            if ($id_job) {
                $content_notif .= " " . __('cho', 'qlcv') . " " . get_the_title($id_job);
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
                        <div class="col-lg-3 form_title lh45" style="color: lightgray;"><?php _e('Công việc lớn', 'qlcv'); ?></div>
                        <div class="col-lg-6 col-12 mb-20">
                            <input type="text" value="<?php echo get_the_title($job); ?>" disabled class="form-control">
                        </div>
                        <div class="col-lg-3"></div>

                        <div class="col-lg-3 form_title lh45"><?php _e('Tên nhiệm vụ', 'qlcv'); ?></div>
                        <div class="col-lg-6 col-12 mb-20"><input type="text" value="<?php echo $current_post->post_title; ?>" name="taskname" class="form-control"></div>
                        <div class="col-lg-3"></div>

                        <div class="col-lg-3 form_title lh45"><?php _e('Người quản lý', 'qlcv'); ?></div>
                        <div class="col-lg-6 col-12 mb-20">
                            <select class="form-control select2-tags mb-20" name="manager">
                                <?php
                                if ($manager["ID"]) {
                                    echo "<option value='" . $manager['ID'] . "'>" . $manager['display_name'] . " (" . $manager['user_email'] . ")</option>";
                                } else {
                                    echo "<option value=''>-- " . __("Chọn người quản lý", 'qlcv') . " --</option>";
                                }

                                $args   = array(
                                    'role__in'    => array('member', 'contributor'),
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
                        <div class="col-lg-3"></div>

                        <div class="col-lg-3 form_title lh45"><?php _e('Người thực hiện', 'qlcv'); ?></div>
                        <div class="col-lg-6 col-12 mb-20">
                            <select class="form-control select2-tags mb-20" name="user">
                                <?php
                                if ($member["ID"]) {
                                    echo "<option value='" . $member['ID'] . "'>" . $member['display_name'] . " (" . $member['user_email'] . ")</option>";
                                } else {
                                    echo "<option value=''>-- " . __("Chọn người thực hiện", 'qlcv') . " --</option>";
                                }

                                /* $args   = array(
                                                    'role__in'    => array('member', 'contributor'),
                                                );
                                                $query = get_users( $args ); */

                                if ($query) {
                                    foreach ($query as $user) {
                                        echo "<option value='" . $user->ID . "'>" . $user->display_name . " (" . $user->user_email . ")</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-lg-3"></div>

                        <div class="col-lg-3 form_title lh45"><?php _e('Đối tác nhận việc', 'qlcv'); ?></div>
                        <div class="col-lg-6 col-12 mb-20">
                            <select class="form-control select2-tags mb-20" name="foreign_partner">
                                <option value="">-- <?php _e('Chọn đối tác nhận việc', 'qlcv'); ?> --</option>
                                <?php
                                $partner = get_field('partner', $postid);
                                if ($partner["ID"]) {
                                    echo "<option value='" . $partner["ID"] . "'>" . $partner['display_name'] . " (" . $partner['user_email'] . ")</option>";
                                }
                                $args   = array(
                                    'role'      => 'foreign_partner', /*subscriber, contributor, author*/
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
                        <div class="col-lg-3"></div>

                        <?php
                        # if status is not finish, cancel or waiting for confirmation, it's can be shown
                        if (($trang_thai != "Chờ phản hồi")
                            && ($trang_thai != "Chờ phê duyệt")
                        ) {

                        ?>
                            <div class="col-lg-3 form_title lh45">Deadline</div>
                            <div class="col-lg-3 col-12 mb-20">
                                <input type="text" class="form-control input-date-single" value="<?php echo $deadline; ?>" name="deadline" data-mask="99/99/9999">
                                <span class="form-help-text">"dd/mm/yyyy"</span>
                            </div>
                            <div class="col-lg-6"></div>
                        <?php
                        } else {
                            # if status is waiting to confirm, it's can be showed
                        ?>
                            <div class="col-lg-3 form_title lh45"><?php _e('Deadline chờ phản hồi', 'qlcv'); ?></div>
                            <div class="col-lg-3 col-12 mb-20">
                                <input type="text" class="form-control input-date-single" value="<?php echo $respone; ?>" name="respone" data-mask="99/99/9999">
                                <span class="form-help-text">"dd/mm/yyyy"</span>
                            </div>
                            <div class="col-lg-6"></div>
                        <?php
                        }
                        echo '<input type="hidden" name="history_link" value="' . $history_link . '">';
                        wp_nonce_field('post_nonce', 'post_nonce_field');
                        ?>

                        <div class="col-lg-3"></div>
                        <div class="col-lg-6 col-12 mb-20"><input type="submit" class="button button-primary" value="<?php _e('Cập nhật', 'qlcv'); ?>"> <a href="javascript:history.go(-1)" class="button button-wikipedia"><?php _e('Huỷ bỏ', 'qlcv'); ?></a></div>
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