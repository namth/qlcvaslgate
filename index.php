<?php 
  get_header();

  get_sidebar();

  $current_user = wp_get_current_user();
?>

        <!-- Content Body Start -->
        <div class="content-body">

            <!-- Page Headings Start -->
            <div class="row justify-content-between align-items-center mb-10">

                <!-- Page Heading Start -->
                <div class="col-12 col-lg-auto mb-20">
                    <div class="page-heading">
                        <h3><?php _e('Danh sách các chức năng', 'qlcv'); ?></h3>
                    </div>
                </div><!-- Page Heading End -->

            </div><!-- Page Headings End -->

            <div class="row mbn-30">
                <!-- Recent Transaction Start -->
                <div class="col-12 mb-30">
                    <table>
                        <tr>
                            <td>
                                <a href="<?php echo get_bloginfo('url'); ?>/danh-sach-nhiem-vu/" class="button button-steam full-box">
                                    <i class="zmdi zmdi-collection-item"></i><span><?php _e('Danh sách nhiệm vụ', 'qlcv'); ?></span>
                                </a>
                            </td>
                            <td>
                                <a href="<?php echo get_bloginfo('url'); ?>/danh-sach-cong-viec/" class="button button-steam full-box">
                                    <i class="zmdi zmdi-card-travel"></i><span><?php _e('Danh sách công việc', 'qlcv'); ?></span>
                                </a>
                            </td>
                            <?php 
                                if ( in_array('administrator', $current_user->roles) ) {
                                
                            ?>                                  
                            <td>
                                <a href="<?php echo get_bloginfo('url'); ?>/danh-sach-khach-hang/" class="button button-steam full-box">
                                    <i class="zmdi zmdi-assignment-account"></i><span><?php _e('Danh sách khách hàng', 'qlcv'); ?></span>
                                </a>
                            </td>                                        
                            <td>
                                <a href="<?php echo get_bloginfo('url'); ?>/danh-sach-nhan-su/?role=partner" class="button button-steam full-box">
                                    <i class="zmdi zmdi-accounts-outline"></i><span><?php _e('Danh sách đối tác', 'qlcv'); ?></span>
                                </a>
                            </td>
                            <?php 
                                }
                            ?>                                  
                            <td>
                                <a href="<?php echo get_bloginfo('url'); ?>/danh-sach-nhan-su/?role=member" class="button button-steam full-box">
                                    <i class="zmdi zmdi-steam"></i><span><?php _e('Danh sách nhân viên', 'qlcv'); ?></span>
                                </a>
                            </td>
                        </tr>
                        <tr>                                       
                            <td>
                                <a href="<?php echo get_bloginfo('url'); ?>/tao-dau-viec-moi/" class="button button-css3 full-box">
                                    <i class="zmdi zmdi-plus-circle-o-duplicate"></i><span><?php _e('Tạo đầu công việc mới', 'qlcv'); ?></span>
                                </a>
                            </td>                                        
                            <td>
                                <a href="" class="button button-css3 full-box">
                                    <i class="zmdi zmdi-account-box-mail"></i><span><?php _e('Tạo khách hàng mới', 'qlcv'); ?></span>
                                </a>
                            </td>                                        
                            <td>
                                <a href="<?php echo get_bloginfo('url'); ?>/them-doi-tac-moi/" class="button button-css3 full-box">
                                    <i class="zmdi zmdi-account-add"></i><span><?php _e('Tạo đối tác mới', 'qlcv'); ?></span>
                                </a>
                            </td>                                        
                            <?php 
                                if ( in_array('administrator', $current_user->roles) ) {
                                
                            ?>
                                <td>
                                    <a href="<?php echo get_bloginfo('url'); ?>/them-nhan-su-moi/" class="button button-css3 full-box">
                                        <i class="zmdi zmdi-account-o"></i><span><?php _e('Tạo nhân sự mới', 'qlcv'); ?></span>
                                    </a>
                                </td>
                            </tr>
                            <tr>    
                                <td>
                                    <a href="<?php echo get_bloginfo('url'); ?>/bao-cao/" class="button button-skype full-box">
                                        <i class="zmdi zmdi-shape"></i><span><?php _e('Báo cáo', 'qlcv'); ?></span>
                                    </a>
                                </td>
                            <?php
                                } else {
                                    echo "</tr><tr>";
                                }
                            ?>
                            <td>                                        
                                <a href="<?php echo get_bloginfo('url'); ?>/author/qlcv/" class="button button-skype full-box">
                                    <i class="zmdi zmdi-settings"></i><span><?php _e('Hồ sơ của bạn', 'qlcv'); ?></span>
                                </a>
                            </td>
                        </tr>
                    </table>
                </div><!-- Recent Transaction End -->

            </div>

        </div><!-- Content Body End -->

<?php 
  get_footer();
?>