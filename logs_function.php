<?php 
/* 
* All functions for logs, create, get, delete
*/
function asl_create_log($log, $postid, $edituser=null, $timestamp=null){
    global $wpdb;

    $current_user_id = get_current_user_id();
    $log = sanitize_text_field($log);
    $postid = intval($postid);
    if($timestamp == null){
        $timestamp = current_time('mysql');
    }

    $log_table = 'wp_asllogs';
    $wpdb->insert(
        $log_table,
        array(
            'userid' => $current_user_id,
            'postid' => $postid,
            'edituser' => $edituser,
            'content' => $log,
            'date' => $timestamp
        )
    );
}

function asl_get_logs($postid){
    global $wpdb;

    $postid = intval($postid);
    $log_table = 'wp_asllogs';
    $logs = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $log_table WHERE postid = %d ORDER BY date DESC",
            $postid
        )
    );

    return $logs;
}