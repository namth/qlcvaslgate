<?php 
  get_header();
  while ( have_posts() ) {
    the_post();
    $background = ot_get_option('background');
?>
  <!-- BEGIN OF page cover -->
  <div class="page-cover" style="opacity: 0.5">
    <!-- Cover Background -->
    <div class="cover-bg bg-img" data-image-src="<?php echo $background['background-image']; ?>"></div>

  </div>
  <!--END OF page cover -->

  <!-- BEGIN OF page main content -->
  <main class="page-main page-fullpage main-anim" id="singlepage">

    <!-- Begin of description section -->
    <div class="section section-description fp-auto-height-responsive " data-section="<?php echo get_queried_object()->slug; ?>">
      <!-- Begin of section wrapper -->
      <div class="section-wrapper dir-col twoside">
        <!-- title -->
        <div class="section-title">
          <h2 class="display-4 display-title anim-2"><?php the_title(); ?></h2>
        </div>

        <!-- content -->
        <div class="item row tin-tuc">
          <div class="col-12 col-sm-12 col-md-12">
            <?php the_content(); ?>
          </div>
        </div>
      </div>
      <!-- End of section wrapper -->
    </div>
    <!-- End of description section -->
  </main>
  <!-- END OF page main content -->
<?php 
  }
  get_footer();
?>