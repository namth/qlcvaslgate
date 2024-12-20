<?php
/*
    Template Name: Thêm mới partner (đối tác)
*/
$history_link   = $_SERVER['HTTP_REFERER'];
$thongbao = $selected = "";

if (
    is_user_logged_in() &&
    isset($_POST['post_nonce_field']) &&
    wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')
) {

    $history_link   = $_POST['history_link'];

    # check partner-code, if it's not exists then add new user
    $input = array(
        'first_name'    => $_POST['first_name'],
        'last_name'     => $_POST['last_name'],
        'company_name'  => $_POST['user_company'],
        'company_website' => $_POST['user_website'],
        'user_code'     => $_POST['user_code'],
        'user_code_select'  => $_POST['user_code_select'],
        'user_code_exists'  => $_POST['user_code_exists'],
        'user_email'    => $_POST['user_email'],
        'phone_number'  => $_POST['phone_number'],
        'address'       => $_POST['address'],
        'country'       => $_POST['country'],
        'city'          => $_POST['city'],
        'vietnam_company' => $_POST['vietnam_company'],
        'languages'     => $_POST['languages'],
        'note'          => $_POST['note'],
        'type_of_client' => $_POST['type_of_client'],
        'detail_client_type' => $_POST['detail_client_type'],
        'fdi'           => $_POST['fdi'],
        'fdi_countries' => $_POST['fdi_countries'],
        'staffs'        => $_POST['staffs'],
        'partner_vip'   => $_POST['partner_vip'],
        'nguon_dau_viec' => $_POST['nguon_dau_viec'],
        'email_cc'      => $_POST['email_cc'],
        'email_bcc'     => $_POST['email_bcc'],
        'role'          => $_POST['role'],
        'worked'        => $_POST['worked'],
        'phan_loai'     => $_POST['phan_loai'],
    );

    $result = process_addnew_partner($input);

    # check if add new partner success
    if ($result['status'] == 'success') {
        $thongbao = '<div class="alert alert-success">Thêm mới partner thành công</div>';

        # redirect to history link
        if ($history_link) {
            wp_redirect( $history_link );
            exit;
        } else {
            # redirect to author page
            wp_redirect( get_author_posts_url( $result['user_id'] ) );
        }
    } else {
        $thongbao = '<div class="alert alert-danger">Có lỗi gì đó, hãy kiểm tra lại</div>';
    }
}

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
                    <div>
                    <?php
                    if ($thongbao) {
                        echo $thongbao;
                    }

                    form_addnew_partner();
                    ?>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- Page Headings End -->

</div><!-- Content Body End -->

<?php
echo '<input type="hidden" name="history_link" value="' . $history_link . '">';

get_footer();
?>