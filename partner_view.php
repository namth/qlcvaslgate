<?php
/*
  Template Name: Trang job dành cho đối tác
*/
if ((isset($_POST['code'])  && ($_POST['code'] != ""))) {

    get_template_part('header', 'nologin');

    // get_sidebar();

    $postid = base64_decode($_POST['code']);
    $current_post = get_post($postid);

    $phan_loai  = get_field('phan_loai', $postid);
    $deadline   = get_field('deadline', $postid);
    $trang_thai = get_field('trang_thai', $postid);
    $link_onedrive = get_field('link_onedrive', $postid);
?>

    <!-- Content Body Start -->
    <div class="content-body">

        <!-- Page Headings Start -->
        <div class="row justify-content-between mb-10">

            <!-- Page Heading Start -->
            <div class="col-8 col-lg-8 mb-20">
                <div class="box">
                    <div class="page-heading box-head">
                        <h3 class="mb-10"><?php echo get_the_title($postid); ?> </h3>
                    </div>
                    <div class="box-body">
                        <div class="d-flex justify-content-between row mbn-20">
                            <!--Thông tin job-->
                            <div class="text-left col-12 col-sm-auto mb-20">
                                <h4 class="fw-600">Chi tiết công việc</h4>
                                <?php
                                switch ($phan_loai) {
                                    case 'Nhãn hiệu':
                                        $logo           = get_field('logo', $postid);
                                        $ten_nhan_hieu  = get_field('ten_nhan_hieu', $postid);
                                        $nhom           = get_field('nhom', $postid);
                                        $so_luong_nhom  = get_field('so_luong_nhom', $postid);

                                        echo "<p>";
                                        if ($logo) {
                                            echo "<img src='" . $logo . "' width='160' class='mb-10'/><br>";
                                        }
                                        echo "Tên nhãn hiệu: " . $ten_nhan_hieu . "<br>";
                                        echo "Nhóm: " . $nhom . "<br>";
                                        echo "Số lượng nhóm: " . $so_luong_nhom . "<br>";
                                        echo "</p>";
                                        break;

                                    case 'Sáng chế':
                                        $ban_mo_ta_sang_che                 = get_field('ban_mo_ta_sang_che', $postid);
                                        $so_luong_yeu_cau_bao_ho            = get_field('so_luong_yeu_cau_bao_ho', $postid);
                                        $so_luong_yeu_cau_bao_ho_doc_lap    = get_field('so_luong_yeu_cau_bao_ho_doc_lap', $postid);

                                        echo "<p>";
                                        echo "Bản mô tả sáng chế: " . $ban_mo_ta_sang_che . "<br>";
                                        echo "Số lượng yêu cầu bảo hộ: " . $so_luong_yeu_cau_bao_ho . "<br>";
                                        echo "Số lượng yêu cầu bảo hộ độc lập: " . $so_luong_yeu_cau_bao_ho_doc_lap . "<br>";
                                        echo "</p>";
                                        break;

                                    case 'Kiểu dáng':
                                        $bo_anh                = get_field('bo_anh', $postid);
                                        $ban_mo_ta_cua_bo_anh  = get_field('ban_mo_ta_cua_bo_anh', $postid);
                                        $so_luong_phuong_an    = get_field('so_luong_phuong_an', $postid);

                                        echo "<p>";
                                        echo "Bộ ảnh: " . $bo_anh . "<br>";
                                        echo "Bản mô tả của bộ ảnh: " . $ban_mo_ta_cua_bo_anh . "<br>";
                                        echo "Số lượng phương án: " . $so_luong_phuong_an . "<br>";
                                        echo "</p>";
                                        break;
                                }

                                echo "<br>";
                                echo $current_post->post_content;
                                echo "<br>";

                                if ($link_onedrive) {
                                    echo "<h5>Tài liệu đi kèm</h5>";
                                    echo auto_url($link_onedrive);
                                }
                                ?>
                            </div>

                        </div>
                    </div>
                </div>

            </div><!-- Page Heading End -->

            <div class="col-4 col-lg-4">
                <div class="box mb-20">
                    <div class="page-heading box-head">
                        <h3 class="mb-10">Thông tin khách hàng</h3>
                    </div>
                    <div class="box-body">
                        <div class="d-flex justify-content-between row mb-20">
                            <!--Thông tin khách hàng-->
                            <div class="col-12 col-sm-auto mb-20">
                                <?php
                                $customer = get_field('customer', $postid);
                                $id_customer = $customer->ID;

                                $name           = get_the_title($id_customer);
                                $business       = get_field('ten_cong_ty', $id_customer);
                                $phone          = get_field('so_dien_thoai', $id_customer);
                                $email          = get_field('email', $id_customer);
                                $address        = get_field('dia_chi', $id_customer);
                                $quoc_gia       = get_field('quoc_gia', $id_customer);

                                echo "<p>";
                                echo "<b>" . $name . "</b><br>";
                                echo $business . "<br>";
                                echo $phone . "<br>";
                                echo $email . "<br>";
                                echo $address . "<br>";
                                echo $quoc_gia . "<br>";
                                echo "</p>";

                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box mb-20">
                    <div class="page-heading box-head">
                        <h3 class="mb-10">Thông tin đối tác</h3>
                    </div>
                    <div class="box-body">
                        <div class="d-flex justify-content-between row mb-20">
                            <!--Thông tin khách hàng-->
                            <div class="col-12 col-sm-auto mb-20">
                                <?php
                                $partner = get_field('partner_2', $postid);
                                echo $partner['user_avatar'];

                                $business       = get_field('ten_cong_ty', 'user_' . $partner['ID']);
                                $phone          = get_field('so_dien_thoai', 'user_' . $partner['ID']);
                                $address        = get_field('dia_chi', 'user_' . $partner['ID']);

                                echo "<p>";
                                echo "<b>" . $partner['display_name'] . "</b><br>";
                                echo $business . "<br>";
                                echo $phone . "<br>";
                                echo $partner['user_email'] . "<br>";
                                echo $address . "<br>";
                                echo "</p>";

                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box mb-20">
                    <div class="page-heading box-head">
                        <h4 class="mb-10">Lịch sử công việc</h4>
                    </div>
                    <div class="box-body">
                        <div class="d-flex justify-content-between row mb-20">
                            <div class="col-12 mb-20">
                                <?php
                                # show các kết quả đã đạt được thông qua lịch sử công việc
                                $lich_su_cong_viec = get_field('lich_su_cong_viec');
                                # print_r($lich_su_cong_viec);
                                if (have_rows('lich_su_cong_viec')) {
                                    while (have_rows('lich_su_cong_viec')) {
                                        the_row();

                                        $mo_ta      = get_sub_field('mo_ta');
                                        $ngay_thang = get_sub_field('ngay_thang');

                                        echo $mo_ta . ": " . $ngay_thang . "<br>";
                                    }
                                }

                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div><!-- Page Headings End -->

    </div><!-- Content Body End -->

<?php
} else {
    get_template_part('header', 'nologin');
?>
    <div class="content-body">

        <!-- Page Headings Start -->
        <div class="row justify-content-between mb-10">

            <!-- Page Heading Start -->
            <div class="col-12 col-lg-12 mb-20">
                <form action="#" method="post" class="enter_code">
                    <input type="text" name="code">
                    <input type="submit" value="Nhập">
                </form>
            </div>
        </div>
    </div>

<?php
}

get_footer();
?>