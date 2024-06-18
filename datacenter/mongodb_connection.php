<?php 

// require_once (__DIR__ . "/secret.php");

# call any api with authentication token
function mongo_api($api, $collection, $data, $method="POST") {
    $body = array_merge([
        'collection'    =>  $collection,
        'database'      =>  MONGO_DATABASE,
        'dataSource'    =>  MONGO_DATASOURCE,
    ], $data);


    $args = array(
        'method'    => $method,
        'timeout'   => '120',
        'headers'   => array(
            'Content-Type'  => 'application/ejson',
            'Accept' => 'application/json',
            'apiKey' => MONGO_ASL_APIKEY,
        ),    
        'body'      => json_encode($body),
    );    


    $response = wp_remote_post(
        $api,
        $args
    );

    if (is_wp_error($response)) {
        return "Lỗi: " . $response;
    } else {
        $response_body = json_decode(wp_remote_retrieve_body($response));
        return $response_body;
    }
}

/* 
    Tìm kiếm một bản ghi trong mongo
*/
function asl_find($collection, $filter) {
    $action     = 'findOne';
    $api_url    = 'https://ap-southeast-1.aws.data.mongodb-api.com/app/' . MONGO_CLIENT_APPID . '/endpoint/data/v1/action/' . $action;

    $find = [
        "filter"    => [$filter]
    ];

    $sent = mongo_api($api_url, $collection, $find);

    return $sent->document;
}


/* 
    Xử lý khi bấm nút export vào mongoDB
*/
add_action('wp_ajax_run_export_mongo', 'run_export_mongo');
function run_export_mongo(){
    $asl_data_type  = $_POST['asl_data_type'];
    $mongo_method   = $_POST['mongo_method'];
    $start_page     = $_POST['start_page'];

    switch ($asl_data_type) {
        case 'customer':
            /* 
                Tiếp tục phân nhánh và lấy được tổng số trang
            */
            switch ($mongo_method) {
                case 'insertMany':
                    $posts_per_page = 20;
        
                    $args   = array(
                        'post_type'     => 'customer',
                        'paged'         => 1,
                        'posts_per_page'=> $posts_per_page,
                    );
        
                    $query = new WP_Query( $args );
                    $total_page = $query->max_num_pages;
                    $function   = 'insert_customer';
                    break;

                case 'update':
                    $total_page = 0;
                    $function   = 'update_customer';
                
                default:
                    $total_page = 0;
                    $function = 'none';
                    break;
            }
            break;

        case 'partner':
            if ($mongo_method == 'insertMany') {
                $posts_per_page = 20;
        
                $count_args  = array(
                    'role__in'  => ['partner', 'foreign_partner'],
                    'number'    => 999999,
                );
                $user_count_query = new WP_User_Query($count_args);
                $partner_number = $user_count_query->get_results();

                $total_page = ceil(count($partner_number)/$posts_per_page);
                $function   = 'insert_partner';
            }
            break;

        case 'member':
            if ($mongo_method == 'insertMany') {
                $posts_per_page = 20;
        
                $count_args  = array(
                    'role__in'  => ['contributor', 'administrator', 'member', 'law_manager', 'ip_manager'],
                    'number'    => 999999,
                );
                $user_count_query = new WP_User_Query($count_args);
                $member_number = $user_count_query->get_results();

                $total_page = ceil(count($member_number)/$posts_per_page);
                $function   = 'insert_member';
            }
            break;

        case 'job':
            if ($mongo_method == 'insertMany') {
                $posts_per_page = 20;
        
                $args   = array(
                    'post_type'     => 'job',
                    'paged'         => 1,
                    'posts_per_page'=> $posts_per_page,
                );
    
                $query = new WP_Query( $args );
                $total_page = $query->max_num_pages;
                $function   = 'insert_job';
            }
            break;
        
        case 'task':
            if ($mongo_method == 'insertMany') {
                $posts_per_page = 20;
        
                $args   = array(
                    'post_type'     => 'task',
                    'paged'         => 1,
                    'posts_per_page'=> $posts_per_page,
                );
    
                $query = new WP_Query( $args );
                $total_page = $query->max_num_pages;
                $function   = 'insert_task';
            }
            break;
        
        default:
            # code...
            break;
    }

    /*
        Sau khi xử lý xong, phải return được $total_page, tên hàm xử lý
    */
    echo json_encode([
        'total_page'    => $total_page,
        'action'        => $mongo_method,
        'function'      => $function,
        'current_page'  => $start_page
    ]);
    exit;
}

add_action('wp_ajax_js_export', 'js_export');
function js_export(){
    // $total_page     = $_POST['total_page'];
    $functional     = $_POST['functional'];
    $current_page   = $_POST['current_page'];

    switch ($functional) {
        case 'insert_customer':
            // $export = export_customer($current_page);
            $export = export_mysql_customer($current_page);
            break;
        
        case 'insert_partner':
            // $export = export_partner($current_page);
            $export = export_mysql_partner($current_page);
            break;
        
        case 'insert_member':
            // $export = export_member($current_page);
            $export = export_mysql_member($current_page);
            break;
        
        case 'insert_job':
            // $export = export_job($current_page);
            $export = export_mysql_job($current_page);
            break;
        
        case 'insert_task':
            // $export = export_task($current_page);
            $export = export_mysql_task($current_page);
            break;
        
        default:
            # code...
            break;
    }

    $current_page++;

    echo json_encode([
        // 'total_page'    => $total_page,
        'function'      => $functional,
        'current_page'  => $current_page,
        'result'        => $export
    ]);
    exit;
}

/* 
    Hàm export customer theo từng paged
*/
function export_mysql_customer($paged) {
    global $wpdb;

    $aslTable = $wpdb->prefix . 'aslcustomer';
    $posts_per_page = 20;

    $args   = array(
        'post_type'     => 'customer',
        'paged'         => $paged,
        'posts_per_page'=> $posts_per_page,
    );

    $query = new WP_Query( $args );

    $list_customer = array();

    if( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();

            $name = get_the_title();
            $so_dien_thoai = get_field('so_dien_thoai');
            $email = get_field('email');
            $cong_ty = get_field('ten_cong_ty');
            $quoc_gia = get_field('quoc_gia');
            $date = DateTime::createFromFormat('m/d/Y', get_the_date('m/d/Y'));

            $customer = [
                'id'            => get_the_ID(),
                'name'          => $name,
                'companyName'   => $cong_ty,
                'country'       => $quoc_gia,
                'phone'         => $so_dien_thoai,
                'email'         => $email,
                'date'          => $date->format('Y-m-d H:i:s'),
            ];
            // print_r($customer);

            $wpdb->insert(
                $aslTable,
                $customer
            );
        } 
        wp_reset_postdata();
    }

    return true;
}

function export_customer($paged) {
    $posts_per_page = 20;

    $args   = array(
        'post_type'     => 'customer',
        'paged'         => $paged,
        'posts_per_page'=> $posts_per_page,
    );

    $query = new WP_Query( $args );

    $action     = 'insertMany';
    $api_url    = 'https://ap-southeast-1.aws.data.mongodb-api.com/app/' . MONGO_CLIENT_APPID . '/endpoint/data/v1/action/' . $action;

    $list_customer = array();

    if( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();

            $name = get_the_title();
            $so_dien_thoai = get_field('so_dien_thoai');
            $email = get_field('email');
            $cong_ty = get_field('ten_cong_ty');
            $quoc_gia = get_field('quoc_gia');

            $customer = (object)[
                'customerid'=> get_the_ID(),
                'name'      => $name,
                'company'   => $cong_ty,
                'country'   => $quoc_gia,
                'phone'     => $so_dien_thoai,
                'email'     => $email
            ];
            // print_r($customer);

            // $filter = ['name' => $name, 'company' => $cong_ty, 'email' => $email];
            /* Check trong DB xem đã có dữ liệu chưa */
            // $find = asl_find('asl_customer', $filter);
            /* Nếu chưa có thì thêm vào danh sách để chuẩn bị add */
            /* Nếu đã có thì thông báo ra màn hình là đã tồn tại */
            // if (!isset($find->_id)) {
                $list_customer[] = $customer;
            // } 
        } 
        wp_reset_postdata();
    }

    $documents = [
        'documents' => $list_customer
    ];

    if (!empty($list_customer)) {
        $sent = mongo_api($api_url, 'asl_customer', $documents);
    }

    return $sent;
}

function export_mysql_partner($current_page) {
    global $wpdb;

    $aslTable = $wpdb->prefix . 'aslpartner';
    $users_per_page = 20;
    $offset = $users_per_page * ($current_page - 1);

    $args   = array(
        'number'    => $users_per_page,
        'role__in'  => ['partner', 'foreign_partner'],
        'paged'     => $current_page,
        'offset'    => $offset,
    );
    $query = new WP_User_Query($args);
    $users = $query->get_results();

    if (!empty($users)) {
        foreach ($users as $user) {
            $so_dien_thoai  = get_field('so_dien_thoai', 'user_' . $user->ID);
            $partner_code   = get_field('partner_code', 'user_' . $user->ID);
            $ten_cong_ty    = get_field('ten_cong_ty', 'user_' . $user->ID);
            $is_company     = get_field('is_company' , 'user_' . $user->ID);
            $author_link    = get_author_posts_url($user->ID);
            $dia_chi        = get_field('dia_chi' , 'user_' . $user->ID);
            $quoc_gia       = get_field('quoc_gia' , 'user_' . $user->ID);
            $city           = get_field('city' , 'user_' . $user->ID);
            $is_company     = get_field('is_company' , 'user_' . $user->ID);
            $staffs         = get_field('staffs' , 'user_' . $user->ID);
            $vietnam_company = get_field('vietnam_company' , 'user_' . $user->ID);
            $languages      = get_field('languages' , 'user_' . $user->ID);
            $email_cc       = get_field('email_cc' , 'user_' . $user->ID);
            $email_bcc      = get_field('email_bcc' , 'user_' . $user->ID);
            $type_of_client = get_field('type_of_client' , 'user_' . $user->ID);
            $vip            = get_field('vip' , 'user_' . $user->ID);
            $worked         = get_field('worked' , 'user_' . $user->ID);
            $fdi            = get_field('fdi' , 'user_' . $user->ID);
            $fdi_countries  = get_field('fdi_countries' , 'user_' . $user->ID);
            $detail_client_type = get_field('detail_client_type' , 'user_' . $user->ID);
            $source         = get_field('source' , 'user_' . $user->ID);

            $tinh_trang     = $worked?"Đã chốt":"Tiềm năng";
            // $date = DateTime::createFromFormat('m/d/Y', get_the_date('m/d/Y'));


            # display user role name
            $role_partner__in = $role_partner__out = 0;
            if (!empty($user->roles) && is_array($user->roles)) {
                $role_partner__in = in_array('partner', $user->roles)?1:0;
                $role_partner__out = in_array('foreign_partner', $user->roles)?1:0;
            }

            $partner =[
                'id'            => $user->ID,
                'name'          => $user->display_name,
                'partner_code'  => $partner_code,
                'companyName'   => $ten_cong_ty,
                'country'       => $quoc_gia,
                'address'       => $dia_chi,
                'city'          => $city,
                'is_company'    => $is_company,
                'staffs'        => $staffs,
                'vn_company'    => $vietnam_company,
                'languages'     => $languages,
                'email_cc'      => $email_cc,
                'email_bcc'     => $email_bcc,
                'type_of_client'=> $type_of_client,
                'vip'           => $vip,
                'status'        => $tinh_trang,
                'fdi'           => $fdi,
                'fdi_from'      => $fdi_countries,
                'client_type'   => $detail_client_type,
                'source'        => $source,
                'phone'         => $so_dien_thoai,
                'email'         => $user->user_email,
                'role_partner__in' => $role_partner__in,
                'role_partner__out'=> $role_partner__out,
                'date'          => $user->user_registered,
            ];

            $wpdb->insert(
                $aslTable,
                $partner
            );
        }
    }
}

function export_partner($current_page) {
    global $wp_roles;

    $users_per_page = 20;
    $offset = $users_per_page * ($current_page - 1);

    $args   = array(
        'number'    => $users_per_page,
        'role__in'  => ['partner', 'foreign_partner'],
        'paged'     => $current_page,
        'offset'    => $offset,
    );
    $query = new WP_User_Query($args);
    $users = $query->get_results();

    $action     = 'insertMany';
    $api_url    = 'https://ap-southeast-1.aws.data.mongodb-api.com/app/' . MONGO_CLIENT_APPID . '/endpoint/data/v1/action/' . $action;
    $mongo_collection = 'asl_partner';

    $list_partner = array();

    if (!empty($users)) {
        foreach ($users as $user) {
            $roles = array();
            $so_dien_thoai  = get_field('so_dien_thoai', 'user_' . $user->ID);
            $partner_code   = get_field('partner_code', 'user_' . $user->ID);
            $ten_cong_ty    = get_field('ten_cong_ty', 'user_' . $user->ID);
            $is_company     = get_field('is_company' , 'user_' . $user->ID);
            $author_link    = get_author_posts_url($user->ID);
            $dia_chi        = get_field('dia_chi' , 'user_' . $user->ID);
            $quoc_gia       = get_field('quoc_gia' , 'user_' . $user->ID);
            $city           = get_field('city' , 'user_' . $user->ID);
            $is_company     = get_field('is_company' , 'user_' . $user->ID);
            $staffs         = get_field('staffs' , 'user_' . $user->ID);
            $vietnam_company = get_field('vietnam_company' , 'user_' . $user->ID);
            $languages      = get_field('languages' , 'user_' . $user->ID);
            $email_cc       = get_field('email_cc' , 'user_' . $user->ID);
            $email_bcc      = get_field('email_bcc' , 'user_' . $user->ID);
            $type_of_client = get_field('type_of_client' , 'user_' . $user->ID);
            $vip            = get_field('vip' , 'user_' . $user->ID);
            $worked         = get_field('worked' , 'user_' . $user->ID);
            $fdi            = get_field('fdi' , 'user_' . $user->ID);
            $fdi_countries  = get_field('fdi_countries' , 'user_' . $user->ID);
            $detail_client_type = get_field('detail_client_type' , 'user_' . $user->ID);
            $source         = get_field('source' , 'user_' . $user->ID);

            $tinh_trang     = $worked?"Đã chốt":"Tiềm năng";



            # display user role name
            if (!empty($user->roles) && is_array($user->roles)) {
                foreach ($user->roles as $role)
                    $roles[] = translate_user_role($wp_roles->roles[$role]['name']);
            }

            if ($staffs) {
                $staff_array = explode("|", $staffs);                
            }

            $partner = (object)[
                'partnerid'     => $user->ID,
                'name'          => $user->display_name,
                'partner_code'  => $partner_code,
                'company'       => $ten_cong_ty,
                'country'       => $quoc_gia,
                'address'       => $dia_chi,
                'city'          => $city,
                'is_company'    => $is_company,
                'staffs'        => $staff_array,
                'vn_company'    => $vietnam_company,
                'languages'     => $languages,
                'email_cc'      => $email_cc,
                'email_bcc'     => $email_bcc,
                'type_of_client'=> $type_of_client,
                'vip'           => $vip,
                'status'        => $tinh_trang,
                'fdi'           => $fdi,
                'fdi_from'      => $fdi_countries,
                'client_type'   => $detail_client_type,
                'source'        => $source,
                'phone'         => $so_dien_thoai,
                'email'         => $user->user_email,
                // 'roles'         => $roles,
            ];

            foreach ($roles as $key => $value) {
                $key = 'role_' . $value;
                $partner->$key = true;
            }

            $list_partner[] = $partner;
        }
    }
    
    $documents = [
        'documents' => $list_partner
    ];

    if (!empty($list_partner)) {
        $sent = mongo_api($api_url, $mongo_collection, $documents);
    }

    return $sent;
}

function export_mysql_member($current_page) {
    global $wpdb;

    $aslTable = $wpdb->prefix . 'aslmember';
    $users_per_page = 20;
    $offset = $users_per_page * ($current_page - 1);

    $args   = array(
        'number'    => $users_per_page,
        'role__in'  => ['contributor', 'administrator', 'member', 'law_manager', 'ip_manager'],
        'paged'     => $current_page,
        'offset'    => $offset,
    );
    $query = new WP_User_Query($args);
    $users = $query->get_results();

    if (!empty($users)) {
        foreach ($users as $user) {
            $roles = array();
            $so_dien_thoai  = get_field('so_dien_thoai', 'user_' . $user->ID);
            $dia_chi        = get_field('dia_chi' , 'user_' . $user->ID);
            $chi_nhanh      = get_field('chi_nhanh' , 'user_' . $user->ID);
            $nhom_cong_viec = get_field('nhom_cong_viec' , 'user_' . $user->ID);
            $work_group = array();
            $brand = array();

            foreach ($nhom_cong_viec as $id_cong_viec) {
                $term = get_term($id_cong_viec);

                $work_group[] = $term->slug;
            }

            foreach ($chi_nhanh as $id_chi_nhanh) {
                $term = get_term($id_chi_nhanh);

                $brand[] = $term->slug;
            }

            if (is_array($work_group)) {
                $group_trademark = in_array('nhan-hieu', $work_group)?1:0;
                $group_patent = in_array('sang-che', $work_group)?1:0;
                $group_design = in_array('kieu-dang', $work_group)?1:0;
                $group_franchise = in_array('franchise', $work_group)?1:0;
                $group_copyright = in_array('ban-quyen', $work_group)?1:0;
                $group_others = in_array('viec-khac', $work_group)?1:0;
                $group_potential = in_array('tiem-nang', $work_group)?1:0;
            }

            if (is_array($brand)) {
                $agency_hn = in_array('ha-noi', $brand)?1:0;
                $agency_hcm = in_array('ho-chi-minh', $brand)?1:0;
            }

            # display user role name
            if (!empty($user->roles) && is_array($user->roles)) {
                $role_admin         = in_array('administrator', $user->roles)?1:0;
                $role_manager       = in_array('contributor', $user->roles)?1:0;
                $role_member        = in_array('member', $user->roles)?1:0;
                $role_law_manager   = in_array('law_manager', $user->roles)?1:0;
                $role_ip_manager    = in_array('ip_manager', $user->roles)?1:0;
            }

            $partner = [
                'id'            => $user->ID,
                'name'          => $user->display_name,
                'address'       => $dia_chi,
                'phone'         => $so_dien_thoai,
                'email'         => $user->user_email,
                'date'          => $user->user_registered,
                'agency_hn'     => $agency_hn,
                'agency_hcm'    => $agency_hcm,
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
            ];

            $wpdb->insert(
                $aslTable,
                $partner
            );
        }
    }
}

function export_member($current_page) {
    global $wp_roles;

    $users_per_page = 20;
    $offset = $users_per_page * ($current_page - 1);

    $args   = array(
        'number'    => $users_per_page,
        'role__in'  => ['contributor', 'administrator', 'member', 'law_manager', 'ip_manager'],
        'paged'     => $current_page,
        'offset'    => $offset,
    );
    $query = new WP_User_Query($args);
    $users = $query->get_results();

    $action     = 'insertMany';
    $api_url    = 'https://ap-southeast-1.aws.data.mongodb-api.com/app/' . MONGO_CLIENT_APPID . '/endpoint/data/v1/action/' . $action;
    $mongo_collection = 'asl_employee';

    $list_partner = array();

    if (!empty($users)) {
        foreach ($users as $user) {
            $roles = array();
            $so_dien_thoai  = get_field('so_dien_thoai', 'user_' . $user->ID);
            $dia_chi        = get_field('dia_chi' , 'user_' . $user->ID);
            $chi_nhanh      = get_field('chi_nhanh' , 'user_' . $user->ID);
            $nhom_cong_viec = get_field('nhom_cong_viec' , 'user_' . $user->ID);
            $work_group = array();
            $brand = array();

            foreach ($nhom_cong_viec as $id_cong_viec) {
                $term = get_term($id_cong_viec);

                $work_group[] = $term->name;
            }

            foreach ($chi_nhanh as $id_chi_nhanh) {
                $term = get_term($id_chi_nhanh);

                $brand[] = $term->name;
            }

            # display user role name
            if (!empty($user->roles) && is_array($user->roles)) {
                foreach ($user->roles as $role)
                    $roles[] = translate_user_role($wp_roles->roles[$role]['name']);
            }

            $partner = (object)[
                'memberid'      => $user->ID,
                'name'          => $user->display_name,
                'address'       => $dia_chi,
                // 'agency'        => $brand,
                // 'work_group'    => $work_group,
                'phone'         => $so_dien_thoai,
                'email'         => $user->user_email
                // 'roles'         => $roles
            ];

            foreach ($brand as $agency) {
                $key = 'agency_' . $agency;
                $partner->$key = true;
            }
            
            foreach ($work_group as $value) {
                $key = 'workGroup_' . $value;
                $partner->$key = true;
            }

            foreach ($roles as $key => $value) {
                $key = 'role_' . $value;
                $partner->$key = true;
            }

            $list_partner[] = $partner;
        }
    }
    
    $documents = [
        'documents' => $list_partner
    ];

    if (!empty($list_partner)) {
        $sent = mongo_api($api_url, $mongo_collection, $documents);
    }

    return $sent;
}

function export_mysql_job($paged) {
    global $wpdb;

    $aslTable = $wpdb->prefix . 'asljob';
    $aslHistory = $wpdb->prefix . 'asljobhistory';
    $aslSupervisor = $wpdb->prefix . 'aslsupervisor';
    $posts_per_page = 20;
    $args   = array(
        'post_type'     => 'job',
        'paged'         => $paged,
        'posts_per_page'=> $posts_per_page,
    );

    $query = new WP_Query( $args );

    if( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();

            $jobID          = get_the_ID();
            $our_ref        = get_field('our_ref');
            $customer       = get_field('customer');
            $phan_loai      = get_field('phan_loai');
            $partner_1      = get_field('partner_1');
            $partner_2      = get_field('partner_2');
            $foreign_partner = get_field('foreign_partner');
            $member         = get_field('member');
            $manager        = get_field('manager');
            $tags_obj       = get_the_tags();
            $tagname_arr    = array();
            if ($tags_obj) {
                foreach ($tags_obj as $key => $value) {
                    $tagname_arr[] = $value->name;
                }
            }
            # get field cash in
            $total_value    = get_field('total_value');
            $paid           = get_field('paid');
            $remainning     = get_field('remainning');
            $currency       = get_field('currency');
            # get field cash out
            $total_cost     = get_field('total_cost');
            $advance_money  = get_field('advance_money');
            $debt           = get_field('debt');
            $currency_out   = get_field('currency_out');
            $payment_status = get_field('payment_status');

            if (get_field('contract_sign_date')) {
                $tmp = DateTime::createFromFormat('d/m/Y', get_field('contract_sign_date'));
                $contract_sign_date = $tmp->format('Y-m-d H:i:s');
            } else $contract_sign_date = "";

            # save history to export
            $work_list  = get_field('lich_su_cong_viec');
            if ($work_list) {
                foreach ($work_list as $key => $value) {
                    // $work_history[] = $value['mo_ta'];
                    if ($value['ngay_thang']) {
                        $tmp = DateTime::createFromFormat('d/m/Y', $value['ngay_thang']);
                        $ngay_thang = $tmp->format('Y-m-d H:i:s');
                    } else $ngay_thang = "";
                    $data_arr = [
                        'jobid' => $jobID,
                        'name'  => $value['mo_ta'],
                        'date'  => $ngay_thang
                    ];

                    $wpdb->insert(
                        $aslHistory,
                        $data_arr
                    );
                }
            }

            # save supervisor to export
            $data_supervisor = get_field('supervisor');
            if ( $data_supervisor ) {
                $supervisors = explode("|", $data_supervisor);
                if(!empty($supervisors)){
                    foreach ($supervisors as $supervisor) {
                        $data_arr = [
                            'jobid' => $jobID,
                            'supervisorid' => $supervisor
                        ];

                        $wpdb->insert(
                            $aslSupervisor,
                            $data_arr
                        );
                    }
                }
            }

            $brand = array();
            $agency = get_the_terms(get_the_ID(), 'agency');
            foreach ($agency as $id_chi_nhanh) {
                $term = get_term($id_chi_nhanh);

                $brand[] = $term->name;
            }

            if (is_array($brand)) {
                $agency_hn = in_array('ha-noi', $brand)?1:0;
                $agency_hcm = in_array('ho-chi-minh', $brand)?1:0;
            }

            $date = DateTime::createFromFormat('m/d/Y', get_the_date('m/d/Y'));
            
            $job = [
                'id'        => get_the_ID(),
                'title'     => get_the_title(),
                'type'      => $phan_loai,
                'our_ref'   => $our_ref,
                'customerid'        => $customer->ID,
                'first_partnerid'   => $partner_1['ID'],
                'partnerid'         => $partner_2['ID'],
                'partner_out_id'    => $foreign_partner['ID'],
                'memberid'      => $member['ID'],
                'managerid'     => $manager['ID'],
                'currency'      => $currency,
                'total_value'   => $total_value,
                'paid'          => $work_list,
                'remainning'    => $remainning,
                'total_cost'    => $total_cost,
                'currency_out'  => $currency_out,
                'advance_money' => $advance_money,
                'debt'          => $debt,
                'payment_status'=> $payment_status,
                'source'        => implode(",", $tagname_arr),
                'date'          => $date->format('Y-m-d H:i:s'),
                'contract_sign_date' => $contract_sign_date,
                'agency_hn'     => $agency_hn,
                'agency_hcm'    => $agency_hcm
            ];

            $sent = $wpdb->insert(
                $aslTable,
                $job
            );
        } 
        wp_reset_postdata();
    }
}

function export_job($paged) {
    $posts_per_page = 20;

    $args   = array(
        'post_type'     => 'job',
        'paged'         => $paged,
        'posts_per_page'=> $posts_per_page,
    );

    $query = new WP_Query( $args );

    $action     = 'insertMany';
    $api_url    = 'https://ap-southeast-1.aws.data.mongodb-api.com/app/' . MONGO_CLIENT_APPID . '/endpoint/data/v1/action/' . $action;
    $mongo_collection = 'asl_job';

    $list_job = array();

    if( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();

            $our_ref        = get_field('our_ref');
            $customer       = get_field('customer');
            $phan_loai      = get_field('phan_loai');
            $partner_2      = get_field('partner_2');
            $member         = get_field('member');
            $manager        = get_field('manager');
            $tags_obj       = get_the_tags();
            $tagname_arr    = array();
            if ($tags_obj) {
                foreach ($tags_obj as $key => $value) {
                    $tagname_arr[] = $value->name;
                }
            }
            $total_value = get_field('total_value');
            $currency = get_field('currency');

            $work_list  = get_field('lich_su_cong_viec');
            $work_history = array();
            if ($work_list) {
                foreach ($work_list as $key => $value) {
                    $work_history[] = $value['mo_ta'];
                }
            }

            $brand = array();
            $agency = get_the_terms(get_the_ID(), 'agency');
            foreach ($agency as $id_chi_nhanh) {
                $term = get_term($id_chi_nhanh);

                $brand[] = $term->name;
            }
            $date = DateTime::createFromFormat('m/d/Y', get_the_date('m/d/Y'));
            
            $job = (object)[
                'jobid'     => get_the_ID(),
                'title'     => get_the_title(),
                'type'      => $phan_loai,
                'our_ref'   => $our_ref,
                'customerid'=> $customer->ID,
                'partnerid' => $partner_2['ID'],
                'memberid'  => $member['ID'],
                'managerid' => $manager['ID'],
                // 'tags'      => $tagname_arr,
                'total_value' => $total_value,
                'currency'  => $currency,
                'work_list' => $work_list,
                // 'agency'    => $brand,
                'date'      => gmdate('Y-m-d', $date->getTimestamp())
            ];

            foreach ($tagname_arr as $tagname) {
                $key = 'tag_' . $tagname;
                $job->$key = true;
            }
            
            foreach ($brand as $agency) {
                $key = 'agency_' . $agency;
                $job->$key = true;
            }
            
            $list_job[] = $job;
        } 
        wp_reset_postdata();
    }

    $documents = [
        'documents' => $list_job
    ];

    if (!empty($list_job)) {
        $sent = mongo_api($api_url, $mongo_collection, $documents);
    }

    return $sent;
}

function export_mysql_task($paged) {
    global $wpdb;

    $aslTable = $wpdb->prefix . 'asltask';
    $aslHistory = $wpdb->prefix . 'asltaskhistory';
    $posts_per_page = 20;

    $args   = array(
        'post_type'     => 'task',
        'paged'         => $paged,
        'posts_per_page'=> $posts_per_page,
    );

    $query = new WP_Query( $args );
    
    if( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            
            $taskid     = get_the_ID();
            $jobID      = get_field('job');
            $user_arr   = get_field('user');
            $manager    = get_field('manager');
            // print_r($user_arr);
            $create     = DateTime::createFromFormat('m/d/Y', get_the_date('m/d/Y'));
            $deadline   = DateTime::createFromFormat('d/m/Y', get_field('deadline'));
            if (get_field('time_to_response')) {
                $tmp = DateTime::createFromFormat('d/m/Y', get_field('time_to_response'));
                $time_to_response = $tmp->format('Y-m-d H:i:s');
            } else $time_to_response = "";
            $trang_thai = get_field('trang_thai');
            $miss_deadline = get_field('miss_deadline')?get_field('miss_deadline'):0;

            $task = [
                'id'        => $taskid,
                'title'     => get_the_title(),
                'jobid'     => $jobID,
                'memberid'  => $user_arr["ID"],
                'managerid' => $manager["ID"],
                'status'    => $trang_thai,
                'deadline'  => $deadline->format('Y-m-d H:i:s'),
                'time_to_response' => $time_to_response,
                'miss_deadline'    => $miss_deadline,
                'date'      => $create->format('Y-m-d H:i:s'),
            ];
                
            $sent = $wpdb->insert(
                $aslTable,
                $task
            );

            # history task export
            if (have_rows('history')) {
                while (have_rows('history')) {
                    the_row();

                    $thoi_gian = DateTime::createFromFormat('d/m/Y', get_sub_field('thoi_gian'));
                    $iduser = get_sub_field('nguoi_thuc_hien');

                    $history_arr = [
                        'taskid'    => $taskid,
                        'content'   => get_sub_field('noi_dung'),
                        'userid'    => $iduser,
                        'date'      => $thoi_gian->format('Y-m-d H:i:s')
                    ];

                    $sent = $wpdb->insert(
                        $aslHistory,
                        $history_arr
                    );
                }
            }
        } 
        wp_reset_postdata();
    }
    return $sent;
}

function export_task($paged) {
    $posts_per_page = 20;

    $args   = array(
        'post_type'     => 'task',
        'paged'         => $paged,
        'posts_per_page'=> $posts_per_page,
    );

    $query = new WP_Query( $args );

    $action     = 'insertMany';
    $api_url    = 'https://ap-southeast-1.aws.data.mongodb-api.com/app/' . MONGO_CLIENT_APPID . '/endpoint/data/v1/action/' . $action;
    $mongo_collection = 'asl_task';

    $list_job = array();
    
    if( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            
            $jobID      = get_field('job');
            $user_arr   = get_field('user');
            $manager    = get_field('manager');
            // print_r($user_arr);
            $create     = DateTime::createFromFormat('m/d/Y', get_the_date('m/d/Y'));
            $deadline   = DateTime::createFromFormat('d/m/Y', get_field('deadline'));
            $trang_thai = get_field('trang_thai');

            $task = (object)[
                'taskid'    => get_the_ID(),
                'jobid'     => $jobID,
                'memberid'  => $user_arr->ID,
                'manager'   => $manager->ID,
                'status'    => $trang_thai,
                'create'    => $create,
                'deadline'  => $deadline,
            ];
                
            $list_task[] = $task;
        } 
        wp_reset_postdata();
    }

    $documents = [
        'documents' => $list_task
    ];

    if (!empty($list_task)) {
        $sent = mongo_api($api_url, $mongo_collection, $documents);
    }

    return $sent;
}
