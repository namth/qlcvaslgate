<?php
/*
    Template Name: Thống kê số lượng công việc theo user 
*/
get_header();

get_sidebar();

$type = $_GET['type'];
$source = $_GET['source'];
if ($type) {
    $get_var = '?type=' . $type;
} else $get_var = "";
if ( isset($_POST['post_nonce_field']) &&
    wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce') ) {

    $f_worked   = $_POST['f_worked'];
    $type       = $_POST['type'];
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
                                <select class="form-control select2-tags mb-20" name="partner">
                                    <option value="">-- <?php _e('Chọn đối tác gửi việc', 'qlcv'); ?> --</option>
                                    <?php
                                    $args   = array(
                                        'role'      => 'partner', /*subscriber, contributor, author*/
                                    );
                                    $query = get_users($args);

                                    if ($query) {
                                        foreach ($query as $user) {
                                            $partner_name = get_field('ten_cong_ty', 'user_' . $user->ID);
                                            $selected = ($user->ID == $member) ? "selected" : "";
                                            echo "<option value='" . $user->ID . "' " . $selected . ">" . $partner_name . " (" . $user->user_email . ")</option>";
                                            $partner_list[] = $user->ID;
                                        }
                                    }

                                    ?>
                                </select>

                            </div>
                        <?php
                        }
                        ?>
                        <div class="col-md-3">
                            <select name="type" class="form-control select2-tags mb-20">
                                <option value=""><?php _e('Tất cả danh mục', 'qlcv'); ?></option>
                                <?php
                                $terms = get_terms(array(
                                    'taxonomy' => 'group',
                                    'hide_empty' => false,
                                ));
                                
                                foreach ($terms as $key => $value) {
                                    $selected = ($value->name == $type) ? "selected" : "";
                                    if (($value->name != "Tiềm năng")) {
                                        echo "<option value='" . $value->name . "' " . $selected . ">" . $value->name . "</option>";
                                    }
                                }    
                                ?>    
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="f_worked" class="form-control select2-tags mb-20">
                                <?php 
                                    $arr = array(
                                        '0'   => __('Tất cả các loại', 'qlcv'),
                                        '1'     => 'Đã chốt',
                                        '2'     => 'Tiềm năng',
                                    );

                                    foreach ($arr as $key => $value) {
                                        $selected = ($key == $f_worked) ? "selected" : "";
                                        echo '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input name="filter_date" type="text" class="form-control input-date-predefined" value="<?php echo $_POST['filter_date']; ?>">
                        </div>
                        <?php
                        wp_nonce_field('post_nonce', 'post_nonce_field');
                        ?>
                        <div class="col-md-2">
                            <input type="submit" class="button button-primary mt-20" value="<?php _e('Lọc', 'qlcv'); ?>" style="padding: 9px 20px;">
                        </div>
                    </form>
                </div>


                <?php
                // Xử lý pagination cho partners
                $partners_per_page = 10;
                $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
                
                if (isset($_POST['partner']) && ($_POST['partner'] != "")) {
                    $partner_list = array($_POST['partner']);
                    $detail = true;
                    $total_partners = 1;
                } else {
                    // Tính tổng số partners và phân trang
                    $total_partners = count($partner_list);
                    $offset = ($current_page - 1) * $partners_per_page;
                    $partner_list = array_slice($partner_list, $offset, $partners_per_page);
                }
                ?>

                <div class="row justify-content-between">
                    <?php if (!isset($_POST['partner']) || empty($_POST['partner'])) : ?>
                        <div class="col-12 mb-3">
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i>
                                <?php printf(__('Hiển thị %d đối tác mỗi trang. Chọn đối tác cụ thể để xem báo cáo chi tiết.', 'qlcv'), $partners_per_page); ?>
                                <strong><?php printf(__('Tổng cộng: %d đối tác', 'qlcv'), $total_partners); ?></strong>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="col-lg-auto mb-10">
                        <h3>Thống kê <?php 
                        echo get_field('ten_cong_ty', 'user_' . $_POST['partner']) . " ";
                        echo $_POST['filter_date']; 
                        ?></h3>
                    </div>
                    <div class="col-12 box mb-20">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php _e('Mã đối tác', 'qlcv'); ?></th>
                                    <th><?php _e('Tên đối tác', 'qlcv'); ?></th>
                                    <?php
                                    if ($detail) {
                                        echo "<th>" . __("Chi tiết công việc", 'qlcv') . "</th>";
                                    }
                                    ?>
                                    <th><?php _e('Số đầu việc', 'qlcv'); ?></th>
                                    <th><?php _e('Tổng giá trị', 'qlcv'); ?></th>
                                    <th><?php _e('Đã thu', 'qlcv'); ?></th>
                                    <th><?php _e('Cần thu', 'qlcv'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $current_user = wp_get_current_user();

                                $args   = array(
                                    'post_type'     => 'job',
                                    'posts_per_page' => 500, // Giới hạn số posts thay vì -1
                                    'fields'        => 'ids', // Chỉ lấy ID để giảm memory
                                    'no_found_rows' => true,  // Tắt pagination count để tăng tốc
                                );
                                
                                // Polylang: Hiển thị tất cả ngôn ngữ thay vì chỉ ngôn ngữ hiện tại
                                if (function_exists('pll_languages_list')) {
                                    $args['lang'] = '';  // Hiển thị tất cả ngôn ngữ
                                }
                                $i = 0;

                                foreach ($partner_list as $partner_1) {
                                    // Kiểm tra memory usage để tránh exhausted
                                    if (memory_get_usage() > 200 * 1024 * 1024) { // 200MB limit
                                        echo "<tr><td colspan='8' class='text-warning'>";
                                        echo "<i class='fa fa-exclamation-triangle'></i> ";
                                        echo __('Dữ liệu quá lớn, vui lòng thu hẹp bộ lọc hoặc chọn đối tác cụ thể', 'qlcv');
                                        echo "</td></tr>";
                                        break;
                                    }

                                    $i++;
                                    $partner_code = get_field('partner_code', 'user_' . $partner_1);
                                    $partner_name = get_field('ten_cong_ty', 'user_' . $partner_1);
                                    $partner_value = array();
                                    $detail_job = array();
                                    $total_job = 0;

                                    $args['meta_query'] = array(
                                        array(
                                            'key'       => 'partner_1',
                                            'value'     => $partner_1,
                                            'compare'   => '=',
                                        ),
                                    );

                                    if (isset($_POST['filter_date'])) {
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

                                    if ( isset($f_worked) && ($f_worked != '0') ){
                                        if ($f_worked == '1') {
                                            $args['tax_query'] = array(
                                                array(
                                                    'taxonomy' => 'group',
                                                    'field'    => 'slug',
                                                    'terms'    => 'tiem-nang',
                                                    'operator' => '!=',
                                                ),
                                            );
                                        } else {
                                            $args['tax_query'] = array(
                                                array(
                                                    'taxonomy' => 'group',
                                                    'field'    => 'slug',
                                                    'terms'    => 'tiem-nang',
                                                ),
                                            );
                                        }
                                    }

                                    if ($type) {
                                        $args['tax_query'][] = array(
                                            array(
                                                'taxonomy'  => 'group',
                                                'field'     => 'slug',
                                                'terms'     => $_POST['type'],
                                            ),
                                        );
                                    }

                                    $query = new WP_Query($args);

                                    $partner_value = array();

                                    if ($query->have_posts()) {
                                        $posts = $query->get_posts();
                                        
                                        foreach ($posts as $post_id) {
                                            // Lấy trực tiếp từ post ID thay vì setup post data
                                            $currency = get_field('currency', $post_id);
                                            if ($currency) {
                                                $total = intval(get_field('total_value', $post_id));
                                                $remainning = intval(get_field('remainning', $post_id));
                                                $paid = intval(get_field('paid', $post_id));

                                                if ($total) {
                                                    // Initialize arrays if they don't exist
                                                    if (!isset($partner_value['Tổng thu'][$currency])) {
                                                        $partner_value['Tổng thu'][$currency] = 0;
                                                    }
                                                    if (!isset($partner_value['Đã thu'][$currency])) {
                                                        $partner_value['Đã thu'][$currency] = 0;
                                                    }
                                                    if (!isset($partner_value['Cần thu'][$currency])) {
                                                        $partner_value['Cần thu'][$currency] = 0;
                                                    }
                                                    
                                                    $partner_value['Tổng thu'][$currency] += $total;
                                                    $partner_value['Đã thu'][$currency] += $paid;
                                                    $partner_value['Cần thu'][$currency] += $remainning;
                                                    $detail_job[] = get_the_title($post_id) . " (" . $total . " " . $currency . ")";
                                                }
                                            }
                                            $total_job++;
                                        }
                                        // Không cần wp_reset_postdata() vì không dùng the_post()
                                    }

                                    if ($total_job) {
                                        echo "<tr>";
                                        echo "<td>" . $i . "</td>";
                                        echo "<td>" . $partner_code . "</td>";
                                        echo "<td><a href='" . get_author_posts_url($partner_1) . "'>" . $partner_name . "</a></td>";
                                        if ($detail) {
                                            echo "<td>" . implode("<br>", $detail_job) . "</td>";
                                        }
                                        echo "<td>" . $total_job . "</td>";

                                        $job_value = array();
                                        if ($partner_value) {
                                            /* foreach ($partner_value as $crcy => $value) {
                                                $job_value[] = ($value) . " " . $crcy;
                                            } */
                                            foreach ($partner_value as $key => $value) {
                                                echo "<td>";
                                                foreach ($value as $currency => $cash) {
                                                    echo $cash . "<br>";
                                                }
                                                echo "</td>";
                                            }
                                        }
                                        echo "<td>" . implode('<br>', $job_value) . "</td>";
                                        echo "</tr>";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-12">
                        <div class="pagination justify-content-center">
                            <?php
                            if (!isset($_POST['partner']) || empty($_POST['partner'])) {
                                // Chỉ hiển thị pagination khi không filter partner cụ thể
                                $total_pages = ceil($total_partners / $partners_per_page);
                                
                                if ($total_pages > 1) {
                                    // Tạo base URL với các filter hiện tại
                                    $current_url = $_SERVER['REQUEST_URI'];
                                    $url_parts = parse_url($current_url);
                                    $base_url = $url_parts['path'];
                                    
                                    // Giữ lại các GET parameters hiện có (trừ paged)
                                    $query_params = array();
                                    if (isset($url_parts['query'])) {
                                        parse_str($url_parts['query'], $query_params);
                                        unset($query_params['paged']);
                                    }
                                    
                                    $base_url .= !empty($query_params) ? '?' . http_build_query($query_params) : '';
                                    $separator = empty($query_params) ? '?' : '&';
                                    
                                    echo '<nav aria-label="Partners pagination">';
                                    echo '<ul class="pagination">';
                                    
                                    // Previous button
                                    if ($current_page > 1) {
                                        echo '<li class="page-item"><a class="page-link" href="' . $base_url . $separator . 'paged=' . ($current_page - 1) . '">';
                                        echo '<i class="fa fa-chevron-left"></i> ' . __('Trước', 'qlcv') . '</a></li>';
                                    }
                                    
                                    // Page numbers
                                    $start_page = max(1, $current_page - 2);
                                    $end_page = min($total_pages, $current_page + 2);
                                    
                                    if ($start_page > 1) {
                                        echo '<li class="page-item"><a class="page-link" href="' . $base_url . $separator . 'paged=1">1</a></li>';
                                        if ($start_page > 2) {
                                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                        }
                                    }
                                    
                                    for ($p = $start_page; $p <= $end_page; $p++) {
                                        $active_class = ($p == $current_page) ? ' active' : '';
                                        echo '<li class="page-item' . $active_class . '">';
                                        if ($p == $current_page) {
                                            echo '<span class="page-link">' . $p . '</span>';
                                        } else {
                                            echo '<a class="page-link" href="' . $base_url . $separator . 'paged=' . $p . '">' . $p . '</a>';
                                        }
                                        echo '</li>';
                                    }
                                    
                                    if ($end_page < $total_pages) {
                                        if ($end_page < $total_pages - 1) {
                                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                        }
                                        echo '<li class="page-item"><a class="page-link" href="' . $base_url . $separator . 'paged=' . $total_pages . '">' . $total_pages . '</a></li>';
                                    }
                                    
                                    // Next button
                                    if ($current_page < $total_pages) {
                                        echo '<li class="page-item"><a class="page-link" href="' . $base_url . $separator . 'paged=' . ($current_page + 1) . '">';
                                        echo __('Sau', 'qlcv') . ' <i class="fa fa-chevron-right"></i></a></li>';
                                    }
                                    
                                    echo '</ul>';
                                    echo '</nav>';
                                    
                                    // Hiển thị thông tin trang
                                    $start_item = ($current_page - 1) * $partners_per_page + 1;
                                    $end_item = min($current_page * $partners_per_page, $total_partners);
                                    echo '<div class="pagination-info text-center mt-3">';
                                    echo sprintf(__('Hiển thị %d-%d trong tổng số %d đối tác', 'qlcv'), $start_item, $end_item, $total_partners);
                                    echo '</div>';
                                }
                            }
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