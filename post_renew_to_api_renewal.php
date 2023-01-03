<?php
/*
    Template Name: POST renewal to renewal system by API
*/
$history_link   = $_SERVER['HTTP_REFERER'];

if (isset($_GET['jobid']) && ($_GET['jobid'] != '')) {
    $jobid = $_GET['jobid'];

    get_header();

    get_sidebar();
?>

    <!-- Content Body Start -->
    <div class="content-body">

        <!-- Page Headings Start -->
        <div class="row justify-content-between align-items-center mb-10">

            <!-- Page Heading Start -->
            <div class="col-12 col-lg-12 mb-20">
                <div class="page-heading">
                    <?php
                    echo '<h3 class="title">' . get_the_title() . '</h3>';
                    ?>
                </div>
            </div><!-- Page Heading End -->

            <div class="col-12 mb-30">
                <div class="box">
                    <div class="box-body">
                        <form action="" method="POST" class="row">
                            <div class="col-12">
                                <h4><?php _e('Chuyển đầu việc', 'qlcv'); ?> "<?php echo get_the_title($jobid); ?>" <?php _e('sang hệ thống gia hạn', 'qlcv'); ?></h4>
                                <br>
                            </div>

                            <div class="col-lg-2 form_title lh45"><?php _e('Năm gia hạn', 'qlcv'); ?></div>
                            <div class="col-lg-5 col-12 mb-20">
                                <select class="form-control select2-tags mb-20" name="renewal_year">
                                    <option value="">-- <?php _e('Chọn số năm hết hạn', 'qlcv'); ?> --</option>
                                    <option value="1"><?php _e('1 năm', 'qlcv'); ?></option>
                                    <option value="3"><?php _e('3 năm', 'qlcv'); ?></option>
                                    <option value="5"><?php _e('5 năm', 'qlcv'); ?></option>
                                    <option value="10"><?php _e('10 năm', 'qlcv'); ?></option>
                                </select>
                            </div>
                            <input type="hidden" name="jobid" value="<?php echo $_GET['jobid']; ?>">
                            <input type="hidden" name="history_link" value="<?php echo $history_link; ?>">

                            <?php
                            wp_nonce_field('post_nonce', 'post_nonce_field');
                            ?>

                            <div class="col-lg-5"></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="submit" class="button button-primary" value="<?php _e('Chuyển tới trang gia hạn', 'qlcv'); ?>"></div>

                        </form>
                        <?php
                        if (
                            isset($_POST['post_nonce_field']) &&
                            wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')
                        ) {
                            $api_url  = get_field('api_url', 'option');
                            $token = get_token();
                            $renewal_year = $_POST['renewal_year'];
                            $history_link = $_POST['history_link'];


                            // print_r($token);

                            if ($token) {
                                # get partner in job
                                $partner        = get_field('partner_2', $jobid);
                                $partner_api_id = get_field('api_id', 'user_' . $partner["ID"]);

                                # if partner exist in renewal system, don't update. If not, create new partner to that
                                if (!$partner_api_id) {
                                    $uid =  $partner["ID"];
                                    $partner_user = get_user_by('ID', $uid);

                                    $so_dien_thoai  = get_field('so_dien_thoai', 'user_' . $uid);
                                    $dia_chi        = get_field('dia_chi', 'user_' . $uid);
                                    $quoc_gia       = get_field('quoc_gia', 'user_' . $uid);
                                    $email_cc       = get_field('email_cc', 'user_' . $uid);
                                    $email_bcc      = get_field('email_bcc', 'user_' . $uid);
                                    $partner_code   = get_field('partner_code', 'user_' . $uid);
                                    $ten_cong_ty    = get_field('ten_cong_ty', 'user_' . $uid);
                                    // $api_id         = get_field('api_id', 'user_' . $uid);

                                    $customer = array(
                                        'title'     => $ten_cong_ty,
                                        'content'   => $partner_user->description,
                                        'status'    => 'publish',
                                    );

                                    $customer_custom_fields = array(
                                        'fields' => array(
                                            'email'     => $partner_user->user_email,
                                            'email_cc'  => $email_cc,
                                            'email_cc'  => $email_bcc,
                                        ),
                                    );
                                    $customer_custom_fields = additional_field($customer_custom_fields, __('Người đại diện', 'qlcv'), $partner_user->display_name);
                                    $customer_custom_fields = additional_field($customer_custom_fields, __('Mã đối tác', 'qlcv'), $partner_code);
                                    $customer_custom_fields = additional_field($customer_custom_fields, __('Số điện thoại', 'qlcv'), $so_dien_thoai);
                                    $customer_custom_fields = additional_field($customer_custom_fields, __('Địa chỉ', 'qlcv'), $dia_chi);
                                    $customer_custom_fields = additional_field($customer_custom_fields, __('Quốc gia', 'qlcv'), $quoc_gia);

                                    $partner_api_id = send_customer_api($token, $customer, $customer_custom_fields, $api_id, $uid);
                                }

                                if ($renewal_year == '1') {
                                    $renewal_year = '+ ' . $renewal_year . ' year';
                                } else {
                                    $renewal_year = '+ ' . $renewal_year . ' years';
                                }
                                $job_id = $_POST['jobid'];

                                # create post renewal
                                $phan_loai      = get_field('phan_loai', $jobid);
                                $so_don         = get_field('so_don', $jobid);
                                $ngay_nop_don   = get_field('ngay_nop_don', $jobid);
                                $so_bang        = get_field('so_bang', $jobid);
                                $ngay_cap_bang  = get_field('ngay_cap_bang', $jobid);
                                $partner_ref    = get_field('partner_ref', $jobid);
                                $our_ref        = get_field('our_ref', $jobid);
                                $country        = get_field('country', $jobid);
                                $link_onedrive  = get_field('link_onedrive', $jobid);
                                $mindful        = get_field('mindful', $jobid);
                                $api_id         = get_field('api_id', $jobid);

                                if (!$ngay_cap_bang) {
                                    echo "Công việc này chưa có ngày cấp bằng, hãy kiểm tra lại. <br>";
                                } else {
                                    $tmp = DateTime::createFromFormat('d/m/Y', $ngay_cap_bang);
                                    $renewal_date = strtotime($tmp->format('d-m-Y'));
                                    $expiration_date = strtotime($renewal_year, $renewal_date);

                                    $renewal_post = array(
                                        'title'     => get_the_title($jobid),
                                        'content'   => $mindful,
                                        'status'    => 'publish',
                                    );

                                    $renewal_custom_fields = array(
                                        'fields' => array(
                                            'renewal_date'      => $renewal_date,
                                            'expiration_date'   => $expiration_date,
                                            'customer'          => $partner_api_id,
                                            'status'            => 'Active',
                                        ),
                                    );
                                    $renewal_custom_fields = additional_field($renewal_custom_fields, __('Phân loại', 'qlcv'), $phan_loai);
                                    $renewal_custom_fields = additional_field($renewal_custom_fields, __('Số đơn', 'qlcv'), $so_don);
                                    $renewal_custom_fields = additional_field($renewal_custom_fields, __('Ngày nộp đơn', 'qlcv'), $ngay_nop_don);
                                    $renewal_custom_fields = additional_field($renewal_custom_fields, __('Số bằng', 'qlcv'), $so_bang);
                                    $renewal_custom_fields = additional_field($renewal_custom_fields, __('Ngày cấp bằng', 'qlcv'), $ngay_cap_bang);
                                    $renewal_custom_fields = additional_field($renewal_custom_fields, __('Số REF của đối tác', 'qlcv'), $partner_ref);
                                    $renewal_custom_fields = additional_field($renewal_custom_fields, __('Số REF của mình', 'qlcv'), $our_ref);
                                    $renewal_custom_fields = additional_field($renewal_custom_fields, __('Quốc gia nộp', 'qlcv'), $country);
                                    $renewal_custom_fields = additional_field($renewal_custom_fields, __('Link tài liệu', 'qlcv'), $link_onedrive);

                                    switch ($phan_loai) {
                                        case 'Nhãn hiệu':
                                            $logo           = get_field('logo', $jobid);
                                            $ten_nhan_hieu  = get_field('ten_nhan_hieu', $jobid);
                                            $nhom  = get_field('nhom', $jobid);
                                            $so_luong_nhom  = get_field('so_luong_nhom', $jobid);
                                            $renewal_custom_fields = additional_field($renewal_custom_fields, __('Logo', 'qlcv'), $logo);
                                            $renewal_custom_fields = additional_field($renewal_custom_fields, __('Tên nhãn hiệu', 'qlcv'), $ten_nhan_hieu);
                                            $renewal_custom_fields = additional_field($renewal_custom_fields, __('Nhóm', 'qlcv'), $nhom);
                                            $renewal_custom_fields = additional_field($renewal_custom_fields, __('Số lượng nhóm', 'qlcv'), $so_luong_nhom);
                                            break;

                                        case 'Sáng chế':
                                            $ban_mo_ta_sang_che  = get_field('ban_mo_ta_sang_che', $jobid);
                                            $so_luong_yeu_cau_bao_ho  = get_field('so_luong_yeu_cau_bao_ho', $jobid);
                                            $so_luong_yeu_cau_bao_ho_doc_lap  = get_field('so_luong_yeu_cau_bao_ho_doc_lap', $jobid);
                                            $renewal_custom_fields = additional_field($renewal_custom_fields, __('Bản mô tả sáng chế', 'qlcv'), $ban_mo_ta_sang_che);
                                            $renewal_custom_fields = additional_field($renewal_custom_fields, __('Số lượng yêu cầu bảo hộ', 'qlcv'), $so_luong_yeu_cau_bao_ho);
                                            $renewal_custom_fields = additional_field($renewal_custom_fields, __('Số lượng yêu cầu bảo hộ độc lập', 'qlcv'), $so_luong_yeu_cau_bao_ho_doc_lap);
                                            break;

                                        case 'Kiểu dáng':
                                            $bo_anh  = get_field('bo_anh', $jobid);
                                            $ban_mo_ta_cua_bo_anh  = get_field('ban_mo_ta_cua_bo_anh', $jobid);
                                            $so_luong_phuong_an  = get_field('so_luong_phuong_an', $jobid);
                                            $renewal_custom_fields = additional_field($renewal_custom_fields, __('Bộ ảnh', 'qlcv'), $bo_anh);
                                            $renewal_custom_fields = additional_field($renewal_custom_fields, __('Bản mô tả của bộ ảnh', 'qlcv'), $ban_mo_ta_cua_bo_anh);
                                            $renewal_custom_fields = additional_field($renewal_custom_fields, __('Số lượng phương án', 'qlcv'), $so_luong_phuong_an);
                                            break;

                                        default:
                                            break;
                                    }

                                    if (!$api_id) {
                                        $api_create_customer = $api_url . '/wp-json/wp/v2/renewal';
                                    } else {
                                        $api_create_customer = $api_url . '/wp-json/wp/v2/renewal/' . $api_id;
                                    }

                                    # update customer post or create new
                                    $args = array(
                                        'method'    => 'POST',
                                        'timeout'       => '45',
                                        'headers'   => array(
                                            'Content-Type'  => 'application/json; charset=utf-8',
                                            'Authorization' => 'Bearer ' . $token,
                                        ),
                                        'body'  => json_encode($renewal_post),
                                    );

                                    $response = wp_remote_post(
                                        $api_create_customer,
                                        $args
                                    );

                                    $response_body = json_decode(wp_remote_retrieve_body($response));
                                    if (!$response_body->id) {
                                        _e("Không tạo mới/cập nhật được bài viết.<br>", 'qlcv');
                                        // print_r($response);
                                    } else {

                                        if (!$api_id) {
                                            $api_id = $response_body->id;

                                            # update api id 
                                            update_field('field_614319e51e117', $api_id, $jobid);
                                            $result = __("<p>Đã tạo đơn gia hạn mới trên hệ thống gia hạn.</p>", 'qlcv');
                                            $new_partner = true;
                                        }

                                        # update acf fields to customer throught API
                                        if ($api_id) {
                                            $url_update_field = $api_url . '/wp-json/acf/v3/renewal/' . $api_id;
                                            $arg_custom_fields = array(
                                                'method'    => 'POST',
                                                'timeout'       => '45',
                                                'headers'   => array(
                                                    'Content-Type'  => 'application/json; charset=utf-8',
                                                    'Authorization' => 'Bearer ' . $token,
                                                ),
                                                'body'  => json_encode($renewal_custom_fields),
                                            );

                                            # call API to add custom fields
                                            $custom_fields_api = wp_remote_post(
                                                $url_update_field,
                                                $arg_custom_fields
                                            );

                                            if (!$new_partner) {
                                                $result = __("<p>Đã cập nhật thành công đơn gia hạn này trên hệ thống gia hạn.</p>", 'qlcv');
                                            }
                                        }
                                    }
                                    echo $result;
                                }
                            } else {
                                _e("Không thể kết nối tới server.", 'qlcv');
                            }
                            echo '<br><a href="' . $history_link . '" class="button button-primary">' . __('Quay lại', 'qlcv') . '</a>';
                        }
                        ?>
                    </div>

                </div>
            </div>


        </div><!-- Page Headings End -->

    </div><!-- Content Body End -->

<?php
    get_footer();
}
