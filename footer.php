        <!-- Footer Section Start -->
        <div class="footer-section">
            <div class="container-fluid">

                <div class="footer-copyright text-center">
                    <p class="text-body-light">2021 &copy; <a href="https://9outfit.com">9outfit</a></p>
                </div>

            </div>
        </div><!-- Footer Section End -->

    </div>

    <!-- JS ============================================ -->

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

    <!-- Plugins & Activation JS For Only This Page -->
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/plugins/smartWizard/jquery.smartWizard.min.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/plugins/smartWizard/smartWizard.active.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/plugins/select2/select2.full.min.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/plugins/select2/select2.active.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/plugins/moment/moment.min.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/plugins/daterangepicker/daterangepicker.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/plugins/daterangepicker/daterangepicker.active.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/plugins/inputmask/bootstrap-inputmask.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/plugins/jsgrid/jsgrid.min.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/plugins/jsgrid/jsgrid-db.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/plugins/jsgrid/jsgrid.active.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/plugins/summernote/summernote-bs4.min.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/plugins/summernote/summernote.active.js"></script>
    <!-- File upload -->
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/plugins/dropify/dropify.min.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/plugins/dropify/dropify.active.js"></script>
    <!-- Echart -->
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/plugins/chartjs/Chart.min.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/plugins/chartjs/chartjs-plugin-labels.min.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/plugins/chartjs/chartjs.active.js"></script>
    <!--VMap-->
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/plugins/vmap/jquery.vmap.min.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/plugins/vmap/maps/jquery.vmap.world.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/plugins/vmap/maps/samples/jquery.vmap.sampledata.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/plugins/vmap/vmap.active.js"></script>

    <?php wp_footer(); ?>
    <style type="text/css">
        <?php 
            $sidebar_background = get_field('sidebar_background', 'option');
            $text_color         = get_field('text_color', 'option');
            $home_line_1_background = get_field('home_line_1_background', 'option');
            $home_line_2_background = get_field('home_line_2_background', 'option');
            $home_line_3_background = get_field('home_line_3_background', 'option');

            if ($sidebar_background) {
                echo ".side-header {
                    background-color: $sidebar_background;
                }"; 
            }

            if ($text_color) {
                echo   ".sub-menu li a:hover,
                        .sub-menu li a{
                            color: $text_color;
                        }
                        .sub-menu li a::before,
                        .side-header-menu > ul > li > a::before{
                            background-color: $text_color;
                        }";
            }

            if ($home_line_1_background) {
                echo ".button-steam,
                .button-steam:hover{
                    background-color: $home_line_1_background;
                    border-color: $home_line_1_background;
                    color: $text_color;
                }";
            }
            if ($home_line_2_background) {
                echo ".button-css3,
                .button-css3:hover{
                    background-color: $home_line_2_background;
                    border-color: $home_line_2_background;
                    color: $text_color;
                }";
            }
            if ($home_line_3_background) {
                echo ".button-skype,
                .button-skype:hover{
                    background-color: $home_line_3_background;
                    border-color: $home_line_3_background;
                    color: $text_color;
                }";
            }
            echo ".button-steam:hover,
            .button-css3:hover,
            .button-skype:hover{
                opacity: 0.8;
            }";
        ?>
        

    </style>
  </body>

</html>