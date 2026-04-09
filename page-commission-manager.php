<?php
/*
Template Name: Commission Manager
*/

get_header();

get_sidebar();

// Check if user is logged in and has permission
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

// Get job_id from URL parameter
$job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;

if (!$job_id) {
    echo '<div class="alert alert-danger">' . __('Không tìm thấy ID công việc.', 'qlcv') . '</div>';
    get_footer();
    exit;
}

// Verify job exists
$job = get_post($job_id);
if (!$job || $job->post_type !== 'job') {
    echo '<div class="alert alert-danger">' . __('Công việc không tồn tại.', 'qlcv') . '</div>';
    get_footer();
    exit;
}

// Get job details
$job_title = get_the_title($job_id);
$our_ref = get_field('our_ref', $job_id);
$total_value = get_field('total_value', $job_id);
$paid_value = get_field('paid', $job_id);
$currency = get_field('currency', $job_id);

// Ensure total_value is a valid number
$total_value = floatval($total_value);
if ($total_value <= 0) {
    $total_value = 0;
}

// Ensure paid_value is a valid number
$paid_value = floatval($paid_value);
if ($paid_value <= 0) {
    $paid_value = 0;
}

// Set default currency if empty
if (empty($currency)) {
    $currency = 'VND';
}

// Get job team members
$manager = get_field('manager', $job_id);
$member = get_field('member', $job_id);
$co_managers = get_field('co_manager', $job_id);
$co_members = get_field('co_member', $job_id);
$supervisors = get_field('supervisor', $job_id);

// Get existing commissions from database
global $wpdb;
$existing_commissions = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM wp_aslcommission WHERE jobid = %d ORDER BY role_type, userid",
    $job_id
));

// Convert to associative array for easier access
$commission_data = array();
foreach ($existing_commissions as $commission) {
    $key = $commission->userid . '_' . $commission->role_type;
    $commission_data[$key] = $commission;
}
?>

<!-- Content Body Start -->
<div class="content-body">

    <!-- Page Headings Start -->
    <div class="row justify-content-between mb-10">
        <div class="col-12 col-lg-12 mb-20">
            <a href="<?php echo get_permalink($job_id); ?>"><?php _e('Chi tiết công việc', 'qlcv'); ?></a> > <?php _e('Phân chia tỷ lệ', 'qlcv'); ?>
        </div>
        <!-- Page Heading Start -->
        <div class="col-12 col-lg-12 mb-20">
            <div class="box">
                <div class="page-heading box-head">
                    <h3 class="mb-10"><i class="fa fa-percentage"></i> <?php _e('Quản lý phân chia tỷ lệ cho nhân sự', 'qlcv'); ?></h3>
                    <div class="job-info">
                        <p><strong><?php _e('Công việc:', 'qlcv'); ?></strong> <?php echo esc_html($job_title); ?></p>
                        <p><strong><?php _e('Số REF:', 'qlcv'); ?></strong> <?php echo esc_html($our_ref); ?></p>
                        <p><strong><?php _e('Tổng giá trị:', 'qlcv'); ?></strong> <?php echo number_format($total_value); ?> <?php echo esc_html($currency); ?></p>
                        <p><strong><?php _e('Thực nhận (Paid):', 'qlcv'); ?></strong> 
                            <span style="color: #28a745; font-size: 1.05em;"><?php echo number_format($paid_value); ?> <?php echo esc_html($currency); ?></span>
                            <small class="text-muted"> — <?php _e('Tỷ lệ % sẽ được tính dựa trên số tiền này', 'qlcv'); ?></small>
                        </p>
                    </div>
                </div>
                
                <div class="box-content">
                    <form id="commission-form" method="post">
                        <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
                        <input type="hidden" name="action" value="save_commission">
                        
                        <div class="commission-section">
                            <h4><?php _e('Thành viên trong đội', 'qlcv'); ?></h4>
                            
                            <!-- Manager -->
                            <?php if ($manager && isset($manager['ID'])): 
                                $manager_id = $manager['ID'];
                                $key = $manager_id . '_manager';
                                $current_percent = isset($commission_data[$key]) ? floatval($commission_data[$key]->commission_percent) : '';
                                // Format percent: remove .0 for whole numbers, keep decimals for non-whole numbers
                                if ($current_percent !== '' && $current_percent == intval($current_percent)) {
                                    $current_percent = intval($current_percent);
                                }
                                $current_amount = isset($commission_data[$key]) ? $commission_data[$key]->commission_amount : '';
                            ?>
                            <div class="row commission-row">
                                <div class="col-md-4">
                                    <label><?php echo esc_html($manager['display_name']); ?></label>
                                    <small class="text-muted d-block"><?php _e('Người quản lý', 'qlcv'); ?></small>
                                    <input type="hidden" name="commissions[<?php echo $manager_id; ?>][userid]" value="<?php echo $manager_id; ?>">
                                    <input type="hidden" name="commissions[<?php echo $manager_id; ?>][role_type]" value="manager">
                                </div>
                                <div class="col-md-3">
                                    <label><?php _e('Tỷ lệ %', 'qlcv'); ?></label>
                                    <input type="number" 
                                           name="commissions[<?php echo $manager_id; ?>][commission_percent]" 
                                           class="form-control commission-percent" 
                                           step="1" 
                                           min="0" 
                                           max="100"
                                           value="<?php echo esc_attr($current_percent); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label><?php _e('Số tiền', 'qlcv'); ?></label>
                                    <input type="number" 
                                           name="commissions[<?php echo $manager_id; ?>][commission_amount]" 
                                           class="form-control commission-amount" 
                                           value="<?php echo esc_attr($current_amount); ?>"
                                           readonly>
                                </div>
                                <div class="col-md-2">
                                    <label><?php _e('Tiền tệ', 'qlcv'); ?></label>
                                    <input type="text" 
                                           name="commissions[<?php echo $manager_id; ?>][currency]" 
                                           class="form-control" 
                                           value="<?php echo esc_attr($currency); ?>" 
                                           readonly>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Member -->
                            <?php if ($member && isset($member['ID'])): 
                                $member_id = $member['ID'];
                                $key = $member_id . '_member';
                                $current_percent = isset($commission_data[$key]) ? floatval($commission_data[$key]->commission_percent) : '';
                                // Format percent: remove .0 for whole numbers, keep decimals for non-whole numbers
                                if ($current_percent !== '' && $current_percent == intval($current_percent)) {
                                    $current_percent = intval($current_percent);
                                }
                                $current_amount = isset($commission_data[$key]) ? $commission_data[$key]->commission_amount : '';
                            ?>
                            <div class="row commission-row">
                                <div class="col-md-4">
                                    <label><?php echo esc_html($member['display_name']); ?></label>
                                    <small class="text-muted d-block"><?php _e('Thành viên thực hiện', 'qlcv'); ?></small>
                                    <input type="hidden" name="commissions[<?php echo $member_id; ?>][userid]" value="<?php echo $member_id; ?>">
                                    <input type="hidden" name="commissions[<?php echo $member_id; ?>][role_type]" value="member">
                                </div>
                                <div class="col-md-3">
                                    <label><?php _e('Tỷ lệ %', 'qlcv'); ?></label>
                                    <input type="number" 
                                           name="commissions[<?php echo $member_id; ?>][commission_percent]" 
                                           class="form-control commission-percent" 
                                           step="1" 
                                           min="0" 
                                           max="100"
                                           value="<?php echo esc_attr($current_percent); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label><?php _e('Số tiền', 'qlcv'); ?></label>
                                    <input type="number" 
                                           name="commissions[<?php echo $member_id; ?>][commission_amount]" 
                                           class="form-control commission-amount" 
                                           value="<?php echo esc_attr($current_amount); ?>"
                                           readonly>
                                </div>
                                <div class="col-md-2">
                                    <label><?php _e('Tiền tệ', 'qlcv'); ?></label>
                                    <input type="text" 
                                           name="commissions[<?php echo $member_id; ?>][currency]" 
                                           class="form-control" 
                                           value="<?php echo esc_attr($currency); ?>" 
                                           readonly>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Co-Managers -->
                            <?php if ($co_managers): 
                                $co_manager_ids = explode('|', $co_managers);
                                foreach ($co_manager_ids as $co_manager_id):
                                    if ($co_manager_id):
                                        $co_manager = get_user_by('ID', $co_manager_id);
                                        $key = $co_manager_id . '_co_manager';
                                        $current_percent = isset($commission_data[$key]) ? floatval($commission_data[$key]->commission_percent) : '';
                                        // Format percent: remove .0 for whole numbers, keep decimals for non-whole numbers
                                        if ($current_percent !== '' && $current_percent == intval($current_percent)) {
                                            $current_percent = intval($current_percent);
                                        }
                                        $current_amount = isset($commission_data[$key]) ? $commission_data[$key]->commission_amount : '';
                            ?>
                            <div class="row commission-row">
                                <div class="col-md-4">
                                    <label><?php echo esc_html($co_manager->display_name); ?></label>
                                    <small class="text-muted d-block"><?php _e('Đồng quản lý', 'qlcv'); ?></small>
                                    <input type="hidden" name="commissions[<?php echo $co_manager_id; ?>][userid]" value="<?php echo $co_manager_id; ?>">
                                    <input type="hidden" name="commissions[<?php echo $co_manager_id; ?>][role_type]" value="co_manager">
                                </div>
                                <div class="col-md-3">
                                    <label><?php _e('Tỷ lệ %', 'qlcv'); ?></label>
                                    <input type="number" 
                                           name="commissions[<?php echo $co_manager_id; ?>][commission_percent]" 
                                           class="form-control commission-percent" 
                                           step="1" 
                                           min="0" 
                                           max="100"
                                           value="<?php echo esc_attr($current_percent); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label><?php _e('Số tiền', 'qlcv'); ?></label>
                                    <input type="number" 
                                           name="commissions[<?php echo $co_manager_id; ?>][commission_amount]" 
                                           class="form-control commission-amount" 
                                           value="<?php echo esc_attr($current_amount); ?>"
                                           readonly>
                                </div>
                                <div class="col-md-2">
                                    <label><?php _e('Tiền tệ', 'qlcv'); ?></label>
                                    <input type="text" 
                                           name="commissions[<?php echo $co_manager_id; ?>][currency]" 
                                           class="form-control" 
                                           value="<?php echo esc_attr($currency); ?>" 
                                           readonly>
                                </div>
                            </div>
                            <?php 
                                    endif;
                                endforeach;
                            endif; ?>

                            <!-- Co-Members -->
                            <?php if ($co_members): 
                                $co_member_ids = explode('|', $co_members);
                                foreach ($co_member_ids as $co_member_id):
                                    if ($co_member_id):
                                        $co_member = get_user_by('ID', $co_member_id);
                                        $key = $co_member_id . '_co_member';
                                        $current_percent = isset($commission_data[$key]) ? floatval($commission_data[$key]->commission_percent) : '';
                                        // Format percent: remove .0 for whole numbers, keep decimals for non-whole numbers
                                        if ($current_percent !== '' && $current_percent == intval($current_percent)) {
                                            $current_percent = intval($current_percent);
                                        }
                                        $current_amount = isset($commission_data[$key]) ? $commission_data[$key]->commission_amount : '';
                            ?>
                            <div class="row commission-row">
                                <div class="col-md-4">
                                    <label><?php echo esc_html($co_member->display_name); ?></label>
                                    <small class="text-muted d-block"><?php _e('Đồng thành viên', 'qlcv'); ?></small>
                                    <input type="hidden" name="commissions[<?php echo $co_member_id; ?>][userid]" value="<?php echo $co_member_id; ?>">
                                    <input type="hidden" name="commissions[<?php echo $co_member_id; ?>][role_type]" value="co_member">
                                </div>
                                <div class="col-md-3">
                                    <label><?php _e('Tỷ lệ %', 'qlcv'); ?></label>
                                    <input type="number" 
                                           name="commissions[<?php echo $co_member_id; ?>][commission_percent]" 
                                           class="form-control commission-percent" 
                                           step="1" 
                                           min="0" 
                                           max="100"
                                           value="<?php echo esc_attr($current_percent); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label><?php _e('Số tiền', 'qlcv'); ?></label>
                                    <input type="number" 
                                           name="commissions[<?php echo $co_member_id; ?>][commission_amount]" 
                                           class="form-control commission-amount" 
                                           value="<?php echo esc_attr($current_amount); ?>"
                                           readonly>
                                </div>
                                <div class="col-md-2">
                                    <label><?php _e('Tiền tệ', 'qlcv'); ?></label>
                                    <input type="text" 
                                           name="commissions[<?php echo $co_member_id; ?>][currency]" 
                                           class="form-control" 
                                           value="<?php echo esc_attr($currency); ?>" 
                                           readonly>
                                </div>
                            </div>
                            <?php 
                                    endif;
                                endforeach;
                            endif; ?>

                            <!-- Supervisors -->
                            <?php if ($supervisors): 
                                $supervisor_ids = explode('|', $supervisors);
                                foreach ($supervisor_ids as $supervisor_id):
                                    if ($supervisor_id):
                                        $supervisor = get_user_by('ID', $supervisor_id);
                                        $key = $supervisor_id . '_supervisor';
                                        $current_percent = isset($commission_data[$key]) ? floatval($commission_data[$key]->commission_percent) : '';
                                        // Format percent: remove .0 for whole numbers, keep decimals for non-whole numbers
                                        if ($current_percent !== '' && $current_percent == intval($current_percent)) {
                                            $current_percent = intval($current_percent);
                                        }
                                        $current_amount = isset($commission_data[$key]) ? $commission_data[$key]->commission_amount : '';
                            ?>
                            <div class="row commission-row">
                                <div class="col-md-4">
                                    <label><?php echo esc_html($supervisor->display_name); ?></label>
                                    <small class="text-muted d-block"><?php _e('Người giám sát', 'qlcv'); ?></small>
                                    <input type="hidden" name="commissions[<?php echo $supervisor_id; ?>][userid]" value="<?php echo $supervisor_id; ?>">
                                    <input type="hidden" name="commissions[<?php echo $supervisor_id; ?>][role_type]" value="supervisor">
                                </div>
                                <div class="col-md-3">
                                    <label><?php _e('Tỷ lệ %', 'qlcv'); ?></label>
                                    <input type="number" 
                                           name="commissions[<?php echo $supervisor_id; ?>][commission_percent]" 
                                           class="form-control commission-percent" 
                                           step="1" 
                                           min="0" 
                                           max="100"
                                           value="<?php echo esc_attr($current_percent); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label><?php _e('Số tiền', 'qlcv'); ?></label>
                                    <input type="number" 
                                           name="commissions[<?php echo $supervisor_id; ?>][commission_amount]" 
                                           class="form-control commission-amount" 
                                           value="<?php echo esc_attr($current_amount); ?>"
                                           readonly>
                                </div>
                                <div class="col-md-2">
                                    <label>Tiền tệ</label>
                                    <input type="text" 
                                           name="commissions[<?php echo $supervisor_id; ?>][currency]" 
                                           class="form-control" 
                                           value="<?php echo esc_attr($currency); ?>" 
                                           readonly>
                                </div>
                            </div>
                            <?php 
                                    endif;
                                endforeach;
                            endif; ?>
                        </div>

                        <div class="commission-summary mt-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="alert alert-info" id="summary-alert">
                                        <h5><?php _e('Tổng kết:', 'qlcv'); ?></h5>
                                        <p><strong><?php _e('Tổng tỷ lệ phân chia:', 'qlcv'); ?></strong> <span id="total-percent">0</span>%</p>
                                        <p><strong><?php _e('Tổng số tiền:', 'qlcv'); ?></strong> <span id="total-amount">0</span> <?php echo esc_html($currency); ?></p>
                                        <div id="validation-message" class="mt-2" style="display: none;">
                                            <strong class="text-danger">⚠️ <?php _e('Phải chia hết 100% cho tất cả nhân sự!', 'qlcv'); ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-save"></i> <?php _e('Lưu phân chia tỷ lệ', 'qlcv'); ?>
                            </button>
                            <a href="<?php echo get_permalink($job_id); ?>" class="btn btn-secondary">
                                <i class="fa fa-arrow-left"></i> <?php _e('Quay lại công việc', 'qlcv'); ?>
                            </a>
                        </div>

                        <div id="notification" class="mt-3"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- Content Body End -->

<style>
.commission-row {
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: #f9f9f9;
}

.commission-row:nth-child(even) {
    background-color: #f5f5f5;
}

.job-info {
    background-color: #e9ecef;
    padding: 15px;
    border-radius: 5px;
    margin-top: 10px;
}

.commission-section h4 {
    color: #495057;
    border-bottom: 2px solid #007bff;
    padding-bottom: 5px;
    margin-bottom: 20px;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.alert-info h5 {
    color: #0c5460;
}

.btn-success {
    background-color: #28a745;
    border-color: #28a745;
}

.btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
}

#validation-message {
    border-radius: 5px;
    padding: 8px 12px;
    margin-top: 10px;
}

.alert-success #total-percent {
    font-weight: bold;
    color: #28a745 !important;
}

.alert-warning #total-percent {
    font-weight: bold;
    color: #ffc107 !important;
}

.alert-danger #total-percent {
    font-weight: bold;
    color: #dc3545 !important;
}

.commission-percent {
    border: 2px solid #ddd;
    transition: border-color 0.3s ease;
}

.commission-percent:focus {
    border-color: #007bff !important;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Tính commission dựa trên thực nhận (paid), không phải tổng giá trị
    const totalValue = <?php echo max(0, floatval($paid_value)); ?>;
    const paidValue  = <?php echo max(0, floatval($paid_value)); ?>;
    
    // Function to calculate commission amounts
    function calculateCommissions() {
        let totalPercent = 0;
        let totalAmount = 0;
        
        $('.commission-percent').each(function() {
            const percent = parseFloat($(this).val()) || 0;
            const amountField = $(this).closest('.commission-row').find('.commission-amount');
            
            const amount = (totalValue * percent) / 100;
            amountField.val(Math.round(amount));
            
            totalPercent += percent;
            totalAmount += amount;
        });
        
        // Format display: remove .0 for whole numbers, keep decimals for non-whole numbers
        const displayPercent = totalPercent == Math.round(totalPercent) ? Math.round(totalPercent) : totalPercent.toFixed(1);
        $('#total-percent').text(displayPercent);
        $('#total-amount').text(Math.round(totalAmount).toLocaleString());
        
        // Validation and styling based on total percent
        const summaryAlert = $('#summary-alert');
        const validationMessage = $('#validation-message');
        
        if (totalPercent === 100) {
            // Exactly 100% - success state
            summaryAlert.removeClass('alert-info alert-warning alert-danger').addClass('alert-success');
            validationMessage.hide();
            $('#total-percent').css('color', '#28a745'); // Green
        } else if (totalPercent > 100) {
            // Over 100% - danger state
            summaryAlert.removeClass('alert-info alert-warning alert-success').addClass('alert-danger');
            validationMessage.show().html('<strong class="text-danger">⚠️ <?php _e('Tổng tỷ lệ vượt quá 100%! Hiện tại:', 'qlcv'); ?> ' + displayPercent + '%</strong>');
            $('#total-percent').css('color', '#dc3545'); // Red
        } else if (totalPercent > 0) {
            // Under 100% but has some values - warning state
            summaryAlert.removeClass('alert-info alert-success alert-danger').addClass('alert-warning');
            validationMessage.show().html('<strong class="text-warning">⚠️ <?php _e('Phải chia hết 100%! Hiện tại:', 'qlcv'); ?> ' + displayPercent + '%</strong>');
            $('#total-percent').css('color', '#ffc107'); // Yellow
        } else {
            // No values entered - info state
            summaryAlert.removeClass('alert-warning alert-success alert-danger').addClass('alert-info');
            validationMessage.show().html('<strong class="text-info">💡 <?php _e('Vui lòng nhập tỷ lệ phân chia cho các thành viên', 'qlcv'); ?></strong>');
            $('#total-percent').css('color', 'inherit');
        }
    }
    
    // Calculate on page load
    calculateCommissions();
    
    // Recalculate when percent changes
    $('.commission-percent').on('input', function() {
        calculateCommissions();
    });
    
    // Form submission
    $('#commission-form').on('submit', function(e) {
        e.preventDefault();
        
        const totalPercent = parseFloat($('#total-percent').text());
        
        // Validate that total percent is exactly 100%
        if (totalPercent !== 100) {
            if (totalPercent > 100) {
                alert('<?php _e('Tổng tỷ lệ phân chia vượt quá 100%! Hiện tại:', 'qlcv'); ?> ' + totalPercent + '%\n\n<?php _e('Vui lòng điều chỉnh để tổng cộng đúng 100%.', 'qlcv'); ?>');
            } else {
                alert('<?php _e('Phải chia hết 100% cho tất cả nhân sự!', 'qlcv'); ?>\n\n<?php _e('Hiện tại chỉ có:', 'qlcv'); ?> ' + totalPercent + '%\n<?php _e('Vui lòng bổ sung thêm', 'qlcv'); ?> ' + (100 - totalPercent) + '% <?php _e('nữa.', 'qlcv'); ?>');
            }
            return;
        }
        
        // Check if there are any commission entries
        let hasCommissions = false;
        $('.commission-percent').each(function() {
            if (parseFloat($(this).val()) > 0) {
                hasCommissions = true;
                return false; // break loop
            }
        });
        
        if (!hasCommissions) {
            alert('<?php _e('Vui lòng nhập tỷ lệ phân chia cho ít nhất một thành viên!', 'qlcv'); ?>');
            return;
        }
        
        $.ajax({
            url: AJAX.ajax_url,
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                const result = JSON.parse(response);
                if (result.status === 'success') {
                    let countdown = 3;
                    $('#notification').html('<div class="alert alert-success">' + result.message + 
                        '<br><strong><?php _e('Đang chuyển hướng trong', 'qlcv'); ?> <span id="countdown">' + countdown + '</span> <?php _e('giây...', 'qlcv'); ?></strong></div>');
                    
                    // Countdown timer
                    const timer = setInterval(function() {
                        countdown--;
                        $('#countdown').text(countdown);
                        
                        if (countdown <= 0) {
                            clearInterval(timer);
                            // Redirect to job detail page
                            window.location.href = '<?php echo get_permalink($job_id); ?>';
                        }
                    }, 1000);
                } else {
                    $('#notification').html('<div class="alert alert-danger">' + result.message + '</div>');
                }
            },
            error: function() {
                $('#notification').html('<div class="alert alert-danger"><?php _e('Có lỗi xảy ra, vui lòng thử lại.', 'qlcv'); ?></div>');
            }
        });
    });
});
</script>

<?php get_footer(); ?>
