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
                                <h4>Chuyển đầu việc "<?php echo get_the_title($jobid); ?>" sang hệ thống gia hạn</h4>
                                <br>
                            </div>

                            <div class="col-lg-2 form_title lh45">Năm gia hạn</div>
                            <div class="col-lg-5 col-12 mb-20">
                                <select class="form-control select2-tags mb-20" name="renewal_year">
                                    <option value="">-- Chọn số năm hết hạn --</option>
                                    <option value="1">1 năm</option>
                                    <option value="3">3 năm</option>
                                    <option value="5">5 năm</option>
                                    <option value="10">10 năm</option>
                                </select>
                            </div>
                            <input type="hidden" name="jobid" value="<?php echo $_GET['jobid']; ?>">
                            <input type="hidden" name="history_link" value="<?php echo $history_link; ?>">

                            <?php
                            wp_nonce_field('post_nonce', 'post_nonce_field');
                            ?>

                            <div class="col-lg-5"></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="submit" class="button button-primary" value="Chuyển tới trang gia hạn"></div>

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
                                    $customer_custom_fields = additional_field($customer_custom_fields, 'Người đại diện', $partner_user->display_name);
                                    $customer_custom_fields = additional_field($customer_custom_fields, 'Mã đối tác', $partner_code);
                                    $customer_custom_fields = additional_field($customer_custom_fields, 'Số điện thoại', $so_dien_thoai);
                                    $customer_custom_fields = additional_field($customer_custom_fields, 'Địa chỉ', $dia_chi);
                                    $customer_custom_fields = additional_field($customer_custom_fields, 'Quốc gia', $quoc_gia);

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
                                    $renewal_custom_fields = additional_field($renewal_custom_fields, 'Phân loại', $phan_loai);
                                    $renewal_custom_fields = additional_field($renewal_custom_fields, 'Số đơn', $so_don);
                                    $renewal_custom_fields = additional_field($renewal_custom_fields, 'Ngày nộp đơn', $ngay_nop_don);
                                    $renewal_custom_fields = additional_field($renewal_custom_fields, 'Số bằng', $so_bang);
                                    $renewal_custom_fields = additional_field($renewal_custom_fields, 'Ngày cấp bằng', $ngay_cap_bang);
                                    $renewal_custom_fields = additional_field($renewal_custom_fields, 'Số REF của đối tác', $partner_ref);
                                    $renewal_custom_fields = additional_field($renewal_custom_fields, 'Số REF của mình', $our_ref);
                                    $renewal_custom_fields = additional_field($renewal_custom_fields, 'Quốc gia nộp', $country);
                                    $renewal_custom_fields = additional_field($renewal_custom_fields, 'Link tài liệu', $link_onedrive);

                                    switch ($phan_loai) {
                                        case 'Nhãn hiệu':
                                            $logo           = get_field('logo', $jobid);
                                            $ten_nhan_hieu  = get_field('ten_nhan_hieu', $jobid);
                                            $nhom  = get_field('nhom', $jobid);
                                            $so_luong_nhom  = get_field('so_luong_nhom', $jobid);
                                            $renewal_custom_fields = additional_field($renewal_custom_fields, 'Logo', $logo);
                                            $renewal_custom_fields = additional_field($renewal_custom_fields, 'Tên nhãn hiệu', $ten_nhan_hieu);
                                            $renewal_custom_fields = additional_field($renewal_custom_fields, 'Nhóm', $nhom);
                                            $renewal_custom_fields = additional_field($renewal_custom_fields, 'Số lượng nhóm', $so_luong_nhom);
                                            break;

                                        case 'Sáng chế':
                                            $ban_mo_ta_sang_che  = get_field('ban_mo_ta_sang_che', $jobid);
                                            $so_luong_yeu_cau_bao_ho  = get_field('so_luong_yeu_cau_bao_ho', $jobid);
                                            $so_luong_yeu_cau_bao_ho_doc_lap  = get_field('so_luong_yeu_cau_bao_ho_doc_lap', $jobid);
                                            $renewal_custom_fields = additional_field($renewal_custom_fields, 'Bản mô tả sáng chế', $ban_mo_ta_sang_che);
                                            $renewal_custom_fields = additional_field($renewal_custom_fields, 'Số lượng yêu cầu bảo hộ', $so_luong_yeu_cau_bao_ho);
                                            $renewal_custom_fields = additional_field($renewal_custom_fields, 'Số lượng yêu cầu bảo hộ độc lập', $so_luong_yeu_cau_bao_ho_doc_lap);
                                            break;

                                        case 'Kiểu dáng':
                                            $bo_anh  = get_field('bo_anh', $jobid);
                                            $ban_mo_ta_cua_bo_anh  = get_field('ban_mo_ta_cua_bo_anh', $jobid);
                                            $so_luong_phuong_an  = get_field('so_luong_phuong_an', $jobid);
                                            $renewal_custom_fields = additional_field($renewal_custom_fields, 'Bộ ảnh', $bo_anh);
                                            $renewal_custom_fields = additional_field($renewal_custom_fields, 'Bản mô tả của bộ ảnh', $ban_mo_ta_cua_bo_anh);
                                            $renewal_custom_fields = additional_field($renewal_custom_fields, 'Số lượng phương án', $so_luong_phuong_an);
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
                                        echo "Không tạo mới/cập nhật được bài viết.<br>";
                                        // print_r($response);
                                    } else {

                                        if (!$api_id) {
                                            $api_id = $response_body->id;

                                            # update api id 
                                            update_field('field_614319e51e117', $api_id, $jobid);
                                            $result = "<p>Đã tạo đơn gia hạn mới trên hệ thống gia hạn.</p>";
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
                                                $result = "<p>Đã cập nhật thành công đơn gia hạn này trên hệ thống gia hạn.</p>";
                                            }
                                        }
                                    }
                                    echo $result;
                                }
                            } else {
                                echo "Không thể kết nối tới server.";
                            }
                            echo '<br><a href="' . $history_link . '" class="button button-primary">Quay lại</a>';
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
