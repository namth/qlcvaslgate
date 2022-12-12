<?php
/*
    Template Name: Danh sách thông báo (Notification)
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
                    <?php
                    // xử lý phân trang
                    $paged = (get_query_var('paged')) ? absint(get_query_var('paged')) : 1;

                    $args   = array(
                        'post_type'     => 'notification',
                        'paged'         => $paged,
                        'posts_per_page' => 30,
                    );
                    if (!in_array('administrator', $current_user->roles)) {
                        $args['meta_query']   = array(
                            'relation'      => 'AND',
                            array(
                                'key'       => 'receiver',
                                'compare'   => '=',
                                'value'     => $current_user->ID,
                            ),
                        );
                    }

                    $query = new WP_Query($args);

                    ?>
                    <div class="col-12 box mb-20">
                        <ul>
                            <?php
                            if ($query->have_posts()) {
                                while ($query->have_posts()) {
                                    $query->the_post();

                                    $link = get_field('destination');
                            ?>
                                    <li>
                                        <a href="<?php echo $link; ?>">
                                            <div class="content">
                                                <!-- <h6>Sub: New Account</h6> -->
                                                <p><?php
                                                    echo get_the_date('d-m-Y') . " - ";
                                                    the_title();
                                                ?></p>
                                            </div>
                                        </a>
                                    </li>
                            <?php
                                }
                                wp_reset_postdata();
                            }
                            ?>
                        </ul>

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