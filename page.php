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
          <div class="box">
            <div class="box-body">
              <?php
              echo get_the_content();
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>
<?php
  }
}

get_footer();
?>