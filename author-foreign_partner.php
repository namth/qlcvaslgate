<?php 
    get_header();

    get_sidebar();
    $this_user = get_queried_object(); 
?>

        <!-- Content Body Start -->
        <div class="content-body">

            <!-- Page Headings Start -->
            <div class="row justify-content-between align-items-center mb-10">

                <!-- Page Heading Start -->
                <div class="col-12 col-lg-auto mb-20">
                    <div class="page-heading">
                        <a class="mb-10" href="<?php echo get_bloginfo('url'); ?>/danh-sach-nhan-su/?role=<?php echo $this_user->roles[0]; ?>">← <?php _e('Danh sách đối tác', 'qlcv'); ?></a>
                        <h3 class="title"><?php _e('Hồ sơ nhân sự', 'qlcv'); ?></h3>
                    </div>
                </div><!-- Page Heading End -->

            </div><!-- Page Headings End -->

            <div class="row mbn-50">
                <?php 
                    $current_user = wp_get_current_user();
                    
                    //$this_user = wp_get_this_user();

                    $link_avatar = get_avatar_url($this_user->ID);
                    // print_r($link_avatar);
                    if ( !empty( $this_user->roles ) && is_array( $this_user->roles ) ) {
                        foreach ( $this_user->roles as $role )
                            $roles[] = translate_user_role($wp_roles->roles[$role]['name']);
                    }
                ?>

                <!--Author Top Start-->
                <div class="col-12 mb-50">
                    <div class="author-top">
                        <div class="inner">
                            <div class="author-profile">
                                <div class="image">
                                    <img src="<?php echo $link_avatar; ?>" alt="">
                                    <button class="edit"><i class="zmdi zmdi-cloud-upload"></i>Change Image</button>
                                </div>
                                <div class="info mb-20">
                                    <h5><?php echo $this_user->display_name; ?></h5>
                                    <span><?php echo implode(', ',$roles); ?></span>
                                    <a href="#" class="edit"><i class="zmdi zmdi-edit"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--Author Top End-->

                <!--Right Sidebar Start-->
                <div class="col-xlg-12 col-12 mb-50">
                    <div class="row mbn-30">

                        <!--Author Information Start-->
                        <div class="col-xlg-6 col-lg-6 col-12 mb-30">
                            <div class="box">
                                <div class="box-head">
                                    <h3 class="title"><?php _e('Thông tin cá nhân', 'qlcv'); ?></h3>
                                </div>
                                <?php 
                                    $so_dien_thoai  = get_field('so_dien_thoai' , 'user_' . $this_user->ID);
                                    $dia_chi        = get_field('dia_chi' , 'user_' . $this_user->ID);
                                    $quoc_gia       = get_field('quoc_gia' , 'user_' . $this_user->ID);
                                    $email_cc       = get_field('email_cc' , 'user_' . $this_user->ID);
                                    $email_bcc      = get_field('email_bcc' , 'user_' . $this_user->ID);
                                    $partner_code   = get_field('partner_code' , 'user_' . $this_user->ID);
                                    $ten_cong_ty    = get_field('ten_cong_ty' , 'user_' . $this_user->ID);
                                ?>
                                <div class="box-body">
                                    <div class="order-details-customer-info">
                                        <ul class="mb-30">
                                            <li><span><i class="ti-user"></i> <?php _e('Mã đối tác', 'qlcv'); ?></span><span> <?php echo $partner_code; ?></span></li>
                                            <li><span><i class="ti-layout-column4"></i> <?php _e('Tên công ty', 'qlcv'); ?></span><span> <?php echo $ten_cong_ty; ?></span></li>
                                            <li><span><i class="ti-email"></i> Email</span><span> <?php echo $this_user->user_email; ?></span></li>
                                            <li><span><i class="ti-email"></i> Email CC</span><span> <?php echo $email_cc; ?></span></li>
                                            <li><span><i class="ti-email"></i> Email BCC</span><span> <?php echo $email_bcc; ?></span></li>
                                            <li><span><i class="ti-mobile"></i> <?php _e('Số điện thoại', 'qlcv'); ?></span><span> <?php echo $so_dien_thoai; ?></span></li>
                                            <li>
                                                <span><i class="ti-map-alt"></i> <?php _e('Địa chỉ', 'qlcv'); ?></span>
                                                <span> <?php echo $dia_chi; ?></span>
                                            </li>
                                            <li>
                                                <span><i class="ti-world"></i> <?php _e('Quốc gia', 'qlcv'); ?></span>
                                                <span> <?php echo $quoc_gia; ?></span>
                                            </li>
                                            <li>
                                                <span><i class="ti-direction"></i> <?php _e('Ghi chú', 'qlcv'); ?></span>
                                                <span> <?php echo $this_user->description; ?></span>
                                            </li>
                                        </ul>

                                        <!-- if current user isn't this user, don't show edit button -->
                                        <?php 
                                            if (($current_user->ID == $this_user->ID) || in_array('contributor', $current_user->roles) ) {
                                                echo '<a href="' . get_bloginfo('url') . '/sua-thong-tin-doi-tac/?uid=' . $this_user->ID . '" class="button button-primary"><span><i class="fa fa-edit"></i>' . __('Chỉnh sửa', 'qlcv') . '</span></a>';
                                            }
                                        ?>
                                        
                                    </div>
                                </div>

                            </div>
                        </div>
                        <!--Author Information End-->

                        <!-- To Do List Start -->
                        <div class="col-xlg-6 col-lg-6 col-12 mb-30">
                            <div class="box">

                                <div class="box-head">
                                    <h3 class="title"><?php _e('Danh sách công việc', 'qlcv'); ?></h3>
                                </div>

                                <div class="box-body p-0">
                                    <?php 
                                        $args   = array(
                                            'post_type'     => 'job',
                                            'posts_per_page'=> 5,
                                        );
                                        $args['meta_query'][] = array(
                                            'relation' => 'OR',
                                            array(
                                                'key'       => 'partner_2',
                                                'value'     => $this_user->ID,
                                                'compare'   => '=',
                                            ),
                                            array(
                                                'key'       => 'partner_1',
                                                'value'     => $this_user->ID,
                                                'compare'   => '=',
                                            ),
                                        );

                                        $query = new WP_Query( $args );
                                    ?>
                                    <!--Todo List Start-->
                                    <ul class="todo-list">
                                        <?php 
                                            if( $query->have_posts() ) {
                                                while ( $query->have_posts() ) {
                                                    $query->the_post();

                                        ?>
                                                    <!--Todo Item Start-->
                                                    <li>
                                                        <div class="list-action">
                                                            <button class="status"><i class="zmdi zmdi-star-outline"></i></button>
                                                            <label class="adomx-checkbox"><input type="checkbox"> <i class="icon"></i></label>
                                                            <button class="remove"><i class="zmdi zmdi-delete"></i></button>
                                                        </div>
                                                        <div class="list-content">
                                                            <?php 
                                                                echo '<a href="' . get_permalink() . '">';
                                                                echo get_the_title(); 
                                                                echo '</a>';
                                                            ?>
                                                        </div>
                                                        <div class="list-action right">
                                                            <button class="remove"><i class="zmdi zmdi-delete"></i></button>
                                                        </div>
                                                    </li>
                                                    <!--Todo Item End-->
                                        <?php 
                                                } wp_reset_postdata();
                                            }
                                        ?>
                                    </ul>
                                    <!--Todo List End-->

                                </div>
                            </div>
                        </div><!-- To Do List End -->

                    </div>
                </div>
                <!--Right Sidebar End-->


            </div>
        </div><!-- Content Body End -->

<?php 
  get_footer();
?>