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
                <h3><?php _e('Kết quả tìm kiếm cho:', 'qlcv'); ?> "<?php echo $s; ?>"</h3>
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
                        <h4 class="title"><?php _e('Các nhiệm vụ tìm được', 'qlcv'); ?></h4>
                    </div>
                    <div class="box-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php _e('Nhiệm vụ', 'qlcv'); ?></th>
                                    <th><?php _e('Công việc lớn', 'qlcv'); ?></th>
                                    <th><?php _e('Người thực hiện', 'qlcv'); ?></th>
                                    <th><?php _e('Deadline', 'qlcv'); ?></th>
                                    <th><?php _e('Trạng thái', 'qlcv'); ?></th>
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

        # cài đặt search theo phân quyền
        $nhom_cong_viec = get_field('nhom_cong_viec', 'user_' . $current_user->ID);
        $chi_nhanh = get_field('chi_nhanh', 'user_' . $current_user->ID);

        if (!empty($nhom_cong_viec)) {
            $args['tax_query'][] = array(
                'taxonomy'  => 'group',
                'field'     => 'ID',
                'terms'     => $nhom_cong_viec,
                'operator'  => 'IN'
            );
        }
        if (!empty($chi_nhanh)) {
            $args['tax_query'][] = array(
                'taxonomy'  => 'agency',
                'field'     => 'ID',
                'terms'     => $chi_nhanh,
                'operator'  => 'IN'
            );
        }

        $query = new WP_Query($args);

        if ($query->have_posts()) {
        ?>
            <div class="col-12 mb-30">
                <div class="box">
                    <div class="box-head">
                        <h4 class="title"><?php echo $query->post_count;  ?> <?php _e('công việc tìm được', 'qlcv'); ?></h4>
                    </div>
                    <div class="box-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><?php _e('STT', 'qlcv'); ?></th>
                                    <th>#</th>
                                    <th><?php _e('Ngày tháng', 'qlcv'); ?></th>
                                    <th><?php _e('Công việc lớn', 'qlcv'); ?></th>
                                    <th><?php _e('Khách hàng', 'qlcv'); ?></th>
                                    <th><?php _e('Đối tác', 'qlcv'); ?></th>
                                    <th><?php _e('Người thực hiện', 'qlcv'); ?></th>
                                    <th><?php _e('Người quản lý', 'qlcv'); ?></th>
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
                                    } else echo "<td>" . __("Chưa có", 'qlcv') . "</td>";
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
            echo '<div class="col-12 mb-30">' . __('Không tìm thấy dữ liệu.', 'qlcv') . '</div>';
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
                            <th><?php _e('Tên nhân sự', 'qlcv'); ?></th>
                            <th><?php _e('Số điện thoại', 'qlcv'); ?></th>
                            <th>Email</th>
                            <th><?php _e('Vai trò', 'qlcv'); ?></th>
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
                                <th><?php _e('Mã code', 'qlcv'); ?></th>
                                <th><?php _e('Tên đối tác / cty', 'qlcv'); ?></th>
                                <th><?php _e('Số điện thoại', 'qlcv'); ?></th>
                                <th>Email</th>
                                <th><?php _e('Vai trò', 'qlcv'); ?></th>
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
            } 
        }
        ?>
    </div>

</div><!-- Content Body End -->

<?php
get_footer();
?>