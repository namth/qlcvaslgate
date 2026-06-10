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
    global $wpdb;

    $asl_data_type  = $_POST['asl_data_type'];
    // $mongo_method   = $_POST['mongo_method'];
    // $start_page     = $_POST['start_page'];

    switch ($asl_data_type) {
        case 'customer':
            $posts_per_page = 20;
        
            $args   = array(
                'post_type'     => 'customer',
                'paged'         => 1,
                'posts_per_page'=> $posts_per_page,
            );

            $query = new WP_Query( $args );
            $total_page = $query->max_num_pages;
            $function   = 'insert_customer';
            # delete all data before insert
            $aslTable = 'wp_aslcustomer';
            delete_mysql_table($aslTable);
            break;

        case 'partner':
            $posts_per_page = 20;
    
            $count_args  = array(
                'role__in'  => ['partner', 'foreign_partner'],
                'number'    => 999999,
            );
            $user_count_query = new WP_User_Query($count_args);
            $partner_number = $user_count_query->get_results();

            $total_page = ceil(count($partner_number)/$posts_per_page);
            $function   = 'insert_partner';
            # delete all data before insert
            // $aslTable = 'wp_aslpartner';
            // delete_mysql_table($aslTable);
            break;

        case 'member':
            $posts_per_page = 20;
    
            $count_args  = array(
                'role__in'  => ['contributor', 'administrator', 'member', 'law_manager', 'ip_manager'],
                'number'    => 999999,
            );
            $user_count_query = new WP_User_Query($count_args);
            $member_number = $user_count_query->get_results();

            $total_page = ceil(count($member_number)/$posts_per_page);
            $function   = 'insert_member';
            # delete all data before insert
            $aslTable = 'wp_aslmember';
            delete_mysql_table($aslTable);
            break;

        case 'job':
            $posts_per_page = 20;
    
            $args   = array(
                'post_type'     => 'job',
                'paged'         => 1,
                'posts_per_page'=> $posts_per_page,
            );

            $query = new WP_Query( $args );
            $total_page = $query->max_num_pages;
            $function   = 'insert_job';
            # delete all data before insert
            $aslTable = 'wp_asljob';
            delete_mysql_table($aslTable);
            $aslTable = 'wp_asljobhistory';
            delete_mysql_table($aslTable);
            $aslTable = 'wp_aslsupervisor';
            delete_mysql_table($aslTable);
            $aslTable = 'wp_asljobgroup';
            delete_mysql_table($aslTable);
            $aslTable = 'wp_asljobcountry';
            delete_mysql_table($aslTable);
            $aslTable = 'wp_asljobtodocument';
            delete_mysql_table($aslTable);
            break;
        
        case 'task':
            $posts_per_page = 20;
    
            $args   = array(
                'post_type'     => 'task',
                'paged'         => 1,
                'posts_per_page'=> $posts_per_page,
            );

            $query = new WP_Query( $args );
            $total_page = $query->max_num_pages;
            $function   = 'insert_task';
            # delete all data before insert
            $aslTable = 'wp_asltask';
            delete_mysql_table($aslTable);
            $aslTable = 'wp_asltaskhistory';
            delete_mysql_table($aslTable);
            break;
        
        case 'job_document':
            $posts_per_page = 20;
    
            $args   = array(
                'post_type'     => 'job',
                'paged'         => 1,
                'posts_per_page'=> $posts_per_page,
            );

            $query = new WP_Query( $args );
            $total_page = $query->max_num_pages;
            $function   = 'insert_job_document';
            # delete all data before insert
            $aslTable = 'wp_asljobtodocument';
            delete_mysql_table($aslTable);
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
        'function'      => $function,
        'data_table'    => $asl_data_type
    ]);
    exit;
}

# delete all data from table before insert new records
function delete_mysql_table($tableName){
    global $wpdb;
    $delete = $wpdb->query("TRUNCATE TABLE $tableName");
    return $delete;
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
        
        case 'insert_job_document':
            $export = export_mysql_job_document($current_page);
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

    $aslTable = 'wp_aslcustomer';
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

            $customer = [
                'customerid'            => get_the_ID(),
                'name'          => $name,
                'companyName'   => $cong_ty ?? '',
                'country'       => $quoc_gia ?? '',
                'phone'         => $so_dien_thoai ?? '',
                'email'         => $email ?? '',
                'date'          => get_the_date('Y-m-d H:i:s'),
            ];
            // print_r($customer);

            $wpdb->replace(
                $aslTable,
                $customer
            );
        } 
        wp_reset_postdata();
    }

    // return true;
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

    $aslTable = 'wp_aslpartner';
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
            $mst            = get_field('field_688ef666d820a', 'user_' . $user->ID);
            $nguoi_dai_dien_phap_luat = get_field('field_688ef675d820b', 'user_' . $user->ID);
            $chuc_vu        = get_field('field_688ef6b4d820c', 'user_' . $user->ID);
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
            $role_partner__in = $role_partner__out = 0;
            if (!empty($user->roles) && is_array($user->roles)) {
                $role_partner__in = in_array('partner', $user->roles)?1:0;
                $role_partner__out = in_array('foreign_partner', $user->roles)?1:0;
            }

            $partner =[
                'partnerid'     => $user->ID,
                'name'          => $user->display_name,
                'partner_code'  => $partner_code,
                'companyName'   => $ten_cong_ty,
                'mst'           => $mst,
                'nguoi_dai_dien_phap_luat' => $nguoi_dai_dien_phap_luat,
                'chuc_vu'       => $chuc_vu,
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

            $insert = $wpdb->replace(
                $aslTable,
                $partner
            );
        }
    }
    // return $insert;
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

    $aslTable = 'wp_aslmember';

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
                'memberid'      => $user->ID,
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

            $wpdb->replace(
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

    $aslTable = 'wp_asljob';
    $aslHistory = 'wp_asljobhistory';
    $aslSupervisor = 'wp_aslsupervisor';
    $aslGroup = 'wp_asljobgroup';
    $aslCountry = 'wp_asljobcountry';
    $aslJobDocument = 'wp_asljobtodocument';
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

            # check partner isset?
            if (is_array($foreign_partner)) {
                $foreign_partner_id = $foreign_partner['ID'];
            } else $foreign_partner_id = NULL;

            if (is_array($partner_1)) {
                $partner_1_id = $partner_1['ID'];
            } else $partner_1_id = NULL;

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
                $tz = new DateTimeZone('Asia/Ho_Chi_Minh');
                $tmp = DateTime::createFromFormat('d/m/Y', get_field('contract_sign_date'), $tz);
                $contract_sign_date = $tmp->format('Y-m-d H:i:s');
            } else $contract_sign_date = NULL;

            # save history to export
            $work_list  = get_field('lich_su_cong_viec');
            if ($work_list) {
                foreach ($work_list as $key => $value) {
                    if (preg_match("/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}$/", $value['ngay_thang'])) {
                        $tz = new DateTimeZone('Asia/Ho_Chi_Minh');
                        $tmp = DateTime::createFromFormat('d/m/Y', $value['ngay_thang'], $tz);
                        $ngay_thang = $tmp->format('Y-m-d H:i:s');
                    } else $ngay_thang = NULL;
                    $clean_name = strip_tags(str_replace(array('<br>', '<br/>', '<br />'), ' ', $value['mo_ta']));
                    $data_arr = [
                        'jobid' => $jobID,
                        'name'  => $clean_name,
                        'date'  => $ngay_thang
                    ];

                    $wpdb->replace(
                        $aslHistory,
                        $data_arr
                    );
                }
            }

            # save country to export
            $country    = get_field('country');
            $list_country = explode(',', $country);
            foreach ($list_country as $key => $value) {
                $data_arr   = [
                    'jobid'         => $jobID,
                    'customerid'    => $customer->ID ?? 0,
                    'partnerid'     => $partner_2['ID'] ?? 0,
                    'memberid'      => $member['ID'] ?? 0,
                    'managerid'     => $manager['ID'] ?? 0,
                    'country'   => trim($value) ?? '',
                    'date'      => get_the_date('Y-m-d H:i:s'),
                ];

                $wpdb->replace(
                    $aslCountry,
                    $data_arr
                );
            }

            # save member to export (Người thực hiện)
            if ( $member && isset($member['ID']) && $member['ID'] ) {
                $data_arr = [
                    'jobid'        => $jobID,
                    'supervisorid' => $member['ID'],
                    'name'         => 'Người thực hiện'
                ];
                $wpdb->replace(
                    $aslSupervisor,
                    $data_arr
                );
            }
            # save manager to export (Người quản lý)
            if ( $manager && isset($manager['ID']) && $manager['ID'] ) {
                $data_arr = [
                    'jobid'        => $jobID,
                    'supervisorid' => $manager['ID'],
                    'name'         => 'Người quản lý'
                ];
                $wpdb->replace(
                    $aslSupervisor,
                    $data_arr
                );
            }
            # save supervisor to export
            $data_supervisor = get_field('supervisor');
            if ( $data_supervisor ) {
                $supervisors = explode("|", $data_supervisor);
                if(!empty($supervisors)){
                    foreach ($supervisors as $supervisor) {
                        $data_arr = [
                            'jobid' => $jobID,
                            'supervisorid' => $supervisor,
                            'name'  => 'Người giám sát'
                        ];

                        $wpdb->replace(
                            $aslSupervisor,
                            $data_arr
                        );
                    }
                }
            }
            # save co_manager to export
            $data_co_manager = get_field('co_manager');
            if ( $data_co_manager ) {
                $co_managers = explode("|", $data_co_manager);
                if(!empty($co_managers)){
                    foreach ($co_managers as $co_manager) {
                        $data_arr = [
                            'jobid' => $jobID,
                            'supervisorid' => $co_manager,
                            'name'  => 'Người đồng quản lý'
                        ];

                        $wpdb->replace(
                            $aslSupervisor,
                            $data_arr
                        );
                    }
                }
            }
            # save co_member to export
            $data_co_member = get_field('co_member');
            if ( $data_co_member ) {
                $co_members = explode("|", $data_co_member);
                if(!empty($co_members)){
                    foreach ($co_members as $co_member) {
                        $data_arr = [
                            'jobid' => $jobID,
                            'supervisorid' => $co_member,
                            'name'  => 'Người đồng thực hiện'
                        ];

                        $wpdb->replace(
                            $aslSupervisor,
                            $data_arr
                        );
                    }
                }
            }

            # group to export
            $groups = get_the_terms(get_the_ID(), 'group');
            $data_arr = [
                'jobid' => $jobID,
                'customerid'        => $customer->ID ?? 0,
                'partnerid'         => $partner_2['ID'] ?? 0,
                'memberid'          => $member['ID'] ?? 0,
                'managerid'         => $manager['ID'] ?? 0,
                'date'  => get_the_date('Y-m-d H:i:s'),
            ];

            $i = 0;
            $list_ip = ['ban-quyen', 'sang-che', 'kieu-dang', 'nhan-hieu'];
            $potential = "";
            
            if ($groups && !is_wp_error($groups)) {
                foreach ($groups as $group) {
                    $groupname = $group->name?$group->name:"";

                    # if $groupname has value, next process
                    if ($groupname) {
                        # if job is potential, set type
                        if ($group->slug == 'tiem-nang') {
                            $data_arr['flag'] = $group->name;
                        } else {
                            # set groupname to $data_arr['groupname']
                            $i++;
                            if ($i==1) {
                                $data_arr['groupname'] = $group->name;
                                $data_arr['flag'] = "Đã chốt";
                                if (in_array($group->slug, $list_ip)) {
                                    $data_arr['type'] = "IP";
                                } else $data_arr['type'] = "Law";
                            } else {
                                if ($group->slug != 'viec-khac') {
                                    $data_arr['groupname'] = $group->name;
                                }
                            }
                        }    

                        # get all child of potential category, compare each ID with $term->term_id, if match, set $data_arr['potential']
                        $all_child = get_term_children(11, 'group');
                        if (in_array($group->term_id, $all_child)) {
                            $potential = $groupname;
                        }
                        
                    }    
                }
            }

            # insert to database
            $wpdb->replace(
                $aslGroup,
                $data_arr
            );
            

            $brand = array();
            $agency = get_the_terms(get_the_ID(), 'agency');
            if ($agency && !is_wp_error($agency)) {
                foreach ($agency as $id_chi_nhanh) {
                    $term = get_term($id_chi_nhanh);
                    $brand[] = $term->slug;
                }
            }

            if (is_array($brand)) {
                $agency_hn = in_array('ha-noi', $brand)?1:0;
                $agency_hcm = in_array('ho-chi-minh', $brand)?1:0;
            }

            # if $data_arr['type'] = 'Law' and $phan_loai is empty, set $phan_loai = 'Việc khác'
            if ($data_arr['type'] == 'Law' && empty($phan_loai)) {
                $phan_loai = 'Việc khác';
            }

            $logo           = get_field('logo', $jobID);
            $ten_nhan_hieu  = get_field('ten_nhan_hieu', $jobID);
            $nhom           = get_field('nhom', $jobID);
            $so_luong_nhom  = get_field('so_luong_nhom', $jobID);
            $our_ref        = get_field('our_ref', $jobID);
            $so_don         = get_field('so_don', $jobID);
            $ngay_nop_don   = get_field('ngay_nop_don', $jobID);
            $formatted_ngay_nop_don = asl_format_date_to_dmy($ngay_nop_don);
            $trademark_color = get_field('trademark_color', $jobID);
            $service_category = get_field('service_category', $jobID);
            $invoice_number = get_field('invoice_number', $jobID);
            
            $so_dien_thoai  = get_field('so_dien_thoai' , 'user_' . $partner_2['ID']);
            $dia_chi        = get_field('dia_chi' , 'user_' . $partner_2['ID']);
            $quoc_gia       = get_field('quoc_gia' , 'user_' . $partner_2['ID']);
            $email_cc       = get_field('email_cc' , 'user_' . $partner_2['ID']);
            $email_bcc      = get_field('email_bcc' , 'user_' . $partner_2['ID']);
            $partner_code   = get_field('partner_code' , 'user_' . $partner_2['ID']);
            $ten_cong_ty    = get_field('ten_cong_ty' , 'user_' . $partner_2['ID']);
            $city           = get_field('city' , 'user_' . $partner_2['ID']);
            $mst            = get_field('mst' , 'user_' . $partner_2['ID']);
            $nguoi_dai_dien_phap_luat = get_field('nguoi_dai_dien_phap_luat' , 'user_' . $partner_2['ID']);
            $chuc_vu        = get_field('chuc_vu' , 'user_' . $partner_2['ID']);

            $jobdoc = [
                'jobid'     => $jobID,
                'job_title' => get_the_title(),
                'our_ref'   => $our_ref,
                'trademark_txt' => $ten_nhan_hieu,
                'trademark_img' => $logo,
                'trademark_class' => $nhom,
                'trademark_totalclass' => $so_luong_nhom,
                'trademark_fillingid' => $so_don,
                'trademark_fillingdate' => $formatted_ngay_nop_don,
                'trademark_color' => $trademark_color,
                'service_category' => $service_category,
                'invoice_number' => $invoice_number,
                'partner_name' => $partner_2['display_name'] ?? '',
                'partner_code' => $partner_code ?? '',
                'partner_companyName' => $ten_cong_ty ?? '',
                'partner_country' => $quoc_gia ?? '',
                'partner_address' => $dia_chi ?? '',
                'partner_city' => $city ?? '',
                'partner_phone' => $so_dien_thoai ?? '',
                'partner_email' => $partner_2['user_email'] ?? '',
                'partner_email_cc' => $email_cc ?? '',
                'partner_email_bcc' => $email_bcc ?? '',
                'partner_tax_number' => $mst ?? '',
                'partner_legal_representative' => $nguoi_dai_dien_phap_luat ?? '',
                'partner_position' => $chuc_vu ?? '',
                'customer_name' => get_the_title($customer->ID) ?? '',
                'customer_companyName' => get_field('ten_cong_ty', $customer->ID) ?? '',
                'customer_address' => get_field('dia_chi', $customer->ID) ?? ''
            ];

            $wpdb->replace(
                $aslJobDocument,
                $jobdoc
            );
            
            $level = get_field('level', $jobID);

            $job = [
                'jobid'             => get_the_ID(),
                'customerid'        => $customer->ID ?? 0,
                'first_partnerid'   => $partner_1_id ?? NULL,
                'partnerid'         => $partner_2['ID'] ?? 0,
                'partner_out_id'    => $foreign_partner_id ?? NULL,
                'memberid'          => $member['ID'] ?? 0,
                'managerid'         => $manager['ID'] ?? 0,
                'title'         => get_the_title() ?? '',
                'type'          => $phan_loai ?? '',
                'type_group'    => $data_arr['type'] ?? '',
                'flag'          => $data_arr['flag'] ?? '',
                'potential'     => $potential ?? '',
                'our_ref'       => $our_ref ?? '',
                'currency'      => $currency ?? '',
                'total_value'   => $total_value ?? 0,
                'paid'          => $paid ?? 0,
                'remainning'    => $remainning ?? 0,
                'total_cost'    => $total_cost ?? 0,
                'currency_out'  => $currency_out ?? '',
                'advance_money' => $advance_money ?? 0,
                'debt'          => $debt ?? 0,
                'payment_status'=> $payment_status ?? '',
                'source'        => implode(",", $tagname_arr),
                'date'          => get_the_date('Y-m-d H:i:s'),
                'contract_sign_date' => $contract_sign_date ?? NULL,
                'agency_hn'     => $agency_hn ?? 0,
                'agency_hcm'    => $agency_hcm ?? 0,
                'level'         => $level ?? '',
                'trademark_color' => get_field('trademark_color', $jobID) ?: '',
                'service_category' => get_field('service_category', $jobID) ?: '',
                'invoice_number' => get_field('invoice_number', $jobID) ?: ''
            ];

            $sent = $wpdb->replace(
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

    $aslTable = 'wp_asltask';
    $aslHistory = 'wp_asltaskhistory';
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
            $customer   = get_field('customer', $jobID);
            $partner_2  = get_field('partner_2', $jobID);

            $deadline   = DateTime::createFromFormat('d/m/Y', get_field('deadline'));
            if (get_field('time_to_response')) {
                $tmp = DateTime::createFromFormat('d/m/Y', get_field('time_to_response'));
                $time_to_response = $tmp->format('Y-m-d H:i:s');
            } else $time_to_response = "";
            $trang_thai = get_field('trang_thai');
            $miss_deadline = get_field('miss_deadline')?get_field('miss_deadline'):0;

            $task = [
                'taskid'    => $taskid,
                'title'     => get_the_title() ?? '',
                'jobid'     => $jobID ?? 0,
                'memberid'  => $user_arr["ID"] ?? 0,
                'managerid' => $manager["ID"] ?? 0,
                'customerid'=> $customer->ID ?? 0,
                'partnerid' => $partner_2['ID'] ?? 0,
                'status'    => $trang_thai ?? '',
                'deadline'  => ($deadline instanceof DateTime) ? $deadline->format('Y-m-d H:i:s') : '',
                'time_to_response'  => $time_to_response ?? '',
                'miss_deadline'     => $miss_deadline ?? 0,
                'date'              => get_the_date('Y-m-d H:i:s'),
            ];
                
            $sent = $wpdb->replace(
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

                    $sent = $wpdb->replace(
                        $aslHistory,
                        $history_arr
                    );
                }
            }
        } 
        wp_reset_postdata();
    }
    // return $sent;
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


/* 
*  Update data to DB
*/
add_action('wp_ajax_setup_page_number', 'setup_page_number');
function setup_page_number(){
    # connect to database
    global $wpdb;
    $limit = 20;
    $aslTable = 'wp_aslpartner';

    $count_sql  = "SELECT COUNT(*) FROM $aslTable";
    $rowcount   = $wpdb->get_var($count_sql);
    $total_page = ceil($rowcount / $limit);

    echo $total_page;
    exit;
}

/* 
* Update data with page number
*/
add_action('wp_ajax_update_data_with_page', 'update_data_with_page');
function update_data_with_page(){
    # connect to database
    global $wpdb;

    $aslTable = 'wp_aslpartner';
    
    $limit = 20;
    $page = $_POST['page'];
    $total_page = $_POST['total_page'];
    // $prev_jobid = $_POST['prev_jobid'];
    // $tmp_countries = explode(', ', $_POST['countries']);
    
    $offset  = ($page - 1) * $limit;
    $logid = "";

    $sql     = "SELECT * 
                FROM $aslTable
                ORDER BY `partnerid` ASC
                LIMIT %d
                OFFSET %d";
    
    $data = $wpdb->get_results($wpdb->prepare($sql, $limit, $offset), ARRAY_A);

    if (!empty($data)) {
        foreach ($data as $key => $value) {
            /* $jobid = $value['jobid'];
            
            # update data to custom field by jobid
            $currency       = $value['currency'];
            $total_value    = $value['total_value'];
            $paid           = $value['paid'];
            $remainning     = $value['remainning'];
            $total_cost     = $value['total_cost'];
            $currency_out   = $value['currency_out'];
            $advance_money  = $value['advance_money'];
            $debt           = $value['debt'];
            
            update_field('field_60a231d395dd8', $total_value, $jobid);
            update_field('field_60a231d395f2e', $paid, $jobid);
            update_field('field_60a231d3961b0', $remainning, $jobid);
            update_field('field_60a231d39602e', $currency, $jobid);
            
            update_field('field_60afae64cfd69', $total_cost, $jobid);
            update_field('field_60afaeb8cfd6a', $advance_money, $jobid);
            update_field('field_60afaf50cfd6b', $debt, $jobid);
            update_field('field_60afafbccfd6c', $currency_out, $jobid); */
            
            /* $jobid = $value['jobid'];
            $country  = $value['country'];
            
            # if not same jobid and prev_jobid and it is the last item of foreach, update country to job
            if ($prev_jobid != $jobid) {
                # update country to job
                $countries = implode(", ", $tmp_countries);
                update_field('field_6099f6bb87256', $countries, $prev_jobid);
                
                # add country to array
                $tmp_countries = array($country);
                $prev_jobid = $jobid;
            } else {
                $tmp_countries[] = $country;
            } */
            
            /* # update country to customer
            $country  = $value['country'];
            $customerid = $value['customerid'];
            update_field('field_6037200ec98cc', $country, $customerid); */

            $country  = $value['country'];
            $partnerid = $value['partnerid'];

            update_field('field_6037200ec98cc', $country, 'user_' . $partnerid);
        }
    }

    echo json_encode([
        'status'        => 'success',
        'current_page'  => ++$page,
        'total_page'    => $total_page,
        // 'prev_jobid'    => $prev_jobid,
        // 'countries'     => implode(", ", $tmp_countries),
        'result'        => $logid
    ]);

    exit;
}
function export_mysql_job_document($paged) {
    global $wpdb;

    $aslJobDocument = 'wp_asljobtodocument';
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
            $partner_2      = get_field('partner_2');

            $logo           = get_field('logo', $jobID);
            $ten_nhan_hieu  = get_field('ten_nhan_hieu', $jobID);
            $nhom           = get_field('nhom', $jobID);
            $so_luong_nhom  = get_field('so_luong_nhom', $jobID);
            $so_don         = get_field('so_don', $jobID);
            $ngay_nop_don   = get_field('ngay_nop_don', $jobID);
            $formatted_ngay_nop_don = asl_format_date_to_dmy($ngay_nop_don);
            $trademark_color = get_field('trademark_color', $jobID);
            $service_category = get_field('service_category', $jobID);
            $invoice_number = get_field('invoice_number', $jobID);

            $so_dien_thoai  = get_field('so_dien_thoai' , 'user_' . $partner_2['ID']);
            $dia_chi        = get_field('dia_chi' , 'user_' . $partner_2['ID']);
            $quoc_gia       = get_field('quoc_gia' , 'user_' . $partner_2['ID']);
            $email_cc       = get_field('email_cc' , 'user_' . $partner_2['ID']);
            $email_bcc      = get_field('email_bcc' , 'user_' . $partner_2['ID']);
            $partner_code   = get_field('partner_code' , 'user_' . $partner_2['ID']);
            $ten_cong_ty    = get_field('ten_cong_ty' , 'user_' . $partner_2['ID']);
            $city           = get_field('city' , 'user_' . $partner_2['ID']);
            $mst            = get_field('mst' , 'user_' . $partner_2['ID']);
            $nguoi_dai_dien_phap_luat = get_field('nguoi_dai_dien_phap_luat' , 'user_' . $partner_2['ID']);
            $chuc_vu        = get_field('chuc_vu' , 'user_' . $partner_2['ID']);

            $jobdoc = [
                'jobid'     => $jobID,
                'job_title' => get_the_title(),
                'our_ref'   => $our_ref,
                'trademark_txt' => $ten_nhan_hieu,
                'trademark_img' => $logo,
                'trademark_class' => $nhom,
                'trademark_totalclass' => $so_luong_nhom,
                'trademark_fillingid' => $so_don,
                'trademark_fillingdate' => $formatted_ngay_nop_don,
                'trademark_color' => $trademark_color,
                'service_category' => $service_category,
                'invoice_number' => $invoice_number,
                'partner_name' => $partner_2['display_name'] ?? '',
                'partner_code' => $partner_code ?? '',
                'partner_companyName' => $ten_cong_ty ?? '',
                'partner_country' => $quoc_gia ?? '',
                'partner_address' => $dia_chi ?? '',
                'partner_city' => $city ?? '',
                'partner_phone' => $so_dien_thoai ?? '',
                'partner_email' => $partner_2['user_email'] ?? '',
                'partner_email_cc' => $email_cc ?? '',
                'partner_email_bcc' => $email_bcc ?? '',
                'partner_tax_number' => $mst ?? '',
                'partner_legal_representative' => $nguoi_dai_dien_phap_luat ?? '',
                'partner_position' => $chuc_vu ?? '',
                'customer_name' => get_the_title($customer->ID) ?? '',
                'customer_companyName' => get_field('ten_cong_ty', $customer->ID) ?? '',
                'customer_address' => get_field('dia_chi', $customer->ID) ?? ''
            ];

            $wpdb->replace(
                $aslJobDocument,
                $jobdoc
            );
        } 
        wp_reset_postdata();
    }
}
