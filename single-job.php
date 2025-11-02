<?php
get_header();

get_sidebar();

// Handle delete finance record at the beginning of page
if (isset($_GET['delete_finance']) && isset($_GET['finance_id']) && isset($_GET['job_id'])) {
    $finance_id = intval($_GET['finance_id']);
    $job_id = intval($_GET['job_id']);
    $current_user = wp_get_current_user();
    
    // Check permissions
    $job_author = get_post_field('post_author', $job_id);
    $is_job_creator = ($current_user->ID == $job_author);
    $is_admin = in_array('administrator', $current_user->roles);
    
    if ($is_job_creator || $is_admin) {
        // Verify nonce for security
        if (isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_finance_' . $finance_id)) {
            // Call the delete function
            if (function_exists('delete_finance_record')) {
                $result = delete_finance_record($finance_id, $job_id);
                if ($result) {
                    // Redirect to same page with success message
                    $redirect_url = add_query_arg(array(
                        'finance_deleted' => '1',
                        'message' => urlencode(__('Đã xóa phiếu thu chi thành công', 'qlcv'))
                    ), get_permalink($job_id));
                    
                    wp_redirect($redirect_url);
                    exit;
                } else {
                    // Add error message to URL
                    $redirect_url = add_query_arg(array(
                        'finance_error' => '1',
                        'message' => urlencode(__('Có lỗi xảy ra khi xóa phiếu thu chi', 'qlcv'))
                    ), get_permalink($job_id));
                    
                    wp_redirect($redirect_url);
                    exit;
                }
            }
        } else {
            // Add permission error to URL
            $redirect_url = add_query_arg(array(
                'finance_error' => '1',
                'message' => urlencode(__('Không có quyền thực hiện hành động này', 'qlcv'))
            ), get_permalink($job_id));
            
            wp_redirect($redirect_url);
            exit;
        }
    } else {
        // Add permission error to URL
        $redirect_url = add_query_arg(array(
            'finance_error' => '1',
            'message' => urlencode(__('Bạn không có quyền xóa phiếu thu chi này', 'qlcv'))
        ), get_permalink($job_id));
        
        wp_redirect($redirect_url);
        exit;
    }
}

$color = [
    'button-primary',
    'button-secondary',
    'button-success',
    'button-danger',
    'button-warning',
    'button-info',
    'button-rss',
    'button-outlook',
    'button-android',
    'button-wikipedia',
    'button-vk',
    'button-skype',
    'button-reddit',
    'button-dark',
    'button-light',
];

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
        # create log, switch job status to official
        $log = __("Chuyển công việc tiềm năng sang công việc chính thức", 'qlcv');
        asl_create_log($log, get_the_ID());
    }

    # update other potential
    if (isset($_POST['other_potential'])) {
        $other_potential = $_POST['other_potential'];
        # get all child term of tiềm năng
        $list_other_potential = get_term_children(11, 'group');
        foreach ($list_other_potential as $potential) {
            $term = get_term($potential, 'group');
            wp_remove_object_terms(get_the_ID(), $term->term_id, 'group');
        }
        if ($other_potential != "Tiềm năng") {
            wp_add_object_terms(get_the_ID(), $other_potential, 'group');

            # create log, add child term of potential
            $log = sprintf(__('Chuyển công việc tiềm năng sang phân loại "%s"', 'qlcv'), $other_potential);
            asl_create_log($log, get_the_ID());
        } else {
            # create log, remove all child term of potential
            $log = __("Hủy các phân loại con của công việc tiềm năng", 'qlcv');
            asl_create_log($log, get_the_ID());
        }
    }
?>

    <!-- Content Body Start -->
    <div class="content-body">
        
        <?php
        // Show success/error messages
        if (isset($_GET['finance_deleted']) && $_GET['finance_deleted'] == '1') {
            $message = isset($_GET['message']) ? urldecode($_GET['message']) : __('Đã xóa phiếu thu chi thành công', 'qlcv');
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
            echo '<i class="fa fa-check-circle"></i> ' . esc_html($message);
            echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
            echo '<span aria-hidden="true">&times;</span>';
            echo '</button>';
            echo '</div>';
        }

        if (isset($_GET['finance_error']) && $_GET['finance_error'] == '1') {
            $message = isset($_GET['message']) ? urldecode($_GET['message']) : __('Có lỗi xảy ra', 'qlcv');
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
            echo '<i class="fa fa-exclamation-triangle"></i> ' . esc_html($message);
            echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
            echo '<span aria-hidden="true">&times;</span>';
            echo '</button>';
            echo '</div>';
        }
        ?>

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
                        <!-- <span class="badge badge-primary"><?php echo $phan_loai; ?></span> -->
                        <?php
                        if (in_array('administrator', $current_user->roles) || in_array('contributor', $current_user->roles)) {
                            $rand_color = $color[array_rand($color)];
                            echo "<span class='badge " . $rand_color . "'>" . $tagname . "</span> ";
                        }
                        # get all child term of tiềm năng of this post and show it
                        $list_other_potential = get_the_terms(get_the_ID(), 'group');
                        if ($list_other_potential) {
                            foreach ($list_other_potential as $potential) {
                                $term = get_term($potential, 'group');
                                # get a random of $color
                                $rand_color = $color[array_rand($color)];
                                echo "<span class='badge " . $rand_color . "'>" . $term->name . "</span> ";
                            }
                        }

                        if ($deadline) {
                            echo '<span class="badge badge-info">' . __($trang_thai, 'qlcv') . '</span> ';
                            echo '<span class="badge badge-outline badge-danger">';
                            echo __("Deadline:", 'qlcv') . " " . $deadline;
                            echo '</span>';
                        }

                        # Hiển thị độ khó
                        $level = get_field('level');
                        if ($level) {
                            $level_colors = array(
                                'Đơn giản' => 'badge-orange',
                                'Trung Bình' => 'badge-secondary',
                                'Khó' => 'badge-danger',
                                'Rất khó' => 'badge-reddit'
                            );
                            $level_color = isset($level_colors[$level]) ? $level_colors[$level] : 'badge-secondary';
                            echo " | <span class='badge " . $level_color . "'>" . __('Độ khó:', 'qlcv') . " " . $level . "</span> ";
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
                                $country        = get_field('country');

                                if ($partner_ref) {
                                    echo __("Số REF của đối tác: ", 'qlcv') . $partner_ref . "<br>";
                                }
                                if ($our_ref) {
                                    echo __("Số REF của mình:", 'qlcv') . " " . $our_ref . "<br>";
                                }
                                if ($so_don) {
                                    echo __("Số đơn:", 'qlcv') . " " . $so_don . "<br>";
                                }
                                if ($ngay_nop_don) {
                                    echo __("Ngày nộp đơn:", 'qlcv') . " " . $ngay_nop_don . "<br>";
                                }
                                if ($so_bang) {
                                    echo __("Số bằng:", 'qlcv') . " " . $so_bang . "<br>";
                                }
                                if ($ngay_cap_bang) {
                                    echo __("Ngày cấp bằng:", 'qlcv') . " " . $ngay_cap_bang . "<br>";
                                }
                                if ($country) echo __("Quốc gia nộp:", 'qlcv') . " " . $country . "<br>";


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
                    echo "<div id='group_action'>";
                    echo '<a href="' . get_the_permalink() . '?update=Done" class="button button-primary"><span><i class="fa fa-sort"></i>' . __('Chuyển thành công việc chính thức', 'qlcv') . '</span></a>';
                    ?>

                        <!-- form select to change category for this post -->
                        <button class="button button-outlook" id="change_cat_button"><i class="fa fa-refresh"></i><?php _e('Chuyển phân loại', 'qlcv'); ?></button>
                        <div id="change_cat">
                            <form action="" method="post">
                                <!-- <input type="hidden" name="post_id" value="<?php echo get_the_ID(); ?>"> -->
                                <select class="form-control select2-tags mb-20" name="other_potential">
                                    <option value=""> -- <?php _e('Chọn phân loại tiềm năng', 'qlcv'); ?> -- </option>
                                    <?php 
                                        $list_other_jobs = get_term_children(11, 'group');
                                        foreach ($list_other_jobs as $jobid) {
                                            $term = get_term($jobid, 'group');
                                            echo "<option value='" . $term->name . "'>" . $term->name . "</option>";
                                        }
                                    ?>
                                    <option value="Tiềm năng"><?php _e('Hủy các phân loại con', 'qlcv'); ?></option>
                                </select>
                                <input class="button button-outlook" type="submit" value="<?php _e('Chọn', 'qlcv'); ?>">
                                <button class="button button-rss cancel"><?php _e('Hủy', 'qlcv'); ?></button>
                            </form>
                        </div> 
                    <?php 
                    echo "</div>";
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
                                    echo '<a href="' . get_bloginfo('url') . '/tao-phieu-thu-chi/?jobid=' . get_the_ID() . '" class="button button-box button-outlook" id="quick_update"><i class="zmdi zmdi-money"></i><span>' . __('Tạo phiếu thu chi', 'qlcv') . '</span></a>';
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
                                                echo '<a href="?stt=' . $status . '" class="button button-sm button-rss"><span><i class="zmdi zmdi-label-heart"></i>' . __($status, 'qlcv') . '</span></a>';
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
                ?>
                <div class="box">
                    <div class="box-head">
                        <div class="row justify-content-between">
                            <div class="col-lg-auto">
                                <h4 class="title"><?php _e('Danh sách nhân sự xử lý', 'qlcv'); ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6 col-12">
                                <h5><?php _e('Người quản lý', 'qlcv'); ?></h5>
                                <?php 
                                    $manager = get_field('manager');
                                    if (isset($manager['ID'])) {
                                        echo '<a href="' . get_author_posts_url($manager['ID']) . '">' . $manager['display_name'] . '</a><br>';
                                        echo $manager['user_email'] . "<br>";
                                        echo get_field('so_dien_thoai', 'user_' . $manager['ID']) . "<br>";
                                    }

                                    # if have co_manager, explode to array and show it
                                    $co_manager = get_field('co_manager');
                                    if ($co_manager) {
                                        echo "<br><h5>" . __('Người cùng quản lý', 'qlcv') . "</h5>";
                                        $co_manager_arr = explode("|", $co_manager);
                                        foreach ($co_manager_arr as $co_manager_id) {
                                            $co_manager_info = get_userdata($co_manager_id);
                                            echo '<a href="' . get_author_posts_url($co_manager_id) . '">' . $co_manager_info->display_name . '</a>';
                                        }
                                    }
                                ?>
                            </div>
                            <div class="col-md-6 col-12">
                                <h5><?php _e('Người thực hiện', 'qlcv'); ?></h5>
                                <?php 
                                    $member = get_field('member');
                                    # if have member, show it
                                    if (isset($member['ID'])) {
                                        echo '<a href="' . get_author_posts_url($member['ID']) . '">' . $member['display_name'] . '</a><br>';
                                        echo $member['user_email'] . "<br>";
                                        echo get_field('so_dien_thoai', 'user_' . $member['ID']) . "<br>";
                                    }
                                    
                                    # if have co_member, explode to array and show it
                                    $co_member = get_field('co_member');
                                    if ($co_member) {
                                        echo "<br><h5>" . __('Người cùng thực hiện', 'qlcv') . "</h5>";
                                        $co_member_arr = explode("|", $co_member);
                                        foreach ($co_member_arr as $co_member_id) {
                                            $co_member_info = get_userdata($co_member_id);
                                            echo '<a href="' . get_author_posts_url($co_member_id) . '">' . $co_member_info->display_name . '</a>';
                                        }
                                    }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box">
                    <div class="box-head">
                        <div class="row justify-content-between">
                            <div class="col-lg-auto">
                                <h4 class="title"><?php _e('Danh sách nhiệm vụ', 'qlcv'); ?></h4>
                            </div>
                            <div class="col-lg-auto">
                                <a href="<?php echo get_bloginfo('url'); ?>/tao-nhiem-vu-moi/?jobid=<?php echo get_the_ID(); ?>" class="button button-sm button-primary"><span><i class="fa fa-tasks"></i><?php _e('Thêm nhiệm vụ', 'qlcv'); ?></span></a>
                                <?php
                                // Kiểm tra quyền để hiển thị nút chia tỷ lệ %
                                $current_user = wp_get_current_user();
                                $job_author = get_post_field('post_author', get_the_ID());
                                $is_job_creator = ($current_user->ID == $job_author);
                                $is_admin = in_array('administrator', $current_user->roles);
                                
                                if ($is_job_creator || $is_admin) {
                                    echo '<a href="' . get_bloginfo('url') . '/chia-ty-le-hoa-hong/?job_id=' . get_the_ID() . '" class="button button-sm button-success" data-tippy-content="' . __('Quản lý phân chia hoa hồng', 'qlcv') . '"><span><i class="fa fa-percentage"></i>' . __('Chia tỷ lệ %', 'qlcv') . '</span></a>';
                                }
                                ?>
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
                                        <th><?php _e('Deadline', 'qlcv'); ?></th>
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
                                    
                                    // Polylang: Hiển thị tất cả ngôn ngữ thay vì chỉ ngôn ngữ hiện tại
                                    if (function_exists('pll_languages_list')) {
                                        $args['lang'] = '';  // Hiển thị tất cả ngôn ngữ
                                    }
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
                                                $deadline_label = __("Xong", "qlcv");
                                            }

                                            // $deadline_label = $work_percent=='100'?"100%":$deadline;

                                            echo "<tr>";
                                            echo '<td class="fw-600"><a href=' . get_permalink() . '>' . get_the_title() . '</a></td>';
                                            echo '<td>' . __($trangthai, 'qlcv') . '</td>';
                                            echo '<td><div class="progress" style="height: 24px;">
                                                            <div class="progress-bar" role="progressbar" style="width: ' . $work_percent . '%" aria-valuenow="' . $work_percent . '" aria-valuemin="0" aria-valuemax="100">' . $deadline . '</div>
                                                            </div>
                                                            </td>';
                                            echo '<td>
                                                            <a href="' . get_bloginfo('url') . '/sua-noi-dung-nhiem-vu/?taskid=' . get_the_ID() . '" class="button button-xs button-box button-android" data-tippy-content="' . __('Sửa nội dung nhiệm vụ', 'qlcv') . '"><i class="fa fa-pencil-square-o"></i></a>
                                                            <a href="' . get_bloginfo('url') . '/sua-task/?taskid=' . get_the_ID() . '" class="button button-xs button-box button-rss" id="quick_update" data-tippy-content="' . __('Sửa deadline và người xử lý', 'qlcv') . '"><i class="zmdi zmdi-assignment"></i></a>
                                                            <a href="' . get_permalink( ) . '?stt=Huỷ" class="button button-xs button-box button-reddit" data-tippy-content="' . __('Huỷ', 'qlcv') . '"><i class="fa fa-trash" onclick="return confirm(\'' . __('Bạn chắc chắn muốn hủy công việc này chứ?', 'qlcv') . '\')"></i></a>
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
                # show log of this job
                $logs = asl_get_logs(get_the_ID());
                # if have not log, don't show it
                if ($logs) {
                    // print_r($logs);
                    echo '<div class="box" style="margin-top:20px;">';
                    echo '<div class="box-head">';
                    echo '<h4 class="title">' . __('Lịch sử hoạt động', 'qlcv') . '</h4>';
                    echo '</div>';
                    echo '<div class="box-body">';
                    echo '<ul class="timeline-list">';
                    foreach ($logs as $log) {
                        $timestamp = strtotime($log->date); // Convert date string to numeric timestamp
                        echo '<li>';
                        echo '<span class="icon">';
                        echo "<img src='" . get_avatar_url($log->userid) . "' style='border-radius:40px;'/>";
                        echo '</span>';
                        echo '<div class="details">';
                        echo '<h5 class="title">';
                        echo get_the_author_meta('display_name', $log->userid);
                        echo '</h5>';
                        echo '<span class="time">' . time_elapsed_string($timestamp) . '</span>';
                        echo '<div class="content">';
                        echo '<p>';
                        echo $log->content;
                        echo '</p>';
                        echo '</div>';
                        echo '</div>';
                        echo '</li>';
                    }
                    echo '</ul>';
                    echo '</div>';
                    echo '</div>';
                }
                
                // Finance records section
                echo '<div class="box" style="margin-top:20px;">';
                echo '<div class="box-head">';
                echo '<div class="row justify-content-between">';
                echo '<div class="col-lg-auto">';
                echo '<h4 class="title">' . __('Danh sách phiếu thu chi', 'qlcv') . '</h4>';
                echo '</div>';
                echo '<div class="col-lg-auto">';
                // Quick action buttons for authorized users
                $current_user = wp_get_current_user();
                $job_author = get_post_field('post_author', get_the_ID());
                $is_job_creator = ($current_user->ID == $job_author);
                $is_admin = in_array('administrator', $current_user->roles);

                if ($is_job_creator || $is_admin) {
                    echo '<a href="' . get_bloginfo('url') . '/tao-phieu-thu-chi/?jobid=' . get_the_ID() . '&type=Thu" class="button button-sm button-success" style="margin-right: 5px;">';
                    echo '<i class="fa fa-plus"></i> ' . __('Thu', 'qlcv');
                    echo '</a>';
                    echo '<a href="' . get_bloginfo('url') . '/tao-phieu-thu-chi/?jobid=' . get_the_ID() . '&type=Chi" class="button button-sm button-danger">';
                    echo '<i class="fa fa-minus"></i> ' . __('Chi', 'qlcv');
                    echo '</a>';
                }
                echo '</div>';
                echo '</div>';
                echo '</div>';
                echo '<div class="box-body">';
                
                // Query finance records for this job
                $finance_args = array(
                    'post_type'     => 'finance',
                    'posts_per_page' => -1,
                    'meta_query'    => array(
                        array(
                            'key'       => 'finance_job',
                            'value'     => get_the_ID(),
                            'compare'   => '='
                        )
                    ),
                    'orderby'       => 'date',
                    'order'         => 'DESC'
                );
                
                // Polylang: Hiển thị tất cả ngôn ngữ
                if (function_exists('pll_languages_list')) {
                    $finance_args['lang'] = '';
                }
                
                $finance_query = new WP_Query($finance_args);
                
                // Store the main job ID before entering the loop
                $main_job_id = get_the_ID();
                
                if ($finance_query->have_posts()) {
                    echo '<div class="table-responsive">';
                    echo '<table class="table table-striped">';
                    echo '<thead>';
                    echo '<tr>';
                    echo '<th>' . __('Ngày', 'qlcv') . '</th>';
                    echo '<th>' . __('Loại', 'qlcv') . '</th>';
                    echo '<th>' . __('Người nhận', 'qlcv') . '</th>';
                    echo '<th>' . __('Số tiền', 'qlcv') . '</th>';
                    echo '<th>' . __('Lý do', 'qlcv') . '</th>';
                    echo '<th>' . __('Hành động', 'qlcv') . '</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';
                    
                    while ($finance_query->have_posts()) {
                        $finance_query->the_post();
                        
                        $finance_date = get_field('finance_date');
                        $finance_type = get_field('finance_type');
                        $finance_user = get_field('finance_user');
                        $finance_value = get_field('finance_value');
                        $finance_currency = get_field('finance_currency');
                        
                        // Format date
                        if ($finance_date) {
                            $date_formatted = DateTime::createFromFormat('Ymd', $finance_date);
                            $display_date = $date_formatted ? $date_formatted->format('d/m/Y') : $finance_date;
                        } else {
                            $display_date = get_the_date('d/m/Y');
                        }
                        
                        // Get user info
                        $user_name = $finance_user ? $finance_user['display_name'] : __('Không xác định', 'qlcv');
                        $user_company = $finance_user ? get_field('ten_cong_ty', 'user_' . $finance_user['ID']) : '';
                        
                        // Type styling
                        $type_class = ($finance_type == 'Thu') ? 'badge-success' : 'badge-danger';
                        
                        echo '<tr>';
                        echo '<td>' . esc_html($display_date) . '</td>';
                        echo '<td><span class="badge ' . $type_class . '">' . esc_html(__($finance_type, 'qlcv')) . '</span></td>';
                        echo '<td style="display: flex;flex-direction: column;">';
                        echo '<strong>' . esc_html($user_name) . '</strong>';
                        if ($user_company) {
                            echo '<small class="text-muted">' . esc_html($user_company) . '</small>';
                        }
                        echo '</td>';
                        echo '<td>';
                        if ($finance_value && $finance_currency) {
                            echo '<strong>' . number_format($finance_value) . ' ' . esc_html($finance_currency) . '</strong>';
                        }
                        echo '</td>';
                        echo '<td>' . esc_html(get_the_title()) . '</td>';
                        echo '<td>';
                        echo '<a href="' . get_permalink() . '" class="button button-xs button-primary" data-tippy-content="' . __('Xem chi tiết', 'qlcv') . '">';
                        echo '<i class="fa fa-eye"></i>';
                        echo '</a>';
                        
                        // Show delete button for job creator and admin
                        $current_user = wp_get_current_user();
                        $job_author = get_post_field('post_author', $main_job_id);
                        $is_job_creator = ($current_user->ID == $job_author);
                        $is_admin = in_array('administrator', $current_user->roles);
                        $finance_post_id = get_the_ID(); // This is the current finance post ID in the loop
                        
                        if ($is_job_creator || $is_admin) {
                            $delete_url = add_query_arg(array(
                                'delete_finance' => '1',
                                'finance_id' => $finance_post_id,
                                'job_id' => $main_job_id,
                                '_wpnonce' => wp_create_nonce('delete_finance_' . $finance_post_id)
                            ), get_permalink($main_job_id));
                            
                            echo '<a href="' . esc_url($delete_url) . '" class="button button-xs button-danger" ';
                            echo 'data-tippy-content="' . __('Xóa phiếu thu chi', 'qlcv') . '" ';
                            echo 'style="margin-left: 5px;" ';
                            echo 'onclick="return confirm(\'' . esc_js(__('Bạn có chắc chắn muốn xóa phiếu thu chi này? Hành động này không thể hoàn tác.', 'qlcv')) . '\')">';
                            echo '<i class="fa fa-trash"></i>';
                            echo '</a>';
                        }
                        
                        echo '</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody>';
                    echo '</table>';
                    echo '</div>';
                    
                    // Summary section
                    wp_reset_postdata();
                    
                    // Calculate totals
                    $total_income = 0;
                    $total_expense = 0;
                    $currencies = array();
                    
                    $finance_query->rewind_posts();
                    while ($finance_query->have_posts()) {
                        $finance_query->the_post();
                        
                        $finance_type = get_field('finance_type');
                        $finance_value = floatval(get_field('finance_value'));
                        $finance_currency = get_field('finance_currency');
                        
                        if ($finance_value && $finance_currency) {
                            if (!isset($currencies[$finance_currency])) {
                                $currencies[$finance_currency] = array('thu' => 0, 'chi' => 0);
                            }
                            
                            if ($finance_type == 'Thu') {
                                $currencies[$finance_currency]['thu'] += $finance_value;
                            } else {
                                $currencies[$finance_currency]['chi'] += $finance_value;
                            }
                        }
                    }
                    
                    // Display summary
                    if (!empty($currencies)) {
                        echo '<hr>';
                        echo '<div class="row">';
                        echo '<div class="col-12">';
                        echo '<h5>' . __('Tổng kết:', 'qlcv') . '</h5>';
                        
                        foreach ($currencies as $currency => $amounts) {
                            $balance = $amounts['thu'] - $amounts['chi'];
                            $balance_class = $balance >= 0 ? 'text-primary' : 'text-danger';
                            
                            echo '<div class="row mb-2">';
                            echo '<div class="col-md-3"><strong>' . esc_html($currency) . ':</strong></div>';
                            echo '<div class="col-md-3">';
                            echo '<span class="text-primary">' . __('Thu:', 'qlcv') . ' ' . number_format($amounts['thu']) . '</span>';
                            echo '</div>';
                            echo '<div class="col-md-3">';
                            echo '<span class="text-danger">' . __('Chi:', 'qlcv') . ' ' . number_format($amounts['chi']) . '</span>';
                            echo '</div>';
                            echo '<div class="col-md-3">';
                            echo '<span class="' . $balance_class . '">' . __('Tổng:', 'qlcv') . ' ' . number_format($balance) . '</span>';
                            echo '</div>';
                            echo '</div>';
                        }
                        
                        echo '</div>';
                        echo '</div>';
                    }
                    
                    wp_reset_postdata();
                } else {
                    echo '<div class="text-center py-4">';
                    echo '<i class="fa fa-info-circle text-muted" style="font-size: 48px;"></i>';
                    echo '<h5 class="text-muted mt-3">' . __('Chưa có phiếu thu chi nào', 'qlcv') . '</h5>';
                    echo '<p class="text-muted">' . __('Bấm vào nút "Thu" hoặc "Chi" ở trên để tạo phiếu mới', 'qlcv') . '</p>';
                    echo '</div>';
                }
                
                echo '</div>';
                echo '</div>';
                
                // Commission display section
                global $wpdb;
                $commissions = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM wp_aslcommission WHERE jobid = %d ORDER BY role_type, userid",
                    get_the_ID()
                ));
                
                if ($commissions) {
                    $job_author = get_post_field('post_author', get_the_ID());
                    $is_job_creator = ($current_user->ID == $job_author);
                    $is_admin = in_array('administrator', $current_user->roles);
                    $can_manage = $is_job_creator || $is_admin;
                    
                    echo '<div class="box" style="margin-top:20px;">';
                    echo '<div class="box-head">';
                    echo '<div class="row justify-content-between">';
                    echo '<div class="col-lg-auto">';
                    echo '<h4 class="title">' . __('Phân chia hoa hồng', 'qlcv') . '</h4>';
                    echo '</div>';
                    if ($can_manage) {
                        echo '<div class="col-lg-auto">';
                        echo '<a href="' . get_bloginfo('url') . '/chia-ty-le-hoa-hong/?job_id=' . get_the_ID() . '" class="button button-sm button-success">';
                        echo '<i class="fa fa-percentage"></i> ' . __('Quản lý chia %', 'qlcv');
                        echo '</a>';
                        echo '</div>';
                    }
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="box-body">';
                    
                    $total_value = get_field('total_value', get_the_ID());
                    $currency = get_field('currency', get_the_ID()) ?: 'VND';
                    $total_percent = 0;
                    $total_amount = 0;
                    
                    foreach ($commissions as $commission) {
                        $user_info = get_userdata($commission->userid);
                        $role_name = '';
                        
                        switch ($commission->role_type) {
                            case 'manager':
                                $role_name = __('Người quản lý', 'qlcv');
                                break;
                            case 'member':
                                $role_name = __('Thành viên thực hiện', 'qlcv');
                                break;
                            case 'co_manager':
                                $role_name = __('Đồng quản lý', 'qlcv');
                                break;
                            case 'co_member':
                                $role_name = __('Đồng thành viên', 'qlcv');
                                break;
                            case 'supervisor':
                                $role_name = __('Người giám sát', 'qlcv');
                                break;
                        }
                        
                        // Check if current user can see this commission
                        $can_view = $can_manage || ($current_user->ID == $commission->userid);
                        
                        if ($can_view) {
                            $percent = floatval($commission->commission_percent);
                            $amount = floatval($commission->commission_amount);
                            
                            // Format percent display
                            $display_percent = ($percent == intval($percent)) ? intval($percent) : $percent;
                            
                            echo '<div class="row commission-display-row" style="padding: 10px; margin-bottom: 5px; border-left: 3px solid #007bff; background-color: #f8f9fa;">';
                            echo '<div class="col-md-4">';
                            echo '<strong>' . esc_html($user_info->display_name) . '</strong>';
                            echo '<br><small class="text-muted">' . $role_name . '</small>';
                            echo '</div>';
                            echo '<div class="col-md-3">';
                            echo '<span class="badge badge-success">' . $display_percent . '%</span>';
                            echo '</div>';
                            echo '<div class="col-md-3">';
                            echo '<strong>' . number_format($amount) . ' ' . esc_html($currency) . '</strong>';
                            echo '</div>';
                            echo '<div class="col-md-2">';
                            if ($current_user->ID == $commission->userid) {
                                echo '<small class="text-primary"><i class="fa fa-user"></i> ' . __('Của bạn', 'qlcv') . '</small>';
                            }
                            echo '</div>';
                            echo '</div>';
                            
                            if ($can_manage) {
                                $total_percent += $percent;
                                $total_amount += $amount;
                            }
                        }
                    }
                    
                    // Show total only for job creator and admin
                    if ($can_manage && $total_percent > 0) {
                        $display_total_percent = ($total_percent == intval($total_percent)) ? intval($total_percent) : $total_percent;
                        
                        echo '<hr>';
                        echo '<div class="row" style="background-color: #e9ecef; padding: 10px; border-radius: 5px;">';
                        echo '<div class="col-md-4"><strong>' . __('Tổng cộng:', 'qlcv') . '</strong></div>';
                        echo '<div class="col-md-3"><strong><span class="badge badge-primary">' . $display_total_percent . '%</span></strong></div>';
                        echo '<div class="col-md-3"><strong>' . number_format($total_amount) . ' ' . esc_html($currency) . '</strong></div>';
                        echo '<div class="col-md-2"></div>';
                        echo '</div>';
                        
                        // Show validation message if not 100%
                        if ($total_percent != 100) {
                            if ($total_percent > 100) {
                                echo '<div class="row" style="padding: 5px 10px;">';
                                echo '<div class="col-12"><small class="text-danger"><i class="fa fa-exclamation-triangle"></i> ' . __('Cảnh báo: Tổng tỷ lệ vượt quá 100%!', 'qlcv') . '</small></div>';
                                echo '</div>';
                            } else {
                                echo '<div class="row" style="padding: 5px 10px;">';
                                echo '<div class="col-12"><small class="text-warning"><i class="fa fa-exclamation-triangle"></i> ' . __('Chưa phân chia hết 100%', 'qlcv') . '</small></div>';
                                echo '</div>';
                            }
                        }
                    }
                    
                    echo '</div>';
                    echo '</div>';
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
                                if($business) echo $business . "<br>";
                                if($phone) echo $phone . "<br>";
                                if($email) echo $email . "<br>";
                                if($address) echo $address . "<br>";
                                if($quoc_gia) echo $quoc_gia . "<br>";
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