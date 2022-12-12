<?php
/*
    Template Name: Danh sách nhiệm vụ (task)
*/
get_header();

get_sidebar();
$current_user = wp_get_current_user();

if (
    isset($_POST['post_nonce_field']) &&
    wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')
) {
    $member = $_POST['member'];
    $status = $_POST['status'];
    $date_value = explode(' - ', $_POST['deadline']);
    $date_1 = date('Ymd', strtotime($date_value[0]));
    $date_2 = date('Ymd', strtotime($date_value[1]));

    $_SESSION['list_task'] = array(
        'member'    => $member,
        'status'    => $status,
        'date_1'    => $date_1,
        'date_2'    => $date_2,
    );
}

/* if ((time() - $_SESSION['list_task']['registered']) > (60 * 30)) {
    unset($_SESSION['list_task']);
} */
if (isset($_SESSION['list_task'])) {
    $member = $_SESSION['list_task']['member'];
    $status = $_SESSION['list_task']['status'];
    $date_1 = $_SESSION['list_task']['date_1'];
    $date_2 = $_SESSION['list_task']['date_2'];
}
?>

<!-- Content Body Start -->
<div class="content-body">

    <!-- Page Headings Start -->
    <div class="row justify-content-between align-items-center mb-10">

        <!-- Page Heading Start -->
        <div class="col-12 col-lg-12 mb-20">
            <!--Basic Start-->
            <div class="col-12 mb-30">
                <div id="filter">
                    <form action="#" method="POST" class="row mb-20">
                        <?php
                        if (in_array('administrator', $current_user->roles) || in_array('contributor', $current_user->roles)) {
                        ?>
                            <div class="col-md-3">
                                <select class="form-control select2-tags mb-20" name="member">
                                    <option value="">-- Chọn người thực hiện --</option>
                                    <?php
                                    $args   = array(
                                        'role__in'      => array('member', 'contributor'), /*subscriber, contributor, author*/
                                    );
                                    $query = get_users($args);

                                    if ($query) {
                                        foreach ($query as $user) {
                                            $selected = ($user->ID == $member) ? "selected" : "";
                                            echo "<option value='" . $user->ID . "' " . $selected . ">" . $user->display_name . " (" . $user->user_email . ")</option>";
                                        }
                                    }
                                    ?>
                                </select>

                            </div>
                        <?php
                        } else {
                            echo '<input type="hidden" name="member" value="' . $current_user->ID . '">';
                        }
                        ?>
                        <div class="col-md-3">
                            <select name="status" id="" class="form-control select2-tags mb-20">
                                <option value="">-- Trạng thái nhiệm vụ --</option>
                                <?php
                                $status_arr = array('Mới', 'Đang thực hiện', 'Hoàn thành', 'Chờ phê duyệt', 'Quản lý đã phê duyệt', 'Huỷ', 'Quá hạn');
                                foreach ($status_arr as $value) {
                                    $selected = ($value == $status) ? "selected" : "";
                                    echo "<option value='" . $value . "' " . $selected . ">" . $value . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input name="deadline" type="text" class="form-control input-date-predefined" value="<?php echo $_POST['deadline']; ?>">
                        </div>
                        <?php
                        wp_nonce_field('post_nonce', 'post_nonce_field');
                        ?>
                        <div class="col-md-3">
                            <input type="submit" class="button button-primary" value="Lọc" style="padding: 9px 20px;">
                        </div>
                    </form>
                </div>

                <?php

                    // xử lý phân trang
                    $paged = (get_query_var('paged')) ? absint(get_query_var('paged')) : 1;

                    $args   = array(
                        'post_type'     => 'task',
                        'paged'         => $paged,
                        'posts_per_page' => 10,
                    );
                    if ($member) {
                        $args['meta_query'][] = array(
                            'relation' => 'OR',
                            array(
                                'key'       => 'user',
                                'value'     => $member,
                                'compare'   => '=',
                            ),
                            array(
                                'key'       => 'manager',
                                'value'     => $member,
                                'compare'   => '=',
                            ),
                        );
                    } else {
                        if (!in_array('administrator', $current_user->roles) && !in_array('contributor', $current_user->roles)) {
                            $args['meta_query'][] = array(
                                'relation' => 'OR',
                                array(
                                    'key'       => 'user',
                                    'value'     => $current_user->ID,
                                    'compare'   => '=',
                                ),
                                array(
                                    'key'       => 'manager',
                                    'value'     => $current_user->ID,
                                    'compare'   => '=',
                                ),
                            );
                        }
                    }
                    if ($status) {
                        $args['meta_query'][] = array(
                            array(
                                'key'       => 'trang_thai',
                                'value'     => $status,
                                'compare'   => '=',
                            ),
                        );
                    }
                    if ($date_1 && $date_2) {
                        $args['meta_query'][] = array(
                            array(
                                'key'       => 'deadline',
                                'compare'   => 'BETWEEN',
                                'type'      => 'DATE',
                                'value'     => array($date_1, $date_2),
                            ),
                        );
                    }

                    $query = new WP_Query($args);
                    $total_args = $args;
                    $total_args['posts_per_page'] = -1;
                    $total_query = new WP_Query($total_args);
                ?>
                
                <div class="row justify-content-between">
                    <div class="col-lg-auto mb-10">
                        <p>Có tổng cộng <?php echo $total_query->post_count; ?> nhiệm vụ tìm thấy</p>
                        <h2>Danh sách nhiệm vụ</h2>
                    </div>
                    <div class="col-lg-auto mb-10">
                        <!-- <div class="page-date-range">
                                    <span>Lọc theo deadline: </span><input type="text" class="form-control input-date-predefined" id="list_task_by_date">
                                </div> -->
                    </div>
                    <div class="col-12 box mb-20">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nhiệm vụ</th>
                                    <th>Công việc lớn</th>
                                    <th>Người thực hiện</th>
                                    <th>Người quản lý</th>
                                    <th>Deadline</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 0;
                                if ($query->have_posts()) {
                                    while ($query->have_posts()) {
                                        $query->the_post();

                                        $i++;
                                        $jobID = get_field('job');
                                        $user_arr = get_field('user');
                                        $manager = get_field('manager');
                                        // print_r($user_arr);
                                        $deadline = get_field('deadline');
                                        $trang_thai = get_field('trang_thai');

                                        // Tính toán tiến độ công việc
                                        $start_time = strtotime(get_the_date('d-m-Y'));
                                        $current_time = current_time('timestamp', 7);
                                        if ($deadline) {
                                            $tmp = DateTime::createFromFormat('d/m/Y', $deadline);
                                            $end_time = strtotime($tmp->format('d-m-Y'));
                                        }

                                        // nếu thời gian hiện tại ít hơn deadline thì mới tính %
                                        if ($current_time < $end_time) {
                                            $work_percent = round(($current_time - $start_time) / ($end_time - $start_time) * 100);
                                        } else {
                                            $work_percent = 100;
                                        }
                                        if (
                                            in_array('administrator', $current_user->roles) ||
                                            (!in_array('administrator', $current_user->roles) && (($current_user->ID == $user_arr['ID']) || ($current_user->ID == $manager['ID'])))
                                        ) {
                                            echo "<tr>";
                                            echo "<td>" . $i . "</td>";
                                            echo "<td><a href='" . get_permalink() . "'>" . get_the_title() . "</a></td>";
                                            echo "<td><a href='" . get_permalink($jobID) . "'>" . get_the_title($jobID) . "</a></td>";
                                            echo "<td>" . $user_arr['nickname'] . " (" . $user_arr['user_email'] . ")</td>";
                                            echo "<td>" . $manager['nickname'] . " (" . $manager['user_email'] . ")</td>";
                                            echo '<td><div class="progress" style="height: 24px;">
                                                                <div class="progress-bar" role="progressbar" style="width: ' . $work_percent . '%" aria-valuenow="' . $work_percent . '" aria-valuemin="0" aria-valuemax="100">' . $deadline . '</div>
                                                                </div>
                                                              </td>';
                                            echo "<td>" . $trang_thai . "</td>";
                                            echo "</tr>";
                                            
                                        }
                                    }
                                    wp_reset_postdata();
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-12">
                        <div class="pagination justify-content-center">
                            <?php
                            $big = 999999999; // need an unlikely integer

                            echo paginate_links(array(
                                'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                                'format'    => '?paged=%#%',
                                'current'   => max(1, get_query_var('paged')),
                                'total'     => $query->max_num_pages,
                                'type'      => 'list',
                            ));
                            ?>
                        </div>
                    </div>
                </div>

            </div>
            <!--Basic End-->


        </div><!-- Page Heading End -->

    </div><!-- Page Headings End -->

</div><!-- Content Body End -->

<?php
get_footer();
?>