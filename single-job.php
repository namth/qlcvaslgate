<?php
get_header();

get_sidebar();

while (have_posts()) {
    the_post();

    $current_user = wp_get_current_user();
    $partner = get_field('partner_2');
    # get all notification where the permalink match with destination in notif
    $args   = array(
        'post_type'     => 'notification',
        'posts_per_page' => '-1',
        'meta_query'    => array(
            'relation'      => 'AND',
            array(
                'key'       => 'destination',
                'compare'   => '=',
                'value'     => get_the_permalink(),
            ),
        ),
    );

    $query = new WP_Query($args);
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $receiver       = get_field('receiver');
            $admin_notif    = get_field('admin_new_notif');
            $manager_notif  = get_field('manager_new_notif');

            if ($admin_notif && in_array('administrator', $current_user->roles)) {
                $admin_notif = 0;
                update_field('field_607e4dfe7799a', $admin_notif);
            } else if (($manager_notif && ($receiver == $current_user->ID)) || !$receiver) {
                $manager_notif = 0;
                update_field('field_60857d1875727', $manager_notif);
            }
        }
        wp_reset_postdata();
    }

    $phan_loai  = get_field('phan_loai');
    $deadline   = get_field('deadline');
    $trang_thai = get_field('trang_thai');
    $link_onedrive = get_field('link_onedrive');

    $tags_obj   = get_the_tags();
    if ($tags_obj) {
        foreach ($tags_obj as $key => $value) {
            $tagname_arr[] = $value->name;
        }
        $tagname = implode(', ', $tagname_arr);
    } else {
        $tagname = __("Nguồn không xác định", 'qlcv');
    }

    if (isset($_GET['update']) && ($_GET['update'] == "Done")) {
        wp_remove_object_terms(get_the_ID(), 'tiem-nang', 'group');
        $current_time = new DateTime();

        # set contract signning date to job
        update_field('field_60ffc8f3d152b', $current_time->format('Ymd'));
        # chuyển đối tác sang trạng thái đã chốt
        update_field('field_61cd79bf1653f', 1, 'user_' . $partner['ID']);
    }
?>

    <!-- Content Body Start -->
    <div class="content-body">

        <!-- Page Headings Start -->
        <div class="row justify-content-between mb-10">

            <div class="col-12 col-lg-12 mb-20">
                <a href="<?php echo get_bloginfo('url'); ?>/danh-sach-cong-viec/"><?php _e('List công việc', 'qlcv'); ?></a> > <?php the_title(); ?>
            </div>
            <!-- Page Heading Start -->
            <div class="col-8 col-lg-8 mb-20">
                <div class="box">
                    <div class="page-heading box-head">
                        <h3 class="mb-10"><?php the_title(); ?> </h3>
                        <span class="badge badge-primary"><?php echo $phan_loai; ?></span>
                        <?php
                        if (in_array('administrator', $current_user->roles)) {
                        ?>
                            <span class="badge badge-secondary"><?php echo $tagname; ?></span>
                        <?php
                        }
                        ?>
                        <?php
                        if ($deadline) {
                            echo '<span class="badge badge-info">' . $trang_thai . '</span> ';
                            echo '<span class="badge badge-outline badge-danger">';
                            echo "Deadline: " . $deadline;
                            echo '</span>';
                        }
                        ?>
                    </div>
                    <div class="box-body full_height_scroll">
                        <div class="d-flex justify-content-between row mbn-20">
                            <!--Thông tin job-->
                            <div class="text-left col-12 mb-20">
                                <h4 class="fw-600"><?php _e('Chi tiết công việc', 'qlcv'); ?></h4>
                                <?php
                                switch ($phan_loai) {
                                    case 'Nhãn hiệu':
                                        $logo           = get_field('logo');
                                        $ten_nhan_hieu  = get_field('ten_nhan_hieu');
                                        $nhom           = get_field('nhom');
                                        $so_luong_nhom  = get_field('so_luong_nhom');

                                        echo "<p>";
                                        if (substr($logo, -1) != '/') {
                                            echo "<img src='" . $logo . "' width='160' class='mb-10'/><br>";
                                        }
                                        echo __("Tên nhãn hiệu: ", 'qlcv') . $ten_nhan_hieu . "<br>";
                                        echo __("Nhóm: ", 'qlcv') . $nhom . "<br>";
                                        echo __("Số lượng nhóm: ", 'qlcv') . $so_luong_nhom . "<br>";
                                        echo "</p>";
                                        break;

                                    case 'Sáng chế':
                                        $ban_mo_ta_sang_che                 = get_field('ban_mo_ta_sang_che');
                                        $so_luong_yeu_cau_bao_ho            = get_field('so_luong_yeu_cau_bao_ho');
                                        $so_luong_yeu_cau_bao_ho_doc_lap    = get_field('so_luong_yeu_cau_bao_ho_doc_lap');

                                        echo "<p>";
                                        echo __("Bản mô tả sáng chế: ", 'qlcv') . $ban_mo_ta_sang_che . "<br>";
                                        echo __("Số lượng yêu cầu bảo hộ: ", 'qlcv') . $so_luong_yeu_cau_bao_ho . "<br>";
                                        echo __("Số lượng yêu cầu bảo hộ độc lập: ", 'qlcv') . $so_luong_yeu_cau_bao_ho_doc_lap . "<br>";
                                        echo "</p>";
                                        break;

                                    case 'Kiểu dáng':
                                        $bo_anh                = get_field('bo_anh');
                                        $ban_mo_ta_cua_bo_anh  = get_field('ban_mo_ta_cua_bo_anh');
                                        $so_luong_phuong_an    = get_field('so_luong_phuong_an');

                                        echo "<p>";
                                        echo __("Bộ ảnh: ", 'qlcv') . $bo_anh . "<br>";
                                        echo __("Bản mô tả của bộ ảnh: ", 'qlcv') . $ban_mo_ta_cua_bo_anh . "<br>";
                                        echo __("Số lượng phương án: ", 'qlcv') . $so_luong_phuong_an . "<br>";
                                        echo "</p>";
                                        break;
                                }

                                echo "<br>";

                                $partner_ref    = get_field('partner_ref');
                                $our_ref        = get_field('our_ref');
                                $so_don         = get_field('so_don');
                                $ngay_nop_don   = get_field('ngay_nop_don');
                                $so_bang        = get_field('so_bang');
                                $ngay_cap_bang  = get_field('ngay_cap_bang');
                                $mindful        = get_field('mindful');

                                if ($partner_ref) {
                                    echo __("Số REF của đối tác: ", 'qlcv') . $partner_ref . "<br>";
                                }
                                if ($our_ref) {
                                    echo __("Số REF của mình: ", 'qlcv') . $our_ref . "<br>";
                                }
                                if ($so_don) {
                                    echo __("Số đơn: ", 'qlcv') . $so_don . "<br>";
                                }
                                if ($ngay_nop_don) {
                                    echo __("Ngày nộp đơn: ", 'qlcv') . $ngay_nop_don . "<br>";
                                }
                                if ($so_bang) {
                                    echo __("Số bằng: ", 'qlcv') . $so_bang . "<br>";
                                }
                                if ($ngay_cap_bang) {
                                    echo __("Ngày cấp bằng: ", 'qlcv') . $ngay_cap_bang . "<br>";
                                }

                                $content = get_the_content();
                                if ($content) {
                                    echo '<hr class="bs-docs-separator">';
                                    echo $content;
                                }

                                if ($link_onedrive) {
                                    echo '<hr class="bs-docs-separator">';
                                    echo '<h4>' . __('Link tài liệu', 'qlcv') . '</h4>';
                                    echo auto_url($link_onedrive);
                                }

                                if ($mindful) {
                                    echo '<hr class="bs-docs-separator">';
                                    echo '<h4 class="fw-600">' . __('Lưu ý công việc', 'qlcv') . '</h4>';

                                    echo $mindful;
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                $terms      = get_the_terms(get_the_ID(), 'group');
                $term_names = wp_list_pluck($terms, 'name');

                if (in_array("Tiềm năng", $term_names)) {
                    echo '<a href="' . get_the_permalink() . '?update=Done" class="button button-primary"><span><i class="fa fa-sort"></i>' . __('Chuyển thành công việc chính thức', 'qlcv') . '</span></a>';

                } else if ($deadline) {
                ?>
                    <div class="box">
                        <div class="box-head">
                            <div class="row justify-content-between">
                                <div class="col-lg-auto">
                                    <h4 class="title"><?php _e('Lịch sử', 'qlcv'); ?></h4>
                                </div>
                                <div class="col-lg-auto">
                                    <?php
                                    # nếu trạng thái là chờ phản hồi hoặc hoàn thành hoặc hủy thì sẽ ko hiển thị nút chức năng nữa
                                    if (($trang_thai != "Hoàn thành")
                                        && ($trang_thai != "Huỷ")
                                        && ($trang_thai != "Chờ phê duyệt")
                                    ) {

                                    ?>
                                        <a href="<?php echo get_bloginfo('url'); ?>/sua-cong-viec/?jobid=<?php echo get_the_ID(); ?>" class="button button-box button-vk"><i class="fa fa-pencil-square-o"></i><span><?php _e('Cập nhật nội dung', 'qlcv'); ?></span></a>
                                    <?php

                                        echo '<button class="button button-box button-android" id="quick_update"><i class="fa fa-sort"></i><span>' . __('Cập nhật trạng thái', 'qlcv') . '</span></button>';

                                        echo '<a href="' . get_bloginfo('url') . '/sua-task/?taskid=' . get_the_ID() . '" class="button button-box button-rss" id="quick_update"><i class="fa fa-calendar"></i><span>' . __('Sửa deadline', 'qlcv') . '</span></a>';
                                    } else {
                                        if (($trang_thai == "Chờ phê duyệt")
                                            && in_array('administrator', $current_user->roles)
                                        ) {
                                            echo '<a href="' . get_bloginfo('url') . '/disapproval/?taskid=' . get_the_ID() . '" class="button button-wikipedia" id="quick_update"><i class="fa fa-times"></i><span>' . __('Không phê duyệt', 'qlcv') . '</span></a>';
                                            echo '<a href="' . get_bloginfo('url') . '/approval/?taskid=' . get_the_ID() . '" class="button button-android" id="quick_update"><i class="fa fa-sort"></i><span>' . __('Hoàn thành', 'qlcv') . '</span></a>';

                                            echo '<a href="' . get_bloginfo('url') . '/sua-task/?taskid=' . get_the_ID() . '" class="button button-box button-rss" id="quick_update"><i class="fa fa-calendar"></i><span>' . __('Sửa deadline', 'qlcv') . '</span></a>';
                                        }
                                    }
                                    echo '<a href="' . get_bloginfo('url') . '/tao-phieu-thu-chi/?jobid=' . get_the_ID() . '" class="button button-box button-outlook" id="quick_update"><i class="zmdi zmdi-money"></i><span>' . __('Sửa deadline', 'qlcv') . '</span></a>';
                                    ?>
                                </div>
                            </div>
                            <?php
                            $terms = get_the_terms($id_job, 'group');
                            $term_id = $terms[0]->term_id;
                            $list_status = get_field('status', 'term_' . $term_id);
                            # nếu trạng thái là chờ phản hồi hoặc hoàn thành hoặc hủy thì sẽ ko hiển thị nút chức năng nữa
                            if (($trang_thai != "Hoàn thành")
                                && ($trang_thai != "Huỷ")
                                && ($trang_thai != "Chờ phê duyệt")
                            ) {

                            ?>
                                <div class="row">
                                    <div class="col-lg-12 quick_update">
                                        <a href="<?php echo get_bloginfo('url'); ?>/finish_task/?taskid=<?php echo get_the_ID(); ?>" class="button button-sm button-android"><span><i class="zmdi zmdi-label-heart"></i><?php _e('Hoàn thành', 'qlcv'); ?></a>
                                        <?php
                                        $print_status = false;
                                        $status_arr = explode(PHP_EOL, $list_status);
                                        foreach ($status_arr as $status) {
                                            if ($print_status) {
                                                echo '<a href="?stt=' . $status . '" class="button button-sm button-rss"><span><i class="zmdi zmdi-label-heart"></i>' . $status . '</span></a>';
                                            }
                                            if (strtolower(trim($status)) == strtolower($trang_thai)) {
                                                $print_status = true;
                                            }
                                        }
                                        ?>
                                        <a href="?stt=Huỷ" class="button button-sm button-vk"><span><i class="zmdi zmdi-label-heart" onclick="return confirm('<?php _e('Bạn chắc chắn muốn hủy công việc này chứ?', 'qlcv'); ?>')"></i><?php _e('Huỷ', 'qlcv'); ?></a>
                                    </div>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                        <div class="box-body">
                        <div class="row">
                            <div class="col-md-6 col-12">
                                <h5><?php _e('Người quản lý', 'qlcv'); ?></h5>
                                <?php 
                                    $manager = get_field('manager');

                                    echo $manager['display_name'] . "<br>";
                                    echo $manager['user_email'] . "<br>";
                                    echo get_field('so_dien_thoai', 'user_' . $manager['ID']) . "<br>";
                                ?>
                            </div>
                            <div class="col-md-6 col-12">
                                <h5><?php _e('Người thực hiện', 'qlcv'); ?></h5>
                                <?php 
                                    $member = get_field('user');

                                    echo $member['display_name'] . "<br>";
                                    echo $member['user_email'] . "<br>";
                                    echo get_field('so_dien_thoai', 'user_' . $member['ID']) . "<br>";
                                ?>
                            </div>
                        </div>
                        <hr>
                            <ul class="timeline-list">
                                <?php
                                $history = get_field('history');
                                // print_r($history);
                                if (have_rows('history', $user_id)) {
                                    while (have_rows('history', $user_id)) {
                                        the_row();

                                        $thoi_gian = DateTime::createFromFormat('d/m/Y', get_sub_field('thoi_gian'));
                                        $timestamp = strtotime($thoi_gian->format('d-m-Y H:i:s'));
                                        $iduser = get_sub_field('nguoi_thuc_hien');
                                        $nguoi_thuc_hien = get_user_by('ID', $iduser);
                                ?>
                                        <li>
                                            <span class="icon">
                                                <?php
                                                echo "<img src='" . get_avatar_url($iduser) . "' style='border-radius:40px;'/>";
                                                ?>
                                            </span>
                                            <div class="details">
                                                <h5 class="title">
                                                    <?php
                                                    echo $nguoi_thuc_hien->nickname . " (" . $nguoi_thuc_hien->user_email . ")";
                                                    ?>
                                                </h5>
                                                <span class="time"><?php echo time_elapsed_string($timestamp); ?></span>
                                                <div class="content">
                                                    <p>
                                                        <?php the_sub_field('noi_dung'); ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </li>
                                <?php
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                <?php
                } 
                {
                ?>
                    <div class="box">
                        <div class="box-head">
                            <div class="row justify-content-between">
                                <div class="col-lg-auto">
                                    <h4 class="title"><?php _e('Danh sách nhiệm vụ', 'qlcv'); ?></h4>
                                </div>
                                <div class="col-lg-auto">
                                    <a href="<?php echo get_bloginfo('url'); ?>/tao-nhiem-vu-moi/?jobid=<?php echo get_the_ID(); ?>" class="button button-sm button-primary"><span><i class="fa fa-tasks"></i><?php _e('Thêm nhiệm vụ', 'qlcv'); ?></span></a>
                                    <a href="<?php echo get_bloginfo('url'); ?>/sua-cong-viec/?jobid=<?php echo get_the_ID(); ?>" class="button button-sm button-box button-android" data-tippy-content="<?php _e('Cập nhật nội dung', 'qlcv'); ?>"><i class="fa fa-pencil-square-o"></i></a>
                                    <a href="<?php echo get_bloginfo('url'); ?>/tao-phieu-thu-chi/?jobid=<?php echo get_the_ID(); ?>" class="button button-sm button-box button-outlook" id="quick_update" data-tippy-content="<?php _e('Tạo phiếu thu chi', 'qlcv'); ?>"><i class="zmdi zmdi-money"></i></a>
                                    <a href="<?php echo get_bloginfo('url'); ?>/renewal_post_api/?jobid=<?php echo get_the_ID(); ?>" class="button button-sm button-box button-skype" data-tippy-content="<?php _e('Chuyển dữ liệu sang renewal', 'qlcv'); ?>"><i class="fa fa-telegram"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="box-body">
                            <div class="table-responsive">
                                <table class="table daily-sale-report">

                                    <!-- Table Head Start -->
                                    <thead>
                                        <tr>
                                            <th><?php _e('Nhiệm vụ', 'qlcv'); ?></th>
                                            <th><?php _e('Trạng thái', 'qlcv'); ?></th>
                                            <th>Deadline</th>
                                            <th></th>
                                        </tr>
                                    </thead><!-- Table Head End -->

                                    <!-- Table Body Start -->
                                    <tbody>
                                        <?php
                                        $args   = array(
                                            'post_type'     => 'task',
                                            'number'        => -1,
                                            'meta_query'    => array(
                                                'relation'      => 'AND',
                                                array(
                                                    'key'       => 'job',
                                                    'compare'   => '=',
                                                    'value'     => get_the_ID(),
                                                ),
                                            ),
                                        );
                                        $query = new WP_Query($args);

                                        // print_r($query);

                                        if ($query->have_posts()) {
                                            while ($query->have_posts()) {
                                                $query->the_post();

                                                $trangthai          = get_field('trang_thai');
                                                $deadline           = get_field('deadline');
                                                $time_to_response   = get_field('time_to_response');

                                                // Tính toán tiến độ công việc
                                                $start_time     = strtotime(get_the_date('d-m-Y'));
                                                $current_time   = current_time('timestamp', 7);
                                                $temp           = new DateTime();
                                                $tmp            = $temp->createFromFormat('d/m/Y', $deadline);
                                                $end_time       = strtotime($tmp->format('d-m-Y'));

                                                // nếu thời gian hiện tại ít hơn deadline thì mới tính %
                                                if ($current_time < $end_time) {
                                                    $work_percent = round(($current_time - $start_time) / ($end_time - $start_time) * 100);
                                                } else {
                                                    $work_percent = 100;
                                                }

                                                # if it have respone date, shown it, if not, show deadline
                                                if ($trang_thai == "Chờ phản hồi") {
                                                    $deadline_label = $time_to_response;
                                                } else if ($trang_thai != "Hoàn thành") {
                                                    $deadline_label = $deadline;
                                                } else {
                                                    $deadline_label = "Xong";
                                                }

                                                // $deadline_label = $work_percent=='100'?"100%":$deadline;

                                                echo "<tr>";
                                                echo '<td class="fw-600"><a href=' . get_permalink() . '>' . get_the_title() . '</a></td>';
                                                echo '<td>' . $trangthai . '</td>';
                                                echo '<td><div class="progress" style="height: 24px;">
                                                                <div class="progress-bar" role="progressbar" style="width: ' . $work_percent . '%" aria-valuenow="' . $work_percent . '" aria-valuemin="0" aria-valuemax="100">' . $deadline . '</div>
                                                                </div>
                                                              </td>';
                                                echo '<td>
                                                                <a href="' . get_bloginfo('url') . '/sua-noi-dung-nhiem-vu/?taskid=' . get_the_ID() . '" class="button button-xs button-box button-android" data-tippy-content="' . __('Sửa nội dung nhiệm vụ', 'qlcv') . '"><i class="fa fa-pencil-square-o"></i></a>
                                                                <a href="' . get_bloginfo('url') . '/sua-task/?taskid=' . get_the_ID() . '" class="button button-xs button-box button-rss" id="quick_update" data-tippy-content="' . __('Sửa deadline và người xử lý', 'qlcv') . '"><i class="zmdi zmdi-assignment"></i></a>
                                                                <a href="' . get_permalink( ) . '?stt=Huỷ" class="button button-xs button-box button-reddit" data-tippy-content="Huỷ"><i class="fa fa-trash" onclick="return confirm(\'' . __('Bạn chắc chắn muốn hủy công việc này chứ?', 'qlcv') . '\')"></i></a>
                                                              </td>';
                                                echo "</tr>";
                                            }
                                            wp_reset_postdata();
                                        }
                                        ?>
                                    </tbody><!-- Table Body End -->

                                </table>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div><!-- Page Heading End -->

            <div class="col-4 col-lg-4">
                <div class="box mb-20">
                    <div class="page-heading box-head">
                        <h4><?php _e('Thông tin đối tác/khách hàng', 'qlcv'); ?></h4>
                    </div>
                    <div class="box-body">
                        <div class="d-flex justify-content-between row mb-20">
                            <!--Thông tin khách hàng-->
                            <div class="col-12 col-sm-auto mb-20">
                                <h5><?php _e('Khách hàng', 'qlcv'); ?></h5>
                                <?php
                                $customer = get_field('customer');
                                $id_customer = $customer->ID;

                                $name           = get_the_title($id_customer);
                                $link_customer  = get_permalink($id_customer);
                                $business       = get_field('ten_cong_ty', $id_customer);
                                $phone          = get_field('so_dien_thoai', $id_customer);
                                $email          = get_field('email', $id_customer);
                                $address        = get_field('dia_chi', $id_customer);
                                $quoc_gia       = get_field('quoc_gia', $id_customer);

                                echo "<p>";
                                echo "<a href='" . $link_customer . "'><b>" . $name . "</b></a><br>";
                                echo $business . "<br>";
                                echo $phone . "<br>";
                                echo $email . "<br>";
                                echo $address . "<br>";
                                echo $quoc_gia . "<br>";
                                echo "</p>";

                                ?>
                            </div>
                        </div>
                        <?php
                        if ($id_customer) {
                            echo '<a href="' . get_bloginfo('url') . '/sua-thong-tin-khach-hang/?uid=' . $id_customer . '" class="button button-sm button-primary"><span><i class="fa fa-edit"></i>' . __('Sửa', 'qlcv') . '</span></a>';
                        }
                        ?>
                    </div>

                    <div class="box-body">
                        <div class="d-flex justify-content-between row mb-20">
                            <!--Thông tin khách hàng-->
                            <div class="col-12 col-sm-auto mb-20">
                                <h5><?php _e('Đối tác gửi việc', 'qlcv'); ?></h5>
                                <?php
                                // echo $partner['user_avatar'];

                                $partner_url    = get_author_posts_url($partner['ID']);
                                $business       = get_field('ten_cong_ty', 'user_' . $partner['ID']);
                                $phone          = get_field('so_dien_thoai', 'user_' . $partner['ID']);
                                $address        = get_field('dia_chi', 'user_' . $partner['ID']);
                                $quoc_gia       = get_field('quoc_gia', 'user_' . $partner['ID']);

                                echo "<p>";
                                echo "<a href='" . $partner_url . "'><b>" . $partner['display_name'] . "</b></a><br>";
                                echo $business . "<br>";
                                if ($phone) {
                                    echo $phone . "<br>";
                                }
                                echo $partner['user_email'] . "<br>";
                                if ($address) {
                                    echo $address . "<br>";
                                }
                                if ($quoc_gia) {
                                    echo $quoc_gia . "<br>";
                                }
                                echo "</p>";

                                ?>
                            </div>
                        </div>
                        <?php
                        if ($partner["ID"]) {
                            echo '<a href="' . get_bloginfo('url') . '/sua-thong-tin-doi-tac/?uid=' . $partner['ID'] . '" class="button button-sm button-primary"><span><i class="fa fa-edit"></i>' . __('Sửa', 'qlcv') . '</span></a>';
                        }
                        ?>
                    </div>
                    <div class="box-body">
                        <div class="d-flex justify-content-between row mb-20">
                            <!--Thông tin khách hàng-->
                            <div class="col-12 col-sm-auto mb-20">
                                <h5><?php _e('Đối tác nhận việc', 'qlcv'); ?></h5>
                                <?php
                                $partner = get_field('foreign_partner');

                                // echo $partner['user_avatar'];
                                $partner_url    = get_author_posts_url($partner['ID']);
                                $business       = get_field('ten_cong_ty', 'user_' . $partner['ID']);
                                $phone          = get_field('so_dien_thoai', 'user_' . $partner['ID']);
                                $address        = get_field('dia_chi', 'user_' . $partner['ID']);
                                $quoc_gia       = get_field('quoc_gia', 'user_' . $partner['ID']);

                                echo "<p>";
                                echo "<a href='" . $partner_url . "'><b>" . $partner['display_name'] . "</b></a><br>";
                                echo $business . "<br>";
                                if ($phone) {
                                    echo $phone . "<br>";
                                }
                                echo $partner['user_email'] . "<br>";
                                if ($address) {
                                    echo $address . "<br>";
                                }
                                if ($quoc_gia) {
                                    echo $quoc_gia . "<br>";
                                }
                                echo "</p>";

                                ?>
                            </div>
                        </div>
                        <?php
                        if ($partner["ID"]) {
                            echo '<a href="' . get_bloginfo('url') . '/sua-thong-tin-doi-tac/?uid=' . $partner['ID'] . '" class="button button-sm button-primary"><span><i class="fa fa-edit"></i>' . __('Sửa', 'qlcv') . '</span></a>';
                        }
                        ?>
                    </div>
                </div>
                <div class="box mb-20">
                    <div class="page-heading box-head">
                        <h4 class="mb-10"><?php _e('Lịch sử công việc', 'qlcv'); ?></h4>
                    </div>
                    <div class="box-body">
                        <div class="d-flex justify-content-between row mb-20">
                            <div class="col-12 mb-20">
                                <?php
                                # show các kết quả đã đạt được thông qua lịch sử công việc
                                if (in_array("Tiềm năng", $term_names)) {
                                    $term       = get_term_by('name', 'Tiềm năng', 'group');
                                } else {
                                    $term       = get_term_by('name', $phan_loai, 'group');
                                }

                                $work_list  = get_field('work_process', 'term_' . $term->term_id);
                                $work_arr   = explode(PHP_EOL, $work_list);
                                $work_arr   = array_map('trim', $work_arr);

                                # get history of work that is current process
                                $work_list  = get_field('lich_su_cong_viec');
                                $work_history = array();
                                foreach ($work_list as $key => $value) {
                                    $work_history[] = $value['mo_ta'];
                                    $work_date[] = $value['ngay_thang'];
                                }

                                $work_not_done = array_diff($work_arr, $work_history);
                                
                                echo "<table class='job_history'>";
                                for ($i=0; $i < count($work_history); $i++) {
                                    echo "<tr class='bg_light_primary'>";
                                    echo '<td class="bg_primary">
                                            </td>
                                            <td>
                                            <span class="text">' . $work_history[$i] . '</span>
                                            </td>';
                                    echo "<td>" . $work_date[$i] . "</td>";
                                    echo '<td><i class="zmdi zmdi-badge-check"></i></td>';
                                    echo "</tr>";
                                }
                                
                                foreach ($work_not_done as $process) {
                                    if ($process) {
                                        echo "<tr>";
                                        echo '<td class="bg_gray"> </td>
                                            <td colspan="3"><span class="text">' . $process . '</span> </td>';
                                                
                                        echo "</tr>";
                                    }
                                }

                                echo "</table>";

                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- Page Headings End -->

    </div><!-- Content Body End -->


<?php
}

get_footer();
?>