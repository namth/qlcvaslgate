<?php
/*
    Template Name: Export Excel
*/
if (
    isset($_POST['post_nonce_field']) &&
    wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')
) {

    require_once get_template_directory() . '/lib/PHPExcel.php';
    require_once get_template_directory() . '/lib/PHPExcel/Writer/Excel2007.php';

    $objPHPExcel = new PHPExcel();

    $objPHPExcel->getProperties()->setCreator("QLCV")
        ->setLastModifiedBy("QLCV")
        ->setTitle("PHPExcel Test Document")
        ->setSubject("PHPExcel Test Subject")
        ->setDescription("Test document for PHPExcel, generated using PHP classes.")
        ->setKeywords("office PHPExcel php")
        ->setCategory("Test result file");

    # get data from the form
    $data_type      = $_POST['data_type'];
    $time_option    = $_POST['time_option'];
    $group          = $_POST['group'];

    if ($time_option) {
        $date_value = explode(' - ', $_POST['timestamp']);
        $date_1 = date('Ymd', strtotime($date_value[0]));
        $date_2 = date('Ymd', strtotime($date_value[1]));
    }

    switch ($data_type) {
        case 'job':
            $args   = array(
                'post_type'      => $data_type,
                'posts_per_page' => '-1',
            );
            if ($date_1 && $date_2) {
                $args['meta_query'][] = array(
                    array(
                        'key'       => 'deadline',
                        'compare'   => 'BETWEEN',
                        'type'      => 'DATE',
                        'value'     => array($date_1, $date_2),
                    ),
                );
            }
            if ($group) {
                $args['tax_query'] = array(
                    'relation' => 'AND',
                    array(
                        'taxonomy'  => 'group',
                        'field'     => 'slug',
                        'terms'     => $group,
                    ),
                );
            }

            $query = new WP_Query($args);

            $i = 1;
            $filename = "qlcv_list_job.xlsx";
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'Mã đầu việc')
                ->setCellValue('B1', 'Ngày tháng')
                ->setCellValue('C1', 'Tên đầu việc')
                ->setCellValue('D1', 'Khách hàng')
                ->setCellValue('E1', 'Đối tác')
                ->setCellValue('F1', 'Phân loại')
                ->setCellValue('G1', 'Nguồn')
                ->setCellValue('H1', 'Số đơn')
                ->setCellValue('I1', 'Ngày nộp đơn')
                ->setCellValue('J1', 'Số bằng')
                ->setCellValue('K1', 'Ngày cấp bằng')
                ->setCellValue('L1', 'Lưu ý')
                ->setCellValue('M1', 'Deadline')
                ->setCellValue('N1', 'Trạng thái');

            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();

                    $i++;
                    $our_ref        = get_field('our_ref');
                    $so_don         = get_field('so_don');
                    $ngay_nop_don   = get_field('ngay_nop_don');
                    $so_bang        = get_field('so_bang');
                    $ngay_cap_bang  = get_field('ngay_cap_bang');
                    $mindful        = strip_tags(get_field('mindful'));
                    $phan_loai      = get_field('phan_loai');
                    $deadline       = get_field('deadline');
                    $trang_thai     = get_field('trang_thai');

                    $customer       = get_field('customer');
                    $c_business     = get_field('ten_cong_ty', $customer->ID);
                    $partner        = get_field('partner_2');
                    $p_business     = get_field('ten_cong_ty', 'user_' . $partner->ID);

                    $tags_obj   = get_the_tags();
                    $tagname_arr = array();
                    if (is_array($tags_obj) || is_object($tags_obj)) {
                        foreach ($tags_obj as $key => $value) {
                            $tagname_arr[] = $value->name;
                        }
                    }
                    $tagname = implode(', ', $tagname_arr);


                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $i, $our_ref)
                        ->setCellValue('B' . $i, get_the_date('d/m/Y'))
                        ->setCellValue('C' . $i, get_the_title())
                        ->setCellValue('D' . $i, $c_business)
                        ->setCellValue('E' . $i, $p_business)
                        ->setCellValue('F' . $i, $phan_loai)
                        ->setCellValue('G' . $i, $tagname)
                        ->setCellValue('H' . $i, $so_don)
                        ->setCellValue('I' . $i, $ngay_nop_don)
                        ->setCellValue('J' . $i, $so_bang)
                        ->setCellValue('K' . $i, $ngay_cap_bang)
                        ->setCellValue('L' . $i, $mindful)
                        ->setCellValue('M' . $i, $deadline)
                        ->setCellValue('N' . $i, $trang_thai);
                }
                wp_reset_postdata();
            }

            break;

        case 'task':
            $args   = array(
                'post_type'      => $data_type,
                'posts_per_page' => '-1',
            );
            if ($date_1 && $date_2) {
                $args['meta_query'][] = array(
                    array(
                        'key'       => 'deadline',
                        'compare'   => 'BETWEEN',
                        'type'      => 'DATE',
                        'value'     => array($date_1, $date_2),
                    ),
                );
            }

            $i = 1;
            $filename = "qlcv_list_task.xlsx";
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'STT')
                ->setCellValue('B1', 'Ngày tháng')
                ->setCellValue('C1', 'Tên nhiệm vụ')
                ->setCellValue('D1', 'Công việc')
                ->setCellValue('E1', 'Người quản lý')
                ->setCellValue('F1', 'Người thực hiện')
                ->setCellValue('G1', 'Deadline')
                ->setCellValue('H1', 'Trạng thái');

            $query = new WP_Query($args);
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();

                    $i++;
                    $jobID = get_field('job');
                    $user_arr = get_field('user');
                    $manager = get_field('manager');
                    // print_r($user_arr);
                    $deadline = get_field('deadline');
                    $trang_thai = get_field('trang_thai');

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $i, ($i - 1))
                        ->setCellValue('B' . $i, get_the_date('d/m/Y'))
                        ->setCellValue('C' . $i, get_the_title())
                        ->setCellValue('D' . $i, get_the_title($jobID))
                        ->setCellValue('E' . $i, $manager['nickname'] . " (" . $manager['user_email'] . ")")
                        ->setCellValue('F' . $i, $user_arr['nickname'] . " (" . $user_arr['user_email'] . ")")
                        ->setCellValue('G' . $i, $deadline)
                        ->setCellValue('H' . $i, $trang_thai);
                }
                wp_reset_postdata();
            }

            break;

        case 'customer':
            $args   = array(
                'post_type'      => $data_type,
                'posts_per_page' => '-1',
            );
            if ($date_1 && $date_2) {
                $args['date_query'] = array(
                    array(
                        'after'     => $date_1,
                        'before'    => $date_2,
                        'inclusive' => true,
                    ),
                );
            }

            $i = 1;
            $filename = "qlcv_list_customer.xlsx";
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'STT')
                ->setCellValue('B1', 'Ngày tháng')
                ->setCellValue('C1', 'Tên người liên hệ')
                ->setCellValue('D1', 'Tên khách hàng')
                ->setCellValue('E1', 'Số điện thoại')
                ->setCellValue('F1', 'Email')
                ->setCellValue('G1', 'Địa chỉ')
                ->setCellValue('H1', 'Quốc gia');

            $query = new WP_Query($args);
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();

                    $i++;
                    $so_dien_thoai = get_field('so_dien_thoai');
                    $email = get_field('email');
                    $cong_ty = get_field('ten_cong_ty');
                    $dia_chi = get_field('dia_chi');
                    $quoc_gia = get_field('quoc_gia');

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $i, ($i - 1))
                        ->setCellValue('B' . $i, get_the_date('d/m/Y'))
                        ->setCellValue('C' . $i, get_the_title())
                        ->setCellValue('D' . $i, $cong_ty)
                        ->setCellValue('E' . $i, $so_dien_thoai)
                        ->setCellValue('F' . $i, $email)
                        ->setCellValue('G' . $i, $dia_chi)
                        ->setCellValue('H' . $i, $quoc_gia);
                }
                wp_reset_postdata();
            }

            break;

        default:
            $count_args  = array(
                'role'      => $data_type,
                'number'    => 999999,
                'date_query' => array(
                    array(
                        'after'     => $date_1,
                        'before'    => $date_2,
                        'inclusive' => true,
                    ),
                ),

            );
            $user_query = new WP_User_Query($count_args);
            $users = $user_query->get_results();

            $i = 1;
            $filename = "qlcv_list_user.xlsx";
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'Ngày tạo')
                ->setCellValue('B1', 'Mã đối tác')
                ->setCellValue('C1', 'Tên đối tác')
                ->setCellValue('D1', 'Tên người liên hệ')
                ->setCellValue('E1', 'Số điện thoại')
                ->setCellValue('F1', 'Email')
                ->setCellValue('G1', 'Địa chỉ')
                ->setCellValue('H1', 'Quốc gia')
                ->setCellValue('I1', 'Tên khách hàng');

            if (!empty($users)) {
                foreach ($users as $user) {

                    $i++;
                    $so_dien_thoai  = get_field('so_dien_thoai', 'user_' . $user->ID);
                    $partner_code   = get_field('partner_code', 'user_' . $user->ID);
                    $ten_cong_ty    = get_field('ten_cong_ty', 'user_' . $user->ID);
                    $dia_chi        = get_field('dia_chi', 'user_' . $user->ID);
                    $quoc_gia       = get_field('quoc_gia', 'user_' . $user->ID);
                    $registered     = $user->user_registered;

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $i, date( "d/m/Y", strtotime( $registered ) ))
                        ->setCellValue('B' . $i, $partner_code)
                        ->setCellValue('C' . $i, $ten_cong_ty)
                        ->setCellValue('D' . $i, $user->display_name)
                        ->setCellValue('E' . $i, $so_dien_thoai)
                        ->setCellValue('F' . $i, $user->user_email)
                        ->setCellValue('G' . $i, $dia_chi)
                        ->setCellValue('H' . $i, $quoc_gia)
                        ->setCellValue('I' . $i, $user->description);
                }
                wp_reset_postdata();
            }

            break;
    }

    // Redirect output to a client’s web browser (Excel2007)
    PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
    PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit;
}

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
                    <div>
                        <form action="#" method="POST" class="row">
                            <div class="col-lg-3 form_title lh45"><?php _e('Loại dữ liệu', 'qlcv'); ?></div>
                            <div class="col-lg-6 col-12 mb-20">
                                <select class="form-control select2-tags mb-20" name="data_type">
                                    <?php
                                    $data_arr = array(
                                        'job'               => 'Công việc',
                                        'task'              => 'Nhiệm vụ',
                                        'partner'           => 'Đối tác gửi việc',
                                        'foreign_partner'   => 'Đối tác nhận việc',
                                        'customer'          => 'Khách hàng',
                                        'member'            => 'Nhân sự',
                                        'contributor'        => 'Cấp quản lý',
                                    );
                                    foreach ($data_arr as $key => $value) {
                                        $selected = ($data_type == $key) ? "selected" : "";
                                        echo "<option value='" . $key . "' " . $selected . ">" . $value . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45"><?php _e('Nhóm công việc', 'qlcv'); ?></div>
                            <div class="col-lg-6 col-12 mb-20">
                                <select class="form-control mb-20" name="group">
                                    <option value=""><?php _e('Tất cả', 'qlcv'); ?></option>
                                    <?php
                                        $terms = get_terms(array(
                                            'taxonomy' => 'group',
                                            'hide_empty' => false,
                                        ));
                                        foreach ($terms as $key => $value) {
                                            $selected = ($group == $value->slug) ? "selected" : "";
                                            echo "<option value='" . $value->slug . "' " . $selected . ">" . $value->name . "</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="col-lg-3"></div>

                            <div class="col-lg-3 form_title lh45"><?php _e('Khoảng thời gian: ', 'qlcv'); ?></div>
                            <div class="col-lg-6 col-12 mb-20">
                                <div class="lh45">
                                    <label for="" class="inline">
                                        <input type="radio" name="time_option" value="0" checked> <?php _e('Toàn thời gian', 'qlcv'); ?>
                                    </label>
                                    <label for="" class="inline">
                                        <input type="radio" name="time_option" value="1"> <?php _e('Thời gian tuỳ chỉnh', 'qlcv'); ?>
                                    </label>
                                </div>
                                <div id="timestamp" style="display: none;">
                                    <input type="text" class="form-control input-date-predefined" name="timestamp">
                                </div>
                            </div>
                            <div class="col-lg-3"></div>

                            <?php
                            wp_nonce_field('post_nonce', 'post_nonce_field');
                            ?>

                            <div class="col-lg-3"></div>
                            <div class="col-lg-6 col-12 mb-20"><input type="submit" class="button button-primary" value="Download"></div>

                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 mb-30">
            <?php
            $data_type      = $_POST['data_type'];
            $time_option    = $_POST['time_option'];

            $args   = array(
                'post_type'      => $data_type,
                'posts_per_page' => '-1',
            );
            if ($date_1 && $date_2) {
                $args['meta_query'][] = array(
                    array(
                        'key'       => 'deadline',
                        'compare'   => 'BETWEEN',
                        'type'      => 'DATE',
                        'value'     => array($date_1, $date_2),
                    ),
                );
            }

            $query = new WP_Query($args);

            $i = 1;

            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();

                    $i++;

                    $tags_obj   = get_the_tags();
                    $tagname_arr = array();
                    if (is_array($tags_obj) || is_object($tags_obj)) {
                        foreach ($tags_obj as $key => $value) {
                            $tagname_arr[] = $value->name;
                        }
                    }
                    $tagname = implode(', ', $tagname_arr);

                    echo $tagname;
                }
            }
            ?>
        </div>

    </div><!-- Page Headings End -->

</div><!-- Content Body End -->

<?php
get_footer();
?>