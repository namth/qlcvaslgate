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
                                    <option value="">-- Chọn đối tác gửi việc --</option>
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
                                <option value="">Tất cả danh mục</option>
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
                                        '0'   => 'Tất cả các loại',
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
                            <input type="submit" class="button button-primary mt-20" value="Lọc" style="padding: 9px 20px;">
                        </div>
                    </form>
                </div>


                <?php
                if (isset($_POST['partner']) && ($_POST['partner'] != "")) {
                    $partner_list = array($_POST['partner']);
                    $detail = true;
                }
                ?>

                <div class="row justify-content-between">
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
                                    <th>Mã đối tác</th>
                                    <th>Tên đối tác</th>
                                    <?php
                                    if ($detail) {
                                        echo "<th>Chi tiết công việc</th>";
                                    }
                                    ?>
                                    <th>Số đầu việc</th>
                                    <th>Tổng giá trị</th>
                                    <th>Đã thu</th>
                                    <th>Cần thu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $current_user = wp_get_current_user();
                                $total_value = array();

                                $args   = array(
                                    'post_type'     => 'job',
                                    'posts_per_page' => -1,
                                );
                                $i = 0;

                                foreach ($partner_list as $partner_1) {

                                    $i++;
                                    $partner_code = get_field('partner_code', 'user_' . $partner_1);
                                    $partner_name = get_field('ten_cong_ty', 'user_' . $partner_1);
                                    $partner_value = array();
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
                                        while ($query->have_posts()) {
                                            $query->the_post();

                                            $currency = get_field('currency');
                                            if ($currency) {
                                                // $temp = get_field('total_value');
                                                $total          = get_field('total_value');
                                                $remainning     = get_field('remainning');
                                                $paid           = get_field('paid');
                                                $currency       = get_field('currency');

                                                if ($total) {
                                                    $partner_value['Tổng thu'][$currency] += $total;
                                                    $partner_value['Đã thu'][$currency] += $paid;
                                                    $partner_value['Cần thu'][$currency] += $remainning;
                                                    // $total_value[$currency] += $temp;
                                                    $money['Thu'][$currency]        += $total;
                                                    $money['Đã thu'][$currency]     += $paid;
                                                    $money['Cần thu'][$currency]    += $remainning;
                                                    $detail_job[] = get_the_title() . " (" . ($temp) . " " . $currency . ")";
                                                }
                                            }
                                            $total_job++;
                                        }
                                        wp_reset_postdata();
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
                        <h4>Tổng</h4>
                        <?php 
                            echo "<table class='table'>
                                    <tr>
                                        <td></td>
                                        <td><b>USD</b></td>
                                        <td><b>VND</b></td>
                                    </tr>";

                            foreach ($money as $key => $value) {
                                echo "<tr>";
                                echo "<td><b>" . $key . ": </b></td>";

                                foreach ($value as $currency => $cash) {
                                    echo "<td>" . $cash . "</td>";
                                }
                                echo "</tr>";
                            }
                            echo "</table>";
                        ?>
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