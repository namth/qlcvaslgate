<?php
/*
    Template Name: Test notification 
*/
get_header();

get_sidebar();


?>
<!-- Content Body Start -->
<div class="content-body">
    <!-- Page Headings Start -->
    <div class="row justify-content-between align-items-center mb-10">
        <div class="col-12 mb-30">
            <?php 
                $status = ['Hoàn thành', 'Huỷ'];
                $args   = array(
                    'post_type'     => array('task', 'job'),
                    'posts_per_page' => '-1',
                    'meta_query'    => array(
                        array(
                            'key'       => 'trang_thai',
                            'value'     => $status,
                            'compare'   => 'NOT IN',
                        ),
                    ),
                );

                $query = new WP_Query($args);
                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();

                        $start_time = strtotime(get_the_date('d-m-Y'));
                        $current_time = current_time('timestamp', 7);

                        $deadline = get_field('deadline');
                        if ($deadline) {
                            $tmp = DateTime::createFromFormat('d/m/Y', $deadline);
                            $end_time = strtotime($tmp->format('d-m-Y'));

                            $half_time = ($end_time + $start_time) / 2;
                            $quater_time = ($end_time + $half_time) / 2;

                            $day_remaining = round(((($end_time - $current_time) / 24) / 60) / 60);
                        } else {
                            echo "Nhiệm vụ: " . get_the_title() . " không có deadline. <br>";
                        }

                        $user_arr = get_field('user');
                        $jobID = get_field('job');
                        $our_ref = get_field('our_ref', $jobID);
                        $manager_arr = get_field('manager', $jobID);
                        $data_supervisor = get_field('supervisor', $jobID);

                        $email_admin = get_field('email_admin', 'option');
                        if ($user_arr) {
                            # code...
                            $to = $user_arr['user_email'];
                        }

                        if ($jobID) {
                            $joblb = " cho " . get_the_title($jobID) . " (" . $our_ref . ")";
                        } else {
                            $joblb = '';
                        }
                        
                    }
                }
            ?>
        </div>

    </div><!-- Page Headings End -->

</div><!-- Content Body End -->

<?php
get_footer();