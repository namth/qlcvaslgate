<?php 
    $url = get_bloginfo('url');
?>
        <!-- Side Header Start -->
        <div class="side-header show">
            <button class="side-header-close"><i class="zmdi zmdi-close"></i></button>
            <!-- Side Header Inner Start -->
            <div class="side-header-inner custom-scroll">

                <nav class="side-header-menu" id="side-header-menu">
                    <?php 
                        wp_nav_menu(array(
                            'container' => '',
                        ));
                    ?>
                    <!-- <ul>
                        <li><a href="#"><i class="ti-home"></i> <span>Bảng tin</span></a>
                        </li>
                        <li class="has-sub-menu"><a href="#"><i class="ti-package"></i> <span>Các công việc</span></a>
                            <ul class="side-header-sub-menu">
                                <li><a href="http://localhost/qlcv/danh-sach-cong-viec/?type=Nhãn hiệu"><span>Nhãn hiệu</span></a></li>
                                <li><a href="http://localhost/qlcv/danh-sach-cong-viec/?type=Sáng chế"><span>Sáng chế</span></a></li>
                                <li><a href="http://localhost/qlcv/danh-sach-cong-viec/?type=Kiểu dáng"><span>Kiểu dáng</span></a></li>
                                <li><a href="http://localhost/qlcv/danh-sach-cong-viec/?type=Việc khác"><span>Việc khác</span></a></li>
                            </ul>
                        </li>
                        <li class="has-sub-menu"><a href="#"><i class="ti-crown"></i> <span>Nhiệm vụ</span></a>
                            <ul class="side-header-sub-menu">
                                <li><a href="<?php echo $url; ?>/danh-sach-cong-viec/"><span>Danh sách công việc</span></a></li>
                                <li><a href="<?php echo $url; ?>/tao-dau-viec-moi/"><span>Tạo công việc mới</span></a></li>
                            </ul>
                        </li>
                        <li class="has-sub-menu"><a href="#"><i class="ti-stamp"></i> <span>Khách hàng</span></a>
                            <ul class="side-header-sub-menu">
                                <li><a href="icons-cryptocurrency.html"><span>Danh sách khách hàng</span></a></li>
                                <li><a href="icons-fontawesome.html"><span>Tạo khách hàng mới</span></a></li>
                            </ul>
                        </li>
                        <li class="has-sub-menu"><a href="#"><i class="ti-stamp"></i> <span>Nhân viên</span></a>
                            <ul class="side-header-sub-menu">
                                <li><a href="icons-cryptocurrency.html"><span>Danh sách nhân viên</span></a></li>
                                <li><a href="icons-fontawesome.html"><span>Tạo nhân viên mới</span></a></li>
                            </ul>
                        </li>
                        <li class="has-sub-menu"><a href="#"><i class="ti-notepad"></i> <span>Cài đặt</span></a>
                            <ul class="side-header-sub-menu">
                                <li><a href="form-basic-elements.html"><span>Hồ sơ của bạn</span></a></li>
                                <li><a href="form-checkbox.html"><span>Cập nhật thông tin</span></a></li>
                                <li><a href="form-date-mask.html"><span>Đổi mật khẩu</span></a></li>
                            </ul>
                        </li>
                        <li class="has-sub-menu"><a href="#"><i class="ti-layout"></i> <span>Báo cáo</span></a>
                            <ul class="side-header-sub-menu">
                                <li><a href="table-basic.html"><span>Basic</span></a></li>
                                <li><a href="table-data-table.html"><span>Data Table</span></a></li>
                                <li><a href="table-footable.html"><span>Footable</span></a></li>
                                <li><a href="table-jsgrid.html"><span>Jsgrid</span></a></li>
                            </ul>
                        </li>
                        <li><a href="widgets.html"><i class="ti-palette"></i> <span>Đăng xuất</span></a></li>

                    </ul> -->
                </nav>

            </div><!-- Side Header Inner End -->
        </div><!-- Side Header End -->
