<?php
/*
    Template Name: Danh sách mẫu email
*/
get_header();

get_sidebar();

$history_link   = $_SERVER['HTTP_REFERER'];

$type = $_GET['type'];
if (isset($_GET['delete'])  && ($_GET['delete'] != "")) {
    wp_delete_post( $_GET['delete'] );

    wp_redirect($history_link);
    exit;
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

                <div class="row justify-content-between">
                    <div class="col-lg-auto mb-10">
                        <h2><?php the_title(); ?></h2>
                    </div>
                    <?php
                    // xử lý phân trang
                    $paged = (get_query_var('paged')) ? absint(get_query_var('paged')) : 1;

                    $args   = array(
                        'post_type'     => 'email',
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

                    $query = new WP_Query($args);

                    ?>
                    <div class="col-12 box mb-20">
                        <table class="table table-hover list_mail">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php _e('Mẫu mail', 'qlcv'); ?></th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 0;
                                $list_mail = array();
                                $link = get_permalink(784);

                                if ($query->have_posts()) {
                                    while ($query->have_posts()) {
                                        $query->the_post();

                                        $i++;

                                        echo "<tr>";
                                        echo "<td>" . $i . "</td>";
                                        echo "<td>" . get_the_title() . "</td>";
                                        echo "<td><a href='" . $link . "?mail=". get_the_ID() ."'><i class='fa fa-edit'></i></a>";
                                        echo "<a href='?delete=". get_the_ID() ."'><i class='fa fa-trash'></i></a></td>";
                                        echo "</tr>";

                                        $list_mail[] = trim(get_the_title());
                                    }
                                    wp_reset_postdata();
                                } else {
                                    echo "<tr><td colspan=6 class='text-center'>" . __("Không có dữ liệu.", 'qlcv') . "</td></tr>";
                                }
                                if ($type) {
                                    $term       = get_term_by('name', $type, 'group');
                                    $work_list  = get_field('work_list', 'term_' . $term->term_id);
                                    $work_arr   = explode(PHP_EOL, $work_list);
    
                                    foreach ($work_arr as $value) {
                                        if ( !in_array(trim($value), $list_mail) ) {
                                            $i++;
                                            $title_encode = base64_encode($type . "/" . $value);
                                            echo "<tr>";
                                            echo "<td>" . $i . "</td>";
                                            echo "<td>" . $value . "</td>";
                                            echo "<td><a href='". $link ."?code=". $title_encode ."'><i class='fa fa-plus-circle'></i></a></td>";
                                            echo "</tr>";
                                        } 
                                        
                                    }
                                }
                                ?>
                                
                            </tbody>
                        </table>
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