<?php
# add custom posts & custom fields
add_action('init','all_my_hooks');
function all_my_hooks(){
    $dir = dirname( __FILE__ );
    require_once( $dir . '/custom_posts.php');
    require_once( $dir . '/custom_fields.php');
    require_once( $dir . '/ajax_filter.php');
    require_once( $dir . '/logs_function.php');
    require_once( $dir . '/form_function.php');
    require_once( $dir . '/commission-helper.php');
    // require_once ($dir . '/datacenter/secret.php');
    require_once ($dir . '/datacenter/mongodb_connection.php');
    require_once ($dir . '/api_qlcv.php');
}

register_nav_menus(array('main-menu' => esc_html__('Main Menu', 'blankslate')));
add_theme_support('title-tag');

// Polylang: Auto-include all languages for job and task post types
add_action('pre_get_posts', 'include_all_languages_for_job_task');
function include_all_languages_for_job_task($query) {
    // Only run on frontend, not in admin
    if (is_admin() || !function_exists('pll_languages_list')) {
        return;
    }
    
    // Check if it's a query for job or task post types
    $post_type = $query->get('post_type');
    if (is_array($post_type)) {
        $has_job_or_task = array_intersect($post_type, ['job', 'task']);
        if (!empty($has_job_or_task)) {
            $query->set('lang', '');
        }
    } elseif (in_array($post_type, ['job', 'task'])) {
        $query->set('lang', '');
    }
}

add_action('wp_enqueue_scripts', 'blankslate_load_scripts');
function blankslate_load_scripts()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script('custom', get_template_directory_uri() . '/assets/js/custom.js', array('jquery'), '1.1', true);
    wp_localize_script('custom', 'AJAX', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));

    if ( !session_id() ) {
        session_start();
    }
}

function time_elapsed_string($datetime, $full = false)
{
    $tz = new DateTimeZone('Asia/Ho_Chi_Minh');
    $now = new DateTime('now', $tz);
    $ago = new DateTime();
    $ago->setTimestamp($datetime);
    $ago->setTimezone($tz);
    $diff = $now->diff($ago);

    // Create a new array to store our values
    $values = [
        'y' => $diff->y,
        'm' => $diff->m,
        'd' => $diff->d,
        'h' => $diff->h,
        'i' => $diff->i,
        's' => $diff->s,
        'w' => 0,
    ];
    
    // Calculate weeks from days
    $values['w'] = floor($values['d'] / 7);
    $values['d'] -= $values['w'] * 7;

    $string = array(
        'y' => __('năm', 'qlcv'),
        'm' => __('tháng', 'qlcv'),
        'w' => __('tuần', 'qlcv'),
        'd' => __('ngày', 'qlcv'),
        'h' => __('giờ', 'qlcv'),
        'i' => __('phút', 'qlcv'),
        's' => __('giây', 'qlcv'),
    );
    foreach ($string as $k => &$v) {
        if ($values[$k]) {
            $v = $values[$k] . ' ' . $v;
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ' . __('trước', 'qlcv') : __('vừa xong', 'qlcv');
}

add_action('wp_ajax_add_user', 'add_new_user');
function add_new_user()
{
    # get data from the form
    $data_parse = parse_str($_POST['data'], $output);

    $result = process_addnew_partner($output);

    echo json_encode($result);
    exit;
}

# process when you choose the range of date that listing all tasks in that time
add_action('wp_ajax_list_task_by_date', 'list_task_by_date');
function list_task_by_date()
{
    $date_value = explode(' - ', $_POST['date_value']);
    $date_1 = date('Ymd', strtotime($date_value[0]));
    $date_2 = date('Ymd', strtotime($date_value[1]));
    $args   = array(
        'post_type'     => 'task',
        'posts_per_page' => -1,
        'meta_query'    => array(
            array(
                'key'       => 'deadline',
                'compare'   => 'BETWEEN',
                'type'      => 'DATE',
                'value'     => array($date_1, $date_2),
            ),
        ),
    );

    $query = new WP_Query($args);
    $i = 0;
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $i++;
            $jobID = get_field('job');
            $user_arr = get_field('user');
            // print_r($user_arr);
            $deadline = get_field('deadline');
            $trang_thai = get_field('trang_thai');

            // Tính toán tiến độ công việc
            $start_time = strtotime(get_the_date('d-m-Y'));
            $current_time = current_time('timestamp', 0);
            $tmp = DateTime::createFromFormat('d/m/Y', $deadline);
            $end_time = strtotime($tmp->format('d-m-Y'));

            // nếu thời gian hiện tại ít hơn deadline thì mới tính %
            if ($current_time < $end_time) {
                $work_percent = round(($current_time - $start_time) / ($end_time - $start_time) * 100);
            } else {
                $work_percent = 100;
            }



            echo "<tr>";
            echo "<td>" . $i . "</td>";
            echo "<td><a href='" . get_permalink() . "'>" . get_the_title() . "</a></td>";
            echo "<td><a href='" . get_permalink($jobID) . "'>" . get_the_title($jobID) . "</a></td>";
            echo "<td>" . $user_arr['nickname'] . " (" . $user_arr['user_email'] . ")</td>";
            echo '<td><div class="progress" style="height: 24px;">
                    <div class="progress-bar" role="progressbar" style="width: ' . $work_percent . '%" aria-valuenow="' . $work_percent . '" aria-valuemin="0" aria-valuemax="100">' . $deadline . '</div>
                    </div>
                  </td>';
            echo "<td>" . $trang_thai . "</td>";
            echo "</tr>";
        }
        wp_reset_postdata();
    }
    // echo $date_value[0];
    exit;
}

add_action('wp_ajax_add_customer', 'add_new_customer');
function add_new_customer()
{
    global $wpdb;
    
    # get data from the form
    $data = parse_str($_POST['data'], $output);

    $customer_name  = $output['customer_name'];
    $user_email     = $output['user_email'];
    $phone_number   = $output['phone_number'];
    $address        = $output['address'];
    $country        = $output['country'];
    $note           = $output['note'];
    $link_onedrive  = $output['link_onedrive'];

    # add new customer
    if ($customer_name) {
        $args = array(
            'post_title'    => $customer_name,
            'post_content'  => $note,
            'post_status'   => 'publish',
            'post_type'     => 'customer',
        );

        $error = false;
        $inserted = wp_insert_post($args, $error);

        $data = array();
        # if it's success create new user,
        # add more info throught custom fields
        update_field('field_600d31f4060eb', $customer_name, $inserted); # tên công ty / tên khách
        update_field('field_600d3235060ed', $user_email, $inserted); # email liên hệ
        update_field('field_600d3211060ec', $phone_number, $inserted); # phone number
        update_field('field_600d323d060ee', $address, $inserted); # address
        update_field('field_6037200ec98cc', $country, $inserted); # country
        update_field('field_6010f85bfcf55', $link_onedrive, $inserted); # link_onedrive

        # Update aslcustomer table
        $aslTable = 'wp_aslcustomer';
        
        $wpdb->insert(
            $aslTable,
            array(
                'customerid'    => $inserted,
                'name'          => $customer_name,
                'companyName'   => $customer_name,
                'country'       => $country,
                'phone'         => $phone_number,
                'email'         => $user_email,
                'date'          => current_time('mysql', 1)
            )
        );

        $data['status'] = 'success';
        if ($user_email) {
            $email_output =  " (" . $user_email . ")";
        }
        $data['content'] = "<option value='" . $inserted . "' selected>" . $customer_name . $email_output . "</option>";
        $data['notification'] = '<div class="alert alert-success" role="alert">
                                    <i class="fa fa-check"></i> ' . __('Đã tạo khách hàng thành công', 'qlcv') . '
                                  </div>';
    } else {
        $data['status'] = 'error';
        $data['notification'] = '<div class="alert alert-danger" role="alert">
                                    <i class="zmdi zmdi-info"></i> ' . __('Có lỗi xảy ra, xin vui lòng kiểm tra lại.', 'qlcv') . '
                                  </div>';
    }

    $data['hide_form']          = '#create_customer';
    $data['div_notification']   = '#create_customer .notification';
    $data['select_element']     = 'select[name="customer"]';

    echo json_encode($data);
    exit;
}

add_action('wp_ajax_copy_customer', 'copy_customer_from_partner');
function copy_customer_from_partner()
{
    # get data from the form
    $partnerID = $_POST['partnerID'];
    $partner = get_user_by('ID', $partnerID);

    $customer_name  = $partner->display_name;
    $user_email     = $partner->user_email;
    $phone_number   = get_field('so_dien_thoai', 'user_' . $partnerID);
    $address        = get_field('dia_chi', 'user_' . $partnerID);
    $ten_cong_ty    = get_field('ten_cong_ty', 'user_' . $partnerID);
    $country        = get_field('quoc_gia', 'user_' . $partnerID);
    $note           = $partner->description;
    $link_onedrive  = get_field('document', 'user_' . $partnerID);

    # kiểm tra xem email đã được sử dụng chưa
    $args   = array(
        'post_type'     => 'customer',
        'meta_query'    => array(
            array(
                'key'       => 'email',
                'value'     => $user_email,
                'compare'   => '=',
            ),
        ),
    );

    $query = new WP_Query($args);
    if ($query->have_posts()) {
        $data['status'] = 'error';
        $data['notification'] = '<div class="alert alert-danger" role="alert">
                                    <i class="zmdi zmdi-info"></i> ' . __('Khách hàng đã tồn tại trong hệ thống.', 'qlcv') . '
                                  </div>';
    } else {
        # add new customer
        if ($customer_name) {
            $args = array(
                'post_title'    => $customer_name,
                'post_content'  => $note,
                'post_status'   => 'publish',
                'post_type'     => 'customer',
            );

            $error = false;
            $inserted = wp_insert_post($args, $error);

            $data = array();
            # if it's success create new user,
            # add more info throught custom fields
            update_field('field_600d31f4060eb', $ten_cong_ty, $inserted); # tên công ty / tên khách
            update_field('field_600d3235060ed', $user_email, $inserted); # email liên hệ
            update_field('field_600d3211060ec', $phone_number, $inserted); # phone number
            update_field('field_600d323d060ee', $address, $inserted); # address
            update_field('field_6037200ec98cc', $country, $inserted); # country
            update_field('field_6010f85bfcf55', $link_onedrive, $inserted); # link_onedrive

            $data['status'] = 'success';
            if ($ten_cong_ty) {
                $text_output =  " (" . $ten_cong_ty . ")";
            }
            $data['content'] = "<option value='" . $inserted . "' selected>" . $customer_name . $text_output . "</option>";
            $data['notification'] = '<div class="alert alert-success" role="alert">
                                        <i class="fa fa-check"></i> ' . __('Đã tạo khách hàng thành công', 'qlcv') . '
                                      </div>';
        } else {
            $data['status'] = 'error';
            $data['notification'] = '<div class="alert alert-danger" role="alert">
                                        <i class="zmdi zmdi-info"></i> ' . __('Có lỗi xảy ra, xin vui lòng kiểm tra lại.', 'qlcv') . '
                                      </div>';
        }
    }

    $data['select_element']     = 'select[name="customer"]';

    echo json_encode($data);
    exit;
}

add_action('wp_ajax_add_new_job', 'add_new_job');
function add_new_job()
{
    global $wpdb;
    
    # get data from the form
    // $file_upload            = $_FILE['file_upload'];
    $data_partner           = $_POST['data_partner'];
    $data_foreign_partner   = $_POST['data_foreign_partner'];
    $data_customer          = $_POST['data_customer'];
    $data_manager           = $_POST['data_manager'];
    $data_co_manager        = $_POST['data_co_manager'];
    $data_co_member         = $_POST['data_co_member'];
    $data_member            = $_POST['data_member'];
    $data_supervisor        = $_POST['data_supervisor'];
    $data_agency            = $_POST['data_agency'];
    // $data_job       = parse_str( $_POST['data_job'], $output );

    $country        = implode(", ", $_POST['country']);
    $job_name       = $_POST['job_name'];
    $partner_ref    = $_POST['partner_ref'];
    $our_ref        = $_POST['our_ref'];
    $danh_muc       = $_POST['danh_muc'];
    $nguon_dau_viec = $_POST['nguon_dau_viec'];
    $partner_1      = $_POST['partner_1']; // người giới thiệu
    $tiem_nang      = $_POST['tiem_nang'];
    $note           = $_POST['note'];
    $mindful        = $_POST['mindful'];
    $link_onedrive  = $_POST['link_onedrive'];
    $level          = !empty($_POST['level']) ? $_POST['level'] : 'Đơn giản'; // độ khó, mặc định là Đơn giản
    # Nhãn hiệu
    $brand_name     = $_POST['brand_name'];
    $brand_group    = $_POST['brand_group'];
    $brand_number_group = $_POST['brand_number_group'];
    # kiểu dáng
    $kdang_pic      = $_POST['kdang_pic'];
    $kdang_info     = $_POST['kdang_info'];
    $kdang_phuongan = $_POST['kdang_phuongan'];
    # sáng chế
    $sche_info      = $_POST['sche_info'];
    $sche_request_1 = $_POST['sche_request_1'];
    $sche_request_2 = $_POST['sche_request_2'];
    # Việc luật
    if (($_POST['deadline']) && ($danh_muc == "Việc luật")) {
        # xử lý chuỗi ngày tháng từ dạng DD/MM/YYYY sang YYYYMMDD để phù hợp với format của ACF custom field
        $deadline_arr   = explode('/', $_POST['deadline']);
        $temp_date      = array_reverse($deadline_arr);
        $new_deadline   = implode('', $temp_date);
        $current_user   = wp_get_current_user();
        $current_time   = current_time('timestamp', 7);
    }
    if($danh_muc == "Việc luật"){
        $danhmuckhac = $_POST['other_job'];
    }
    # finance
    $currency       = $_POST['currency'];
    $total_value    = $_POST['total_value'];
    $paid           = $_POST['paid'];
    # tính toán số tiền còn lại
    $remaining      = 0;
    if ($total_value) {
        # validate các số có null không, trước khi tính toán
        $remaining      = $paid?$total_value - $paid:$total_value;
    }

    # add new customer
    if ( $job_name && $data_partner && $data_customer &&
        $data_manager && $data_member ) {

        $args = array(
            'post_title'    => $job_name,
            'post_content'  => $note,
            'post_status'   => 'publish',
            'post_type'     => 'job',
        );

        $error = false;
        $inserted = wp_insert_post($args, $error);

        $data = array();
        # if it's success create new job,
        # add more info throught custom fields
        if ($inserted) {
            # tạo mã code cho đầu việc
            $code = base64_encode($inserted);

            # tạo số ref cho công việc
            if (($our_ref == "")) {
                $terms = get_term_by('name', $danh_muc, 'group');
                $term_id    = $terms->term_id;
                $groups_code = get_field('groups_code', 'term_' . $term_id);
                $order_number = get_field('order_number', 'term_' . $term_id);
                $partner_code = get_field('partner_code', 'user_' . $data_partner);

                $order_number++;
                $our_ref = $groups_code . $order_number . $partner_code;

                update_field('order_number', $order_number, 'term_' . $term_id);
            }
            update_field('field_606fe68f81af2', $code, $inserted);
            update_field('field_600fe093bb385', $data_customer, $inserted); # customer
            # nếu không có người giới thiệu thì người gửi việc sẽ là người giới thiệu
            if (!$partner_1) {
                $partner_1 = $data_partner;
            }
            update_field('field_602f78f1c59ba', $partner_1, $inserted); # partner 1
            update_field('field_602f7923c59bb', $data_partner, $inserted); # partner 2
            update_field('field_609bf99f726ef', $data_foreign_partner, $inserted); # data_foreign_partner
            update_field('field_603629217fe93', $data_manager, $inserted); # manager
            update_field('field_603627f913b2c', $data_member, $inserted); # data_member
            update_field('field_659ce731517c9', $data_supervisor, $inserted); # data_supervisor
            update_field('field_675f0e8c798f8', $data_co_manager, $inserted); # data_co_manager
            update_field('field_675f0e27798f7', $data_co_member, $inserted); # data_co_member
            update_field('field_600fdbda0269e', $danh_muc, $inserted); # phân loại
            update_field('field_6099f6bb87256', $country, $inserted); # quốc gia nộp
            update_field('field_6099f71187257', $partner_ref, $inserted); # Số REF của đối tác
            update_field('field_6099f75a87258', $our_ref, $inserted); # Số REF của mình
            update_field('field_60a38cc126a5f', $link_onedrive, $inserted); # Link tài liệu
            update_field('field_60fceb18a736d', $mindful, $inserted); # Lưu ý công việc
            update_field('field_69070e5d8880e', $level, $inserted); # Độ khó
            # if it's a simple job (has deadline), system will be updated history & status
            if ($new_deadline) {
                update_field('field_600fde50f9be7', $new_deadline, $inserted); # deadline
                update_field('field_600fde92f9be9', "Mới", $inserted); # Status

                $noi_dung = "đã tạo nhiệm vụ mới";
                $row_update = array(
                    'nguoi_thuc_hien'   => $current_user,
                    'noi_dung'          => $noi_dung,
                    'thoi_gian'         => $current_time,
                );

                add_row('field_6010e02533119', $row_update, $inserted);
            }
            # update finance
            update_field('field_60a231d39602e', $currency, $inserted); # currency
            update_field('field_60a231d395dd8', $total_value, $inserted); # total value
            update_field('field_60a231d395f2e', $paid, $inserted); # paid
            update_field('field_60a231d3961b0', $remaining, $inserted); # remaining

            if ($tiem_nang) {
                wp_set_object_terms($inserted, array("Tiềm năng", $danh_muc), 'group');
            } else wp_set_object_terms($inserted, $danh_muc, 'group');
            
            #set danh mục khác nếu có
            if ($danhmuckhac && ($danh_muc == "Việc luật")) wp_set_object_terms($inserted, $danhmuckhac, 'group', true);
            #set nguồn đầu việc
            if ($nguon_dau_viec) wp_set_object_terms($inserted, $nguon_dau_viec, 'post_tag');

            if($data_agency){
                wp_set_object_terms($inserted, $data_agency, 'agency');
            }

            switch ($danh_muc) {
                case 'Nhãn hiệu':
                    if (isset($_FILES['file_upload'])) {
                        require_once(ABSPATH . 'wp-admin/includes/file.php');
                        $uploadedfile = $_FILES['file_upload'];
                        # check neu file upload khong co loi, tuc la khong empty
                        if ($uploadedfile["error"] == 0) {
                            $movefile = wp_handle_upload($uploadedfile, array('test_form' => false));
                            //On sauvegarde la photo dans le média library
                            if ($movefile) {
                                $wp_upload_dir = wp_upload_dir();
                                $attachment = array(
                                    'guid' => $wp_upload_dir['url'] . '/' . basename($movefile['file']),
                                    'post_mime_type' => $movefile['type'],
                                    'post_title' => preg_replace('/\.[^.]+$/', '', basename($movefile['file'])),
                                    'post_content' => '',
                                    'post_status' => 'inherit',
                                );
                                $attach_id = wp_insert_attachment($attachment, $movefile['file']);

                                update_field('field_600fdca20269f', $attach_id, $inserted);
                            }
                        }
                    }
                    update_field('field_600fd7db6154d', $brand_name, $inserted);
                    update_field('field_600fd7ec6154e', $brand_group, $inserted);
                    update_field('field_600fd7f46154f', $brand_number_group, $inserted);

                    break;

                case 'Kiểu dáng':
                    update_field('field_600fd8b88a1c1', $kdang_pic, $inserted);
                    update_field('field_600fd8f38a1c2', $kdang_info, $inserted);
                    update_field('field_600fd9048a1c3', $kdang_phuongan, $inserted);
                    break;

                case 'Sáng chế':
                    update_field('field_600fd84dcbfa2', $sche_info, $inserted);
                    update_field('field_600fd874cbfa3', $sche_request_1, $inserted);
                    update_field('field_600fd895cbfa5', $sche_request_2, $inserted);
                    break;
            }

            # send email notification
            $email_admin = get_field('email_admin', 'option');
            $user_arr = get_user_by('ID', $data_member);
            $manager_arr = get_user_by('ID', $data_manager);
            $to = $user_arr->user_email;

            $email_title = __("Công việc mới:", 'qlcv') . " <b>" . $job_name . "</b>";
            $email_content = $user_arr->display_name . ' ' . __('hãy kiểm tra để thực hiện.', 'qlcv');
            $email_content .= "<br>" . __("Link tới công việc:", 'qlcv') . " " . get_the_permalink($inserted);
            $email_content = auto_url($email_content);

            $headers = [];
            $headers[] = 'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>';
            $headers[] = 'Cc: ' . $email_admin;
            $headers[] = 'Cc: ' . $manager_arr->user_email;
            # send email to supervisor
            if ($data_supervisor) {
                $supervisors = explode("|", $data_supervisor);
                if(!empty($supervisors)){
                    foreach ($supervisors as $supervisor) {
                        $supervisor_obj = get_user_by('ID', $supervisor);
                        $headers[] = 'Cc: ' . $supervisor_obj->user_email;
                    }
                }
            }

            $sent = wp_mail($to, $email_title, $email_content, $headers);

            # notification 
            create_notification($inserted, $email_title, $manager_arr->ID, $user_arr->ID);
            
            # Update MySQL tables for job data
            
            # 1. Update asljob table
            $aslTable = 'wp_asljob';
            $foreign_partner_id = !empty($data_foreign_partner) ? $data_foreign_partner : NULL;
            
            # Get information about agency for the job
            $brand = array();
            $agency_terms = get_the_terms($inserted, 'agency');
            if($agency_terms) {
                foreach ($agency_terms as $id_chi_nhanh) {
                    $term = get_term($id_chi_nhanh);
                    $brand[] = $term->slug;
                }
            }
            
            $agency_hn = in_array('ha-noi', $brand) ? 1 : 0;
            $agency_hcm = in_array('ho-chi-minh', $brand) ? 1 : 0;
            
            # Get contract sign date
            $contract_sign_date = NULL;
            if (get_field('contract_sign_date', $inserted)) {
                $tmp = DateTime::createFromFormat('d/m/Y', get_field('contract_sign_date', $inserted));
                $contract_sign_date = $tmp->format('Y-m-d H:i:s');
            }
            
            # Get tags as source
            $tags_obj = get_the_tags($inserted);
            $tagname_arr = array();
            if ($tags_obj) {
                foreach ($tags_obj as $key => $value) {
                    $tagname_arr[] = $value->name;
                }
            }
            
            # Determine job type and flag
            $job_type = $danh_muc;
            $job_type_group = '';
            $job_flag = $tiem_nang ? 'Tiềm năng' : 'Đã chốt';
            $potential = '';
            
            # Get groups
            $groups = get_the_terms($inserted, 'group');
            if($groups) {
                foreach ($groups as $group) {
                    if ($group->slug == 'tiem-nang') {
                        $potential = $group->name;
                    }
                    
                    # Determine if IP or Law type
                    $list_ip = ['ban-quyen', 'sang-che', 'kieu-dang', 'nhan-hieu'];
                    if (in_array($group->slug, $list_ip)) {
                        $job_type_group = "IP";
                    } elseif($job_type_group == '') {
                        $job_type_group = "Law";
                    }
                    
                    # Get potential type if applicable
                    $all_child = get_term_children(11, 'group'); // 11 is ID of "Tiềm năng" category
                    if (in_array($group->term_id, $all_child)) {
                        $potential = $group->name;
                    }
                }
            }
            
            # Insert into asljob table
            $wpdb->insert(
                $aslTable,
                array(
                    'jobid'             => $inserted,
                    'customerid'        => $data_customer,
                    'first_partnerid'   => $partner_1,
                    'partnerid'         => $data_partner,
                    'partner_out_id'    => $foreign_partner_id,
                    'memberid'          => $data_member,
                    'managerid'         => $data_manager,
                    'title'             => $job_name,
                    'type'              => $job_type,
                    'type_group'        => $job_type_group,
                    'flag'              => $job_flag,
                    'potential'         => $potential,
                    'our_ref'           => $our_ref,
                    'currency'          => $currency,
                    'total_value'       => $total_value,
                    'paid'              => $paid,
                    'remainning'        => $remaining,
                    'currency_out'      => get_field('currency_out', $inserted),
                    'total_cost'        => get_field('total_cost', $inserted),
                    'advance_money'     => get_field('advance_money', $inserted),
                    'debt'              => get_field('debt', $inserted),
                    'payment_status'    => get_field('payment_status', $inserted),
                    'source'            => implode(",", $tagname_arr),
                    'date'              => current_time('mysql', 1),
                    'contract_sign_date' => $contract_sign_date,
                    'agency_hn'         => $agency_hn,
                    'agency_hcm'        => $agency_hcm,
                    'level'             => $level
                )
            );
            
            # 3. Update aslsupervisor table
            $aslSupervisor = 'wp_aslsupervisor';
            
            # Add member (Người thực hiện)
            if ($data_member) {
                $wpdb->insert(
                    $aslSupervisor,
                    array(
                        'jobid'        => $inserted,
                        'supervisorid' => $data_member,
                        'name'         => 'Người thực hiện'
                    )
                );
            }
            
            # Add manager (Người quản lý)
            if ($data_manager) {
                $wpdb->insert(
                    $aslSupervisor,
                    array(
                        'jobid'        => $inserted,
                        'supervisorid' => $data_manager,
                        'name'         => 'Người quản lý'
                    )
                );
            }
            
            # Add supervisors if any
            if ($data_supervisor) {
                $supervisors = explode("|", $data_supervisor);
                if(!empty($supervisors)){
                    foreach ($supervisors as $supervisor) {
                        $wpdb->insert(
                            $aslSupervisor,
                            array(
                                'jobid'        => $inserted,
                                'supervisorid' => $supervisor,
                                'name'         => 'Người giám sát'
                            )
                        );
                    }
                }
            }
            
            # Add co-managers if any
            if ($data_co_manager) {
                $co_managers = explode("|", $data_co_manager);
                if(!empty($co_managers)){
                    foreach ($co_managers as $co_manager) {
                        $wpdb->insert(
                            $aslSupervisor,
                            array(
                                'jobid'        => $inserted,
                                'supervisorid' => $co_manager,
                                'name'         => 'Người đồng quản lý'
                            )
                        );
                    }
                }
            }
            
            # Add co-members if any
            if ($data_co_member) {
                $co_members = explode("|", $data_co_member);
                if(!empty($co_members)){
                    foreach ($co_members as $co_member) {
                        $wpdb->insert(
                            $aslSupervisor,
                            array(
                                'jobid'        => $inserted,
                                'supervisorid' => $co_member,
                                'name'         => 'Người đồng thực hiện'
                            )
                        );
                    }
                }
            }
            
            # 4. Update asljobgroup table
            $aslGroup = 'wp_asljobgroup';
            $wpdb->insert(
                $aslGroup,
                array(
                    'jobid'      => $inserted,
                    'customerid' => $data_customer,
                    'partnerid'  => $data_partner,
                    'memberid'   => $data_member,
                    'managerid'  => $data_manager,
                    'groupname'  => $danh_muc,
                    'flag'       => $job_flag,
                    'type'       => $job_type_group,
                    'date'       => current_time('mysql', 1)
                )
            );
            
            # 5. Update asljobcountry table
            $aslCountry = 'wp_asljobcountry';
            $countries = explode(',', $country);
            foreach ($countries as $single_country) {
                $wpdb->insert(
                    $aslCountry,
                    array(
                        'jobid'      => $inserted,
                        'customerid' => $data_customer,
                        'partnerid'  => $data_partner,
                        'memberid'   => $data_member,
                        'managerid'  => $data_manager,
                        'country'    => trim($single_country),
                        'date'       => current_time('mysql', 1)
                    )
                );
            }
            
            # 6. Update asljobtodocument table
            $aslJobDocument = 'wp_asljobtodocument';
            $partner = get_user_by('ID', $data_partner);
            $so_don = get_field('so_don', $inserted);
            $ngay_nop_don = get_field('ngay_nop_don', $inserted);
            
            $wpdb->insert(
                $aslJobDocument,
                array(
                    'jobid'                 => $inserted,
                    'job_title'             => $job_name,
                    'our_ref'               => $our_ref,
                    'trademark_txt'         => $brand_name,
                    'trademark_img'         => $kdang_pic,
                    'trademark_class'       => $brand_group,
                    'trademark_totalclass'  => $brand_number_group,
                    'trademark_fillingid'   => $so_don,
                    'trademark_fillingdate' => $ngay_nop_don,
                    'partner_name'          => $partner->display_name,
                    'partner_code'          => get_field('partner_code', 'user_' . $data_partner),
                    'partner_companyName'   => get_field('ten_cong_ty', 'user_' . $data_partner),
                    'partner_country'       => get_field('quoc_gia', 'user_' . $data_partner),
                    'partner_address'       => get_field('dia_chi', 'user_' . $data_partner),
                    'partner_city'          => get_field('city', 'user_' . $data_partner),
                    'partner_phone'         => get_field('so_dien_thoai', 'user_' . $data_partner),
                    'partner_email'         => $partner->user_email,
                    'partner_email_cc'      => get_field('email_cc', 'user_' . $data_partner),
                    'partner_email_bcc'     => get_field('email_bcc', 'user_' . $data_partner),
                    'partner_tax_number'    => get_field('mst', 'user_' . $data_partner),
                    'partner_legal_representative' => get_field('nguoi_dai_dien_phap_luat', 'user_' . $data_partner),
                    'partner_position'      => get_field('chuc_vu', 'user_' . $data_partner)
                )
            );
            
            $data['status'] = 'success';
            $data['notification'] = '<div class="alert alert-success" role="alert">
                                        <i class="fa fa-check"></i> ' . __('Đã tạo công việc mới thành công', 'qlcv') . '
                                      </div>';
    
            $data['redirect_link'] = get_permalink($inserted);
        }
    } else {
        $data['status'] = 'error';
        $data['notification'] = '<div class="alert alert-danger" role="alert">
                                    <i class="zmdi zmdi-info"></i> ' . __('Có lỗi xảy ra, xin vui lòng kiểm tra lại. Những trường đánh dấu * là bắt buộc.', 'qlcv') . '
                                  </div>';
    }

    $data['div_notification']   = '#create_new_job';
    // $data['test'] = $data_supervisor;

    echo json_encode($data);
    exit;
}

# using author role template
function author_role_template($templates = '')
{
    $templates = [];
    $author = get_queried_object();
    // print_r($author);
    $role = $author->roles[0];
    if (!is_array($templates) && !empty($templates)) {
        $templates = locate_template(array("author-$role.php", $templates), false);
    } elseif (empty($templates)) {
        $templates = locate_template("author-$role.php", false);
    } else {
        $new_template = locate_template(array("author-$role.php"));
        if (!empty($new_template)) {
            array_unshift($templates, $new_template);
        }
    }
    return $templates;
}
add_filter('author_template', 'author_role_template');

# logout with redirect to home
add_action('wp_logout', 'ps_redirect_after_logout');
function ps_redirect_after_logout()
{
    wp_redirect(get_bloginfo('url'));
    exit();
}

# create random password
function createRandomPassword()
{
    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
    srand((float)microtime() * 1000000);
    $i = 0;
    $pass = '';

    while ($i <= 7) {
        $num = rand() % 33;
        $tmp = substr($chars, $num, 1);
        $pass = $pass . $tmp;
        $i++;
    }

    return $pass;
}

# replace content by any template
function replace_content($arr_replace, $content)
{
    if (is_array($arr_replace)) {
        foreach ($arr_replace as $key => $value) {
            $content = str_replace($key, $value, $content);
        }

        return $content;
    }
}

function get_email_template($post_name, $taxonomy, $post_type)
{
    global $wpdb;
    global $post;

    $args = array(
        's'         => $post_name,
        'post_type' => $post_type,
        'tax_query' => array(
            array(
                'taxonomy' => 'group',
                'field'    => 'name',
                'terms'    => $taxonomy,
            ),
        ),
    );
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        while ($query->have_posts()) {

            return $query->posts[0];
        }
    } else {
        return null;
    }
}

# create notification & send email.
function create_notification($postid, $content, $manager_id, $receiver_id)
{
    $link = get_the_permalink($postid);
    $args = array(
        'post_title'    => $content,
        'post_status'   => 'publish',
        'post_type'     => 'notification',
    );
    $error = false;
    $inserted = wp_insert_post($args, $error);
    update_field('field_607e4dd677999', $link, $inserted);
    if ($manager_id) {
        update_field('field_6127bd871eed8', $manager_id, $inserted);
    }
    if ($receiver_id) {
        update_field('field_608529d6e89fb', $receiver_id, $inserted);
    }

    update_field('field_607e4dfe7799a', 1, $inserted);
    update_field('field_60857d1875727', 1, $inserted);
    update_field('field_6127bdb4be5a1', 1, $inserted);
}

function wpse27856_set_content_type()
{
    return "text/html";
}
add_filter('wp_mail_content_type', 'wpse27856_set_content_type');

# hide admin bar
add_action('after_setup_theme', 'remove_admin_bar');
function remove_admin_bar()
{
    if (!current_user_can('administrator') && !is_admin()) {
        show_admin_bar(false);
    }
}

# check all task to notificate with another task will be coming deadline
add_action('deadline_notification', 'sendmail_deadline_notification');
function sendmail_deadline_notification()
{
    #check all task not finish and check if the deadline is running half or a quarter to deadline
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
            # nếu có trường deadline thì mới xử lý tiếp, không thì kết thúc.
            if ($deadline) {
                $tmp = DateTime::createFromFormat('d/m/Y', $deadline);
                $end_time = strtotime($tmp->format('d-m-Y'));
    
                $half_time = ($end_time + $start_time) / 2;
                $quater_time = ($end_time + $half_time) / 2;
    
                $day_remaining = round(((($end_time - $current_time) / 24) / 60) / 60);
    
                $user_arr = get_field('user');
                $jobID = get_field('job');
                $our_ref = get_field('our_ref', $jobID);
                $manager_arr = get_field('manager', $jobID);
                $data_supervisor = get_field('supervisor', $jobID);
    
                $email_admin = get_field('email_admin', 'option');
                $to = $user_arr['user_email'];
                if ($jobID) {
                    $joblb = " cho " . get_the_title($jobID) . " (" . $our_ref . ")";
                } else {
                    $joblb = '';
                }
    
                if ($to) {
                    # reset cờ để kiểm tra, nếu có gửi email sẽ set bằng true
                    $sendFlag = false;
                    
                    if ((date('d/m/Y', $current_time) == date('d/m/Y', $half_time)) ||
                        (date('d/m/Y', $current_time) == date('d/m/Y', $quater_time))
                    ) {
                        # send mail notification
                        $lan_nhac = (date('d/m/Y', $current_time) == date('d/m/Y', $quater_time)) ? '2' : '1';
    
                        $email_title = __('Lưu ý công việc', 'qlcv') . ' ' . get_the_title() . $joblb . ' ' . __('chưa trả lời.', 'qlcv');
                        $email_content = 'Dear ' . $user_arr['display_name'] . '<br>';
                        $email_content .= __("Số REF:", 'qlcv') . " " . $our_ref . "; " . __("Người quản lý:", 'qlcv') . " " . $manager_arr['display_name'] . "<br>";
                        $email_content .= __("Lần nhắc thứ ", 'qlcv') . "" . $lan_nhac . " đối với đầu việc: " . get_the_title() . "<br>";
                        $email_content .= __('Thời hạn để xử lý công việc này là', 'qlcv') . ' ' . $deadline . '. ' . __('Như vậy, bạn còn', 'qlcv') . ' ' . $day_remaining . ' ' . __('ngày để trả lời.', 'qlcv');
                        $email_content .= "<br>" . __("Link tới công việc:", 'qlcv') . " " . get_the_permalink();
                        $email_content = auto_url($email_content);
                        $email_content .= "<br><br>" . __("Trân trọng, ", 'qlcv');
    
                        // $headers = [];
                        // $headers[] = 'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>';
                        // $headers[] = 'Cc: ' . $email_admin;
                        // $headers[] = 'Cc: ' . $manager_arr['user_email'];
    
                        // $sent = wp_mail($to, $email_title, $email_content, $headers);
                        $sendFlag = true;
                    } else if (date('d/m/Y', $current_time) == date('d/m/Y', $end_time)) {
                        # send mail notification
                        $email_title = __('Lưu ý công việc đến hạn ', 'qlcv');
                        $email_content = 'Dear ' . $user_arr['display_name'] . '<br>';
                        $email_content .= __("Số REF:", 'qlcv') . " " . $our_ref . "; " . __("Người quản lý:", 'qlcv') . " " . $manager_arr['display_name'] . "<br>";
                        $email_content .= "Lần nhắc thứ 3 đối với đầu việc: " . get_the_title() . "<br>";
                        $email_content .= __('Lưu ý công việc', 'qlcv') . ' ' . get_the_title() . $joblb . ' ' . __('đến hạn trả lời hôm nay và', 'qlcv') . ' ' . $user_arr['display_name'] . ' ' . __('chưa trả lời.', 'qlcv') . ' <br>';
                        $email_content .= $user_arr['display_name'] . ' ' . __('cần trả lời ngay.', 'qlcv');
                        $email_content .= "<br>" . __("Link tới công việc:", 'qlcv') . " " . get_the_permalink();
                        $email_content = auto_url($email_content);
                        $email_content .= "<br><br>" . __("Trân trọng, ", 'qlcv');
    
                        // $headers = [];
                        // $headers[] = 'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>';
                        // $headers[] = 'Cc: ' . $email_admin;
                        // $headers[] = 'Cc: ' . $manager_arr['user_email'];
    
                        // $sent = wp_mail($to, $email_title, $email_content, $headers);
                        $sendFlag = true;
                    } else if ($day_remaining == '-1') {
                        # miss deadline
                        $email_title = __('Lưu ý công việc', 'qlcv') . ' ' . get_the_title() . $joblb . ' đã quá hạn trả lời.';
                        $email_content = 'Dear ' . $user_arr['display_name'] . '<br>';
                        $email_content .= __("Số REF:", 'qlcv') . " " . $our_ref . "; " . __("Người quản lý:", 'qlcv') . " " . $manager_arr['display_name'] . "<br>";
                        $email_content .= __("Lần nhắc thứ 4 đối với đầu việc:", 'qlcv') . " " . get_the_title() . "<br>";
                        $email_content .= $user_arr['display_name'] . ' ' . __('cần gửi báo cáo cho người quản lý về lý do chưa trả lời này.', 'qlcv');
                        $email_content .= "<br>" . __("Link tới công việc:", 'qlcv') . " " . get_the_permalink();
                        $email_content = auto_url($email_content);
                        $email_content .= "<br><br>" . __("Trân trọng, ", 'qlcv');
    
                        $sendFlag = true;
                        # marked to this task is missed.
                        $miss_deadline = get_field('field_6010e0a43311a');
                        update_field('field_6010e0a43311a', $miss_deadline + 1);
                        update_field('field_600fde92f9be9', 'Quá hạn');
                    }
                    
                    # nếu cờ gửi email được bật, sẽ tiến hành gửi email cho admin, người chịu trách nhiệm, người quản lý, và người giám sát.
                    if ($sendFlag) {
                        $headers = [];
                        $headers[] = 'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>';
                        if ($email_admin) {
                            $headers[] = 'Cc: ' . $email_admin;
                        }
                        if ($manager_arr['user_email']) {
                            $headers[] = 'Cc: ' . $manager_arr['user_email'];
                        }
                        # send email to supervisor
                        if ( $data_supervisor ) {
                            $supervisors = explode("|", $data_supervisor);
                            if(!empty($supervisors)){
                                foreach ($supervisors as $supervisor) {
                                    $supervisor_obj = get_user_by('ID', $supervisor);
                                    $headers[] = 'Cc: ' . $supervisor_obj->user_email;
                                }
                            }
                        }
                        $sent = wp_mail($to, $email_title, $email_content, $headers);
        
                        # push notification & save history
                        if ($sent) {
                            create_notification(get_the_ID(), $email_title, $manager_arr['ID'], $user_arr['ID']);
                            $sent = 0;
                        }
                    }
                }
            }
        }
    }

    # check all notification, if they're over 7 days and have been seen, then they will be deleted
    $args   = array(
        'post_type'     => 'notification',
        'posts_per_page' => '-1',
    );

    $query = new WP_Query($args);
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $start_time = strtotime(get_the_date('d-m-Y'));
            $last_7_days = strtotime("-1 week");
            if ($start_time < $last_7_days) {
                wp_delete_post(get_the_ID(), true);
            }
        }
    }
}

// Add custom Theme Functions here
if (function_exists('acf_add_options_page')) {
    acf_add_options_page(array(
        'page_title'    => 'Theme options', // Title hiển thị khi truy cập vào Options page
        'menu_title'    => __('Tùy biến chung', 'qlcv'), // Tên menu hiển thị ở khu vực admin
        'menu_slug'     => 'theme-settings', // Url hiển thị trên đường dẫn của options page
        'capability'    => 'edit_posts',
        'redirect'      => false
    ));

    acf_add_options_page(array(
        'page_title'    => __('Cấu hình khác phần mềm QLCV', 'qlcv'), // Title hiển thị khi truy cập vào Options page
        'menu_title'    => __('Cấu hình khác', 'qlcv'), // Tên menu hiển thị ở khu vực admin
        'menu_slug'     => 'other-acf-settings', // Url hiển thị trên đường dẫn của options page
        'capability'    => 'edit_posts',
        'parent_slug'   => 'theme-settings',
        'redirect'      => false
    ));
}


function auto_url( $text = null ) {
    $regex  = '/((http|ftp|https):\/\/)([^\s]+)/';
    return preg_replace_callback( $regex, function( $m ) {
      $link = $name = $m[0];
      if ( empty( $m[1] ) ) {
        $link = "http://".$link;
      }
      return '<a href="'.$link.'" target="_blank" rel="nofollow">'.$name.'</a>';
    }, $text );
}


add_filter('user_search_columns', function ($search_columns) {
    $search_columns[] = 'display_name';
    return $search_columns;
});

# search partner where partner-code is matching, return true if exists
function search_partner($partner_code)
{
    $args   = array(
        'role__in'  => array('partner', 'foreign_partner'),
        'number'    => -1,
        'meta_query' => array(
            array(
                'key'     => 'partner_code',
                'value'   => $partner_code,
                'compare' => '='
            ),
        )
    );
    $query = new WP_User_Query($args);
    $users = $query->get_results();

    if (!empty($users)) {
        return $users[0]->ID;
    } else return false;
}

function get_token()
{
    $api_url  = get_field('api_url', 'option');
    $username = get_field('username', 'option');
    $password = get_field('password', 'option');

    $user = array(
        'username' => $username,
        'password' => $password,
    );

    echo "Connect to " . $api_url . " ...<br>";
    // echo "Username: " . $username . "<br>";
    // echo "Password: " . $password . "<br>";

    # authenticate to get token
    $jwt = wp_remote_post(
        $api_url . '/wp-json/jwt-auth/v1/token',
        array(
            'method'        => 'POST',
            'timeout'       => '45',
            'headers'       => array('Content-Type' => 'application/json; charset=utf-8'),
            'body'          => json_encode($user),
        )
    );

    $token = json_decode(wp_remote_retrieve_body($jwt));
    // print_r($jwt);
    if (!$token->token) {
        return false;
    } else {
        return $token->token;
    }
};

function send_customer_api($token, $customer, $custom_fields, $api_id, $uid)
{
    $api_url  = get_field('api_url', 'option');

    if ($token) {
        # create new post if not exist
        if (!$api_id) {
            $api_create_customer = $api_url . '/wp-json/wp/v2/post_customer';
        } else {
            $api_create_customer = $api_url . '/wp-json/wp/v2/post_customer/' . $api_id;
        }

        # update customer post or create new
        $args = array(
            'method'    => 'POST',
            'timeout'   => '45',
            'headers'   => array(
                'Content-Type'  => 'application/json; charset=utf-8',
                'Authorization' => 'Bearer ' . $token,
            ),
            'body'  => json_encode($customer),
        );

        $response = wp_remote_post(
            $api_create_customer,
            $args
        );

        $response_body = json_decode(wp_remote_retrieve_body($response));
        if (!$response_body->id) {
            echo __("Không tạo mới/cập nhật được bài viết.<br>", 'qlcv');
            print_r($response);
        } else {

            if (!$api_id) {
                $api_id = $response_body->id;

                # update api id 
                update_field('field_614319e51e117', $api_id, 'user_' . $uid);
                $result = __("<p>Đã tạo khách hàng mới trên hệ thống gia hạn.</p>", 'qlcv');
                $new_partner = true;
            }

            # update acf fields to customer throught API
            if ($api_id) {
                // print_r($custom_fields);
                $url_update_field = $api_url . '/wp-json/acf/v3/post_customer/' . $api_id;
                // print_r($url_update_field);
                $arg_custom_fields = array(
                    'method'    => 'POST',
                    'timeout'       => '45',
                    'headers'   => array(
                        'Content-Type'  => 'application/json; charset=utf-8',
                        'Authorization' => 'Bearer ' . $token,
                    ),
                    'body'  => json_encode($custom_fields),
                );

                # call API to add custom fields
                $custom_fields_api = wp_remote_post(
                    $url_update_field,
                    $arg_custom_fields
                );
                // print_r($custom_fields_api);
                if (!$new_partner) {
                    $result = __("<p>Đã cập nhật thành công khách hàng này trên hệ thống gia hạn.</p>", 'qlcv');
                }
            }
        }
        echo $result;
        return $api_id;
    } else {
        return false;
    }
}

# add to additional field
function additional_field($target_arr, $name, $value)
{
    if ($value) {
        $target_arr['fields']['additional_field'][] = array(
            'data_name' => $name,
            'data_value' => $value
        );
    }

    return $target_arr;
}

# reading excel
function wp_reading_excel($tmp_name)
{
    try {
        $inputFileType = PHPExcel_IOFactory::identify($tmp_name);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($tmp_name);
    } catch (Exception $e) {
        die('Lỗi không thể đọc file "' . pathinfo($tmp_name, PATHINFO_BASENAME) . '": ' . $e->getMessage());
    }

    // Lấy sheet hiện tại
    $sheet = $objPHPExcel->getSheet(0);

    // Lấy tổng số dòng của file
    $highestRow = $sheet->getHighestRow();
    // Lấy tổng số cột của file
    $highestColumn = $sheet->getHighestColumn();

    //  Thực hiện việc lặp qua từng dòng của file, để lấy thông tin
    for ($row = 1; $row <= $highestRow; $row++) {
        // Lấy dữ liệu từng dòng và đưa vào mảng $rowData
        $data = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
        if ($data[0][0]) {
            $rowData[] = $data[0];
        }
    }

    return $rowData;
}

function update_job_history( $mota, $ngaythang, $postid ) {
    global $wpdb;

    $logs = get_field('lich_su_cong_viec', $postid);
    $finish_update = array(
        'mo_ta'         => $mota,
        'ngay_thang'    => $ngaythang,
    );

    if ($logs) {
        array_push($logs, $finish_update);
        update_field('field_606ed4e802a6a', $logs, $postid);
    } else {
        add_row('field_606ed4e802a6a', $finish_update, $postid);
    }
    
    // 2. Update MySQL asljobhistory table
    $aslHistory = 'wp_asljobhistory';
    
    // Insert new history record
    $clean_mota = strip_tags(str_replace(array('<br>', '<br/>', '<br />'), ' ', $mota));
    $wpdb->insert(
        $aslHistory,
        array(
            'jobid' => $postid,
            'name'  => $clean_mota,
            'date'  => current_time('mysql', 1)
        )
    );
}

function check_finish_job($jobid) {
    # get type of job
    # if not "Nhan hieu", "Kieu dang", "Sang che", check status
    # else check history
    $phan_loai = get_field('phan_loai', $jobid);
    
    $types = array('Nhãn hiệu', "Kiểu dáng", "Sáng chế");
    if (!in_array($phan_loai, $types)) {
        $status = get_field('trang_thai', $jobid);
        if ($status == "Hoàn thành") {
            return true;
        } else return false;
    } else {
        $term       = get_term_by('name', $phan_loai, 'group');
        $work_list  = get_field('work_process', 'term_' . $term->term_id);
        $work_arr   = explode(PHP_EOL, $work_list);
        $work_arr   = array_map('trim', $work_arr);

        # get history of work that is current process
        $work_list  = get_field('lich_su_cong_viec', $jobid);
        $work_history = array();
        foreach ($work_list as $key => $value) {
            $work_history[] = $value['mo_ta'];
            $work_date[] = $value['ngay_thang'];
        }

        $work_not_done = array_diff($work_arr, $work_history);
        if (empty($work_not_done)) {
            return true;
        } return false;
    }
}

# Custom pagination ajax
function show_pagination($current_page, $total_page){
    # validate dữ liệu
    if (($current_page > 0) && ($current_page <= $total_page)) {
        $pagination = '<ul class="page-numbers">';
        $temp = '<li><span aria-current="page" class="page-numbers current">' . $current_page . '</span></li>';
    
        # tính toán hiện số trang trước trang hiện tại
        for ($i=1; $i <= 4; $i++) { 
            # tính toán số trang trước trang current
            $previous_page = $current_page - $i;
            if (($i <= 2) && ($previous_page > 0)) {
                $temp = '<li><a href="#" class="page-numbers" data-page="' . $previous_page . '">' . $previous_page . '</a></li>' . $temp;
            } else if (($i == 3) && ($previous_page > 1)) {
                $temp = '<li><span class="page-numbers dots">…</span></li>' . $temp;
            } else if (($i == 4) && ($previous_page >= 0)) {
                $temp = '<li><a href="#" class="page-numbers" data-page="1">1</a></li>' . $temp;
            }
        }

        # hiển thị nút trang trước
        if ($current_page != 1) {
            $previous_page = $current_page - 1;
            $prev_text = __('« Trang trước', 'qlcv');
            $temp = '<li><a class="prev page-numbers" href="#" data-page="' . $previous_page . '">' . $prev_text . '</a></li>' . $temp;
        }

        # tính toán hiện số trang sau trang hiện tại
        for ($i=1; $i <= 4; $i++) { 
            # tính toán số trang sau trang current
            $next_page = $current_page + $i;
            if (($i <= 2) && ($next_page <= $total_page)) {
                $temp .= '<li><a href="#" class="page-numbers" data-page="' . $next_page . '">' . $next_page . '</a></li>';
            } else if (($i == 3) && ($next_page <= $total_page)) {
                $temp .= '<li><span class="page-numbers dots">…</span></li>';
            } else if (($i == 4) && ($next_page <= $total_page + 1)) {
                $temp .= '<li><a href="#" class="page-numbers" data-page="' . $total_page . '">' . $total_page . '</a></li>';
            }
        }
    
        # hiển thị nút trang sau
        if ($current_page != $total_page) {
            $next_page = $current_page + 1;
            $next_text = __('Trang sau »', 'qlcv');
            $temp .= '<li><a href="#" class="next page-numbers" data-page="' . $next_page . '">' . $next_text . '</a></li>';
        }
            
        $pagination .= $temp . '</ul>';
        return $pagination;
    }
}

// Log finance history to wp_aslfinancehistory table
function log_finance_history($finance_post_id, $jobid, $userid, $finance_type, $finance_value, $finance_currency, $finance_date, $finance_title, $finance_content, $action_type = 'create') {
    global $wpdb;
    
    $current_user = wp_get_current_user();
    $created_by = $current_user->ID;
    
    $table_name = 'wp_aslfinancehistory';
    
    $result = $wpdb->insert(
        $table_name,
        array(
            'finance_post_id'   => $finance_post_id,
            'jobid'             => $jobid,
            'userid'            => $userid,
            'finance_type'      => $finance_type,
            'finance_value'     => $finance_value,
            'finance_currency'  => $finance_currency,
            'finance_date'      => $finance_date,
            'finance_title'     => $finance_title,
            'finance_content'   => $finance_content,
            'action_type'       => $action_type,
            'created_by'        => $created_by
        ),
        array(
            '%d', // finance_post_id
            '%d', // jobid
            '%d', // userid
            '%s', // finance_type
            '%f', // finance_value
            '%s', // finance_currency
            '%s', // finance_date
            '%s', // finance_title
            '%s', // finance_content
            '%s', // action_type
            '%d'  // created_by
        )
    );
    
    return $result !== false;
}

// Delete finance record and its history
function delete_finance_record($finance_post_id) {
    global $wpdb;
    
    // Check if the finance post exists
    $finance_post = get_post($finance_post_id);
    if (!$finance_post || $finance_post->post_type !== 'finance') {
        return false;
    }
    
    // Get finance data before deletion for reverting wallet/job calculations
    $finance_type = get_field('finance_type', $finance_post_id);
    $finance_value = floatval(get_field('finance_value', $finance_post_id));
    $finance_currency = get_field('finance_currency', $finance_post_id);
    $finance_job = get_field('finance_job', $finance_post_id);
    
    // Revert wallet balance
    if ($finance_currency == "USD") {
        $total_wallet = floatval(get_field('total_usd', 'option'));
        $wallet_field = 'field_60bb2f7cf9156';
    } else {
        $total_wallet = floatval(get_field('total_vnd', 'option'));
        $wallet_field = 'field_60bb2f98f9157';
    }
    
    // Reverse the wallet operation
    if ($finance_type == "Thu") {
        $new_wallet_total = $total_wallet - $finance_value;
        
        // Reverse job calculations
        $job_paid = floatval(get_field('paid', $finance_job)) - $finance_value;
        $job_remainning = floatval(get_field('remainning', $finance_job)) + $finance_value;
        
        update_field('field_60a231d395f2e', $job_paid, $finance_job);
        update_field('field_60a231d3961b0', $job_remainning, $finance_job);
        
        // Cập nhật lại commission_amount khi paid giảm
        if (function_exists('update_commission_amounts_by_paid')) {
            update_commission_amounts_by_paid($finance_job, $job_paid);
        }
    } else if ($finance_type == "Chi") {
        $new_wallet_total = $total_wallet + $finance_value;
        
        // Reverse job calculations
        $job_advance = floatval(get_field('advance_money', $finance_job)) - $finance_value;
        $job_debt = floatval(get_field('debt', $finance_job)) + $finance_value;
        
        update_field('field_60afaeb8cfd6a', $job_advance, $finance_job);
        update_field('field_60afaf50cfd6b', $job_debt, $finance_job);
    }
    
    // Update wallet
    update_field($wallet_field, $new_wallet_total, 'option');
    
    // Delete from finance history table
    $table_name = 'wp_aslfinancehistory';
    $wpdb->delete(
        $table_name,
        array('finance_post_id' => $finance_post_id),
        array('%d')
    );
    
    // Delete the WordPress post
    $deleted = wp_delete_post($finance_post_id, true);
    
    return $deleted !== false;
}

// AJAX handler for deleting finance record
add_action('wp_ajax_delete_finance_record', 'ajax_delete_finance_record');
function ajax_delete_finance_record() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'delete_finance_nonce')) {
        wp_die('Security check failed');
    }
    
    $finance_post_id = intval($_POST['finance_id']);
    $job_id = intval($_POST['job_id']);
    
    // Check permissions
    $current_user = wp_get_current_user();
    $job_author = get_post_field('post_author', $job_id);
    $is_job_creator = ($current_user->ID == $job_author);
    $is_admin = in_array('administrator', $current_user->roles);
    
    if (!$is_job_creator && !$is_admin) {
        wp_die('You do not have permission to delete this record');
    }
    
    $result = delete_finance_record($finance_post_id);
    
    if ($result) {
        wp_send_json_success(array('message' => __('Đã xóa phiếu thu chi thành công', 'qlcv')));
    } else {
        wp_send_json_error(array('message' => __('Không thể xóa phiếu thu chi', 'qlcv')));
    }
}

// Get finance history for a specific job
function get_finance_history($jobid, $limit = 50) {
    global $wpdb;
    
    $table_name = 'wp_aslfinancehistory';
    
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT fh.*, u.display_name as user_name, u2.display_name as created_by_name, 
                uc.ten_cong_ty as user_company
         FROM {$table_name} fh
         LEFT JOIN {$wpdb->users} u ON fh.userid = u.ID
         LEFT JOIN {$wpdb->users} u2 ON fh.created_by = u2.ID
         LEFT JOIN {$wpdb->usermeta} uc ON fh.userid = uc.user_id AND uc.meta_key = 'ten_cong_ty'
         WHERE fh.jobid = %d
         ORDER BY fh.created_date DESC
         LIMIT %d",
        $jobid,
        $limit
    ));
    
    return $results;
}

// Get finance history for all jobs (with pagination)
function get_all_finance_history($page = 1, $per_page = 20, $filters = array()) {
    global $wpdb;
    
    $table_name = 'wp_aslfinancehistory';
    $offset = ($page - 1) * $per_page;
    
    $where_conditions = array('1=1');
    $where_values = array();
    
    // Add filters
    if (!empty($filters['jobid'])) {
        $where_conditions[] = 'fh.jobid = %d';
        $where_values[] = $filters['jobid'];
    }
    
    if (!empty($filters['finance_type'])) {
        $where_conditions[] = 'fh.finance_type = %s';
        $where_values[] = $filters['finance_type'];
    }
    
    if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
        $where_conditions[] = 'fh.finance_date BETWEEN %s AND %s';
        $where_values[] = $filters['date_from'];
        $where_values[] = $filters['date_to'];
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Get total count
    $count_query = "SELECT COUNT(*) FROM {$table_name} fh WHERE {$where_clause}";
    if (!empty($where_values)) {
        $total_items = $wpdb->get_var($wpdb->prepare($count_query, $where_values));
    } else {
        $total_items = $wpdb->get_var($count_query);
    }
    
    // Get results
    $query = "SELECT fh.*, u.display_name as user_name, u2.display_name as created_by_name,
                     uc.ten_cong_ty as user_company, p.post_title as job_title
              FROM {$table_name} fh
              LEFT JOIN {$wpdb->users} u ON fh.userid = u.ID
              LEFT JOIN {$wpdb->users} u2 ON fh.created_by = u2.ID
              LEFT JOIN {$wpdb->usermeta} uc ON fh.userid = uc.user_id AND uc.meta_key = 'ten_cong_ty'
              LEFT JOIN {$wpdb->posts} p ON fh.jobid = p.ID
              WHERE {$where_clause}
              ORDER BY fh.created_date DESC
              LIMIT %d OFFSET %d";
    
    $all_values = array_merge($where_values, array($per_page, $offset));
    
    if (!empty($all_values)) {
        $results = $wpdb->get_results($wpdb->prepare($query, $all_values));
    } else {
        $query_no_prepare = str_replace(array('%d', '%s'), '', $query);
        $results = $wpdb->get_results($query_no_prepare);
    }
    
    return array(
        'results' => $results,
        'total_items' => $total_items,
        'total_pages' => ceil($total_items / $per_page),
        'current_page' => $page
    );
}


function CreateDatabaseQlcv()
{
    global $wpdb;
    $charsetCollate = $wpdb->get_charset_collate();
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    # table 1
    $aslTable = 'wp_aslcustomer';
    $createAslTable = "CREATE TABLE `{$aslTable}` (
        `customerid` bigint(20) UNSIGNED NOT NULL,
        `name` varchar(255) NOT NULL,
        `companyName` varchar(255) NOT NULL,
        `country` varchar(255) NOT NULL,
        `phone` varchar(20) NULL,
        `email` varchar(255) NOT NULL,
        `date` timestamp NOT NULL,
        PRIMARY KEY (`customerid`)
    ) {$charsetCollate};";
    dbDelta($createAslTable);

    # table 2
    $aslTable = 'wp_aslpartner';
    $createAslTable = "CREATE TABLE `{$aslTable}` (
        `partnerid` bigint(20) UNSIGNED NOT NULL,
        `name` varchar(255) NOT NULL,
        `partner_code` varchar(20) NULL,
        `companyName` varchar(255) NULL,
        `mst` varchar(255) NULL,
        `nguoi_dai_dien_phap_luat` varchar(255) NULL,
        `chuc_vu` varchar(255) NULL,
        `country` varchar(255) NULL,
        `address` varchar(255) NULL,
        `city` varchar(255) NULL,
        `is_company` tinyint(4) NULL,
        `staffs` varchar(255) NULL,
        `vn_company` tinyint(4) NULL,
        `languages` varchar(255) NULL,
        `email_cc` varchar(255) NULL,
        `email_bcc` varchar(255) NULL,
        `type_of_client` varchar(255) NULL,
        `vip` varchar(255) NULL,
        `status` varchar(255) NULL,
        `fdi` varchar(255) NULL,
        `fdi_from` varchar(255) NULL,
        `client_type` varchar(255) NULL,
        `source` varchar(255) NULL,
        `phone` varchar(20) NULL,
        `email` varchar(255) NULL,
        `role_partner__in` tinyint(4) NULL,
        `role_partner__out` tinyint(4) NULL,
        `date` timestamp NULL,
        PRIMARY KEY (`partnerid`)
    ) {$charsetCollate};";
    dbDelta($createAslTable);

    # table 3
    $aslTable = 'wp_aslmember';
    $createAslTable = "CREATE TABLE `{$aslTable}` (
        `memberid` bigint(20) UNSIGNED NOT NULL,
        `name` varchar(255) NOT NULL,
        `address` varchar(255) NOT NULL,
        `phone` varchar(20) NULL,
        `email` varchar(255) NOT NULL,
        `date` timestamp NOT NULL,
        `agency_hn` tinyint(4) NOT NULL,
        `agency_hcm` tinyint(4) NOT NULL,
        `group_trademark` tinyint(4) NOT NULL,
        `group_patent` tinyint(4) NOT NULL,
        `group_design` tinyint(4) NOT NULL,
        `group_franchise` tinyint(4) NOT NULL,
        `group_copyright` tinyint(4) NOT NULL,
        `group_others` tinyint(4) NOT NULL,
        `group_potential` tinyint(4) NOT NULL,
        `role_admin` tinyint(4) NOT NULL,
        `role_manager` tinyint(4) NOT NULL,
        `role_member` tinyint(4) NOT NULL,
        `role_law_manager` tinyint(4) NOT NULL,
        `role_ip_manager` tinyint(4) NOT NULL,
        PRIMARY KEY (`memberid`)
    ) {$charsetCollate};";
    dbDelta($createAslTable);

    # table 4
    $aslTable = 'wp_asljob';
    $createAslTable = "CREATE TABLE `{$aslTable}` (
        `jobid` bigint(20) UNSIGNED NOT NULL,
        `customerid` bigint(20) UNSIGNED NULL,
        `first_partnerid` bigint(20) UNSIGNED NULL,
        `partnerid` bigint(20) UNSIGNED NULL,
        `partner_out_id` bigint(20) UNSIGNED NULL,
        `memberid` bigint(20) UNSIGNED NULL,
        `managerid` bigint(20) UNSIGNED NULL,
        `title` varchar(255) NOT NULL,
        `type` varchar(255) NOT NULL,
        `type_group` varchar(255) NOT NULL,
        `flag` varchar(255) NOT NULL,
        `potential` varchar(255) NULL,
        `our_ref` varchar(50) NULL,
        `currency` varchar(5) NULL,
        `total_value` bigint(20) UNSIGNED NULL,
        `paid` bigint(20) UNSIGNED NULL,
        `remainning` bigint(20) UNSIGNED NULL,
        `currency_out` varchar(5) NULL,
        `total_cost` bigint(20) UNSIGNED NULL,
        `advance_money` bigint(20) UNSIGNED NULL,
        `debt` bigint(20) UNSIGNED NULL,
        `payment_status` varchar(255) NULL,
        `source` varchar(255) NULL,
        `date` timestamp NOT NULL,
        `contract_sign_date` timestamp NULL,
        `agency_hn` tinyint(4) NOT NULL,
        `agency_hcm` tinyint(4) NOT NULL,
        `level` varchar(50) NULL,
        PRIMARY KEY (`jobid`)
    ) {$charsetCollate};";
    dbDelta($createAslTable);

    # table 5
    $aslTable = 'wp_asltask';
    $createAslTable = "CREATE TABLE `{$aslTable}` (
        `taskid` bigint(20) UNSIGNED NOT NULL,
        `jobid` bigint(20) UNSIGNED NOT NULL,
        `memberid` bigint(20) UNSIGNED NOT NULL,
        `managerid` bigint(20) UNSIGNED NOT NULL,
        `customerid` bigint(20) UNSIGNED NULL,
        `partnerid` bigint(20) UNSIGNED NULL,
        `title` varchar(255) NOT NULL,
        `status` varchar(255) NULL,
        `deadline` timestamp NULL,
        `time_to_response` timestamp NULL,
        `miss_deadline` tinyint(4) NULL,
        `date` timestamp NOT NULL,
        PRIMARY KEY (`taskid`)
    ) {$charsetCollate};";
    dbDelta($createAslTable);

    # table 6
    $aslTable = 'wp_asljobhistory';
    $createAslTable = "CREATE TABLE `{$aslTable}` (
        `jobid` bigint(20) UNSIGNED NOT NULL,
        `name` varchar(255) NOT NULL,
        `date` timestamp NULL
    ) {$charsetCollate};";
    dbDelta($createAslTable);

    # table 7
    $aslTable = 'wp_asltaskhistory';
    $createAslTable = "CREATE TABLE `{$aslTable}` (
        `taskid` bigint(20) UNSIGNED NOT NULL,
        `userid` bigint(20) UNSIGNED NOT NULL,
        `content` varchar(255) NOT NULL,
        `date` timestamp NOT NULL
    ) {$charsetCollate};";
    dbDelta($createAslTable);

    # table 8
    $aslTable = 'wp_aslsupervisor';
    $createAslTable = "CREATE TABLE `{$aslTable}` (
        `jobid` bigint(20) UNSIGNED NOT NULL,
        `supervisorid` bigint(20) UNSIGNED NOT NULL,
        `name` varchar(255) NULL
    ) {$charsetCollate};";
    dbDelta($createAslTable);

    # table 9
    $aslTable = 'wp_asljobcountry';
    $createAslTable = "CREATE TABLE `{$aslTable}` (
        `jobid` bigint(20) UNSIGNED NOT NULL,
        `customerid` bigint(20) UNSIGNED NULL,
        `partnerid` bigint(20) UNSIGNED NULL,
        `memberid` bigint(20) UNSIGNED NULL,
        `managerid` bigint(20) UNSIGNED NULL,
        `country` varchar(255) NULL,
        `date` timestamp NOT NULL,
        PRIMARY KEY (`jobid`)
    ) {$charsetCollate};";
    dbDelta($createAslTable);

    # table 10
    $aslTable = 'wp_asljobgroup';
    $createAslTable = "CREATE TABLE `{$aslTable}` (
        `jobid` bigint(20) UNSIGNED NOT NULL,
        `customerid` bigint(20) UNSIGNED NULL,
        `partnerid` bigint(20) UNSIGNED NULL,
        `memberid` bigint(20) UNSIGNED NULL,
        `managerid` bigint(20) UNSIGNED NULL,
        `groupname` varchar(255) NOT NULL,
        `flag` varchar(255) NOT NULL,
        `type` varchar(255) NOT NULL,
        `date` timestamp NOT NULL,
        PRIMARY KEY (`jobid`)
    ) {$charsetCollate};";
    dbDelta($createAslTable);

    # table 11
    $aslTable = 'wp_asllogs';
    $createAslTable = "CREATE TABLE `{$aslTable}` (
        `logid` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `userid` bigint(20) UNSIGNED NOT NULL,
        `postid` bigint(20) UNSIGNED NULL,
        `edituser` bigint(20) UNSIGNED NULL,
        `content` varchar(255) NOT NULL,
        `date` timestamp NOT NULL,
        PRIMARY KEY (`logid`)
    ) {$charsetCollate};";
    dbDelta($createAslTable);

    # table 12
    $aslTable = 'wp_asljobtodocument';
    $createAslTable = "CREATE TABLE `{$aslTable}` (
        `jobid` bigint(20) UNSIGNED NOT NULL,
        `job_title` varchar(255) NOT NULL,
        `our_ref` varchar(50) NULL,
        `trademark_txt` varchar(255) NULL,
        `trademark_img` varchar(255) NULL,
        `trademark_class` varchar(255) NULL,
        `trademark_totalclass` varchar(255) NULL,
        `trademark_fillingid` varchar(255) NULL,
        `trademark_fillingdate` varchar(255) NULL,
        `partner_name` varchar(255) NULL,
        `partner_code` varchar(20) NULL,
        `partner_companyName` varchar(255) NULL,
        `partner_country` varchar(255) NULL,
        `partner_address` varchar(255) NULL,
        `partner_city` varchar(255) NULL,
        `partner_phone` varchar(20) NULL,
        `partner_email` varchar(255) NULL,
        `partner_email_cc` varchar(255) NULL,
        `partner_email_bcc` varchar(255) NULL,
        `partner_tax_number` varchar(255) NULL,
        `partner_legal_representative` varchar(255) NULL,
        `partner_position` varchar(255) NULL,
        PRIMARY KEY (`jobid`)
    ) {$charsetCollate};";
    dbDelta($createAslTable);

    # table 13 - Commission Management
    $aslTable = 'wp_aslcommission';
    $createAslTable = "CREATE TABLE `{$aslTable}` (
        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `jobid` bigint(20) UNSIGNED NOT NULL,
        `userid` bigint(20) UNSIGNED NOT NULL,
        `role_type` varchar(50) NOT NULL,
        `commission_percent` decimal(5,2) NOT NULL,
        `commission_amount` bigint(20) UNSIGNED NULL,
        `currency` varchar(5) NULL,
        `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `jobid` (`jobid`),
        KEY `userid` (`userid`),
        UNIQUE KEY `unique_job_user_role` (`jobid`, `userid`, `role_type`)
    ) {$charsetCollate};";
    dbDelta($createAslTable);

    # table 14 - Finance History
    $aslTable = 'wp_aslfinancehistory';
    $createAslTable = "CREATE TABLE `{$aslTable}` (
        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `finance_post_id` bigint(20) UNSIGNED NOT NULL,
        `jobid` bigint(20) UNSIGNED NOT NULL,
        `userid` bigint(20) UNSIGNED NOT NULL,
        `finance_type` varchar(10) NOT NULL,
        `finance_value` decimal(15,2) NOT NULL,
        `finance_currency` varchar(5) NOT NULL,
        `finance_date` varchar(8) NOT NULL,
        `finance_title` text NOT NULL,
        `finance_content` longtext NULL,
        `action_type` varchar(10) DEFAULT 'create',
        `created_by` bigint(20) UNSIGNED NOT NULL,
        `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `finance_post_id` (`finance_post_id`),
        KEY `jobid` (`jobid`),
        KEY `userid` (`userid`),
        KEY `finance_type` (`finance_type`),
        KEY `finance_date` (`finance_date`)
    ) {$charsetCollate};";
    dbDelta($createAslTable);

}
add_action('after_switch_theme', 'CreateDatabaseQlcv');


// Export single member to wp_aslmember table
function export_single_member_to_table($user_id) {
    global $wpdb;
    
    $user = get_user_by('ID', $user_id);
    if (!$user) {
        return false;
    }
    
    // Check if user has member-related roles
    $member_roles = ['contributor', 'administrator', 'member', 'law_manager', 'ip_manager'];
    $has_member_role = false;
    foreach ($member_roles as $role) {
        if (in_array($role, $user->roles)) {
            $has_member_role = true;
            break;
        }
    }
    
    if (!$has_member_role) {
        return false;
    }
    
    $aslTable = 'wp_aslmember';
    
    // Get user custom fields
    $so_dien_thoai  = get_field('so_dien_thoai', 'user_' . $user->ID);
    $dia_chi        = get_field('dia_chi', 'user_' . $user->ID);
    $chi_nhanh      = get_field('chi_nhanh', 'user_' . $user->ID);
    $nhom_cong_viec = get_field('nhom_cong_viec', 'user_' . $user->ID);
    
    $work_group = array();
    $brand = array();

    // Process work groups
    if ($nhom_cong_viec && is_array($nhom_cong_viec)) {
        foreach ($nhom_cong_viec as $id_cong_viec) {
            $term = get_term($id_cong_viec);
            if ($term && !is_wp_error($term)) {
                $work_group[] = $term->slug;
            }
        }
    }

    // Process agencies
    if ($chi_nhanh && is_array($chi_nhanh)) {
        foreach ($chi_nhanh as $id_chi_nhanh) {
            $term = get_term($id_chi_nhanh);
            if ($term && !is_wp_error($term)) {
                $brand[] = $term->slug;
            }
        }
    }

    // Set work group flags
    $group_trademark = in_array('nhan-hieu', $work_group) ? 1 : 0;
    $group_patent = in_array('sang-che', $work_group) ? 1 : 0;
    $group_design = in_array('kieu-dang', $work_group) ? 1 : 0;
    $group_franchise = in_array('franchise', $work_group) ? 1 : 0;
    $group_copyright = in_array('ban-quyen', $work_group) ? 1 : 0;
    $group_others = in_array('viec-khac', $work_group) ? 1 : 0;
    $group_potential = in_array('tiem-nang', $work_group) ? 1 : 0;

    // Set agency flags
    $agency_hn = in_array('ha-noi', $brand) ? 1 : 0;
    $agency_hcm = in_array('ho-chi-minh', $brand) ? 1 : 0;

    // Set role flags
    $role_admin = in_array('administrator', $user->roles) ? 1 : 0;
    $role_manager = in_array('contributor', $user->roles) ? 1 : 0;
    $role_member = in_array('member', $user->roles) ? 1 : 0;
    $role_law_manager = in_array('law_manager', $user->roles) ? 1 : 0;
    $role_ip_manager = in_array('ip_manager', $user->roles) ? 1 : 0;

    // Prepare data array
    $member_data = array(
        'memberid'          => $user->ID,
        'name'              => $user->display_name,
        'address'           => $dia_chi ?: '',
        'phone'             => $so_dien_thoai ?: '',
        'email'             => $user->user_email,
        'date'              => $user->user_registered,
        'agency_hn'         => $agency_hn,
        'agency_hcm'        => $agency_hcm,
        'group_trademark'   => $group_trademark,
        'group_patent'      => $group_patent,
        'group_design'      => $group_design,
        'group_franchise'   => $group_franchise,
        'group_copyright'   => $group_copyright,
        'group_others'      => $group_others,
        'group_potential'   => $group_potential,
        'role_admin'        => $role_admin,
        'role_manager'      => $role_manager,
        'role_member'       => $role_member,
        'role_law_manager'  => $role_law_manager,
        'role_ip_manager'   => $role_ip_manager,
    );

    // Check if member already exists
    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$aslTable} WHERE memberid = %d",
        $user->ID
    ));

    if ($existing) {
        // Update existing record
        $result = $wpdb->update(
            $aslTable,
            $member_data,
            array('memberid' => $user->ID)
        );
    } else {
        // Insert new record
        $result = $wpdb->insert(
            $aslTable,
            $member_data
        );
    }

    return $result !== false;
}


add_action('wp_ajax_remove_attachment', 'remove_attachment');
function remove_attachment() {
    $url = $_POST['url'];
    unlink($url);
    // echo "Đã xóa file $url thành công.";

    # update to email attachment
    $attachment = explode('|', $_POST['attachment']);
    $id_email = $_POST['id_email'];
    update_field('field_67add7781ca67', implode(PHP_EOL, $attachment), $id_email);
    exit;
}

add_action('wp_ajax_save_commission', 'save_commission_data');
function save_commission_data() {
    global $wpdb;
    
    // Verify nonce and permissions
    if (!is_user_logged_in()) {
        echo json_encode(array('status' => 'error', 'message' => 'Bạn cần đăng nhập để thực hiện thao tác này.'));
        exit;
    }
    
    $job_id = intval($_POST['job_id']);
    $commissions = $_POST['commissions'];
    
    if (!$job_id || !$commissions) {
        echo json_encode(array('status' => 'error', 'message' => 'Dữ liệu không hợp lệ.'));
        exit;
    }
    
    // Verify job exists
    $job = get_post($job_id);
    if (!$job || $job->post_type !== 'job') {
        echo json_encode(array('status' => 'error', 'message' => 'Công việc không tồn tại.'));
        exit;
    }
    
    // Lấy paid (thực nhận) từ DB để tính commission_amount
    $paid_value = floatval(get_field('paid', $job_id));
    
    // Calculate total percentage
    $total_percent = 0;
    foreach ($commissions as $commission) {
        if (isset($commission['commission_percent']) && $commission['commission_percent'] > 0) {
            $total_percent += floatval($commission['commission_percent']);
        }
    }
    
    // Check if total exceeds 100%
    if ($total_percent > 100) {
        echo json_encode(array('status' => 'error', 'message' => 'Tổng tỷ lệ phân chia không được vượt quá 100%.'));
        exit;
    }
    
    $success_count = 0;
    $error_count = 0;
    
    // Process each commission
    foreach ($commissions as $commission) {
        $userid = intval($commission['userid']);
        $role_type = sanitize_text_field($commission['role_type']);
        $commission_percent = floatval($commission['commission_percent']);
        $currency = sanitize_text_field($commission['currency']);
        
        // Skip if no percentage set
        if ($commission_percent <= 0) {
            continue;
        }
        
        // Tính commission_amount dựa trên paid (thực nhận), KHÔNG dùng total_value
        $commission_amount = intval(round($paid_value * $commission_percent / 100));
        
        // Check if record exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM wp_aslcommission WHERE jobid = %d AND userid = %d AND role_type = %s",
            $job_id, $userid, $role_type
        ));
        
        if ($existing) {
            // Update existing record
            $result = $wpdb->update(
                'wp_aslcommission',
                array(
                    'commission_percent' => $commission_percent,
                    'commission_amount' => $commission_amount,
                    'currency' => $currency,
                    'updated_date' => current_time('mysql', 1)
                ),
                array(
                    'id' => $existing->id
                ),
                array('%f', '%d', '%s', '%s'),
                array('%d')
            );
        } else {
            // Insert new record
            $result = $wpdb->insert(
                'wp_aslcommission',
                array(
                    'jobid' => $job_id,
                    'userid' => $userid,
                    'role_type' => $role_type,
                    'commission_percent' => $commission_percent,
                    'commission_amount' => $commission_amount,
                    'currency' => $currency,
                    'created_date' => current_time('mysql', 1),
                    'updated_date' => current_time('mysql', 1)
                ),
                array('%d', '%d', '%s', '%f', '%d', '%s', '%s', '%s')
            );
        }
        
        if ($result !== false) {
            $success_count++;
        } else {
            $error_count++;
        }
    }
    
    // Prepare response message
    if ($success_count > 0 && $error_count == 0) {
        $message = "Đã lưu thành công phân chia hoa hồng cho {$success_count} người" . ($paid_value > 0 ? " (dựa trên thực nhận: " . number_format($paid_value) . ")" : "") . ".";
        $status = 'success';
    } elseif ($success_count > 0 && $error_count > 0) {
        $message = "Đã lưu thành công {$success_count} bản ghi, {$error_count} bản ghi lỗi.";
        $status = 'warning';
    } else {
        $message = "Có lỗi xảy ra khi lưu dữ liệu.";
        $status = 'error';
    }
    
    echo json_encode(array('status' => $status, 'message' => $message));
    exit;
}

/**
 * Cập nhật lại commission_amount cho tất cả nhân sự của một job khi giá trị paid thay đổi.
 * Hàm này được gọi sau khi update_job hoặc finance cập nhật paid.
 */
function update_commission_amounts_by_paid($job_id, $new_paid_value = null) {
    global $wpdb;
    
    if ($new_paid_value === null) {
        $new_paid_value = floatval(get_field('paid', $job_id));
    }
    
    // Lấy tất cả commissions của job này
    $commissions = $wpdb->get_results($wpdb->prepare(
        "SELECT id, commission_percent FROM wp_aslcommission WHERE jobid = %d AND commission_percent > 0",
        $job_id
    ));
    
    if (empty($commissions)) {
        return; // Không có commission nào, bỏ qua
    }
    
    foreach ($commissions as $c) {
        $new_amount = intval(round($new_paid_value * floatval($c->commission_percent) / 100));
        $wpdb->update(
            'wp_aslcommission',
            array(
                'commission_amount' => $new_amount,
                'updated_date'      => current_time('mysql', 1)
            ),
            array('id' => $c->id),
            array('%d', '%s'),
            array('%d')
        );
    }
}