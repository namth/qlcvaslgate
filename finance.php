<?php 
/*
    Template Name: Sổ cái tài chính
*/
  get_header();

  get_sidebar();

?>

        <!-- Content Body Start -->
        <div class="content-body">

            <!-- Page Headings Start -->
            <div class="row justify-content-between align-items-center mb-10">

                <!-- Page Heading Start -->
                <div class="col-12 col-lg-12 mb-20 row">
                    <div class="col-xlg-3 col-md-3 col-12 mb-30">
                        <div class="top-report">
                            <div class="head">
                                <h4>USD</h4>
                            </div>
                            <div class="content">
                                <?php 
                                    $total_usd = get_field('total_usd', 'option');
                                    echo "<h2>" . ($total_usd) . "</h2>";
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xlg-3 col-md-3 col-12 mb-30">
                        <div class="top-report">
                            <div class="head">
                                <h4>VND</h4>
                            </div>
                            <div class="content">
                                <?php 
                                    $total_vnd = get_field('total_vnd', 'option');
                                    echo "<h2>" . number_format($total_vnd) . "</h2>";
                                ?>
                            </div>
                        </div>
                    </div>
                    <!--Basic Start-->
                    <div class="col-12 mb-30">

                        <div class="row justify-content-between">
                            <div class="col-lg-auto mb-10">
                                <h2>Chi tiết thu chi</h2>
                            </div>
                            <?php 
                                $current_user = wp_get_current_user();

                                // xử lý phân trang
                                $paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

                                $args   = array(
                                    'post_type'     => 'finance',
                                    'paged'         => $paged,
                                    'posts_per_page'=> 20,
                                );

                                $query = new WP_Query( $args );

                            ?>
                            <div class="col-12 box mb-20">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Ngày tháng</th>
                                            <th>Nội dung</th>
                                            <th>Loại</th>
                                            <th>Công việc</th>
                                            <th>Đối tác</th>
                                            <th>Số tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            if( $query->have_posts() ) {
                                                while ( $query->have_posts() ) {
                                                    $query->the_post();

                                                    $f_date = get_field('finance_date');
                                                    $f_type = get_field('finance_type');
                                                    $f_user = get_field('finance_user');
                                                    $f_job  = get_field('finance_job');
                                                    $f_val  = get_field('finance_value');
                                                    $f_cur  = get_field('finance_currency');
                                                    if ($f_type=="Thu") {
                                                        $f_value = '+' . ($f_val) . $f_cur;
                                                    } else {
                                                        $f_value = '-' . ($f_val) . $f_cur;
                                                    }
                                                    echo "<tr>";
                                                    echo "<td>" . $f_date . "</td>";
                                                    echo "<td><a href='" . get_permalink() . "'>" . get_the_title() . "</a></td>";
                                                    echo "<td>" . $f_type . "</td>";
                                                    echo "<td><a href='" . get_permalink($f_job) . "'>" . get_the_title($f_job) . "</a></td>";
                                                    echo "<td><a href='" . get_author_posts_url($f_user['ID']) . "'>" . $f_user['display_name'] . "</a></td>";
                                                    echo "<td>" . $f_value . "</td>";
                                                    
                                                    echo "</tr>";
                                                } wp_reset_postdata();
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

                                        echo paginate_links( array(
                                            'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
                                            'format'    => '?paged=%#%',
                                            'current'   => max( 1, get_query_var('paged') ),
                                            'total'     => $query->max_num_pages,
                                            'type'      => 'list',
                                        ) );
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