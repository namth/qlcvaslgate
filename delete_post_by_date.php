<?php 
/*
	Template Name: Xoá bài viết theo thời gian
*/

/*$args   = array(
	'post_per_page' => '-1',
    'date_query' => array(
        array(
            'year'  => '2020',
            'month' => '11',
            'day'   => '04',
        ),
    ),
);

$query = new WP_Query( $args );

while ( $query->have_posts() ) {
	$query->the_post();
	echo get_the_title();
	wp_delete_post( get_the_ID() );
	echo "<br>Đã xoá<br>";
}*/
    echo "ket qua <br>";
    #update all task, set manager to task
    
    $args   = array(
        'post_type'     => 'task',
        'posts_per_page'=> '-1',
    );

    $query = new WP_Query( $args );
    if( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            
            $taskid = get_the_ID();
            $jobid = get_field('job');
            $manager = get_field('manager', $jobid);

            update_field('field_60fd46973dd42', $manager, $taskid);
        }
    }

