<?php 
add_action('rest_api_init', function (){
    # get access token
    register_rest_route('qlcv/v1', 'gettoken', array(
        'methods'   => 'POST',
        'callback'  => 'api_get_token',
    ));
    # check token
    register_rest_route('qlcv/v1', 'checktoken', array(
        'methods'   => 'POST',
        'callback'  => 'api_check_token',
        'permission_callback' => 'check_access',
    ));
    # get list column of table
    register_rest_route('qlcv/v1', 'columns/(?P<table>\w+)', array(
        'methods'   => 'GET',
        'callback'  => 'api_columns_table',
        'permission_callback' => 'check_access',
    ));

    # get data from table with table name and list field name (separate by comma)
    # route: /wp-json/qlcv/v1/asldata/{table}
    register_rest_route('qlcv/v1', 'asldata/(?P<table>\w+)', array(
        'methods'   => 'GET',
        'callback'  => 'api_data_table',
        'permission_callback' => 'check_access',
    ));

    # get data from table with POST method
    # route: /wp-json/qlcv/v1/aslpostdata
    register_rest_route('qlcv/v1', 'aslpostdata', array(
        'methods'   => 'POST',
        'callback'  => 'api_post_data_table',
        'permission_callback' => 'check_access',
    ));
});

/* 
*  Hàm lấy mã token, lấy nonce của tên người dùng và pass đã mã hoá, sau đó sử dụng hash sha256
*/
function api_get_token(){
    $username = $_POST['username'];
    $password = $_POST['password'];
    $check = wp_authenticate_username_password( NULL, $username, $password );

    if($check->ID){
        $randomCode = wp_create_nonce($username .'|'. $check->data->user_pass);
        $data['token'] = hash('sha256', $randomCode);
        // $data['user'] = $check;
    } else {
        $data['error_code'] = 401;
        $data['message']    = "Không tạo được token.";
    }
    return $data;
}

/* Kiểm tra token, nếu qua bước check_access thì return thành công luôn
*  Hàm này sử dụng để kiểm tra nhanh token có sử dụng hợp lệ hay không, nếu không thì lấy lại token mới.
*/
function api_check_token() {
    return array(
        'code'      => 'success',
        'message'   => 'Kiểm tra token hợp lệ.',
    );
}

/* Hàm kiểm tra header truyền token vào có hợp lệ hay không */
function check_access(WP_REST_Request $request){
    $token  = $request->get_header('Authorization');
    $username = 'qlcv';
    $user_obj = get_user_by('login', $username);
    $randomCode = wp_create_nonce($username.'|'.$user_obj->data->user_pass);
    $datatoken = hash('sha256', $randomCode);

    if ($token === $datatoken) {
        return true;
    }
    return false;
}

/* Hàm lấy dữ liệu từ bảng */
function api_data_table($params) {
    global $wpdb;
    $table = $params['table'];

    # if have not field then get all
    if (isset($params['field']) && $params['field']) {
        $field = $params['field'];
    } else {
        $field = "*";
    }

    # if have where params then get where
    if (isset($params['where']) && $params['where']) {
        $where = " WHERE " . $params['where'];
    } else {
        $where = "";
    }

    # if have order params then get order
    # order = field ASC|DESC
    # orderby = field
    if (isset($params['orderby']) && $params['orderby']) {
        $order = " ORDER BY " . $params['orderby'];
        if (isset($params['order']) && $params['order']) {
            $order .= " " . $params['order'];
        }
    } else {
        $order = "";
    }

    # if have limit params then get limit
    if (isset($params['limit']) && $params['limit']) {
        $limit = " LIMIT " . $params['limit'];
    } else {
        $limit = "";
    }

    # if have offset params then get offset
    if (isset($params['offset']) && $params['offset']) {
        $limit .= " OFFSET " . $params['offset'];
    }

    // $table = 'wp_' . $table;

    $data = $wpdb->get_results("SELECT $field FROM $table $where $order $limit", ARRAY_A);

    return $data;
}

/* Hàm lấy dữ liệu từ bảng qua POST method */
function api_post_data_table(WP_REST_Request $request) {
    global $wpdb;
    
    # get table name from POST data
    $table = $request->get_param('table');
    if (!$table) {
        return new WP_Error('missing_table', 'Tên bảng là bắt buộc.', array('status' => 400));
    }

    # if have not field then get all
    $field = $request->get_param('field');
    if (!$field) {
        $field = "*";
    }

    # check if search param exists, prioritize search over where
    $search_param = $request->get_param('search');
    if ($search_param) {
        # get searchable columns (text-based columns only)
        $columns = $wpdb->get_results($wpdb->prepare("
            SELECT COLUMN_NAME 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = %s
            AND DATA_TYPE IN ('varchar', 'text', 'longtext', 'mediumtext', 'tinytext', 'char')
        ", $table));
        
        if (!empty($columns)) {
            $conditions = [];
            foreach($columns as $col) {
                $conditions[] = $col->COLUMN_NAME . " LIKE '%" . esc_sql($search_param) . "%'";
            }
            $where = " WHERE " . implode(' OR ', $conditions);
        } else {
            $where = "";
        }
    } else {
        # if have where params then get where
        $where_param = $request->get_param('where');
        if ($where_param) {
            $where = " WHERE " . $where_param;
        } else {
            $where = "";
        }
    }

    # if have order params then get order
    # order = field ASC|DESC
    # orderby = field
    $orderby_param = $request->get_param('orderby');
    if ($orderby_param) {
        $order = " ORDER BY " . $orderby_param;
        $order_param = $request->get_param('order');
        if ($order_param) {
            $order .= " " . $order_param;
        }
    } else {
        $order = "";
    }

    # if have limit params then get limit
    $limit_param = $request->get_param('limit');
    if ($limit_param) {
        $limit = " LIMIT " . $limit_param;
    } else {
        $limit = "";
    }

    # if have offset params then get offset
    $offset_param = $request->get_param('offset');
    if ($offset_param) {
        $limit .= " OFFSET " . $offset_param;
    }

    // $table = 'wp_' . $table;

    $data = $wpdb->get_results("SELECT $field FROM $table $where $order $limit", ARRAY_A);

    return $data;
}

/* Hàm lấy danh sách cột của bảng */
function api_columns_table($params) {
    global $wpdb;
    $table = $params['table'];
    // $table = 'wp_' . $table;

    # get oly column name
    $data = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table'");

    # get name and put to array
    $data = array_column($data, 'COLUMN_NAME');

    # if have not field then get all
    if (isset($params['field']) && $params['field']) {
        $fields = $params['field'];
        $list_field = explode(',', $fields);

        # get duplicate item in 2 array and put to new array
        $data = array_intersect($data, $list_field);
    } 

    return $data;
}

/* Hàm thêm dữ liệu vào bảng */
// function api_post_data_table($params) {
//     global $wpdb;
//     $table = $params['table'];

//     # get data
//     $data = $params['data'];

//     # insert data
//     $result = $wpdb->insert( $table, $data );

//     return $result;
// }