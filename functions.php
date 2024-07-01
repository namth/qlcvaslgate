<?php
# add custom posts & custom fields
add_action('init','all_my_hooks');
function all_my_hooks(){
    $dir = dirname( __FILE__ );
    require_once( $dir . '/custom_posts.php');
    require_once( $dir . '/custom_fields.php');
    require_once( $dir . '/ajax_filter.php');
    // require_once ($dir . '/datacenter/secret.php');
    require_once ($dir . '/datacenter/mongodb_connection.php');
}

register_nav_menus(array('main-menu' => esc_html__('Main Menu', 'blankslate')));
add_theme_support('title-tag');

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
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    $now = new DateTime;
    $ago = new DateTime;
    $ago->setTimestamp($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

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
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v;
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
    $data = parse_str($_POST['data'], $output);

    $company_name   = $output['company_name'];
    $user_code      = $output['user_code'];
    $first_name     = $output['first_name'];
    $last_name      = $output['last_name'];
    $user_email     = $output['user_email'];
    $phone_number   = $output['phone_number'];
    $address        = $output['address'];
    $country        = $output['country'];
    $note           = $output['note'];
    $link_onedrive  = $output['link_onedrive'];
    $role           = $output['role'];;
    $type_of_client = $output['type_of_client'];;
    $display_name   = $first_name . " " . $last_name;
    $user_pass      = 'd1412@pass';

    $worked         = $output['worked'];
    $nguon_dau_viec = $output['nguon_dau_viec'];
    $partner_vip    = $output['partner_vip'];
    $email_cc       = $output['email_cc'];
    $email_bcc      = $output['email_bcc'];
    $city           = $output['city']; #
    $vietnam_company= $output['vietnam_company'];
    if ($output['languages']) {
        $languages      = implode(", ", $output['languages']);
    }

    $phan_loai      = $output['phan_loai'];
    if ($output['detail_client_type']) {
        $detail_client_type = implode(", ", $output['detail_client_type']);
    }
    $fdi            = $output['fdi'];
    if ($output['fdi_countries']) {
        $fdi_countries  = implode(", ", $output['fdi_countries']);
    }
    
    if ($phan_loai) {
        if ($output['staffs']) {
            $staffs     = implode("|", $output['staffs']);
        }
    }

    if (search_partner($user_code)) {
        $error_partner_code = true;
        $error_message = __("<b>Trùng mã đối tác</b>", 'qlcv');
    }

    # add new user
    $args = array(
        'user_login'    => $user_email,
        'user_email'    => $user_email,
        'user_pass'     => $user_pass,
        'first_name'    => $first_name,
        'last_name'     => $last_name,
        'display_name'  => $display_name,
        'description'   => $note,
        'role'          => $role,
    );

    $new_partner = wp_insert_user($args);

    $data = array();
    # if it's success create new user,
    # add more info throught custom fields
    if (!is_wp_error($new_partner) && !$error_partner_code) {
        update_field('field_600d31f4060eb', $company_name, 'user_' . $new_partner); # company_name
        update_field('field_607a4fb37b7e0', $user_code, 'user_' . $new_partner); # user_code
        update_field('field_600d3211060ec', $phone_number, 'user_' . $new_partner); # phone number
        update_field('field_600d323d060ee', $address, 'user_' . $new_partner); # address
        update_field('field_6037200ec98cc', $country, 'user_' . $new_partner); # country
        update_field('field_6010f85bfcf55', $link_onedrive, 'user_' . $new_partner); # link_onedrive
        update_field('field_60a3cbacb1330', $type_of_client, 'user_' . $new_partner); # type_of_client
        update_field('field_65a5625b5eb0e', $city, 'user_' . $new_partner); # city
        update_field('field_65a562035eb0c', $vietnam_company, 'user_' . $new_partner); # $vietnam_company
        update_field('field_65a4acb5db9c6', $phan_loai, 'user_' . $new_partner); # $có phải là cty hay không
        update_field('field_65a4acebdb9c7', $staffs, 'user_' . $new_partner); # $update người trong công ty
        update_field('field_65a5622f5eb0d', $languages, 'user_' . $new_partner); # $languages
        update_field('field_6039b28e2ba07', $email_cc, 'user_' . $new_partner); # email_cc
        update_field('field_609a038489e8c', $email_bcc, 'user_' . $new_partner); # email_bcc
        update_field('field_61cd79bf1653f', $worked, 'user_' . $new_partner); # đã chốt hoặc tiềm năng
        update_field('field_65de936686343', $nguon_dau_viec, 'user_' . $new_partner); # nguồn đến từ đâu
        update_field('field_65dcc97fa77b9', $detail_client_type, 'user_' . $new_partner); # chuyên ngành đối tác
        update_field('field_65ddcd2141e6f', $fdi, 'user_' . $new_partner); # có vốn fdi không
        if ($fdi && $fdi_countries) {
            update_field('field_65ddcd7941e70', $fdi_countries, 'user_' . $new_partner); # quốc gia đầu tư
        }

        $data['status'] = 'success';
        $data['content'] = "<option value='" . $new_partner . "' selected>" . $display_name . " (" . $user_email . ")</option>";
        $data['notification'] = '<div class="alert alert-success" role="alert">
                                    <i class="fa fa-check"></i> ' . __('Đã tạo tài khoản thành công', 'qlcv') . '
                                  </div>';
    } else {
        $data['status'] = 'error';
        $data['notification'] = '<div class="alert alert-danger" role="alert">
                                    <i class="zmdi zmdi-info"></i> ' . __('Có lỗi xảy ra, xin vui lòng kiểm tra lại.', 'qlcv') . ' ' . $error_message . '
                                  </div>';
    }

    switch ($role) {
        case 'partner':
            $data['hide_form']          = '#create_partner';
            $data['div_notification']   = '#create_partner .notification';
            $data['select_element']     = 'select[name="partner"]';
            break;

        case 'foreign_partner':
            $data['hide_form']          = '#create_foreign_partner';
            $data['div_notification']   = '#create_foreign_partner .notification';
            $data['select_element']     = 'select[name="foreign_partner"]';
            break;

        default:
            break;
    }

    echo json_encode($data);
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
    # get data from the form
    // $file_upload            = $_FILE['file_upload'];
    $data_partner           = $_POST['data_partner'];
    $data_foreign_partner   = $_POST['data_foreign_partner'];
    $data_customer          = $_POST['data_customer'];
    $data_manager           = $_POST['data_manager'];
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
    # việc khác
    if (($_POST['deadline']) && ($danh_muc == "Việc khác")) {
        # xử lý chuỗi ngày tháng từ dạng DD/MM/YYYY sang YYYYMMDD để phù hợp với format của ACF custom field
        $deadline_arr   = explode('/', $_POST['deadline']);
        $temp_date      = array_reverse($deadline_arr);
        $new_deadline   = implode('', $temp_date);
        $current_user   = wp_get_current_user();
        $current_time   = current_time('timestamp', 7);
    }
    if($danh_muc == "Việc khác"){
        $danhmuckhac = $_POST['other_job'];
    }
    # finance
    $currency       = $_POST['currency'];
    $total_value    = $_POST['total_value'];
    $paid           = $_POST['paid'];
    # tính toán số tiền còn lại
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
            update_field('field_600fdbda0269e', $danh_muc, $inserted); # phân loại
            update_field('field_6099f6bb87256', $country, $inserted); # quốc gia nộp
            update_field('field_6099f71187257', $partner_ref, $inserted); # Số REF của đối tác
            update_field('field_6099f75a87258', $our_ref, $inserted); # Số REF của mình
            update_field('field_60a38cc126a5f', $link_onedrive, $inserted); # Link tài liệu
            update_field('field_60fceb18a736d', $mindful, $inserted); # Lưu ý công việc
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
            if ($danhmuckhac && ($danh_muc == "Việc khác")) wp_set_object_terms($inserted, $danhmuckhac, 'group', true);
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
    $logs = get_field('lich_su_cong_viec', $postid);
    $finish_update = array(
        'mo_ta'         => $mota,
        'ngay_thang'    => $ngaythang,
    );

    if ($logs) {
        array_push($logs, $finish_update);
        update_field('field_606ed4e802a6a', $logs, $postid);
    } else add_row('field_606ed4e802a6a', $finish_update, $postid);
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
            $temp = '<li><a class="prev page-numbers" href="#" data-page="' . $previous_page . '">« Trang trước</a></li>' . $temp;
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
            $temp .= '<li><a href="#" class="next page-numbers" data-page="' . $next_page . '">Trang sau »</a></li>';
        }
            
        $pagination .= $temp . '</ul>';
        return $pagination;
    }
}


function CreateDatabaseQlcv()
{
    global $wpdb;
    $charsetCollate = $wpdb->get_charset_collate();
    # table 1
    $aslTable = $wpdb->prefix . 'aslcustomer';
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
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($createAslTable);

    # table 2
    $aslTable = $wpdb->prefix . 'aslpartner';
    $createAslTable = "CREATE TABLE `{$aslTable}` (
        `partnerid` bigint(20) UNSIGNED NOT NULL,
        `name` varchar(255) NOT NULL,
        `partner_code` varchar(20) NULL,
        `companyName` varchar(255) NULL,
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
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($createAslTable);

    # table 3
    $aslTable = $wpdb->prefix . 'aslmember';
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
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($createAslTable);

    # table 4
    $aslTable = $wpdb->prefix . 'asljob';
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
        `our_ref` varchar(50) NULL,
        `currency` varchar(5) NULL,
        `total_value` varchar(255) NULL,
        `paid` varchar(255) NULL,
        `remainning` varchar(255) NULL,
        `total_cost` varchar(255) NULL,
        `currency_out` varchar(5) NULL,
        `advance_money` varchar(255) NULL,
        `debt` varchar(255) NULL,
        `payment_status` varchar(255) NULL,
        `source` varchar(255) NULL,
        `date` timestamp NOT NULL,
        `contract_sign_date` timestamp NOT NULL,
        `agency_hn` tinyint(4) NOT NULL,
        `agency_hcm` tinyint(4) NOT NULL,
        PRIMARY KEY (`jobid`)
    ) {$charsetCollate};";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($createAslTable);

    # table 5
    $aslTable = $wpdb->prefix . 'asltask';
    $createAslTable = "CREATE TABLE `{$aslTable}` (
        `taskid` bigint(20) UNSIGNED NOT NULL,
        `jobid` bigint(20) UNSIGNED NOT NULL,
        `memberid` bigint(20) UNSIGNED NOT NULL,
        `managerid` bigint(20) UNSIGNED NOT NULL,
        `title` varchar(255) NOT NULL,
        `status` varchar(255) NULL,
        `deadline` timestamp NULL,
        `time_to_response` timestamp NULL,
        `miss_deadline` tinyint(4) NULL,
        `date` timestamp NOT NULL,
        PRIMARY KEY (`taskid`)
    ) {$charsetCollate};";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($createAslTable);

    # table 6
    $aslTable = $wpdb->prefix . 'asljobhistory';
    $createAslTable = "CREATE TABLE `{$aslTable}` (
        `jobid` bigint(20) UNSIGNED NOT NULL,
        `name` varchar(255) NOT NULL,
        `date` timestamp NULL
    ) {$charsetCollate};";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($createAslTable);

    # table 7
    $aslTable = $wpdb->prefix . 'asltaskhistory';
    $createAslTable = "CREATE TABLE `{$aslTable}` (
        `taskid` bigint(20) UNSIGNED NOT NULL,
        `userid` bigint(20) UNSIGNED NOT NULL,
        `content` varchar(255) NOT NULL,
        `date` timestamp NOT NULL
    ) {$charsetCollate};";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($createAslTable);

    # table 8
    $aslTable = $wpdb->prefix . 'aslsupervisor';
    $createAslTable = "CREATE TABLE `{$aslTable}` (
        `jobid` bigint(20) UNSIGNED NOT NULL,
        `supervisorid` bigint(20) UNSIGNED NOT NULL
    ) {$charsetCollate};";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($createAslTable);

    # table 9
    $aslTable = $wpdb->prefix . 'asljobgroup';
    $createAslTable = "CREATE TABLE `{$aslTable}` (
        `jobid` bigint(20) UNSIGNED NOT NULL,
        `groupname` varchar(255) NOT NULL,
        `type` varchar(255) NOT NULL,
        `date` timestamp NOT NULL
    ) {$charsetCollate};";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($createAslTable);


}
add_action('after_switch_theme', 'CreateDatabaseQlcv');
