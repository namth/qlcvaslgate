<?php 
  get_header();

  get_sidebar();
  $this_user = get_queried_object(); 
  $current_user = wp_get_current_user();
?>

        <!-- Content Body Start -->
        <div class="content-body">

            <!-- Page Headings Start -->
            <div class="row justify-content-between align-items-center mb-10">

                <!-- Page Heading Start -->
                <div class="col-12 col-lg-auto mb-20">
                    <div class="page-heading">
                        <a class="mb-10" href="<?php echo get_bloginfo('url'); ?>/danh-sach-nhan-su/?role=<?php echo $current_user->roles[0]; ?>">← <?php _e('Danh sách nhân sự', 'qlcv'); ?></a>
                        <h3 class="title"><?php _e('Hồ sơ nhân sự', 'qlcv'); ?></h3>
                    </div>
                </div><!-- Page Heading End -->

            </div><!-- Page Headings End -->

            <div class="row mbn-50">
                <?php 
                    $link_avatar = get_avatar_url($current_user->ID);
                    // print_r($link_avatar);
                    if ( !empty( $current_user->roles ) && is_array( $current_user->roles ) ) {
                        foreach ( $current_user->roles as $role )
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
                                    <h5><?php echo $current_user->display_name; ?></h5>
                                    <span><?php echo implode(', ',$roles); ?></span>
                                    <a href="#" class="edit"><i class="zmdi zmdi-edit"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--Author Top End-->

                <!--Right Sidebar Start-->
                <div class="col-xlg-4 col-12 mb-50">
                    <div class="row mbn-30">

                        <!--Author Information Start-->
                        <div class="col-xlg-12 col-lg-6 col-12 mb-30">
                            <div class="box">
                                <div class="box-head">
                                    <h3 class="title"><?php _e('Thông tin cá nhân', 'qlcv'); ?></h3>
                                </div>
                                <?php 
                                    $so_dien_thoai  = get_field('so_dien_thoai' , 'user_' . $this_user->ID);
                                    $dia_chi        = get_field('dia_chi' , 'user_' . $this_user->ID);
                                    $quoc_gia       = get_field('quoc_gia' , 'user_' . $this_user->ID);
                                ?>
                                <div class="box-body">
                                    <div class="order-details-customer-info">
                                        <ul class="mb-30">
                                            <li><span><i class="ti-email"></i> Email</span><span> <?php echo $this_user->user_email; ?></span></li>
                                            <li><span><i class="ti-mobile"></i> <?php _e('Số điện thoại', 'qlcv'); ?></span><span> <?php echo $so_dien_thoai; ?></span></li>
                                            <li>
                                                <span><i class="ti-map-alt"></i> <?php _e('Địa chỉ', 'qlcv'); ?></span>
                                                <span> <?php echo $dia_chi; ?></span>
                                            </li>
                                            <li>
                                                <span><i class="ti-world"></i> <?php _e('Quốc gia', 'qlcv'); ?></span>
                                                <span> <?php echo $quoc_gia; ?></span>
                                            </li>
                                        </ul>

                                       <?php 
                                            if (($current_user->ID == $this_user->ID) || in_array('administrator', $current_user->roles) ) {
                                                echo '<a href="' . get_bloginfo('url') . '/sua-thong-tin/?uid=' . $this_user->ID . '" class="button button-primary"><span><i class="fa fa-edit"></i>' . __('Chỉnh sửa', 'qlcv') . '</span></a>';
                                            }
                                        ?>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <!--Author Information End-->

                        <!-- To Do List Start -->
                        <div class="col-xlg-12 col-lg-6 col-12 mb-30">
                            <div class="box">

                                <div class="box-head">
                                    <h3 class="title"><?php _e('Danh sách công việc', 'qlcv'); ?></h3>
                                </div>

                                <div class="box-body p-0">
                                    <?php 
                                        $args   = array(
                                            'post_type'     => 'job',
                                            'posts_per_page'=> 20,
                                        );
                                        $args['meta_query'][] = array(
                                            'relation' => 'OR',
                                            array(
                                                'key'       => 'member',
                                                'value'     => $this_user->ID,
                                                'compare'   => '=',
                                            ),
                                            array(
                                                'key'       => 'manager',
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

                                                    if (check_finish_job(get_the_ID(  ))) {
                                                        $class = "zmdi zmdi-badge-check";
                                                    } else $class = "fa fa-file-text-o";

                                        ?>
                                                    <!--Todo Item Start-->
                                                    <li>
                                                        <div class="list-action">
                                                            <i class="<?php echo $class; ?>"></i>
                                                        </div>
                                                        <div class="list-content">
                                                            <?php 
                                                                echo '<a href="' . get_permalink() . '">';
                                                                echo get_the_title(); 
                                                                echo '</a>';
                                                            ?>
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