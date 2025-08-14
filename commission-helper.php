<?php
/**
 * Commission Manager Helper Functions
 * Các function hỗ trợ cho việc quản lý hoa hồng
 */

/**
 * Get commission manager page URL for a specific job
 */
function get_commission_manager_url($job_id) {
    $commission_page = get_page_by_path('commission-manager');
    if ($commission_page) {
        return add_query_arg('job_id', $job_id, get_permalink($commission_page->ID));
    }
    return '#';
}

/**
 * Display commission manager button
 */
function display_commission_manager_button($job_id, $class = 'btn btn-info') {
    if (!$job_id) return;
    
    $url = get_commission_manager_url($job_id);
    echo '<a href="' . esc_url($url) . '" class="' . esc_attr($class) . '">';
    echo '<i class="fa fa-percentage"></i> Quản lý hoa hồng';
    echo '</a>';
}

/**
 * Get commission summary for a job
 */
function get_job_commission_summary($job_id) {
    global $wpdb;
    
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT 
            SUM(commission_percent) as total_percent,
            SUM(commission_amount) as total_amount,
            currency,
            COUNT(*) as total_people,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
            SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_count
        FROM wp_aslcommission 
        WHERE jobid = %d AND commission_percent > 0
        GROUP BY currency",
        $job_id
    ));
    
    return $results;
}

/**
 * Display commission summary widget
 */
function display_commission_summary_widget($job_id) {
    $summaries = get_job_commission_summary($job_id);
    
    if (!$summaries) {
        echo '<div class="alert alert-info">
                <i class="fa fa-info-circle"></i> 
                Chưa có dữ liệu phân chia hoa hồng cho công việc này.
              </div>';
        return;
    }
    
    foreach ($summaries as $summary) {
        ?>
        <div class="commission-summary-widget">
            <div class="card">
                <div class="card-header">
                    <h6><i class="fa fa-percentage"></i> Tóm tắt phân chia hoa hồng</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Tổng tỷ lệ:</strong><br>
                            <span class="badge badge-primary"><?php echo number_format($summary->total_percent, 2); ?>%</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Tổng số tiền:</strong><br>
                            <span class="badge badge-success"><?php echo number_format($summary->total_amount); ?> <?php echo esc_html($summary->currency); ?></span>
                        </div>
                        <div class="col-md-3">
                            <strong>Số người:</strong><br>
                            <span class="badge badge-info"><?php echo $summary->total_people; ?> người</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Trạng thái:</strong><br>
                            <?php if ($summary->pending_count > 0): ?>
                                <span class="badge badge-warning"><?php echo $summary->pending_count; ?> chờ</span>
                            <?php endif; ?>
                            <?php if ($summary->approved_count > 0): ?>
                                <span class="badge badge-info"><?php echo $summary->approved_count; ?> duyệt</span>
                            <?php endif; ?>
                            <?php if ($summary->paid_count > 0): ?>
                                <span class="badge badge-success"><?php echo $summary->paid_count; ?> đã trả</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <?php display_commission_manager_button($job_id, 'btn btn-sm btn-outline-primary'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .commission-summary-widget .card {
            border-left: 4px solid #007bff;
        }
        .commission-summary-widget .badge {
            font-size: 0.9em;
        }
        </style>
        <?php
    }
}

/**
 * Get commission data for a specific user and job
 */
function get_user_commission($job_id, $user_id, $role_type = null) {
    global $wpdb;
    
    $where_clause = "jobid = %d AND userid = %d";
    $params = array($job_id, $user_id);
    
    if ($role_type) {
        $where_clause .= " AND role_type = %s";
        $params[] = $role_type;
    }
    
    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM wp_aslcommission WHERE {$where_clause}",
        $params
    ));
}

/**
 * Check if a job has commission setup
 */
function job_has_commission_setup($job_id) {
    global $wpdb;
    
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM wp_aslcommission WHERE jobid = %d AND commission_percent > 0",
        $job_id
    ));
    
    return $count > 0;
}

/**
 * Get commission statistics for reporting
 */
function get_commission_statistics($date_from = null, $date_to = null, $status = null) {
    global $wpdb;
    
    $where_conditions = array();
    $params = array();
    
    if ($date_from) {
        $where_conditions[] = "c.created_date >= %s";
        $params[] = $date_from;
    }
    
    if ($date_to) {
        $where_conditions[] = "c.created_date <= %s";
        $params[] = $date_to;
    }
    
    if ($status) {
        $where_conditions[] = "c.status = %s";
        $params[] = $status;
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    $query = "
        SELECT 
            c.*,
            u.display_name as user_name,
            p.post_title as job_title
        FROM wp_aslcommission c
        LEFT JOIN wp_users u ON c.userid = u.ID
        LEFT JOIN wp_posts p ON c.jobid = p.ID
        {$where_clause}
        ORDER BY c.created_date DESC
    ";
    
    if (!empty($params)) {
        return $wpdb->get_results($wpdb->prepare($query, $params));
    } else {
        return $wpdb->get_results($query);
    }
}

/**
 * Calculate commission amount based on percentage and job value
 */
function calculate_commission_amount($job_id, $percentage) {
    $total_value = get_field('total_value', $job_id);
    if (!$total_value || !$percentage) {
        return 0;
    }
    
    return ($total_value * $percentage) / 100;
}

/**
 * Validate commission percentages for a job
 */
function validate_job_commission_total($job_id, $exclude_user_id = null, $exclude_role = null) {
    global $wpdb;
    
    $where_clause = "jobid = %d";
    $params = array($job_id);
    
    if ($exclude_user_id && $exclude_role) {
        $where_clause .= " AND NOT (userid = %d AND role_type = %s)";
        $params[] = $exclude_user_id;
        $params[] = $exclude_role;
    }
    
    $total = $wpdb->get_var($wpdb->prepare(
        "SELECT SUM(commission_percent) FROM wp_aslcommission WHERE {$where_clause}",
        $params
    ));
    
    return floatval($total);
}
?>
