<?php
/*
    Template Name: Thêm nhiệm vụ mới
*/
if (isset($_GET['jobid'])  && ($_GET['jobid'] != "")) {
    # lấy dữ liệu bài viết
    $job            = $_GET['jobid'];
    $current_post   = get_post($job);
    $member         = get_field('member', $job); # người thực hiện
    $manager        = get_field('manager', $job); # người quản lý 
    $history_link   = $_SERVER['HTTP_REFERER'];

    if (
        is_user_logged_in() &&
        isset($_POST['post_nonce_field']) &&
        wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')
    ) {

        $current_user = wp_get_current_user();
        $current_time = current_time('timestamp', 7);

        # Lấy dữ liệu từ form
        $taskname       = $_POST['taskname'];
        $content        = $_POST['content'];
        $member         = $_POST['member'];
        $manager        = $_POST['manager'];
        $date_history   = $_POST['date_history'];
        $history_link   = $_POST['history_link'];
        # work process
        $work_process   = $_POST['work_process'];
        $work_date      = $_POST['work_date'];
        $other_work_process = $_POST['other_work_process'];
        $other_work_date    = $_POST['other_work_date'];
        
        # nếu taskname có thay đổi thì mới update
        if (isset($taskname) && ($taskname != "")) {
            $inserted = wp_insert_post(array(
                'post_title'    => $taskname,
                'post_content'  => $content,
                'post_status'   => 'publish',
                'post_type'     => 'task',
            ));

            # nếu update thành công thì update history
            if ($inserted) {
                # xử lý chuỗi ngày tháng từ dạng DD/MM/YYYY sang YYYYMMDD để phù hợp với format của ACF custom field
                $deadline_arr   = explode('/', $_POST['deadline']);
                $temp_date      = array_reverse($deadline_arr);
                $new_deadline   = implode('', $temp_date);
                # Tạo mã code
                $code = createRandomPassword();

                # update vào csdl
                update_field('field_607a56b7622af', $code, $inserted);
                update_field('field_600fdff6b4390', $job, $inserted); # job
                update_field('field_600fded7b438f', $member, $inserted); # người thực hiện
                update_field('field_60fd46973dd42', $manager, $inserted); # người quản lý
                update_field('field_600fde50f9be7', $new_deadline, $inserted); # deadline thực hiện
                update_field('field_600fde92f9be9', "Mới", $inserted); # Status
                # work process 
                if (is_array($work_process)) {
                    for ($i=0; $i < count($work_process); $i++) { 
                        if ($work_process[$i] && $work_date[$i]) {
                            # cập nhật lịch sử
                            update_job_history($work_process[$i], $work_date[$i], $job);
                        }
                    }
                }
                if ($other_work_process && $other_work_date) {
                    update_job_history($other_work_process, $other_work_date, $job);
                }

                $noi_dung = $current_user->display_name . " đã tạo nhiệm vụ mới";
                $row_update = array(
                    'nguoi_thuc_hien'   => $current_user,
                    'noi_dung'          => $noi_dung,
                    'thoi_gian'         => $current_time,
                );

                add_row('field_6010e02533119', $row_update, $inserted);

                # send email notification
                $email_admin = get_field('email_admin', 'option');
                $user_arr = get_user_by('ID', $member);
                $to = $user_arr->user_email;
                if ($to) {
                    $email_title = $noi_dung . ": " . $taskname;
                    $email_content = $user_arr->display_name . ' ' . __('hãy kiểm tra để thực hiện nhiệm vụ mới.', 'qlcv');
                    $email_content .= "<br>". __('Link tới công việc:', 'qlcv') . get_the_permalink($inserted);
                    $email_content = auto_url($email_content);
    
                    $headers[] = 'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>';
                    $headers[] = 'Cc: ' . $email_admin;
                    if ($manager_arr['user_email']) {
                        $headers[] = 'Cc: ' . $manager_arr['user_email'];
                    }
    
                    $sent = wp_mail($to, $email_title, $email_content, $headers);
                }

                # notification 
                create_notification($inserted, $email_title, $manager, $member);

                # đợi 3 giây và chuyển trang
                # sleep(3);
                wp_redirect($history_link);
                exit;
            }
        }
    }
}
get_header();

get_sidebar();

$phan_loai  = get_field('phan_loai', $job);

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
                        <div class="col-lg-3 form_title lh45" style="color: lightgray;"><?php _e('Công việc', 'qlcv'); ?></div>
                        <div class="col-lg-6 col-12 mb-20">
                            <input type="text" value="<?php echo get_the_title($job); ?>" disabled class="form-control">
                        </div>
                        <div class="col-lg-3"></div>

                        <div class="col-lg-3 form_title lh45"><?php _e('Tên nhiệm vụ', 'qlcv'); ?> <span class="text-danger">*</span></div>
                        <div class="col-lg-6 col-12 mb-20">
                            <!--  -->
                            <?php
                            $terms      = get_the_terms($job, 'group');
                            $term_names = wp_list_pluck($terms, 'name');

                            if (in_array("Tiềm năng", $term_names)) {
                                $term       = get_term_by('name', 'Tiềm năng', 'group');
                                $work_list  = get_field('work_list', 'term_' . $term->term_id);
                            } else {
                                $term       = get_term_by('name', $phan_loai, 'group');
                                $work_list  = get_field('work_list', 'term_' . $term->term_id);
                            }

                            $work_arr   = explode(PHP_EOL, $work_list);
                            $work_arr   = array_map('trim', $work_arr);
                            
                            if (!$work_list) {
                                echo '<input type="text" class="form-control" name="taskname">';
                            } else {
                                echo '<select class="form-control select2-tags mb-20" name="taskname">';
                                foreach ($work_arr as $value) {
                                    echo "<option value='" . $value . "'>" . $value . "</option>";
                                }
                                echo '</select>';
                            }
                            ?>
                        </div>
                        <div class="col-lg-3"></div>

                        <div class="col-lg-3 form_title lh45"><?php _e('Ngày phát sinh nhiệm vụ', 'qlcv'); ?></div>
                        <div class="col-lg-3 col-12 mb-20">
                            <input type="text" class="form-control" name="date_history" data-mask="99/99/9999">
                            <span class="form-help-text">"dd/mm/yyyy"</span>
                        </div>
                        <div class="col-lg-6"></div>

                        <div class="col-lg-3 form_title lh45"><?php _e('Lịch sử thực hiện', 'qlcv'); ?></div>
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
                                    $work_list  = get_field('lich_su_cong_viec', $job);
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
                        <div class="col-lg-3"></div>

                        <div class="col-lg-3 form_title lh45"><?php _e('Người thực hiện', 'qlcv'); ?> <span class="text-danger">*</span></div>
                        <div class="col-lg-6 col-12 mb-20">
                            <select class="form-control select2-tags mb-20" name="member">
                                <?php
                                if ($member) {
                                    echo "<option value='" . $member['ID'] . "'>" . $member['display_name'] . " (" . $member['user_email'] . ")</option>";
                                } else {
                                    echo "<option>-- " . __('Chọn nhân sự thực hiện','qlcv') . " --</option>";
                                }

                                $args   = array(
                                    'role__in'      => array('member', 'contributor'), /*subscriber, contributor, author*/
                                    'exclude'   => $member['ID'],
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

                        <div class="col-lg-3 form_title lh45"><?php _e('Người quản lý', 'qlcv'); ?> <span class="text-danger">*</span></div>
                        <div class="col-lg-6 col-12 mb-20">
                            <select class="form-control select2-tags mb-20" name="manager">
                                <?php
                                if ($manager) {
                                    echo "<option value='" . $manager['ID'] . "'>" . $manager['display_name'] . " (" . $manager['user_email'] . ")</option>";
                                } else {
                                    echo "<option>-- " . __('Chọn người quản lý','qlcv') . " --</option>";
                                }

                                $args   = array(
                                    'role__in'  => array('member', 'contributor'), /*subscriber, contributor, author*/
                                    'exclude'   => $manager['ID'],
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

                        <div class="col-lg-3 form_title lh45"><?php _e('Đối tác nhận việc', 'qlcv'); ?></div>
                        <div class="col-lg-6 col-12 mb-20">
                            <select class="form-control select2-tags mb-20" name="foreign_partner">
                                <option value="">-- <?php _e('Chọn đối tác nhận việc', 'qlcv'); ?> --</option>
                                <?php
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

                        <div class="col-lg-3 form_title lh45">Deadline <span class="text-danger">*</span></div>
                        <div class="col-lg-3 col-12 mb-20">
                            <input type="text" class="form-control input-date-single" value="" name="deadline" data-mask="99/99/9999">
                            <span class="form-help-text">"dd/mm/yyyy"</span>
                        </div>
                        <div class="col-lg-6"></div>

                        <div class="col-lg-3 form_title lh45"><?php _e('Nội dung nhiệm vụ', 'qlcv'); ?></div>
                        <div class="col-lg-8 col-12 mb-20">
                            <textarea class="summernote" name="content"></textarea>
                        </div>
                        <div class="col-lg-1"></div>
                                
                        <?php
                        wp_nonce_field('post_nonce', 'post_nonce_field');
                        echo '<input type="hidden" name="history_link" value="' . $history_link . '">';
                        ?>

                        <div class="col-lg-3"></div>
                        <div class="col-lg-6 col-12 mb-20"><input type="submit" class="button button-primary" value="<?php _e('Cập nhật', 'qlcv'); ?>"> <a href="javascript:history.go(-1)" class="button button-wikipedia">Huỷ bỏ</a></div>
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