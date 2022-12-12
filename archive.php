<?php 
  get_header();
  $background = ot_get_option('background');
?>
  <!-- BEGIN OF page cover -->
  <div class="page-cover">
    <!-- Cover Background -->
    <div class="cover-bg bg-img" data-image-src="<?php echo $background['background-image']; ?>"></div>

  </div>
  <!--END OF page cover -->

  <!-- BEGIN OF page main content -->
  <main class="page-main page-fullpage main-anim" id="mainpage">

    <!-- Begin of description section -->
    <div class="section section-description fp-auto-height-responsive " data-section="<?php echo get_queried_object()->slug; ?>">
      <!-- Begin of section wrapper -->
      <div class="section-wrapper dir-col twoside">
        <!-- title -->
        <div class="section-title text-center">
          <h2 class="display-4 display-title anim-2"><?php echo get_queried_object()->name; ?></h2>
        </div>

        <!-- content -->
        <div class="item row tin-tuc">
          <?php 
            if ( have_posts() ) {
              while ( have_posts() ) {
                the_post();
          ?>
              <div class="col-12 col-sm-6 col-md-4 top-vh">
                <div class="section-content anim translateUp">
                  <div class="images text-center">
                    <div class="img-frame-normal">
                      <div class="img-1 shadow">
                        <a href="<?php the_permalink(); ?>">
                          <?php the_post_thumbnail(); ?>
                        </a>
                      </div>
                      <div class="text-center pos-abs">
                        <a href="<?php the_permalink(); ?>">
                          <h5><?php the_title(); ?></h5>
                        </a>
                        <!-- <div class="content">
                          <?php the_excerpt(); ?>
                        </div> -->
                      </div>
                    </div>
                  </div>
                </div>
              </div>
          <?php
              }
            }
          ?>
          <div class="col-12 col-sm-12 col-md-12 text-center">
            <?php 
              echo paginate_links();
            ?>
          </div>
        </div>
      </div>
      <!-- End of section wrapper -->
    </div>
    <!-- End of description section -->
  </main>
  <!-- END OF page main content -->
<?php 
  get_footer();
?>