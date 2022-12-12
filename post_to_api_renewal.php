<?php
/*
    Template Name: POST user to renewal system by API
*/
$history_link   = $_SERVER['HTTP_REFERER'];

if (isset($_GET['uid']) && ($_GET['uid'] != '')) {
    $uid = $_GET['uid'];
    $current_user = get_user_by('ID', $uid);

    $so_dien_thoai  = get_field('so_dien_thoai', 'user_' . $uid);
    $dia_chi        = get_field('dia_chi', 'user_' . $uid);
    $quoc_gia       = get_field('quoc_gia', 'user_' . $uid);
    $email_cc       = get_field('email_cc', 'user_' . $uid);
    $email_bcc      = get_field('email_bcc', 'user_' . $uid);
    $partner_code   = get_field('partner_code', 'user_' . $uid);
    $ten_cong_ty    = get_field('ten_cong_ty', 'user_' . $uid);
    $api_id         = get_field('api_id', 'user_' . $uid);

    $customer = array(
        'title'     => $ten_cong_ty,
        'content'   => $current_user->description,
        'status'    => 'publish',
    );

    $custom_fields = array(
        'fields' => array(
            'email'     => $current_user->user_email,
            'email_cc'  => $email_cc,
            'email_cc'  => $email_bcc,
            'additional_field' => array(
                array(
                    'data_name' => 'Người đại diện',
                    'data_value' => $current_user->display_name,
                ),
                array(
                    'data_name' => 'Mã đối tác',
                    'data_value' => $partner_code,
                ),
                array(
                    'data_name' => 'Số điện thoại',
                    'data_value' => $so_dien_thoai,
                ),
                array(
                    'data_name' => 'Địa chỉ',
                    'data_value' => $dia_chi,
                ),
                array(
                    'data_name' => 'Quốc gia',
                    'data_value' => $quoc_gia,
                ),
            )
        ),
    );

    get_header();

    get_sidebar();
?>

    <!-- Content Body Start -->
    <div class="content-body">

        <!-- Page Headings Start -->
        <div class="row justify-content-between align-items-center mb-10">

            <!-- Page Heading Start -->
            <div class="col-12 col-lg-12 mb-20">
                <div class="page-heading">
                    <?php
                    echo '<h3 class="title">' . get_the_title() . '</h3>';
                    ?>
                </div>
            </div><!-- Page Heading End -->

            <div class="col-12 mb-30">
                <div class="box">
                    <div class="box-body">
                        <?php

                        $token = get_token();
                        $send_api = send_customer_api($token, $customer, $custom_fields, $api_id, $uid);

                        echo '<a href="' . $history_link . '" class="button button-primary">Quay lại</a>';

                        ?>
                    </div>

                </div>
            </div>


        </div><!-- Page Headings End -->

    </div><!-- Content Body End -->

<?php
    get_footer();
}
