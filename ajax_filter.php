<?php
add_action('wp_ajax_ajax_filter_jobs', 'ajax_filter_jobs');
function ajax_filter_jobs()
{
    $data = parse_str($_POST['data'], $output);
    $current_user = wp_get_current_user();
    $paged = $_POST['paged'];
    $type = "";
    if (isset($_GET['type'])) {
        $type = $_GET['type'];
    }
    if (isset($_GET['source'])) {
        $source = $_GET['source'];
    }
    if ($type) {
        $get_var = '?type=' . $type;
    } else $get_var = "";


    $member     = $output['member'];
    $manager    = $output['manager'];
    $partner    = $output['partner'];
    $_agency    = $output['agency'];
    $_post_tag  = $output['post_tag'];
    $_customer  = $output['_customer'];
    # nếu có nhập deadline thì tính toán
    if ($output['deadline']) {
        $date_value = explode(' - ', $output['deadline']);
        $date_1     = date('Y-m-d', strtotime($date_value[0]));
        $date_2     = date('Y-m-d', strtotime($date_value[1]));
    }
    $args   = array(
        'post_type'     => 'job',
        'paged'         => $paged,
        'posts_per_page' => 20,
    );

    $nhom_cong_viec = get_field('nhom_cong_viec', 'user_' . $current_user->ID);
    if (isset($type) && ($type != '')) {
        if (is_array($nhom_cong_viec)) {

            # cài đặt phân quyền ngay từ đầu không cho truy cập vào phân loại nào 
            $args['tax_query'] = array(
                'relation' => 'AND',
                array(
                    'taxonomy'  => 'group',
                    'field'     => 'slug',
                    'terms'     => 'none',
                ),
            );

            # tìm kiếm trong nhóm, nếu loại công việc truyền trên biến type mà đã đc phân quyền thì hiển thị
            foreach ($nhom_cong_viec as $id_cong_viec) {
                $term = get_term($id_cong_viec);

                if ($term->name == $type) {
                    # nếu type trên đường link mà có trong phân quyền thì hiển thị
                    $args['tax_query'] = array(
                        'relation' => 'AND',
                        array(
                            'taxonomy'  => 'group',
                            'field'     => 'slug',
                            'terms'     => $term->slug,
                        ),
                    );

                    break;
                }
            }
        }
    } else {
        # filter theo phân quyền
        $args['tax_query'][] = array(
            'taxonomy'  => 'group',
            'field'     => 'ID',
            'terms'     => $nhom_cong_viec,
            'operator'  => 'IN'
        );
    }


    if (isset($_agency) && ($_agency != '')) {
        # filter theo chi nhánh được lựa chọn
        $args['tax_query'][] = array(
            // array(
            'taxonomy'  => 'agency',
            'field'     => 'slug',
            'terms'     => $_agency,
            // ),
        );
    } else {
        # filter theo phân quyền
        $chi_nhanh = get_field('chi_nhanh', 'user_' . $current_user->ID);
        $args['tax_query'][] = array(
            'taxonomy'  => 'agency',
            'field'     => 'ID',
            'terms'     => $chi_nhanh,
            'operator'  => 'IN'
        );
    }

    if (isset($_post_tag) && ($_post_tag != '')) {
        $args['tax_query'][] = array(
            // array(
            'taxonomy'  => 'post_tag',
            'field'     => 'slug',
            'terms'     => $_post_tag,
            // ),
        );
    }

    /* Nếu không phải danh sách tiềm năng thì ko hiện việc tiềm năng */
    if ($type != 'tiem-nang') {
        $args['tax_query'][] = array(
            'taxonomy'  => 'group',
            'field'     => 'slug',
            'terms'     => 'tiem-nang',
            'operator'  => 'NOT IN',
        );
    }
    if (isset($source) && ($source != '')) {
        $args['tag'] = $source;
    }

    /* 
            Lọc người thực hiện và người quản lý
            -- Nếu không phải admin --
              A
             /!\ Chỉ lọc những việc liên quan đến current user.
            <=-=>
            * Nếu có set người quản lý thì ưu tiên lọc theo người thực hiện trước, 
                * nếu có set người thực hiện thì search theo người thực hiện
                * nếu có set người thực hiện mà không phải là current user thì search theo người quản lý
            * Nếu không set người quản lý thì
                * Nếu có set người thực hiện thì mà không phải current user thì set người quản lý là current user rồi search
                * Nếu có set người thực hiện mà trùng với current user thì search thẳng luôn
            * Nếu không set gì thì search theo current user cả 2 vị trí
            -- Nếu là admin --
            * Nếu có set người quản lý thì search theo người quản lý
            * Nếu có set người thực hiện thì search theo người thực hiện 
        */
    if (!in_array('administrator', $current_user->roles)) {
        if ($manager) {
            if ($member && ($member != $current_user->ID)) {
                $args['meta_query'][] = array(
                    array(
                        'key'       => 'member',
                        'value'     => $member,
                        'compare'   => '=',
                    ),
                    array(
                        'key'       => 'manager',
                        'value'     => $current_user->ID,
                        'compare'   => '=',
                    ),
                );
            } else {
                $args['meta_query'][] = array(
                    array(
                        'key'       => 'manager',
                        'value'     => $manager,
                        'compare'   => '=',
                    ),
                    array(
                        'key'       => 'member',
                        'value'     => $current_user->ID,
                        'compare'   => '=',
                    ),
                );
            }
        } else {
            if ($member) {
                if ($member != $current_user->ID) {
                    $args['meta_query'][] = array(
                        array(
                            'key'       => 'member',
                            'value'     => $member,
                            'compare'   => '=',
                        ),
                        array(
                            'key'       => 'manager',
                            'value'     => $current_user->ID,
                            'compare'   => '=',
                        ),
                    );
                } else {
                    $args['meta_query'][] = array(
                        array(
                            'key'       => 'member',
                            'value'     => $member,
                            'compare'   => '=',
                        ),
                    );
                }
            } else {
                $args['meta_query'][] = array(
                    'relation' => 'OR',
                    array(
                        'key'       => 'member',
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
    } else {
        if ($member) {
            $args['meta_query'][] = array(
                array(
                    'key'       => 'member',
                    'value'     => $member,
                    'compare'   => '=',
                ),
            );
        }
        if ($manager) {
            $args['meta_query'][] = array(
                array(
                    'key'       => 'manager',
                    'value'     => $manager,
                    'compare'   => '=',
                ),
            );
        }
    }

    /* Search theo partner */
    if ($partner) {
        $args['meta_query'][] = array(
            array(
                'key'       => 'partner_2',
                'value'     => $partner,
                'compare'   => '=',
            ),
        );
    }

    /* Search theo customer */
    if ($_customer) {
        $args['meta_query'][] = array(
            array(
                'key'       => 'customer',
                'value'     => $_customer,
                'compare'   => '=',
            ),
        );
    }

    if ($date_1 && $date_2) {
        $args['date_query'] = array(
            array(
                'after'     => $date_1,
                'before'    => $date_2,
                'inclusive' => true,
            ),
        );
    }

    $query = new WP_Query($args);

    // print_r($args);
    // $total_args = $args;
    // $total_args['posts_per_page'] = -1;
    // $total_query = new WP_Query($total_args);

?>
    <div class="row justify-content-between">
        <div class="col-lg-auto mb-10">
            <p><?php _e('Có tổng cộng', 'qlcv'); ?> <?php echo $query->found_posts; ?> <?php _e('công việc tìm thấy', 'qlcv'); ?></p>
            <h2><?php _e('Danh sách công việc', 'qlcv'); ?></h2>
        </div>
        <div class="col-lg-auto mb-10 right_button">
            <a href="<?php echo get_bloginfo('home') . '/tao-dau-viec-moi/' . $get_var; ?>" class="button button-primary"><span><i class="fa fa-plus"></i><?php _e('Tạo công việc mới', 'qlcv'); ?></span></a>
        </div>
        <div class="col-12 box mb-20">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?php _e('Ngày tháng', 'qlcv'); ?></th>
                        <th><?php _e('Công việc lớn', 'qlcv'); ?></th>
                        <th><?php _e('Lịch sử CV', 'qlcv'); ?></th>
                        <?php
                        if (!$type || ($type == 'tiem-nang')) {
                            _e("<th>Phân loại</th>", 'qlcv');
                        }
                        ?>
                        <th><?php _e('Khách hàng', 'qlcv'); ?></th>
                        <th><?php _e('Đối tác', 'qlcv'); ?></th>
                        <th><?php _e('Người thực hiện', 'qlcv'); ?></th>
                        <th><?php _e('Người quản lý', 'qlcv'); ?></th>
                        <th><?php _e('Nguồn việc', 'qlcv'); ?></th>
                        <th><?php _e('Chi nhánh', 'qlcv'); ?></th>
                        <th><?php _e('Giá trị', 'qlcv'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 0;
                    if ($query->have_posts()) {
                        while ($query->have_posts()) {
                            $query->the_post();

                            $i++;
                            $our_ref        = get_field('our_ref');
                            $customer       = get_field('customer');
                            $phan_loai      = get_field('phan_loai');
                            $customer_comp  = get_field('ten_cong_ty', $customer->ID);
                            $partner_2      = get_field('partner_2');
                            $partner_comp   = get_field('ten_cong_ty', 'user_' . $partner_2['ID']);
                            $member         = get_field('member');
                            $manager        = get_field('manager');
                            $tags_obj       = get_the_tags();
                            $tagname_arr    = array();
                            if ($tags_obj) {
                                foreach ($tags_obj as $key => $value) {
                                    $tagname_arr[] = $value->name;
                                }
                                $tagname = implode(', ', $tagname_arr);
                            } else {
                                $tagname = "";
                            }
                            $total_value = get_field('total_value');
                            $currency = get_field('currency');
                            if ($total_value) {
                                $job_value = ($total_value) . " " . $currency;
                            } else {
                                $job_value = "";
                            }

                            $work_list  = get_field('lich_su_cong_viec');
                            $work_history = array();
                            if ($work_list) {
                                foreach ($work_list as $key => $value) {
                                    $work_history[] = $value['mo_ta'];
                                    // $work_date[] = $value['ngay_thang'];
                                }
                            }

                            $agency = get_the_terms(get_the_ID(), 'agency');

                            echo "<tr>";
                            echo "<td>" . $our_ref . "</td>";
                            echo "<td>" . get_the_date('d/m/Y') . "</td>";
                            echo "<td><a href='" . get_permalink() . "'>" . get_the_title() . "</a></td>";
                            echo "<td>" . end($work_history) . "</td>";
                            if (!$type || ($type == 'tiem-nang')) {
                                echo "<td>" . '<span class="badge badge-secondary">' . $phan_loai . '</span>' . "</td>";
                            }
                            echo "<td><a href='" . $customer->guid . "'>" . $customer_comp . "</a></td>";
                            echo "<td><a href='" . get_author_posts_url($partner_2['ID']) . "'>" . $partner_comp . "</a></td>";
                            if ($member) {
                                echo "<td><a href='" . get_author_posts_url($member['ID']) . "'>" . $member['display_name'] . "</a></td>";
                            } else echo "<td>Chưa có</td>";
                            echo "<td><a href='" . get_author_posts_url($manager['ID']) . "'>" . $manager['display_name'] . "</a></td>";
                            echo "<td>" . $tagname . "</td>";
                            if (!is_wp_error($agency)) {
                                echo "<td>" . $agency[0]->name . "</td>";
                            } else echo "<td></td>";
                            echo "<td>" . $job_value . "</td>";
                            echo '<td><a href="' . get_bloginfo('url') . '/renewal_post_api/?jobid=' . get_the_ID() . '"><i class="fa fa-telegram"></i></a></td>';
                            echo "</tr>";
                        }
                        wp_reset_postdata();
                    } else {
                        echo "<tr><td colspan=6 class='text-center'>Không có dữ liệu.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <div class="col-12">
            <div class="pagination justify-content-center" id="job_pagination">
                <?php
                /* $big = 999999999; // need an unlikely integer

                echo paginate_links(array(
                    'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                    'format'    => '?paged=%#%',
                    'current'   => max(1, get_query_var('paged')),
                    'total'     => $query->max_num_pages,
                    'type'      => 'list',
                )); */
                
                echo show_pagination($paged, $query->max_num_pages);
                ?>
            </div>
        </div>
    </div>
<?php

    exit;
}

add_action('wp_ajax_ajax_filter_tasks', 'ajax_filter_tasks');
function ajax_filter_tasks()
{
    $data = parse_str($_POST['data'], $output);
    $current_user = wp_get_current_user();
    $paged = $_POST['paged'];
    $post_per_page = 10;

    $member     = $output['member'];
    $status     = $output['status'];
    if ($output['deadline']) {
        $date_value = explode(' - ', $output['deadline']);
        $date_1     = date('Y-m-d', strtotime($date_value[0]));
        $date_2     = date('Y-m-d', strtotime($date_value[1]));
    }

                $args   = array(
                    'post_type'     => 'task',
                    'paged'         => $paged,
                    'posts_per_page' => $post_per_page,
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
                ?>

                <div class="row justify-content-between">
                    <div class="col-lg-auto mb-10">
                        <p><?php _e('Có tổng cộng', 'qlcv'); ?> <?php echo $query->found_posts; ?> <?php _e('nhiệm vụ tìm thấy', 'qlcv'); ?></p>
                        <h2><?php _e('Danh sách nhiệm vụ', 'qlcv'); ?></h2>
                    </div>
                    <div class="col-12 box mb-20">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php _e('Nhiệm vụ', 'qlcv'); ?></th>
                                    <th><?php _e('Công việc lớn', 'qlcv'); ?></th>
                                    <th><?php _e('Người thực hiện', 'qlcv'); ?></th>
                                    <th><?php _e('Người quản lý', 'qlcv'); ?></th>
                                    <th><?php _e('Deadline', 'qlcv'); ?></th>
                                    <th><?php _e('Trạng thái', 'qlcv'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = ($paged - 1) * $post_per_page;
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
                        <div class="pagination justify-content-center" id="task_pagination">
                            <?php
                            echo show_pagination($paged, $query->max_num_pages);
                            ?>
                        </div>
                    </div>
                </div>
    <?php
    exit;
}