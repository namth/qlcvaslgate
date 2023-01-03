<?php
/*
    Template Name: Danh sách nhân sự (user)
*/
get_header();

get_sidebar();
if (isset($_GET['role']) && ($_GET['role'] != '')) {
    $role = $_GET['role'];
} else $role = '';

$current_user = wp_get_current_user();
?>

<!-- Content Body Start -->
<div class="content-body">

    <!-- Page Headings Start -->
    <div class="row justify-content-between align-items-center mb-10">

        <!-- Page Heading Start -->
        <div class="col-12 col-lg-12 mb-20">
            <!--Basic Start-->
            <div class="col-12 mb-30">

                <?php
                    // pagination
                    $paged = (get_query_var('paged')) ? absint(get_query_var('paged')) : 1;
                    $count_args  = array(
                        'role'      => $role,
                        'number'    => 999999,
                    );
                    $user_count_query = new WP_User_Query($count_args);
                    $user_count = $user_count_query->get_results();

                    // count the number of users found in the query
                    $total_users = $user_count ? count($user_count) : 1;

                    // how many users to show per page
                    $users_per_page = 20;

                    // calculate the total number of pages.
                    $total_pages = 1;
                    $offset = $users_per_page * ($paged - 1);
                    $total_pages = ceil($total_users / $users_per_page);


                    $args   = array(
                        'role'      => $role, /*partner, member, subscriber, contributor, author*/
                        'number'    => $users_per_page,
                        'paged'     => $paged,
                        'offset'    => $offset,
                    );
                    $query = new WP_User_Query($args);
                    $users = $query->get_results();

                    $total_user_args = $args;
                    $total_user_args['number'] = 99999;
                    $total_user_args['paged'] = 1;
                    $total_user_args['offset'] = 0;
                    $total_user_query = new WP_User_Query($total_user_args);
                ?>
                <div class="row justify-content-between">
                    <div class="col-lg-auto mb-10">
                        <?php 
                            switch ($role) {
                                case 'partner':
                                    $role_name = 'đối tác';
                                    $_create_link = '/them-doi-tac-moi/';
                                    break;
                                    
                                case 'foreign_partner':
                                    $role_name = 'đối tác nhận việc';
                                    $_create_link = '/them-doi-tac-moi/';
                                    break;
                                    
                                case 'contributor':
                                    $role_name = 'quản lý';
                                    $_create_link = '/them-nhan-su-moi/';
                                    break;

                                default:
                                    $role_name = 'nhân sự';
                                    $_create_link = '/them-nhan-su-moi/';
                                    break;
                            }
                        ?>
                        <p><?php _e('Có tổng cộng', 'qlcv'); ?> <?php echo sizeof($total_user_query->get_results()) . " " . $role_name; ?> <?php _e('tìm thấy', 'qlcv'); ?></p>
                        <h2><?php
                            echo __('Danh sách', 'qlcv') . ' ' . $role_name;
                        ?></h2>
                    </div>
                    <div class="col-lg-auto mb-10 right_button">
                        <a href="<?php echo get_bloginfo('url') . $_create_link; ?>" class="button button-primary"><span><i class="fa fa-plus"></i><?php _e('Tạo mới', 'qlcv'); ?></span></a>
                    </div>
                    <div class="col-12 box mb-20">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <?php
                                    if (($role == 'partner') || ($role == 'foreign_partner')) {
                                        echo "<th>" . __("Mã đối tác", 'qlcv') . "</th>";
                                        echo "<th>" . __("Tên người liên hệ", 'qlcv') . "</th>";
                                        echo "<th>" . __("Tên đối tác", 'qlcv') . "</th>";
                                    } else {
                                        echo "<th>" . __("Tên nhân sự", 'qlcv') . "</th>";
                                    }

                                    echo "<th>" . __("Số điện thoại", 'qlcv') . "</th>
                                                <th>Email</th>";

                                    if (in_array('contributor', $current_user->roles)) {
                                        echo "<th>" . __("Sửa", 'qlcv') . "</th>";
                                    }
                                    ?>

                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = $offset;
                                if (!empty($users)) {
                                    foreach ($users as $user) {
                                        $i++;
                                        $roles = array();
                                        $so_dien_thoai  = get_field('so_dien_thoai', 'user_' . $user->ID);
                                        $partner_code   = get_field('partner_code', 'user_' . $user->ID);
                                        $ten_cong_ty    = get_field('ten_cong_ty', 'user_' . $user->ID);

                                        echo "<tr>";
                                        echo "<td>" . $i . "</td>";
                                        if (($role == 'partner') || ($role == 'foreign_partner')) {
                                            echo "<td>" . $partner_code . "</td>";
                                            echo "<td><a href='" . get_author_posts_url($user->ID) . "'>" . $user->display_name . "</a></td>";
                                            echo "<td>" . $ten_cong_ty . "</td>";
                                        } else {
                                            echo "<td><a href='" . get_author_posts_url($user->ID) . "'>" . $user->display_name . "</a></td>";
                                        }

                                        if ($so_dien_thoai) {
                                            echo "<td>" . $so_dien_thoai . "</td>";
                                        } else echo "<td>" . __("Chưa có", 'qlcv') . "</td>";

                                        echo "<td>" . $user->user_email . "</td>";

                                        # display user role name
                                        if (!empty($user->roles) && is_array($user->roles)) {
                                            foreach ($user->roles as $role)
                                                $roles[] = translate_user_role($wp_roles->roles[$role]['name']);
                                        }

                                        if (in_array('contributor', $current_user->roles)) {
                                            echo '<td><a href="' . get_bloginfo('url') . '/sua-thong-tin-doi-tac/?uid=' . $user->ID . '"><i class="fa fa-edit"></i></a></td>';
                                            echo '<td><a href="' . get_bloginfo('url') . '/post-to-renewal-system/?uid=' . $user->ID . '"><i class="fa fa-telegram"></i></a></td>';
                                        }
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan=6 class='text-center'>" . __("Không có dữ liệu.", 'qlcv') . "</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-12">
                        <div class="pagination justify-content-center">
                            <?php
                            $big = 999999999; // need an unlikely integer

                            echo paginate_links(array(
                                'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                                'format'    => '?paged=%#%',
                                'current'   => max(1, get_query_var('paged')),
                                'total'     => $total_pages,
                                'type'      => 'list',
                            ));
                            ?>
                        </div>
                    </div>
                </div>

            </div>
            <!--Basic End-->


        </div><!-- Page Heading End -->

    </div><!-- Page Headings End -->

</div><!-- Content Body End -->

<?php
get_footer();
?>