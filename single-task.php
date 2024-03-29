<?php
# remove check from admin new notif box when user have been seen
while (have_posts()) {
    the_post();

    $current_user = wp_get_current_user();
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

    # xử lý cập nhật trạng thái
    if (isset($_GET['stt']) && ($_GET['stt'] != "")) {
        if (is_user_logged_in()) {

            $new_stt = $_GET['stt'];
            $noi_dung = __("đã thay đổi trạng thái thành", 'qlcv') . " " . $new_stt;

            $current_time = current_time('timestamp', 7);

            $row_update = array(
                'nguoi_thuc_hien'   => $current_user,
                'noi_dung'          => $noi_dung,
                'thoi_gian'         => $current_time,
            );

            // update status
            update_field('field_600fde92f9be9', $new_stt);
            // update history
            $logs = get_field('history');
            if ($logs) {
                array_unshift($logs, $row_update);
                update_field('field_6010e02533119', $logs);
            } else add_row('field_6010e02533119', $row_update);

            # push notification
            $id_job = get_field('job');
            $content_notif = $current_user->display_name . " " . $noi_dung . " <b>" . get_the_title() . "</b> " . __("cho", 'qlcv') . " " . get_the_title($id_job);
            $user = get_field('user');
            $manager = get_field('manager');
            create_notification(get_the_ID(), $content_notif, $manager['ID'], $user['ID']);

            wp_redirect(get_permalink());
            exit;
        }
    }

    get_header();

    get_sidebar();

    // Thông tin job mà task trực thuộc
    $id_job = get_field('job');

    $name       = get_the_title($id_job);
    $permalink  = get_permalink($id_job);
    $phan_loai  = get_field('phan_loai', $id_job);
    $trang_thai = get_field('trang_thai');
    $deadline   = get_field('deadline');
    $respone    = get_field('time_to_response');

    # lấy thông tin user hiện tại
    $current_user = wp_get_current_user();
    // print_r($current_user);
?>

    <!-- Content Body Start -->
    <div class="content-body">

        <!-- Page Headings Start -->
        <div class="row justify-content-between mb-10">

            <div class="col-12 col-lg-12 mb-20">
                <a href="<?php echo $permalink; ?>"><?php echo $name; ?></a> > <?php the_title(); ?>
            </div>
            <!-- Page Heading Start -->
            <div class="col-8 col-lg-8 mb-20">
                <div class="box">
                    <div class="page-heading box-head">
                        <h3 class="mb-10"><?php the_title(); ?> </h3>
                        <span class="badge badge-primary"><?php echo $trang_thai; ?></span>
                        <?php
                        if ($deadline) {
                            echo '<span class="badge badge-outline badge-danger">';
                            echo "Deadline: " . $deadline;
                            echo '</span>';
                        }
                        ?>
                    </div>
                    <div class="box-body">
                        <div class="d-flex justify-content-between row mbn-20">
                            <!--Thông tin job-->
                            <div class="text-left col-12 col-sm-auto mb-20">
                                <?php
                                ?>
                                <?php the_content(); ?>
                            </div>
                        </div>
                    </div>
                </div>

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
                                    && ($trang_thai != "Quản lý đã phê duyệt")
                                ) {

                                ?>
                                    <a href="<?php echo get_bloginfo('url'); ?>/sua-noi-dung-nhiem-vu/?taskid=<?php echo get_the_ID(); ?>" class="button button-box button-vk"><i class="fa fa-pencil-square-o"></i><span><?php _e('Cập nhật nội dung', 'qlcv'); ?></span></a>
                                <?php
                                    echo '<button class="button button-box button-android" id="quick_update"><i class="fa fa-sort"></i><span>' . __('Cập nhật trạng thái', 'qlcv') . '</span></button>';
                                    echo '<a href="' . get_bloginfo('url') . '/sua-task/?taskid=' . get_the_ID() . '" class="button button-box button-rss" id="quick_update"><i class="zmdi zmdi-assignment"></i><span>' . __('Sửa deadline', 'qlcv') . '</span></a>';
                                } else {
                                    if ((($trang_thai == "Chờ phê duyệt")
                                        && (in_array('administrator', $current_user->roles) || in_array('contributor', $current_user->roles)))
                                        || (($trang_thai == "Quản lý đã phê duyệt") && in_array('administrator', $current_user->roles))
                                    ) {
                                        echo '<a href="' . get_bloginfo('url') . '/disapproval/?taskid=' . get_the_ID() . '" class="button button-wikipedia" id="quick_update"><i class="fa fa-times"></i><span>' . __('Không phê duyệt', 'qlcv') . '</span></a>';
                                        echo '<a href="' . get_bloginfo('url') . '/confirm/?taskid=' . get_the_ID() . '" class="button button-android" id="quick_update"><i class="fa fa-sort"></i><span>' . __('Hoàn thành', 'qlcv') . '</span></a>';
                                    }
                                }
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
                                    <a href="?stt=Huỷ" class="button button-sm button-vk"><span><i class="fa fa-trash" onclick="return confirm('<?php _e('Bạn chắc chắn muốn hủy công việc này chứ?', 'qlcv'); ?>')"></i><?php _e('Huỷ', 'qlcv'); ?></a>
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
                            if (have_rows('history')) {
                                while (have_rows('history')) {
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
            </div><!-- Page Heading End -->

            <div class="col-4 col-lg-4">
                <div class="box mb-20">
                    <div class="page-heading box-head">
                        <h3 class="mb-10"><?php _e('Thông tin công việc', 'qlcv'); ?></h3>
                        <span class="badge badge-primary"><?php echo $phan_loai; ?></span>
                    </div>
                    <div class="box-body">
                        <div class="d-flex justify-content-between row mbn-20">
                            <!--Thông tin khách hàng-->
                            <div class="col-12 col-sm-auto mb-20">
                                <?php
                                echo "<p>";
                                echo "<a href='" . $permalink . "'><b>" . $name . "</b></a><br>";

                                switch ($phan_loai) {
                                    case 'Nhãn hiệu':
                                        $logo           = get_field('logo', $id_job);
                                        $ten_nhan_hieu  = get_field('ten_nhan_hieu', $id_job);
                                        $nhom           = get_field('nhom', $id_job);
                                        $so_luong_nhom  = get_field('so_luong_nhom', $id_job);

                                        if (substr($logo, -1) != '/') {
                                            echo "<img src='" . $logo . "' width='160' class='mb-10'/><br>";
                                        }
                                        echo __("Tên nhãn hiệu: ", 'qlcv') . $ten_nhan_hieu . "<br>";
                                        echo __("Nhóm: ", 'qlcv') . $nhom . "<br>";
                                        echo __("Số lượng nhóm: ", 'qlcv') . $so_luong_nhom . "<br>";
                                        break;

                                    case 'Sáng chế':
                                        $so_luong_yeu_cau_bao_ho            = get_field('so_luong_yeu_cau_bao_ho', $id_job);
                                        $so_luong_yeu_cau_bao_ho_doc_lap    = get_field('so_luong_yeu_cau_bao_ho_doc_lap', $id_job);

                                        $ban_mo_ta_sang_che                 = auto_url(get_field('ban_mo_ta_sang_che', $id_job));

                                        echo __("Bản mô tả sáng chế: ", 'qlcv') . $ban_mo_ta_sang_che . "<br>";
                                        echo __("Số lượng yêu cầu bảo hộ: ", 'qlcv') . $so_luong_yeu_cau_bao_ho . "<br>";
                                        echo __("Số lượng yêu cầu bảo hộ độc lập: ", 'qlcv') . $so_luong_yeu_cau_bao_ho_doc_lap . "<br>";
                                        break;

                                    case 'Kiểu dáng':
                                        $bo_anh                 = auto_url(get_field('bo_anh', $id_job));
                                        $ban_mo_ta_cua_bo_anh   = auto_url(get_field('ban_mo_ta_cua_bo_anh', $id_job));
                                        $so_luong_phuong_an     = get_field('so_luong_phuong_an', $id_job);

                                        echo __("Bộ ảnh: ", 'qlcv') . $bo_anh . "<br>";
                                        echo __("Bản mô tả của bộ ảnh: ", 'qlcv') . $ban_mo_ta_cua_bo_anh . "<br>";
                                        echo __("Số lượng phương án: ", 'qlcv') . $so_luong_phuong_an . "<br>";
                                        break;
                                }

                                echo "</p>";

                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                $partner = get_field('partner');
                if ($partner['ID']) {
                ?>
                    <div class="box mb-20">
                        <div class="page-heading box-head">
                            <h3 class="mb-10"><?php _e('Thông tin đối tác', 'qlcv'); ?></h3>
                        </div>
                        <div class="box-body">
                            <div class="d-flex justify-content-between row mb-20">
                                <!--Thông tin khách hàng-->
                                <div class="col-12 col-sm-auto mb-20">
                                    <?php
                                    echo $partner['user_avatar'];

                                    $partner_url    = get_author_posts_url($partner['ID']);
                                    $business       = get_field('ten_cong_ty', 'user_' . $partner['ID']);
                                    $phone          = get_field('so_dien_thoai', 'user_' . $partner['ID']);
                                    $address        = get_field('dia_chi', 'user_' . $partner['ID']);

                                    echo "<p>";
                                    echo "<a href='" . $partner_url . "'><b>" . $partner['display_name'] . "</b></a><br>";
                                    echo $business . "<br>";
                                    echo $phone . "<br>";
                                    echo $partner['user_email'] . "<br>";
                                    echo $address . "<br>";
                                    echo "</p>";

                                    ?>
                                </div>
                            </div>
                            <?php
                            echo '<a href="' . get_bloginfo('url') . '/sua-thong-tin-doi-tac/?uid=' . $partner['ID'] . '" class="button button-primary"><span><i class="fa fa-edit"></i>' . __('Sửa', 'qlcv') . '</span></a>';
                            ?>
                        </div>
                    </div>
                <?php
                }
                $supervisor = explode("|", get_field('supervisor'));
                if ($supervisor) {
                ?>
                    <div class="box mb-20">
                        <div class="page-heading box-head">
                            <h3 class="mb-10"><?php _e('Thông tin người giám sát', 'qlcv'); ?></h3>
                        </div>
                        <div class="box-body">
                            <div class="d-flex justify-content-between row mb-20">
                                <?php 
                                    foreach ($supervisor as $supervisorID) {
                                        $supervisor_user = get_user_by("ID", $supervisorID);
                                ?>
                                <div class="col-12 col-sm-auto mb-20">
                                    <?php
                                        $supervisor_url = get_author_posts_url($supervisor_user->ID);
                                        $phone          = get_field('so_dien_thoai', 'user_' . $supervisor_user->ID);

                                        echo "<p>";
                                        echo "<a href='" . $supervisor_url . "'><b>" . $supervisor_user->display_name . "</b></a><br>";
                                        if($phone) echo $phone . "<br>";
                                        echo $supervisor_user->user_email . "<br>";
                                        echo "</p>";

                                    ?>
                                </div>
                                <?php 
                                    }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>

        </div><!-- Page Headings End -->

    </div><!-- Content Body End -->

<?php
}

get_footer();
?>