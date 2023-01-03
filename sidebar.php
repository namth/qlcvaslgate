<?php 
    $url = get_bloginfo('url');
?>
        <!-- Side Header Start -->
        <div class="side-header show">
            <button class="side-header-close"><i class="zmdi zmdi-close"></i></button>
            <!-- Side Header Inner Start -->
            <div class="side-header-inner custom-scroll">

                <nav class="side-header-menu" id="side-header-menu">
                    <?php 
                        wp_nav_menu(array(
                            'container' => '',
                        ));
                    ?>
                    
                </nav>

            </div><!-- Side Header Inner End -->
        </div><!-- Side Header End -->
