<?php 
/*
    Template Name: Login
*/
if(is_user_logged_in()) {
    // redirect sang trang chủ
    wp_redirect( get_bloginfo('url') );
    exit;
} else {
    // check form
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && 
        isset( $_POST['post_nonce_field'] ) && 
        wp_verify_nonce( $_POST['post_nonce_field'], 'post_nonce' ) ) {
        
        if (isset($_POST)) {
            $error = false;
            
            if ( isset($_POST['username']) && ($_POST['username'] != "") ) {
                $username = $_POST['username'];
            } else {
                $error = true;
                $error_user = __('Mời bạn nhập User ID / Email.', 'qlcv');
            }

            if ( isset($_POST['password']) && ($_POST['password'] != "") ) {
                $password = $_POST['password'];
            } else {
                $error = true;
                $error_password = __('Mời bạn nhập mật khẩu.', 'qlcv');
            }

            if ( isset($_POST['remember']) && ($_POST['remember'] == "on") ) {
                $remember = true;
            } else {
                $remember = false;
            }

        } else $error = true;
        
        if (!$error) {
            // dùng wp_signon() để đăng nhập
            $user = wp_signon( array(
                'user_login'    => $_POST['username'],
                'user_password' => $_POST['password'],
                'remember'      => $remember,
            ), false );

            // print_r($user);

            $userID = $user->ID;

            wp_set_current_user( $userID, $username );
            wp_set_auth_cookie( $userID, true, false );
            do_action( 'wp_login', $username );
            
            // redirect sang trang chủ
            wp_redirect( get_bloginfo('url') );
            exit;
        }
    }
    // redirect sang trang chủ
}
?>
<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Adomx - Responsive Bootstrap 4 Admin Template</title>
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

    <!-- Custom Style CSS Only For Demo Purpose -->
    <link id="cus-style" rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/style-primary.css">

</head>

<body>

    <div class="main-wrapper">

        <!-- Content Body Start -->
        <div class="content-body m-0 p-0">

            <div class="login-register-wrap">
                <div class="row">

                    <div class="d-flex align-self-center justify-content-center order-2 order-lg-1 col-lg-5 col-12">
                        <div class="login-register-form-wrap">
                            <div class="content">
                                <h1>Đăng nhập</h1>
                            </div>

                            <div class="alert alert-secondary" role="alert">
                                <?php 
                                    if ($error_user) {
                                        echo $error_user;
                                    } else if ($error_password) {
                                        echo $error_password;
                                    }
                                ?>
                            </div>

                            <div class="login-register-form">
                                <form action="" method="POST" enctype="multipart/form-data">
                                    <div class="row">
                                        <?php 
                                            $username = $_POST["username"]?$_POST["username"]:"";
                                        ?>
                                        <div class="col-12 mb-20"><input class="form-control" type="text" placeholder="User ID / Email" name="username"></div>
                                        <div class="col-12 mb-20"><input class="form-control" type="password" placeholder="Password" name="password"></div>
                                        <?php 
                                            wp_nonce_field( 'post_nonce', 'post_nonce_field' );
                                        ?>
                                        <div class="col-12 mb-20"><label for="remember" class="adomx-checkbox-2"><input id="remember" type="checkbox" checked name="remember"><i class="icon"></i>Ghi nhớ.</label></div>
                                        
                                        <div class="col-12 mt-10"><button class="button button-primary button-outline">Đăng nhập</button></div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="login-register-bg order-1 order-lg-2 col-lg-7 col-12">
                        <div class="content">
                            <h1>Sign in</h1>
                            <p></p>
                        </div>
                    </div>

                </div>
            </div>

        </div><!-- Content Body End -->

    </div>

    <!-- JS
============================================ -->

    <!-- Global Vendor, plugins & Activation JS -->
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/vendor/modernizr-3.6.0.min.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/vendor/jquery-3.3.1.min.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/vendor/popper.min.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/vendor/bootstrap.min.js"></script>
    <!--Plugins JS-->
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/plugins/tippy4.min.js.js"></script>
    <!--Main JS-->
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/main.js"></script>

</body>

</html>