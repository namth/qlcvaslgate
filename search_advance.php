<?php
/*
    Template Name: Search nâng cao
*/

if (
    isset($_POST['post_nonce_field']) &&
    wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')
) {

    # get data from the form
    $data_type      = $_POST['data_type'];
    $search         = $_POST['keyword'];
    $partner        = $_POST['partner'];
    $foreign_partner = $_POST['foreign_partner'];
    $_customer      = $_POST['_customer'];
    $member         = $_POST['member'];
    $filter_date    = $_POST['filter_date'];
    $type           = trim($_POST['jtype']);
}

if (isset($_GET['type'])) {
    $type = $_GET['type'];
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

        <div class="col-12 mb-30">
            <div class="box">
                <div class="box-body">
                    <div>
                        <form action="#" method="POST" class="row">
                            <div class="col-lg-3 form_title lh45"><?php _e('Phân loại:', 'qlcv'); ?> </div>
                            <div class="col-lg-6 col-12 mb-20">
                                <select class="form-control select2-tags mb-20" name="jtype">
                                    <option value="">Tất cả</option>
                                    <?php
                                    $terms = get_terms(array(
                                        'taxonomy' => 'group',
                                        'hide_empty' => false,
                                    ));
                                      
                                    foreach ($terms as $key => $value) {
                                        $selected = ($value->name == $type) ? "selected" : "";
                                        echo "<option value='" . $value->name . "' " . $selected . ">" . $value->name . "</option>";
                                    }    
                                    ?>    
                                </select>
                            </div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45"><?php _e('Loại dữ liệu', 'qlcv'); ?></div>
                            <div class="col-lg-6 col-12 mb-20">
                                <select class="form-control select2-tags mb-20" name="data_type">
                                    <?php
                                    $data_arr = array(
                                        'job'               => 'Công việc',
                                        'task'              => 'Nhiệm vụ',
                                        'partner'           => 'Đối tác',
                                        'foreign_partner'   => 'Đối tác nước ngoài',
                                        'customer'          => 'Khách hàng',
                                        'member'            => 'Nhân sự',
                                    );    
                                    foreach ($data_arr as $key => $value) {
                                        $selected = ($data_type == $key) ? "selected" : "";
                                        echo "<option value='" . $key . "' " . $selected . ">" . $value . "</option>";
                                    }    
                                    ?>    
                                </select>
                            </div>    
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45"><?php _e('Từ khóa:', 'qlcv'); ?> </div>
                            <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="keyword" value="<?php echo $search; ?>"></div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45"><?php _e('Thời gian:', 'qlcv'); ?> </div>
                            <div class="col-lg-6 col-12 mb-20">
                                <input name="filter_date" type="text" class="form-control input-date-predefined" value="<?php echo $_POST['filter_date']; ?>">
                            </div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45"><?php _e('Lọc theo:', 'qlcv'); ?></div>
                            <div class="col-lg-4 col-12 mb-20">
                                <select class="form-control select2-tags mb-20" name="partner">
                                    <option value="">-- <?php _e('Đối tác', 'qlcv'); ?> --</option>
                                    <?php
                                    $args   = array(
                                        'role'      => 'partner', /*subscriber, contributor, author*/
                                    );
                                    $query = get_users($args);

                                    if ($query) {
                                        foreach ($query as $user) {
                                            $ten_cong_ty    = get_field('ten_cong_ty', 'user_' . $user->ID);
                                            $partner_code   = get_field('partner_code', 'user_' . $user->ID);
                                            $selected = ($partner == $user->ID) ? "selected" : "";
                                            echo "<option value='" . $user->ID . "'>" . $partner_code . " - " . $ten_cong_ty . " (" . $user->user_email . ")</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-lg-4 col-12 mb-20">
                                <select class="form-control select2-tags mb-20" name="foreign_partner">
                                    <option value="">-- <?php _e('Đối tác nước ngoài', 'qlcv'); ?> --</option>
                                    <?php
                                    $args   = array(
                                        'role'      => 'foreign_partner', /*subscriber, contributor, author*/
                                    );
                                    $query = get_users($args);

                                    if ($query) {
                                        foreach ($query as $user) {
                                            $ten_cong_ty    = get_field('ten_cong_ty', 'user_' . $user->ID);
                                            $partner_code   = get_field('partner_code', 'user_' . $user->ID);
                                            $selected = ($foreign_partner == $user->ID) ? "selected" : "";
                                            echo "<option value='" . $user->ID . "'>" . $partner_code . " - " . $ten_cong_ty . " (" . $user->user_email . ")</option>";
                                        }
                                    }
                                    ?>
                                </select>

                            </div>

                            <div class="col-lg-3 form_title lh45"></div>
                            <div class="col-lg-4 col-12 mb-20">
                                <select class="form-control select2-tags mb-20" name="_customer">
                                    <option value="">-- <?php _e('Khách hàng', 'qlcv'); ?> --</option>
                                    <?php
                                    $args   = array(
                                        'post_type'     => 'customer',
                                    );
                                    $query = new WP_Query($args);

                                    if ($query->have_posts()) {
                                        while ($query->have_posts()) {
                                            $query->the_post();

                                            $cty = get_field('ten_cong_ty');
                                            $email = get_field('email');

                                            $selected = ($_customer == get_the_ID()) ? "selected" : "";
                                            echo "<option value='" . get_the_ID() . "' " . $selected . ">" . $cty;
                                            if ($email) {
                                                echo " (" . $email . ")";
                                            }
                                            echo "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-lg-4 col-12 mb-20">
                                <?php
                                /* If user isn't admin, always filter by member (hidden select member) */
                                $current_user = wp_get_current_user();

                                if (in_array('administrator', $current_user->roles)) {

                                ?>
                                    <select class="form-control select2-tags mb-20" name="member">
                                        <option value="">-- <?php _e('Nhân sự', 'qlcv'); ?> --</option>
                                        <?php
                                        $args   = array(
                                            'role__in'      => array('member', 'contributor'), /*subscriber, contributor, author*/
                                        );
                                        $query = get_users($args);

                                        if ($query) {
                                            foreach ($query as $user) {
                                                $selected = ($member == $user->ID) ? "selected" : "";
                                                echo "<option value='" . $user->ID . "' " . $selected . ">" . $user->display_name . " (" . $user->user_email . ")</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                    
                                <?php
                                } else {
                                    echo '<input type="hidden" name="member" value="' . $current_user->ID . '">';
                                }
                                ?>
                            </div>


                            <?php
                            wp_nonce_field('post_nonce', 'post_nonce_field');
                            ?>

                            <div class="col-lg-3"></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="submit" class="button button-primary" value="<?php _e('Tìm kiếm', 'qlcv'); ?>"></div>

                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 mb-30">
            <div class="page-heading">
                <?php
                if ($data_type) {
                    echo "<h3>" . __("Kết quả tìm kiếm", 'qlcv') . "</h3>";
                    // echo $data_type;
                    switch ($data_type) {
                        case 'job':
                            $args   = array(
                                'post_type'     => 'job',
                                'posts_per_page' => '-1',
                            );

                            if ($search) {
                                $keyword = '%' . $wpdb->esc_like($search) . '%';
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

                                if ($post_ids) {
                                    $args['post__in'] = $post_ids;
                                } else {
                                    $args['s'] = $search;
                                }
                            }

                            if ($partner) {
                                $args['meta_query'][] = array(
                                    'relation' => 'OR',
                                    array(
                                        'key'       => 'partner_2',
                                        'value'     => $partner,
                                        'compare'   => '=',
                                    ),
                                    array(
                                        'key'       => 'partner_1',
                                        'value'     => $partner,
                                        'compare'   => '=',
                                    ),
                                );
                            }

                            if ($foreign_partner) {
                                $args['meta_query'][] = array(
                                    'relation' => 'AND',
                                    array(
                                        'key'       => 'foreign_partner',
                                        'value'     => $foreign_partner,
                                        'compare'   => '=',
                                    ),
                                );
                            }

                            if ($_customer) {
                                $args['meta_query'][] = array(
                                    array(
                                        'key'       => 'customer',
                                        'value'     => $_customer,
                                        'compare'   => '=',
                                    ),
                                );
                            }

                            if ($member) {
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

                            if ($filter_date) {
                                $date_value = explode(' - ', $_POST['filter_date']);
                                $date_1 = date('Ymd', strtotime(($date_value[0])));
                                $date_2 = date('Ymd', strtotime($date_value[1]));

                                if ($date_1 && $date_2) {
                                    $args['date_query'] = array(
                                        array(
                                            'after'     => $date_1,
                                            'before'    => $date_2,
                                            'inclusive' => true,
                                        ),
                                    );
                                }
                            }

                            /* if (isset($type) && ($type != '')) {
                                $args['tax_query'] = array(
                                    'relation' => 'AND',
                                    array(
                                        'taxonomy'  => 'group',
                                        'field'     => 'slug',
                                        'terms'     => $type,
                                    ),
                                );
                            } */
                            # phân quyền theo loại công việc cho user
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
                            
                            # cài đặt phân quyền theo chi nhánh cho user
                            $chi_nhanh = get_field('chi_nhanh', 'user_' . $current_user->ID);
                            if (!empty($chi_nhanh)) {
                                $args['tax_query'][] = array(
                                    'taxonomy'  => 'agency',
                                    'field'     => 'ID',
                                    'terms'     => $chi_nhanh,
                                    'operator'  => 'IN'
                                );
                            }
                            

                            $query = new WP_Query($args);
                            // print_r($args);
                            if ($query->have_posts()) {
                ?>
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th><?php _e('Công việc lớn', 'qlcv'); ?></th>
                                            <th><?php _e('Số đơn', 'qlcv'); ?></th>
                                            <th><?php _e('Số bằng', 'qlcv'); ?></th>
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
                                            $customer = get_field('customer');
                                            $partner_2 = get_field('partner_2');
                                            // print_r($customer);
                                            $member = get_field('member');
                                            $manager = get_field('manager');
                                            $so_don = get_field('so_don');
                                            $so_bang = get_field('so_bang');

                                            echo "<tr>";
                                            echo "<td>" . $i . "</td>";
                                            echo "<td><a href='" . get_permalink() . "'>" . get_the_title() . "</a></td>";
                                            echo "<td>" . $so_don . "</td>";
                                            echo "<td>" . $so_bang . "</td>";
                                            echo "<td><a href='" . $customer->guid . "'>" . $customer->post_title . "</a></td>";
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

                            <?php
                            }
                            break;

                        case 'task':
                            $args   = array(
                                'post_type'     => 'task',
                                'posts_per_page' => '-1',
                            );

                            if ($search) {
                                $keyword = '%' . $wpdb->esc_like($search) . '%';
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

                            if ($member) {
                                $args['meta_query'] = array(
                                    array(
                                        'key'       => 'user',
                                        'value'     => $member,
                                        'compare'   => '=',
                                    ),
                                );
                            }
                            $query = new WP_Query($args);

                            if ($query->have_posts()) {
                            ?>
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
                            <?php
                            }
                            break;

                        case 'customer':
                            $args   = array(
                                'post_type'     => 'customer',
                                'posts_per_page' => '-1',
                            );

                            if ($search) {
                                $keyword = '%' . $wpdb->esc_like($search) . '%';
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

                            if ($customer) {
                                $args['p'] = $customer;
                            }
                            $query = new WP_Query($args);

                            if ($query->have_posts()) {
                            ?>
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th><?php _e('Tên khách hàng', 'qlcv'); ?></th>
                                            <th><?php _e('Tên công ty ', 'qlcv'); ?></th>
                                            <th><?php _e('Số điện thoại', 'qlcv'); ?></th>
                                            <th>Email</th>
                                            <th><?php _e('Quốc gia', 'qlcv'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 0;
                                        while ($query->have_posts()) {
                                            $query->the_post();

                                            $i++;
                                            $so_dien_thoai = get_field('so_dien_thoai');
                                            $email = get_field('email');
                                            $cong_ty = get_field('ten_cong_ty');
                                            $quoc_gia = get_field('quoc_gia');

                                            echo "<tr>";
                                            echo "<td>" . $i . "</td>";
                                            echo "<td><a href='" . get_permalink() . "'>" . get_the_title() . "</a></td>";
                                            if ($cong_ty) {
                                                echo "<td>" . $cong_ty . "</td>";
                                            } else echo "<td></td>";
                                            if ($so_dien_thoai) {
                                                echo "<td>" . $so_dien_thoai . "</td>";
                                            } else echo "<td>Chưa có</td>";
                                            if ($email) {
                                                echo "<td>" . $email . "</td>";
                                            } else echo "<td>Chưa có</td>";
                                            if ($quoc_gia) {
                                                echo "<td>" . $quoc_gia . "</td>";
                                            } else echo "<td>Chưa có</td>";
                                            echo "</tr>";
                                        }
                                        wp_reset_postdata();
                                        ?>
                                    </tbody>
                                </table>
                            <?php
                            } else {
                                echo "<tr><td colspan=6 class='text-center'>Không có dữ liệu.</td></tr>";
                            }
                            break;

                        default:
                            $args   = array(
                                'role'      => $data_type,
                                'number'    => 999,
                            );

                            if ($search) {

                                $args['meta_query'] = array(
                                    'relation' => 'OR',
                                    array(
                                        'key'     => 'first_name',
                                        'value'   => $search,
                                        'compare' => 'LIKE',
                                    ),
                                    array(
                                        'key'     => 'last_name',
                                        'value'   => $search,
                                        'compare' => 'LIKE',
                                    ),
                                    array(
                                        'key'     => 'user_email',
                                        'value'   => $search,
                                        'compare' => 'LIKE',
                                    ),
                                    array(
                                        'key'       => 'so_dien_thoai',
                                        'value'     => $search,
                                        'compare'   => 'LIKE',
                                    ),
                                    array(
                                        'key'       => 'ten_cong_ty',
                                        'value'     => $search,
                                        'compare'   => 'LIKE',
                                    ),
                                    array(
                                        'key'       => 'dia_chi',
                                        'value'     => $search,
                                        'compare'   => 'LIKE',
                                    ),
                                    array(
                                        'key'       => 'partner_code',
                                        'value'     => $search,
                                        'compare'   => 'LIKE',
                                    ),
                                );

                                $q2 = new WP_User_Query(array(
                                    'search' => "*{$search}*",
                                    'search_columns' => array(
                                        'user_login',
                                        'user_nicename',
                                        'user_email',
                                        'user_url',
                                    ),
                                ));

                                $users2 = $q2->get_results();
                            }
                            $user_count_query = new WP_User_Query($args);
                            $users1 = $user_count_query->get_results();

                            if ($users2) {
                                $users = array_unique(array_merge($users2, $users1), SORT_REGULAR);
                            } else {
                                $users = $users1;
                            }

                            if (!empty($users)) {
                            ?>
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th><?php _e('Mã đối tác', 'qlcv'); ?></th>
                                            <th><?php _e('Họ và tên', 'qlcv'); ?></th>
                                            <th><?php _e('Công ty', 'qlcv'); ?></th>
                                            <th><?php _e('Số điện thoại', 'qlcv'); ?></th>
                                            <th>Email</th>
                                            <th><?php _e('Địa chỉ', 'qlcv'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 0;
                                        foreach ($users as $user) {
                                            $i++;

                                            $so_dien_thoai  = get_field('so_dien_thoai', 'user_' . $user->ID);
                                            $partner_code   = get_field('partner_code', 'user_' . $user->ID);
                                            $ten_cong_ty    = get_field('ten_cong_ty', 'user_' . $user->ID);
                                            $dia_chi        = get_field('dia_chi', 'user_' . $user->ID);

                                            echo "<tr>";
                                            echo "<td>" . $i . "</td>";
                                            echo "<td>" . $partner_code . "</td>";
                                            echo "<td><a href='" . get_author_posts_url($user->ID) . "'>" . $user->display_name . "</a></td>";
                                            echo "<td>" . $ten_cong_ty . "</td>";
                                            if ($so_dien_thoai) {
                                                echo "<td>" . $so_dien_thoai . "</td>";
                                            } else echo "<td>Chưa có</td>";
                                            echo "<td>" . $user->user_email . "</td>";
                                            echo "<td>" . $dia_chi . "</td>";

                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                <?php
                            }
                            break;
                    }
                }

                ?>
            </div>
        </div>

    </div><!-- Page Headings End -->

</div><!-- Content Body End -->

<?php
get_footer();
?>