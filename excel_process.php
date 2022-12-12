<?php
/* 
    Template Name: Import partner to system from excel files
*/
if (
    isset($_POST['post_nonce_field']) &&
    wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')
) {

    $file_excel = $_FILES['file_excel'];
    $role       = $_POST['role'];

    require_once get_template_directory() . '/lib/PHPExcel.php';
    require_once get_template_directory() . '/lib/PHPExcel/Writer/Excel2007.php';

    $rowData = wp_reading_excel($file_excel['tmp_name']);
    //In dữ liệu ra file excel

}

get_header();
get_sidebar();
?>
<div class="content-body">
    <h1><?php the_title(); ?></h1>
    <br>
    <div class="row">
        <div class="col-12 col-lg-12 mb-20">
            <form action="" method="post" enctype="multipart/form-data" class="row">
                <div class="col-lg-3 form_title lh45 text-left text-lg-right">Phân loại</div>
                <div class="col-lg-5 col-12 mb-20">
                    <select name="role" class="form-control select2-tags mb-20">
                        <option value="partner">Đối tác gửi việc</option>
                        <option value="foreign_partner">Đối tác nhận việc</option>
                    </select>
                </div>
                <div class="col-lg-4 col-12"></div>

                <div class="col-lg-3 form_title lh45 text-left text-lg-right">File Excel</div>
                <div class="col-lg-5 col-12 mb-20">
                    <input type="file" name="file_excel" class="form-control">
                </div>
                <div class="col-lg-4 col-12"></div>

                <div class="col-lg-3 col-12">
                    <?php
                    wp_nonce_field('post_nonce', 'post_nonce_field');
                    ?>
                </div>
                <div class="col-lg-9 col-12">
                    <input type="submit" value="Import" class="button button-primary">
                </div>
            </form>
            <?php
            if ($rowData) {
                # remove the first item, that's the header.
                $removed = array_shift($rowData);
                // print_r($rowData);

                # default password for user.
                $user_pass      = 'd1412@pass';
                foreach ($rowData as $new_partner) {
                    if ($new_partner[5]) {
                        # if it have an email, then ...
                        $partner_code   = $new_partner[1];
                        $first_name     = $new_partner[2];
                        $last_name      = $new_partner[3];
                        $display_name   = $first_name . " " . $last_name;

                        # check if partner code is exist, then just update custom field.
                        $partner_id = search_partner($partner_code);
                        if ($partner_id) {
                            $args = array(
                                'ID'            => $partner_id,
                                'first_name'    => $first_name,
                                'last_name'     => $last_name,
                                'display_name'  => $display_name,
                                'description'   => $new_partner[13],
                            );

                            $new_partner_id = wp_update_user($args);

                            echo "<br>Đã cập nhật $new_partner_id thành công: " . $display_name . " - " . $new_partner[4];
                        } else {
                            # Create new user
                            $args = array(
                                'user_login'    => $partner_code,
                                'user_email'    => $new_partner[5],
                                'user_pass'     => $user_pass,
                                'first_name'    => $first_name,
                                'last_name'     => $last_name,
                                'display_name'  => $display_name,
                                'description'   => $new_partner[13],
                                'role'          => $role,
                            );

                            $new_partner_id = wp_insert_user($args);

                            echo "<br>Đã tạo đối tác mới $new_partner_id thành công: " . $display_name . " - " . $new_partner[4];
                        }
                        if ($new_partner_id) {
                            # update custom fields
                            update_field('field_607a4fb37b7e0', $partner_code, 'user_' . $new_partner_id); # user_code
                            update_field('field_600d31f4060eb', $new_partner[4], 'user_' . $new_partner_id); # company_name
                            update_field('field_600d3211060ec', $new_partner[6], 'user_' . $new_partner_id); # phone number
                            update_field('field_600d323d060ee', $new_partner[7], 'user_' . $new_partner_id); # address
                            update_field('field_6037200ec98cc', $new_partner[8], 'user_' . $new_partner_id); # country
                            update_field('field_6039b28e2ba07', $new_partner[9], 'user_' . $new_partner_id); # email cc
                            update_field('field_609a038489e8c', $new_partner[10], 'user_' . $new_partner_id); # email bcc
                            update_field('field_60a3cbacb1330', $new_partner[11], 'user_' . $new_partner_id); # type_of_client
                            update_field('field_6010f85bfcf55', $new_partner[12], 'user_' . $new_partner_id); # link_onedrive
                        }
                    } else {
                        echo "<br>Đã xảy ra lỗi: ";
                        print_r($new_partner);
                    }
                }
            }
            ?>
        </div>

        <div class="col-12 col-lg-12 mb-20">
            <h3>Mẫu file excel</h3>
            <style>
                .excel_form {
                    text-align: center;
                }

                .excel_form th,
                .excel_form td {
                    text-align: center;
                    padding: 5px 15px;
                }
            </style>
            <table border="1" class="excel_form">
                <tr>
                    <th> </th>
                    <th>A</th>
                    <th>B</th>
                    <th>C</th>
                    <th>D</th>
                    <th>E</th>
                    <th>F</th>
                    <th>G</th>
                    <th>H</th>
                    <th>I</th>
                    <th>J</th>
                    <th>K</th>
                    <th>L</th>
                    <th>M</th>
                    <th>N</th>
                </tr>
                <tr>
                    <th>1</th>
                    <td>STT</td>
                    <td>Mã đối tác</td>
                    <td>Họ</td>
                    <td>Tên</td>
                    <td>Tên đối tác</td>
                    <td>Email</td>
                    <td>Số điện thoại</td>
                    <td>Địa chỉ</td>
                    <td>Quốc gia</td>
                    <td>Email CC</td>
                    <td>Email BCC</td>
                    <td>Phân loại</td>
                    <td>Link hồ sơ</td>
                    <td>Ghi chú</td>
                </tr>
                <tr>
                    <th>2</th>
                    <td>1</td>
                    <td>SBL</td>
                    <td>Pham</td>
                    <td>Duy Khuong</td>
                    <td>SB LAW</td>
                    <td>khuong.pham@sblaw.vn</td>
                    <td>0973292669</td>
                    <td>Hanoi</td>
                    <td>Vietnam</td>
                    <td></td>
                    <td></td>
                    <td>IP Agent</td>
                    <td></td>
                    <td>Không</td>
                </tr>
                <tr>
                    <th>3</th>
                    <td>2</td>
                    <td>...</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </table>

            <br>
            <h3>Hướng dẫn cập nhật qua file excel</h3>
            <ol>
                <li>Tạo file excel đúng theo mẫu trên</li>
                <li>Trường Mã đối tác và email là bắt buộc phải có, các trường khác có thể để trống</li>
                <li>Chọn đúng loại vai trò cho đối tác và tải file excel lên và bấm Import</li>
                <li>Dữ liệu nào chưa có sẽ được tạo mới, dữ liệu đã có sẽ được cập nhật.</li>
            </ol>
        </div>
    </div>

</div>
<?php

get_footer();
