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

    $table = $wpdb->prefix . $table;

    $data = $wpdb->get_results("SELECT $field FROM $table $where", ARRAY_A);

    return $data;
}


/* Hàm lấy danh sách cột của bảng */
function api_columns_table($params) {
    global $wpdb;
    $table = $params['table'];
    $table = $wpdb->prefix . $table;

    # get oly column name
    $data = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table'", ARRAY_A);

    # get name and put to array
    $data = array_column($data, 'COLUMN_NAME');

    return $data;
}