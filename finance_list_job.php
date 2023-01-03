<?php 
/*
    Template Name: Danh sách tài chính của công việc
*/
get_header();

get_sidebar();

$type = $_GET['type'];
if ($type) {
    $get_var = '?type=' . $type;
} else $get_var = "";

$post_per_page = 20;

if ( isset($_POST['post_nonce_field']) &&
    wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce') ) {

    $f_worked   = $_POST['f_worked'];
    $type       = $_POST['type'];
    $post_per_page = 200;
}
?>

        <!-- Content Body Start -->
        <div class="content-body">

            <!-- Page Headings Start -->
            <div class="row justify-content-between align-items-center mb-10">

                <div class="col-12 mb-30">
                    <div id="filter">
                        <form action="#" method="POST" class="mb-20">
                            <div class="row mb-20">
                                <div class="col-md-3 mb-20">
                                    <select name="f_worked" id="" class="form-control select2-tags mb-20">
                                        <?php 
                                            $arr = array(
                                                '0'   => __('Tất cả các loại', 'qlcv'),
                                                '1'     => 'Đã chốt',
                                                '2'     => 'Tiềm năng'
                                            );

                                            foreach ($arr as $key => $value) {
                                                $selected = ($key == $f_worked) ? "selected" : "";
                                                echo '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-20">
                                    <select class="form-control select2-tags mb-20" name="type">
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
                                <div class="col-md-3 mb-20">
                                    <input name="filter_date" type="text" class="form-control input-date-predefined">
                                </div>
                                <?php
                                wp_nonce_field('post_nonce', 'post_nonce_field');
                                ?>
                                <div class="col-md-3 mb-20">
                                    <input type="submit" class="button button-primary" value="<?php _e('Lọc', 'qlcv'); ?>" style="padding: 9px 20px;">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Page Heading Start -->
                <div class="col-12 col-lg-12 mb-20">
                    <div class="col-12 mb-30">
                        <a href="<?php echo get_bloginfo('home') . '/tao-phieu-thu-chi/' . $get_var; ?>" class="button button-primary"><span><i class="fa fa-plus"></i><?php _e('Tạo phiếu thu chi mới', 'qlcv'); ?></span></a>
                    </div>
                    <!--Basic Start-->
                    <div class="col-12 mb-30">

                        <div class="row justify-content-between">
                            <div class="col-lg-auto mb-10">
                                <h2><?php _e('Danh sách công việc', 'qlcv'); ?></h2>
                            </div>
                            <?php 
                                $current_user = wp_get_current_user();

                                // xử lý phân trang
                                $paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

                                $args   = array(
                                    'post_type'     => 'job',
                                    'paged'         => $paged,
                                    'posts_per_page'=> $post_per_page,
                                );

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

                                if (isset($_POST['type']) && ($_POST['type'] != '')) {
                                    if ($_POST['type']!="all") {
                                        $args['tax_query'] = array(
                                            array(
                                                'taxonomy'  => 'group',
                                                'field'     => 'slug',
                                                'terms'     => $_POST['type'],
                                            ),
                                        );
                                    }
                                }

                                $query = new WP_Query( $args );

                            ?>
                            <div class="col-12 box mb-20">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th><?php _e('Ngày tháng', 'qlcv'); ?></th>
                                            <th><?php _e('Công việc', 'qlcv'); ?></th>
                                            <th><?php _e('Khách hàng', 'qlcv'); ?></th>
                                            <th><?php _e('Đối tác', 'qlcv'); ?></th>
                                            <th><?php _e('Tổng thu', 'qlcv'); ?></th>
                                            <th><?php _e('Đã thu', 'qlcv'); ?></th>
                                            <th><?php _e('Cần thu', 'qlcv'); ?></th>
                                            <th><?php _e('Tổng chi', 'qlcv'); ?></th>
                                            <th><?php _e('Đã chi', 'qlcv'); ?></th>
                                            <th><?php _e('Còn nợ lại', 'qlcv'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            $i = 0;
                                            if( $query->have_posts() ) {
                                                while ( $query->have_posts() ) {
                                                    $query->the_post();

                                                    $i++;
                                                    $our_ref = get_field('our_ref');
                                                    $customer = get_field('customer');
                                                    $partner_2 = get_field('partner_2');
                                                    // print_r($customer);
                                                    $total_value    = get_field('total_value');
                                                    $remainning     = get_field('remainning');
                                                    $paid           = get_field('paid');
                                                    $currency       = get_field('currency');
                                                    $total_cost     = get_field('total_cost');
                                                    $debt           = get_field('debt');
                                                    $currency_out   = get_field('currency_out');
                                                    $advance_money  = get_field('advance_money');

                                                    $money['Thu'][$currency]        += $total_value;
                                                    $money['Đã thu'][$currency]     += $paid;
                                                    $money['Cần thu'][$currency]    += $remainning;
                                                    $money['Chi'][$currency_out]    += $total_cost;
                                                    $money['Đã chi'][$currency_out] += $advance_money;
                                                    $money['Cần chi'][$currency_out]+= $debt;

                                                    echo "<tr>";
                                                    echo "<td>" . $our_ref . "</td>";
                                                    echo "<td>" . get_the_date('d/m/Y') . "</td>";
                                                    echo "<td><a href='" . get_permalink() . "'>" . get_the_title() . "</a></td>";
                                                    echo "<td><a href='" . $customer->guid . "'>" . $customer->post_title . "</a></td>";
                                                    echo "<td><a href='" . get_author_posts_url($partner_2['ID']) . "'>" . $partner_2['display_name'] . "</a></td>";
                                                    if ($total_value) {
                                                        echo "<td>" . ($total_value) . " " . $currency . "</td>";
                                                    } else echo "<td></td>";
                                                    if ($paid) {
                                                        echo "<td>" . ($paid) . " " . $currency . "</td>";
                                                    } else echo "<td></td>";
                                                    if ($remainning) {
                                                        echo "<td>" . ($remainning) . " " . $currency . "</td>";
                                                    } else echo "<td></td>";
                                                    if ($total_cost) {
                                                        echo "<td>" . ($total_cost) . " " . $currency_out . "</td>";
                                                    } else echo "<td></td>";
                                                    if ($advance_money) {
                                                        echo "<td>" . ($advance_money) . " " . $currency_out . "</td>";
                                                    } else echo "<td></td>";
                                                    if ($debt) {
                                                        echo "<td>" . ($debt) . " " . $currency_out . "</td>";
                                                    } else echo "<td></td>";
                                                    
                                                    echo "</tr>";
                                                } wp_reset_postdata();
                                            } else {
                                                echo "<tr><td colspan=6 class='text-center'>" . __("Không có dữ liệu.", 'qlcv') . "</td></tr>";
                                            }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-12">
                                <div class="pagination justify-content-center">
                                    <?php 
                                        $big = 999999999; // need an unlikely integer

                                        echo paginate_links( array(
                                            'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
                                            'format'    => '?paged=%#%',
                                            'current'   => max( 1, get_query_var('paged') ),
                                            'total'     => $query->max_num_pages,
                                            'type'      => 'list',
                                        ) );
                                    ?>
                                </div>
                                <div>
                                    <?php 
                                        if ($post_per_page = 200) {
                                            echo "<h3>Tổng kết</h3>";
                                            // print_r($money);
                                            $money["Đã thu - đã chi"]['USD'] = $money["Đã thu"]["USD"] - $money["Đã chi"]["USD"];
                                            $money["Đã thu - đã chi"]['VND'] = $money["Đã thu"]["VND"] - $money["Đã chi"]["VND"];

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