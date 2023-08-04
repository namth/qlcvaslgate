<?php
/*
    Template Name: Danh sách công việc (Job)
*/
get_header();

get_sidebar();

$type = $_GET['type'];
$source = $_GET['source'];
if ($type) {
    $get_var = '?type=' . $type;
} else $get_var = "";

if (
    isset($_POST['post_nonce_field']) &&
    wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')
) {
    $member     = $_POST['member'];
    $manager    = $_POST['manager'];
    $partner    = $_POST['partner'];
    $_agency    = $_POST['agency'];
    $_post_tag  = $_POST['post_tag'];
    $_customer  = $_POST['_customer'];
    $date_value = explode(' - ', $_POST['deadline']);
    $date_1     = date('Y-m-d', strtotime($date_value[0]));
    $date_2     = date('Y-m-d', strtotime($date_value[1]));

    $_SESSION['list_job'] = array(
        'member'        => $member,
        'manager'       => $manager,
        'partner'       => $partner,
        '_customer'     => $_customer,
        'date_1'        => $date_1,
        'date_2'        => $date_2,
    );
}

$current_user = wp_get_current_user();

?>

<!-- Content Body Start -->
<div class="content-body">

    <!-- Page Headings Start -->
    <div class="row justify-content-between align-items-center mb-10">

        <!-- Page Heading Start -->
        <div class="col-12 col-lg-12 mb-20">
            <div class="col-12 mb-30">
                <div id="filter">
                    <form action="#" method="POST" class="mb-20">
                        <div class="row mb-20">
                        <?php
                        if (in_array('administrator', $current_user->roles) || in_array('contributor', $current_user->roles)) {
                        ?>
                            <div class="col-md-4 mb-20">
                                <select class="form-control select2-tags mb-20" name="member">
                                    <option value="">-- <?php _e('Người thực hiện', 'qlcv'); ?> --</option>
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
                            <div class="col-md-4 mb-20">
                                <select class="form-control select2-tags mb-20" name="manager">
                                    <option value="">-- <?php _e('Người quản lý', 'qlcv'); ?> --</option>
                                    <?php
                                    $args   = array(
                                        'role__in'      => array('member', 'contributor'), /*subscriber, contributor, author*/
                                    );
                                    $query = get_users($args);

                                    if ($query) {
                                        foreach ($query as $user) {
                                            $selected = ($user->ID == $manager) ? "selected" : "";
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
                            <div class="col-md-4 mb-20">
                                <select name="partner" id="" class="form-control select2-tags mb-20">
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
                                                echo "<option value='" . $user->ID . "' " . $selected . ">" . $partner_code . " - " . $ten_cong_ty . " (" . $user->user_email . ")</option>";
                                            }
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-20">
                                <select name="_customer" id="" class="form-control select2-tags mb-20">
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
                                            } wp_reset_postdata();
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-20">
                                <input name="deadline" type="text" class="form-control input-date" value="<?php if ($_POST['deadline']) {
                                                                                                        echo $_POST['deadline'];
                                                                                                    } else echo date('m/d/Y', strtotime('-1 month')) . ' - ' . date('m/d/Y'); ?>">
                            </div>
                            <div class="col-md-4 mb-20">
                                <select name="agency" id="" class="form-control select2-tags mb-20">
                                    <option value="">-- <?php _e('Chi nhánh', 'qlcv'); ?> --</option>
                                    <?php
                                        $terms = get_terms(array(
                                            'taxonomy' => 'agency',
                                            'hide_empty' => false,
                                        ));
                                        foreach ($terms as $value) {
                                            $selected = ($_agency == $value->slug) ? "selected" : "";
                                            echo "<option value='" . $value->slug . "' " . $selected . ">" . $value->name . "</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-20">
                                <select name="post_tag" id="" class="form-control select2-tags mb-20">
                                    <option value="">-- <?php _e('Nguồn việc', 'qlcv'); ?> --</option>
                                    <?php
                                        $terms = get_terms(array(
                                            'taxonomy' => 'post_tag',
                                            'hide_empty' => false,
                                        ));
                                        foreach ($terms as $value) {
                                            $selected = ($_post_tag == $value->slug) ? "selected" : "";
                                            echo "<option value='" . $value->slug . "' " . $selected . ">" . $value->name . "</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                            <?php
                            wp_nonce_field('post_nonce', 'post_nonce_field');
                            ?>
                            <div class="col-md-4 mb-20">
                                <input type="submit" class="button button-primary" value="<?php _e('Lọc', 'qlcv'); ?>" style="padding: 9px 20px;">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!--Basic Start-->
            <div class="col-12 mb-30">
                    <?php
                    // print_r($_POST);
                    // xử lý phân trang
                    $paged = (get_query_var('paged')) ? absint(get_query_var('paged')) : 1;

                    $args   = array(
                        'post_type'     => 'job',
                        'paged'         => $paged,
                        'posts_per_page' => 20,
                    );

                    if (isset($type) && ($type != '')) {
                        $args['tax_query'] = array(
                            'relation' => 'AND',
                            array(
                                'taxonomy'  => 'group',
                                'field'     => 'slug',
                                'terms'     => $type,
                            ),
                        );
                    }

                    if (isset($_agency) && ($_agency != '')) {
                        $args['tax_query'][] = array(
                            // array(
                                'taxonomy'  => 'agency',
                                'field'     => 'slug',
                                'terms'     => $_agency,
                            // ),
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
                    if ($type != 'tiem-nang'){
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
                                }
                                else {
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
                    $total_args = $args;
                    $total_args['posts_per_page'] = -1;
                    $total_query = new WP_Query($total_args);

                    ?>
                <div class="row justify-content-between">
                    <div class="col-lg-auto mb-10">
                        <p><?php _e('Có tổng cộng', 'qlcv'); ?> <?php echo $total_query->post_count; ?> <?php _e('công việc tìm thấy', 'qlcv'); ?></p>
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
                                        if ($work_list){
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