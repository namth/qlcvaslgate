<?php
get_header();
get_sidebar();

if (have_posts()) {
  while (have_posts()) {
    the_post();
?>
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
              <?php
              $f_date = get_field('finance_date');
              $f_type = get_field('finance_type');
              $f_user = get_field('finance_user');
              $f_job  = get_field('finance_job');
              $f_val  = get_field('finance_value');
              $f_cur  = get_field('finance_currency');
              if ($f_type == "Thu") {
                $f_value = '+' . number_format($f_val) . $f_cur;
              } else {
                $f_value = '-' . number_format($f_val) . $f_cur;
              }

              echo "Ngày tháng: <b>" . $f_date . "</b><br>";
              echo "Phân loại: <b>" . $f_type. "</b><br>";
              echo "Nội dung: <code>" . get_the_content(). "</code><br>";
              echo "Số tiền: <b>" . $f_value. "</b><br>";
              echo "Công việc: <b><a href='" . get_permalink($f_job) . "'>" . get_the_title($f_job) . "</a></b><br>";
              echo "Đối tác: <b><a href='" . get_author_posts_url($f_user['ID']) . "'>" . $f_user['display_name'] . "</a></b><br>";
              ?>
        </div>
        <a href="javascript:history.go(-1)" class="button button-primary">Quay lại</a>
      </div>
    </div>
<?php
  }
}

get_footer();
?>