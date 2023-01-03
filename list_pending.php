<?php 
/*
    Template Name: Danh sách nhiệm vụ chờ phê duyệt (task)
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
                    <div class="col-12 mb-30">
                        <a href="<?php echo get_bloginfo('url'); ?>/tao-dau-viec-moi/" class="button button-primary"><span><i class="fa fa-plus"></i><?php _e('Tạo công việc mới', 'qlcv'); ?></span></a>
                    </div>
                    <!--Basic Start-->
                    <div class="col-12 mb-30">
                        <?php 
                            // xử lý phân trang
                            $paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

                            $args   = array(
                                'post_type'     => 'task',
                                'paged'         => $paged,
                                'posts_per_page'=> 20,
                                'meta_query'    => array(
                                    'relation'      => 'OR',
                                    array(
                                        'key'       => 'trang_thai',
                                        'compare'   => '=',
                                        'value'     => 'Chờ phê duyệt',
                                    ),
                                    array(
                                        'key'       => 'trang_thai',
                                        'compare'   => '=',
                                        'value'     => 'Quản lý đã phê duyệt',
                                    ),
                                ),
                            );
                            $query = new WP_Query( $args );

                            $total_args = $args;
                            $total_args['posts_per_page'] = -1;
                            $total_query = new WP_Query($total_args);
                        ?>
                        <div class="row justify-content-between">
                            <div class="col-lg-auto mb-10">
                                <p><?php _e('Có tổng cộng', 'qlcv'); ?> <?php echo $total_query->post_count; ?> <?php _e('công việc tìm thấy', 'qlcv'); ?></p>
                                <h2><?php _e('Danh sách công việc chờ phê duyệt', 'qlcv'); ?></h2>
                            </div>
                            <div class="col-lg-auto mb-10">
                                <div class="page-date-range">
                                    <span><?php _e('Lọc theo deadline:', 'qlcv'); ?> </span><input type="text" class="form-control input-date-predefined" id="list_task_by_date">
                                </div>
                            </div>
                            <div class="col-12 box mb-20">
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
                                            if( $query->have_posts() ) {
                                                while ( $query->have_posts() ) {
                                                    $query->the_post();

                                                    $i++;
                                                    $jobID = get_field('job');
                                                    $user_arr = get_field('user');
                                                    // print_r($user_arr);
                                                    $deadline = get_field('deadline');
                                                    $trang_thai = get_field('trang_thai');

                                                    // Tính toán tiến độ công việc
                                                    $start_time = strtotime( get_the_date( 'd-m-Y' ) );
                                                    $current_time = current_time( 'timestamp', 0 );
                                                    $tmp = DateTime::createFromFormat( 'd/m/Y',$deadline );
                                                    $end_time = strtotime( $tmp->format('d-m-Y') );

                                                    // nếu thời gian hiện tại ít hơn deadline thì mới tính %
                                                    if ($current_time < $end_time) {
                                                        $work_percent = round( ($current_time - $start_time) / ( $end_time - $start_time ) *100 );
                                                    } else {
                                                        $work_percent = 100;
                                                    }



                                                    echo "<tr>";
                                                    echo "<td>" . $i . "</td>";
                                                    echo "<td><a href='" . get_permalink() . "'>" . get_the_title() . "</a></td>";
                                                    echo "<td><a href='" . get_permalink($jobID) . "'>" . get_the_title($jobID) . "</a></td>";
                                                    echo "<td>" . $user_arr['nickname'] . " (" . $user_arr['user_email'] . ")</td>";
                                                    echo '<td><div class="progress" style="height: 24px;">
                                                            <div class="progress-bar" role="progressbar" style="width: '.$work_percent.'%" aria-valuenow="'.$work_percent.'" aria-valuemin="0" aria-valuemax="100">' . $deadline . '</div>
                                                            </div>
                                                          </td>';
                                                    echo "<td>" . $trang_thai . "</td>";
                                                    echo "</tr>";
                                                } wp_reset_postdata();
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