<?php
get_header();

get_sidebar();

$s = $_POST['s'];
$current_user = wp_get_current_user();
?>

<!-- Content Body Start -->
<div class="content-body">

    <!-- Page Headings Start -->
    <div class="row justify-content-between align-items-center mb-10">

        <!-- Page Heading Start -->
        <div class="col-12 col-lg-auto mb-20">
            <div class="page-heading">
                <h3>Kết quả tìm kiếm cho: "<?php echo $s; ?>"</h3>
            </div>
        </div><!-- Page Heading End -->

    </div><!-- Page Headings End -->

    <div class="row mbn-30">
        <!-- Recent Transaction Start -->
        <?php
        $args   = array(
            'post_type'      => 'task',
            'posts_per_page' => '-1',
            's'              => $s,
        );
        $query = new WP_Query($args);

        if ($query->have_posts()) {
        ?>
            <div class="col-12 mb-30">
                <div class="box">
                    <div class="box-head">
                        <h4 class="title">Các nhiệm vụ tìm được</h4>
                    </div>
                    <div class="box-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nhiệm vụ</th>
                                    <th>Công việc lớn</th>
                                    <th>Người thực hiện</th>
                                    <th>Deadline</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 0;

                                while ($query->have_posts()) {
                                    $query->the_post();

                                    $i++;
                                    $jobID              = get_field('job');
                                    $user_arr           = get_field('user');
                                    $deadline           = get_field('deadline');
                                    $time_to_response   = get_field('time_to_response');
                                    $trang_thai         = get_field('trang_thai');

                                    // Tính toán tiến độ công việc
                                    $start_time = strtotime(get_the_date('d-m-Y'));
                                    $current_time = current_time('timestamp', 0);
                                    $temp = new DateTime();
                                    $tmp = $temp->createFromFormat('d/m/Y', $deadline);
                                    $end_time = strtotime($tmp->format('d-m-Y'));

                                    // nếu thời gian hiện tại ít hơn deadline thì mới tính %
                                    if ($current_time < $end_time) {
                                        $work_percent = round(($current_time - $start_time) / ($end_time - $start_time) * 100);
                                    } else {
                                        $work_percent = 100;
                                    }

                                    # if it have respone date, shown it, if not, show deadline
                                    if ($trang_thai == "Chờ phản hồi") {
                                        $deadline_label = $time_to_response;
                                    } else if ($trang_thai != "Hoàn thành") {
                                        $deadline_label = $deadline;
                                    } else {
                                        $deadline_label = "Xong";
                                    }


                                    echo "<tr>";
                                    echo "<td>" . $i . "</td>";
                                    echo "<td><a href='" . get_permalink() . "'>" . get_the_title() . "</a></td>";
                                    echo "<td><a href='" . get_permalink($jobID) . "'>" . get_the_title($jobID) . "</a></td>";
                                    echo "<td>" . $user_arr['nickname'] . " (" . $user_arr['user_email'] . ")</td>";
                                    echo '<td><div class="progress" style="height: 24px;">
                                                    <div class="progress-bar" role="progressbar" style="width: ' . $work_percent . '%" aria-valuenow="' . $work_percent . '" aria-valuemin="0" aria-valuemax="100">' . $deadline_label . '</div>
                                                    </div>
                                                  </td>';
                                    echo "<td>" . $trang_thai . "</td>";
                                    echo "</tr>";
                                }
                                wp_reset_postdata();
                                ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div><!-- Recent Transaction End -->

        <?php
        } # end search task

        $args   = array(
            'post_type'     => 'job',
            'posts_per_page' => '-1',
        );

        if ($s) {
            $keyword = '%' . $wpdb->esc_like($s) . '%';
            // Search in all custom fields
            $post_ids_meta = $wpdb->get_col($wpdb->prepare("
                            SELECT DISTINCT post_id FROM {$wpdb->postmeta}
                            WHERE meta_value LIKE '%s'
                        ", $keyword));

            // Search in post_title and post_content
            $post_ids_post = $wpdb->get_col($wpdb->prepare("
                            SELECT DISTINCT ID FROM {$wpdb->posts}
                            WHERE post_title LIKE '%s'
                            OR post_content LIKE '%s'
                        ", $keyword, $keyword));

            $post_ids = array_merge($post_ids_meta, $post_ids_post);

            $args['post__in'] = $post_ids;
        }

        if (!in_array('administrator', $current_user->roles)) {
            $args['meta_query'][] = array(
                'relation' => 'OR',
                array(
                    'key'       => 'member',
                    'value'     => $member,
                    'compare'   => '=',
                ),
                array(
                    'key'       => 'manager',
                    'value'     => $member,
                    'compare'   => '=',
                ),
            );
        }

        $query = new WP_Query($args);

        if ($query->have_posts()) {
        ?>
            <div class="col-12 mb-30">
                <div class="box">
                    <div class="box-head">
                        <h4 class="title"><?php echo $query->post_count;  ?> công việc tìm được</h4>
                    </div>
                    <div class="box-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>#</th>
                                    <th>Ngày tháng</th>
                                    <th>Công việc lớn</th>
                                    <th>Khách hàng</th>
                                    <th>Đối tác</th>
                                    <th>Người thực hiện</th>
                                    <th>Người quản lý</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 0;
                                while ($query->have_posts()) {
                                    $query->the_post();

                                    $i++;
                                    $our_ref    = get_field('our_ref');
                                    $customer   = get_field('customer');
                                    $partner_2  = get_field('partner_2');
                                    // print_r($customer);
                                    $member     = get_field('member');
                                    $manager    = get_field('manager');
                                    $customer_comp  = get_field('ten_cong_ty', $customer->ID);

                                    echo "<tr>";
                                    echo "<td>" . $i . "</td>";
                                    echo "<td>" . $our_ref . "</td>";
                                    echo "<td>" . get_the_date('d/m/Y') . "</td>";
                                    echo "<td><a href='" . get_permalink() . "'>" . get_the_title() . "</a></td>";
                                    echo "<td><a href='" . $customer->guid . "'>" . $customer_comp . "</a></td>";
                                    echo "<td><a href='" . get_author_posts_url($partner_2['ID']) . "'>" . $partner_2['display_name'] . "</a></td>";
                                    if ($member) {
                                        echo "<td><a href='" . get_author_posts_url($member['ID']) . "'>" . $member['display_name'] . "</a></td>";
                                    } else echo "<td>Chưa có</td>";
                                    echo "<td><a href='" . get_author_posts_url($manager['ID']) . "'>" . $manager['display_name'] . "</a></td>";
                                    echo "</tr>";
                                }
                                wp_reset_postdata();
                                ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div><!-- Recent Transaction End -->

        <?php
        } else {
            echo '<div class="col-12 mb-30">Không tìm thấy dữ liệu.</div>';
        }
        # end search job

        $count_args  = array(
            'search'    => $s,
            'number'    => 999999,
        );
        $user_count_query = new WP_User_Query($count_args);
        $users = $user_count_query->get_results();

        if (!empty($users)) {

        ?>
            <div class="col-12 box mb-20">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tên nhân sự</th>
                            <th>Số điện thoại</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 0;
                        foreach ($users as $user) {
                            $i++;
                            $roles = array();
                            $so_dien_thoai = get_field('so_dien_thoai', 'user_' . $user->ID);

                            echo "<tr>";
                            echo "<td>" . $i . "</td>";
                            echo "<td><a href='" . get_author_posts_url($user->ID) . "'>" . $user->display_name . "</a></td>";
                            if ($so_dien_thoai) {
                                echo "<td>" . $so_dien_thoai . "</td>";
                            } else echo "<td>Chưa có</td>";
                            echo "<td>" . $user->user_email . "</td>";

                            # display user role name
                            if (!empty($user->roles) && is_array($user->roles)) {
                                foreach ($user->roles as $role)
                                    $roles[] = translate_user_role($wp_roles->roles[$role]['name']);
                            }

                            echo "<td>" . implode(', ', $roles) . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>


            <?php
        } else {
            $count_args  = array(
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key'     => 'so_dien_thoai',
                        'value'   => $s,
                        'compare' => '='
                    ),
                    array(
                        'key'     => 'partner_code',
                        'value'   => $s,
                        'compare' => '='
                    ),
                )
            );
            $user_count_query = new WP_User_Query($count_args);
            $users = $user_count_query->get_results();

            if (!empty($users)) {
            ?>
                <div class="col-12 box mb-20">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Mã code</th>
                                <th>Tên đối tác / cty</th>
                                <th>Số điện thoại</th>
                                <th>Email</th>
                                <th>Vai trò</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            foreach ($users as $user) {
                                $i++;
                                $roles = array();
                                $partner_code = get_field('partner_code', 'user_' . $user->ID);
                                $ten_cong_ty = get_field('ten_cong_ty', 'user_' . $user->ID);
                                $so_dien_thoai = get_field('so_dien_thoai', 'user_' . $user->ID);

                                echo "<tr>";
                                echo "<td>" . $i . "</td>";
                                echo "<td>" . $partner_code . "</td>";
                                echo "<td><a href='" . get_author_posts_url($user->ID) . "'>" . $ten_cong_ty . "</a></td>";
                                if ($so_dien_thoai) {
                                    echo "<td>" . $so_dien_thoai . "</td>";
                                } else echo "<td>Chưa có</td>";
                                echo "<td>" . $user->user_email . "</td>";

                                # display user role name
                                if (!empty($user->roles) && is_array($user->roles)) {
                                    foreach ($user->roles as $role)
                                        $roles[] = translate_user_role($wp_roles->roles[$role]['name']);
                                }

                                echo "<td>" . implode(', ', $roles) . "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
        <?php
            } /* else {
                echo '<div class="col-12 mb-30">Không tìm thấy dữ liệu.</div>';
            } */
        }
        ?>
    </div>

</div><!-- Content Body End -->

<?php
get_footer();
?>