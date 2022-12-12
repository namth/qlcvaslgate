<?php
if (!is_user_logged_in()) {
    if (!is_page_template('partner_view.php')) {
        wp_redirect(get_bloginfo('url') . "/login/");
        exit;
    }
}

$current_user = wp_get_current_user();
$logo = get_field('logo', 'option');
?>
<!doctype html>
<html class="no-js" <?php language_attributes(); ?>>

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="robots" content="noindex, follow" />
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo get_template_directory_uri(); ?>/assets/images/favicon.ico">

    <!-- CSS ============================================ -->

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/vendor/bootstrap.min.css">

    <!-- Icon Font CSS -->
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/vendor/material-design-iconic-font.min.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/vendor/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/vendor/themify-icons.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/vendor/cryptocurrency-icons.css">

    <!-- Plugins CSS -->
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/plugins/plugins.css">

    <!-- Helper CSS -->
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/helper.css">

    <!-- Main Style CSS -->
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/style.css">

    <?php wp_head(); ?>
    <!-- Custom Style CSS Only For Demo Purpose -->
    <link id="cus-style" rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/style-primary.css">
</head>

<body>

    <div class="main-wrapper">


        <!-- Header Section Start -->
        <div class="header-section">
            <div class="container-fluid">
                <div class="row justify-content-between align-items-center">

                    <!-- Header Logo (Header Left) Start -->
                    <div class="header-logo col-auto">
                        <a href="<?php echo get_bloginfo('url'); ?>">
                            <img src="<?php echo $logo; ?>" alt="">
                        </a>
                    </div><!-- Header Logo (Header Left) End -->

                    <!-- Header Right Start -->
                    <div class="header-right flex-grow-1 col-auto">
                        <div class="row justify-content-between align-items-center">

                            <!-- Side Header Toggle & Search Start -->
                            <div class="col-auto">
                                <div class="row align-items-center">

                                    <!--Side Header Toggle-->
                                    <div class="col-auto"><button class="side-header-toggle"><i class="zmdi zmdi-menu"></i></button></div>

                                    <!--Header Search-->
                                    <div class="col-auto">

                                        <div class="header-search">

                                            <button class="header-search-open d-block d-xl-none"><i class="zmdi zmdi-search"></i></button>

                                            <div class="header-search-form">
                                                <form action="<?php echo home_url('/'); ?>" method="post">
                                                    <input type="text" name="s" placeholder="Search Here">
                                                    <button><i class="zmdi zmdi-search"></i></button>
                                                </form>
                                                <button class="header-search-close d-block d-xl-none"><i class="zmdi zmdi-close"></i></button>
                                            </div>

                                        </div>
                                    </div>

                                </div>
                            </div><!-- Side Header Toggle & Search End -->

                            <!-- Header Notifications Area Start -->
                            <div class="col-auto">

                                <ul class="header-notification-area">
                                    <?php
                                    // print_r($current_user);

                                    $link_user = get_author_posts_url($current_user->ID);

                                    # read notification
                                    $args   = array(
                                        'post_type'     => 'notification',
                                        'posts_per_page' => 10,
                                    );
                                    # get only new notification
                                    $argument = $args;
                                    # for admin
                                    if (in_array('administrator', $current_user->roles)) {
                                        $argument['meta_query']   = array(
                                            array(
                                                'key'       => 'admin_new_notif',
                                                'compare'   => '=',
                                                'value'     => '1',
                                            ),
                                        );
                                    } else { #for manager
                                        $args['meta_query']     = array(
                                            'relation'      => 'OR',
                                            array(
                                                'key'       => 'receiver',
                                                'compare'   => '=',
                                                'value'     => $current_user->ID,
                                            ),
                                            array(
                                                'key'       => 'manager',
                                                'compare'   => '=',
                                                'value'     => $current_user->ID,
                                            ),
                                        );
                                        $argument['meta_query'] = array(
                                            'relation'      => 'AND',
                                            array(
                                                'key'       => 'receiver',
                                                'compare'   => '=',
                                                'value'     => $current_user->ID,
                                            ),
                                            array(
                                                'key'       => 'manager_new_notif',
                                                'compare'   => '=',
                                                'value'     => '1',
                                            ),
                                        );
                                    }
                                    $query = new WP_Query($args);
                                    $new_notif = new WP_Query($argument);

                                    if (in_array('administrator', $current_user->roles)) {
                                    ?>

                                    <li class="adomx-dropdown col-auto">
                                        <a class="button button-primary button-xs" href="<?php echo get_bloginfo('url') ?>/wp-admin"><i class="zmdi zmdi-code-setting"></i>
                                            Admin
                                        </a>
                                    </li>
                                    <?php 
                                    }
                                    ?>
                                    
                                    <!--Mail-->
                                    <li class="adomx-dropdown col-auto">
                                        <a class="toggle" href="#"><i class="zmdi zmdi-notifications"></i>
                                            <?php
                                            if ($new_notif->have_posts()) {
                                                echo '<span class="badge"></span>';
                                            }
                                            ?>
                                        </a>

                                        <!-- Dropdown -->
                                        <div class="adomx-dropdown-menu dropdown-menu-mail">
                                            <div class="head">
                                                <h4 class="title">Thông báo mới</h4>
                                            </div>
                                            <div class="body custom-scroll">
                                                <ul>
                                                    <?php
                                                    if ($query->have_posts()) {
                                                        while ($query->have_posts()) {
                                                            $query->the_post();

                                                            $link               = get_field('destination');
                                                            $admin_new_notif    = get_field('admin_new_notif');
                                                            $manager_new_notif  = get_field('manager_new_notif');
                                                    ?>
                                                            <li>
                                                                <a href="<?php echo $link; ?>">
                                                                    <div class="content">
                                                                        <!-- <h6>Sub: New Account</h6> -->
                                                                        <p><?php the_title(); ?></p>
                                                                    </div>
                                                                    <?php
                                                                    if (in_array('administrator', $current_user->roles)) {
                                                                        if ($admin_new_notif) {
                                                                            echo '<span class="reply"><i class="zmdi zmdi-circle notif_icon"></i></span>';
                                                                        }
                                                                    } else {
                                                                        if ($manager_new_notif) {
                                                                            echo '<span class="reply"><i class="zmdi zmdi-circle"></i></span>';
                                                                        }
                                                                    }
                                                                    ?>
                                                                </a>
                                                            </li>
                                                    <?php
                                                        }
                                                        wp_reset_postdata();
                                                    }
                                                    ?>
                                                </ul>
                                            </div>
                                            <div class="footer">
                                                <a href="<?php echo get_bloginfo('url'); ?>/list-notification" class="view-all">Xem tất cả</a>
                                            </div>
                                        </div>

                                    </li>

                                    <!--User-->
                                    <li class="adomx-dropdown col-auto">
                                        <a class="toggle" href="#">
                                            <span class="user">
                                                <span class="avatar">
                                                    <?php echo get_avatar($current_user->ID); ?>
                                                    <span class="status"></span>
                                                </span>
                                                <span class="name"><?php echo $current_user->display_name; ?></span>
                                            </span>
                                        </a>

                                        <!-- Dropdown -->
                                        <div class="adomx-dropdown-menu dropdown-menu-user">
                                            <div class="head">
                                                <h5 class="name"><a href="<?php echo $link_user; ?>"><?php echo $current_user->display_name; ?></a></h5>
                                                <a class="mail" href="#"><?php echo $current_user->user_email; ?></a>
                                            </div>
                                            <div class="body">
                                                <ul>
                                                    <li><a href="<?php echo $link_user; ?>"><i class="zmdi zmdi-account"></i>Hồ sơ của bạn</a></li>
                                                    <!-- <li><a href="<?php echo get_template_directory_uri(); ?>/#"><i class="zmdi zmdi-email-open"></i>Inbox</a></li> -->
                                                    <li><a href="<?php echo get_template_directory_uri(); ?>/#"><i class="zmdi zmdi-wallpaper"></i>Công việc</a></li>
                                                </ul>
                                                <ul>
                                                    <!-- <li><a href="<?php echo get_template_directory_uri(); ?>/#"><i class="zmdi zmdi-settings"></i>Setting</a></li> -->
                                                    <li><a href="<?php echo wp_logout_url(); ?>"><i class="zmdi zmdi-lock-open"></i>Đăng xuất</a></li>
                                                </ul>
                                                <!-- <ul>
                                                    <li><a href="<?php echo get_template_directory_uri(); ?>/#"><i class="zmdi zmdi-paypal"></i>Payment</a></li>
                                                    <li><a href="<?php echo get_template_directory_uri(); ?>/#"><i class="zmdi zmdi-google-pages"></i>Invoice</a></li>
                                                </ul> -->
                                            </div>
                                        </div>

                                    </li>

                                </ul>

                            </div><!-- Header Notifications Area End -->

                        </div>
                    </div><!-- Header Right End -->

                </div>
            </div>
        </div><!-- Header Section End -->