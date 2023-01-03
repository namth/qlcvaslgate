<?php 
/*
    Template Name: Danh sách khách hàng (Customer)
*/
  get_header();

  get_sidebar();
?>

        <!-- Content Body Start -->
        <div class="content-body">

            <!-- Page Headings Start -->
            <div class="row justify-content-between align-items-center mb-10">

                <!-- Page Heading Start -->
                <div class="col-12 col-lg-12 mb-20">
                    <!--Basic Start-->
                    <div class="col-12 mb-30">

                        <div class="row justify-content-between">
                            <div class="col-lg-auto mb-10">
                                <h2><?php the_title(); ?></h2>
                            </div>
                            <div class="col-lg-auto mb-10">
                                <a href="<?php echo get_bloginfo('url'); ?>/tao-dau-viec-moi/" class="button button-primary"><span><i class="fa fa-plus"></i>Tạo mới</span></a>
                            </div>
                            <?php 
                                // xử lý phân trang
                                $paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

                                $args   = array(
                                    'post_type'     => 'customer',
                                    'paged'         => $paged,
                                    'posts_per_page'=> 20,
                                );

                                $query = new WP_Query( $args );

                            ?>
                            <div class="col-12 box mb-20">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th><?php _e('Tên người liên hệ', 'qlcv'); ?></th>
                                            <th><?php _e('Tên khách hàng', 'qlcv'); ?></th>
                                            <th><?php _e('Số điện thoại', 'qlcv'); ?></th>
                                            <th><?php _e('Email', 'qlcv'); ?></th>
                                            <th><?php _e('Quốc gia', 'qlcv'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            $i = 0;
                                            if( $query->have_posts() ) {
                                                while ( $query->have_posts() ) {
                                                    $query->the_post();

                                                    $i++;
                                                    $so_dien_thoai = get_field('so_dien_thoai');
                                                    $email = get_field('email');
                                                    $cong_ty = get_field('ten_cong_ty');
                                                    // print_r($customer);
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