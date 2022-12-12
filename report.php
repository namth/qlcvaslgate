<?php
/*
    Template Name: Báo cáo
*/
get_header();

get_sidebar();
?>

<!-- Content Body Start -->
<div class="content-body">

    <!-- Page Headings Start -->
    <div class="row justify-content-between align-items-center mb-10">

        <!-- Page Heading Start -->
        <div class="col-12 col-lg-auto mb-20">
            <div class="page-heading">
                <h3>Báo cáo <span>/ Kết quả công việc</span></h3>
            </div>
        </div><!-- Page Heading End -->

        <!-- Page Button Group Start -->
        <div class="col-12 col-lg-auto mb-20">
            <div class="page-date-range">
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="text" class="form-control input-date-predefined" name="filter_date">
                    <input type="submit" class="button button-primary" value="Lọc">
                </form>
            </div>
        </div><!-- Page Button Group End -->

    </div><!-- Page Headings End -->

    <?php
    $data = array();

    # đếm các công việc tiềm năng đã chốt trong khoảng thời gian
    $args_job_p = array(
        'post_type' => 'job',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'group',
                'field'    => 'slug',
                'terms'    => 'tiem-nang',
            ),
        ),
    );

    # thống kê jobs đã chốt
    $args_job   = array(
        'post_type' => 'job',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'group',
                'field'    => 'slug',
                'terms'    => array('tiem-nang'),
                'operator' => 'NOT IN',
            ),
        ),
    );

    # thống kê task
    $args_task   = array(
        'post_type'     => 'task',
        'posts_per_page' => -1,
    );

    if (isset($_POST['filter_date'])) {

        $date_value = explode(' - ', $_POST['filter_date']);
        $date_1 = date('Ymd', strtotime(($date_value[0])));
        $date_2 = date('Ymd', strtotime($date_value[1]));

        if ($date_1 && $date_2) {
            $args_job['date_query'] = array(
                array(
                    'after'     => $date_1,
                    'before'    => $date_2,
                    'inclusive' => true,
                ),
            );
            $args_task['date_query'] = array(
                array(
                    'after'     => $date_1,
                    'before'    => $date_2,
                    'inclusive' => true,
                ),
            );

            $args_job_p['meta_query'] = array(
                array(
                    'key' => 'contract_sign_date',
                    'value' => array($date_1, $date_2),
                    'compare' => 'BETWEEN',
                    'type' => 'DATE',
                ),
            );
        }
    }

    $query = new WP_Query($args_job);
    // print_r($args_job);

    $data['total_job'] = $query->post_count;
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $terms = wp_get_post_terms(get_the_ID(), 'group');
            $term_names = wp_list_pluck($terms, 'name');
            
            foreach ($terms as $term) {
                $data['group'][$term->name]++;
                // $data['total']++;
            }
        

            $terms = wp_get_post_terms(get_the_ID(), 'post_tag');
            foreach ($terms as $term) {
                $data['tags'][$term->name]++;
            }
        }
        wp_reset_postdata();
    }

    // đếm các đầu việc tiềm năng
    $query = new WP_Query($args_job_p);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $terms = wp_get_post_terms(get_the_ID(), 'group');
            $term_names = wp_list_pluck($terms, 'name');

            foreach ($terms as $term) {
                if ($term->name != "Tiềm năng") {
                    $data['group-tiem_nang'][$term->name]++;
                }
                // $data['total']++;
            }
        }
        wp_reset_postdata();
    }

    $query = new WP_Query($args_task);

    $data['total_task'] = $query->post_count;
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $status = get_field('trang_thai');
            $data['status'][$status]++;
        }
        wp_reset_postdata();
    }

    ?>
    <!-- Top Report Wrap Start -->
    <div class="row">
        <!-- Top Report Start -->
        <div class="col-xlg-3 col-md-3 col-12 mb-30">
            <div class="top-report">
                <!-- Head -->
                <div class="head">
                    <h4>Tổng số khách</h4>
                    <a href="<?php echo get_bloginfo('url'); ?>/danh-sach-khach-hang/" class="view"><i class="zmdi zmdi-eye"></i></a>
                </div>

                <!-- Content -->
                <div class="content">
                    <?php
                    $total_customer = wp_count_posts('customer');
                    echo "<h2>" . number_format($total_customer->publish) . "</h2>";
                    // print_r($total_customer);
                    ?>
                </div>

                <!-- Footer -->
                <div class="footer">
                    <div class="progess">
                        <div class="progess-bar" style="width: 100%;"></div>
                    </div>
                    <!-- <p>92% of unique visitor</p> -->
                </div>

            </div>
        </div><!-- Top Report End -->

        <!-- Top Report Start -->
        <div class="col-xlg-3 col-md-3 col-12 mb-30">
            <div class="top-report">

                <!-- Head -->
                <div class="head">
                    <h4>Tổng số đối tác</h4>
                    <a href="<?php echo get_bloginfo('url'); ?>/danh-sach-nhan-su/?role=partner" class="view"><i class="zmdi zmdi-eye"></i></a>
                </div>

                <!-- Content -->
                <div class="content">
                    <?php
                    $total_user = count_users();
                    echo "<h2>" . number_format($total_user['avail_roles']['partner']) . "</h2>";
                    // print_r($total_user);
                    ?>
                </div>

                <!-- Footer -->
                <div class="footer">
                    <div class="progess">
                        <div class="progess-bar" style="width: 98%;"></div>
                    </div>
                    <!-- <p>98% of unique visitor</p> -->
                </div>

            </div>
        </div><!-- Top Report End -->

        <!-- Top Report Start -->
        <div class="col-xlg-3 col-md-3 col-12 mb-30">
            <div class="top-report">

                <!-- Head -->
                <div class="head">
                    <h4>Tổng số công việc</h4>
                    <a href="#" class="view"><i class="zmdi zmdi-eye"></i></a>
                </div>

                <!-- Content -->
                <div class="content">
                    <?php
                    echo "<h2>" . number_format($data['total_job']) . "</h2>";
                    ?>
                </div>

                <!-- Footer -->
                <div class="footer">
                    <div class="progess">
                        <div class="progess-bar" style="width: 100%;"></div>
                    </div>
                    <!-- <p>88% of unique visitor</p> -->
                </div>

            </div>
        </div><!-- Top Report End -->

        <!-- Top Report Start -->
        <div class="col-xlg-3 col-md-3 col-12 mb-30">
            <div class="top-report">

                <!-- Head -->
                <div class="head">
                    <h4>Tổng số nhiệm vụ</h4>
                    <a href="#" class="view"><i class="zmdi zmdi-eye"></i></a>
                </div>

                <!-- Content -->
                <div class="content">
                    <?php
                    echo "<h2>" . number_format($data['total_task']) . "</h2>";
                    ?>
                </div>

                <!-- Footer -->
                <div class="footer">
                    <div class="progess">
                        <?php
                        if ($data['total_task']) {
                            $process = ceil($data['status']['Hoàn thành'] / $data['total_task'] * 100);
                        } else {
                            $process = 0;
                        }
                        ?>
                        <div class="progess-bar" style="width: <?php echo $process; ?>%;"></div>
                    </div>
                    <p><?php echo $process; ?>% đã hoàn thành</p>
                </div>

            </div>
        </div><!-- Top Report End -->
    </div><!-- Top Report Wrap End -->

    <div class="row mbn-30">

        <!-- Biểu đồ nhiệm vụ -->
        <div class="col-md-6 mb-30">
            <div class="box">
                <div class="box-head">
                    <h4 class="title">Biểu đồ nhiệm vụ</h4>
                </div>
                <div class="box-body">
                    <div class="chartjs-market-trends-chart">
                        <?php

                        $label = array();
                        $data_value = array();
                        foreach ($data['status'] as $key => $value) {
                            $label[] = "'" . $key . "'";
                            $data_value[] = $value;
                        }

                        $config = "var GRAPHconfig = {
                            type: 'pie',
                            data: {
                                labels: [" . implode(',', $label) . "],
                                datasets: [{
                                    data: [" . implode(',', $data_value) . "],
                                    backgroundColor: ['#428bfa', '#fb7da4', '#ff9666', '#17a2b8', '#ee2f2f', '#8727df'],
                                    hoverBackgroundColor: ['#428bfa', '#fb7da4', '#ff9666', '#17a2b8', '#ee2f2f', '#8727df'],
                                    hoverBorderColor: 'beige'
                                }]
                            },
                            options: {
                                maintainAspectRatio: false,
                                legend: {
                                    position: 'left',
                                    labels: {
                                        boxWidth: 30,
                                        padding: 20,
                                        fontColor: '#aaaaaa',
                                    }
                                },
                                tooltips: {
                                    mode: 'point',
                                    intersect: false,
                                    xPadding: 10,
                                    yPadding: 10,
                                    caretPadding: 10,
                                    cornerRadius: 4,
                                    titleFontSize: 0,
                                    titleMarginBottom: 2,
                                    displayColors: false,
                                },
                                hover: {
                                    mode: 'nearest',
                                    intersect: true
                                },
                                animation: {
                                    animateScale: true,
                                    animateRotate: true
                                },
                                plugins: {
                                    labels: {
                                      render: 'percentage',
                                      fontColor: ['white', 'black', 'yellow', 'white', 'white'],
                                      precision: 1
                                    }
                                }
                            }
                        };";

                        echo "<script defer>";
                        echo "jQuery(document).ready(function($) {";
                        echo    "if( $('#chartjs-task-statistics-chart').length ) {
                                    var GRAPH = document.getElementById('chartjs-task-statistics-chart').getContext('2d');";
                        echo        $config;
                        echo        "var GRAPHchartjs = new Chart(GRAPH, GRAPHconfig);
                                            }";
                        echo "});</script>";
                        ?>
                        <canvas id="chartjs-task-statistics-chart"></canvas>
                    </div>
                </div>
            </div>
        </div><!-- Revenue Statistics Chart End -->

        <!-- Tỷ lệ các công việc -->
        <div class="col-md-6 mb-30">
            <div class="box">
                <div class="box-head">
                    <h4 class="title">Tỷ lệ các công việc đã chốt</h4>
                </div>
                <div class="box-body">
                    <div class="chartjs-market-trends-chart">
                        <?php
                        $label = array();
                        $data_value = array();
                        foreach ($data['group'] as $key => $value) {
                            $label[] = "'" . $key . "'";
                            $data_value[] = $value;
                        }

                        $config = "var MTCconfig = {
                                        type: 'doughnut',
                                        data: {
                                            labels: [" . implode(',', $label) . "],
                                            datasets: [{
                                                data: [" . implode(',', $data_value) . "],
                                                backgroundColor: [
                                                    '#fb7da4',
                                                    '#7dfb9b',
                                                    '#428bfa',
                                                    '#ff9666',
                                                    '#ee2f2f',
                                                ],
                                            }]
                                        },
                                        options: {
                                            maintainAspectRatio: false,
                                            legend: {
                                                position: 'left',
                                                labels: {
                                                    boxWidth: 30,
                                                    padding: 20,
                                                    fontColor: '#aaaaaa',
                                                }
                                            },
                                            tooltips: {
                                                mode: 'point',
                                                intersect: false,
                                                xPadding: 10,
                                                yPadding: 10,
                                                caretPadding: 10,
                                                cornerRadius: 4,
                                                titleFontSize: 0,
                                                titleMarginBottom: 2,
                                            },
                                            hover: {
                                                mode: 'nearest',
                                                intersect: true
                                            },
                                            animation: {
                                                animateScale: true,
                                                animateRotate: true
                                            },
                                            plugins: {
                                                labels: {
                                                  render: 'percentage',
                                                  fontColor: ['white', 'black', 'yellow', 'white', 'white'],
                                                  precision: 1
                                                }
                                            }
                                        }
                                    };";

                        echo "<script defer>";
                        echo "jQuery(document).ready(function($) {";
                        echo    "if( $('#chartjs-job-chart').length ) {
                                    var MTC = document.getElementById('chartjs-job-chart').getContext('2d');";
                        echo        $config;
                        echo        "var MTCchartjs = new Chart(MTC, MTCconfig);
                                            }";
                        echo "});</script>";
                        ?>
                        <canvas id="chartjs-job-chart"></canvas>
                    </div>
                </div>
            </div>
        </div><!-- Market Trends Chart End -->

        <!-- Nguồn việc -->
        <div class="col-md-6 mb-30">
            <div class="box">
                <div class="box-head">
                    <h4 class="title">Nguồn việc</h4>
                </div>
                <div class="box-body">
                    <div class="chartjs-market-trends-chart">
                        <?php

                        $label = array();
                        $data_value = array();
                        foreach ($data['tags'] as $key => $value) {
                            $label[] = "'" . $key . "'";
                            $data_value[] = $value;
                        }

                        $config = "var GRAPHconfig = {
                            type: 'pie',
                            data: {
                                labels: [" . implode(',', $label) . "],
                                datasets: [{
                                    data: [" . implode(',', $data_value) . "],
                                    backgroundColor: ['#428bfa', '#fb7da4', '#ff9666', '#17a2b8', '#ee2f2f', '#8727df'],
                                    hoverBackgroundColor: ['#428bfa', '#fb7da4', '#ff9666', '#17a2b8', '#ee2f2f', '#8727df'],
                                    hoverBorderColor: 'beige'
                                }]
                            },
                            options: {
                                maintainAspectRatio: false,
                                legend: {
                                    position: 'left',
                                    labels: {
                                        boxWidth: 30,
                                        padding: 20,
                                        fontColor: '#aaaaaa',
                                    }
                                },
                                tooltips: {
                                    mode: 'point',
                                    intersect: false,
                                    xPadding: 10,
                                    yPadding: 10,
                                    caretPadding: 10,
                                    cornerRadius: 4,
                                    titleFontSize: 0,
                                    titleMarginBottom: 2,
                                    displayColors: false,
                                },
                                hover: {
                                    mode: 'nearest',
                                    intersect: true
                                },
                                animation: {
                                    animateScale: true,
                                    animateRotate: true
                                },
                                plugins: {
                                    labels: {
                                      render: 'percentage',
                                      fontColor: ['white', 'black', 'yellow', 'white', 'white'],
                                      precision: 1
                                    }
                                }
                            }
                        };";

                        echo "<script defer>";
                        echo "jQuery(document).ready(function($) {";
                        echo    "if( $('#chartjs-source-jobs-chart').length ) {
                                    var GRAPH = document.getElementById('chartjs-source-jobs-chart').getContext('2d');";
                        echo        $config;
                        echo        "var GRAPHchartjs = new Chart(GRAPH, GRAPHconfig);
                                            }";
                        echo "});</script>";
                        ?>
                        <canvas id="chartjs-source-jobs-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tỷ lệ công việc tiềm năng -->
        <div class="col-md-6 mb-30">
            <div class="box">
                <div class="box-head">
                    <h4 class="title">Tỷ lệ công việc tiềm năng</h4>
                </div>
                <div class="box-body">
                    <div class="chartjs-market-trends-chart">
                        <?php
                        $label = array();
                        $data_value = array();
                        foreach ($data['group-tiem_nang'] as $key => $value) {
                            $label[] = "'" . $key . "'";
                            $data_value[] = $value;
                        }

                        $config = "var MTCconfig = {
                                        type: 'doughnut',
                                        data: {
                                            labels: [" . implode(',', $label) . "],
                                            datasets: [{
                                                data: [" . implode(',', $data_value) . "],
                                                backgroundColor: [
                                                    '#fb7da4',
                                                    '#7dfb9b',
                                                    '#428bfa',
                                                    '#ff9666',
                                                    '#ee2f2f',
                                                ],
                                            }]
                                        },
                                        options: {
                                            maintainAspectRatio: false,
                                            legend: {
                                                position: 'left',
                                                labels: {
                                                    boxWidth: 30,
                                                    padding: 20,
                                                    fontColor: '#aaaaaa',
                                                }
                                            },
                                            tooltips: {
                                                mode: 'point',
                                                intersect: false,
                                                xPadding: 10,
                                                yPadding: 10,
                                                caretPadding: 10,
                                                cornerRadius: 4,
                                                titleFontSize: 0,
                                                titleMarginBottom: 2,
                                            },
                                            hover: {
                                                mode: 'nearest',
                                                intersect: true
                                            },
                                            animation: {
                                                animateScale: true,
                                                animateRotate: true
                                            },
                                            plugins: {
                                                labels: {
                                                  render: 'percentage',
                                                  fontColor: ['white', 'black', 'yellow', 'white', 'white'],
                                                  precision: 1
                                                }
                                            }
                                        }
                                    };";

                        echo "<script defer>";
                        echo "jQuery(document).ready(function($) {";
                        echo    "if( $('#chartjs-potential-chart').length ) {
                                    var MTC = document.getElementById('chartjs-potential-chart').getContext('2d');";
                        echo        $config;
                        echo        "var MTCchartjs = new Chart(MTC, MTCconfig);
                                            }";
                        echo "});</script>";
                        ?>
                        <canvas id="chartjs-potential-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Finance -->
        <div class="col-md-6 mb-30">
            <div class="box">
                <div class="box-head">
                    <h4 class="title">Thu chi VND</h4>
                </div>
                <div class="box-body">
                    <div class="chartjs-market-trends-chart">
                        <?php

                        $args   = array(
                            'post_type'     => 'finance',
                            'posts_per_page' => -1,
                        );
                        if ($date_1 && $date_2) {
                            $args['date_query'] = array(
                                array(
                                    'after'     => $date_1,
                                    'before'    => $date_2,
                                    'inclusive' => true,
                                ),
                            );
                        }

                        $query = new WP_Query($args);

                        if ($query->have_posts()) {
                            while ($query->have_posts()) {
                                $query->the_post();

                                $f_type = get_field('finance_type');
                                $f_val  = get_field('finance_value');
                                $f_cur  = get_field('finance_currency');
                                $data['finance'][$f_type][$f_cur] += $f_val;
                            }
                        }

                        // print_r($data['finance']);

                        $config = "var GRAPHconfig = {
                            type: 'pie',
                            data: {
                                labels: ['Thu', 'Chi'],
                                datasets: [{
                                    data: [" . $data['finance']['Thu']['VND'] . ", " . $data['finance']['Chi']['VND'] . "],
                                    backgroundColor: ['#428bfa', '#fb7da4', '#ff9666', '#17a2b8', '#ee2f2f', '#8727df'],
                                    hoverBackgroundColor: ['#428bfa', '#fb7da4', '#ff9666', '#17a2b8', '#ee2f2f', '#8727df'],
                                    hoverBorderColor: 'beige'
                                }]
                            },
                            options: {
                                maintainAspectRatio: false,
                                legend: {
                                    position: 'left',
                                    labels: {
                                        boxWidth: 30,
                                        padding: 20,
                                        fontColor: '#aaaaaa',
                                    }
                                },
                                tooltips: {
                                    mode: 'point',
                                    intersect: false,
                                    xPadding: 10,
                                    yPadding: 10,
                                    caretPadding: 10,
                                    cornerRadius: 4,
                                    titleFontSize: 0,
                                    titleMarginBottom: 2,
                                    displayColors: false,
                                },
                                hover: {
                                    mode: 'nearest',
                                    intersect: true
                                },
                                animation: {
                                    animateScale: true,
                                    animateRotate: true
                                },
                                plugins: {
                                    labels: {
                                      render: 'percentage',
                                      fontColor: ['white', 'black', 'yellow', 'white', 'white'],
                                      precision: 1
                                    }
                                }
                            }
                        };";

                        $config2 = "var GRAPHconfig2 = {
                            type: 'pie',
                            data: {
                                labels: ['Thu', 'Chi'],
                                datasets: [{
                                    data: [" . $data['finance']['Thu']['USD'] . ", " . $data['finance']['Chi']['USD'] . "],
                                    backgroundColor: ['#428bfa', '#fb7da4', '#ff9666', '#17a2b8', '#ee2f2f', '#8727df'],
                                    hoverBackgroundColor: ['#428bfa', '#fb7da4', '#ff9666', '#17a2b8', '#ee2f2f', '#8727df'],
                                    hoverBorderColor: 'beige'
                                }]
                            },
                            options: {
                                maintainAspectRatio: false,
                                legend: {
                                    position: 'left',
                                    labels: {
                                        boxWidth: 30,
                                        padding: 20,
                                        fontColor: '#aaaaaa',
                                    }
                                },
                                tooltips: {
                                    mode: 'point',
                                    intersect: false,
                                    xPadding: 10,
                                    yPadding: 10,
                                    caretPadding: 10,
                                    cornerRadius: 4,
                                    titleFontSize: 0,
                                    titleMarginBottom: 2,
                                    displayColors: false,
                                },
                                hover: {
                                    mode: 'nearest',
                                    intersect: true
                                },
                                animation: {
                                    animateScale: true,
                                    animateRotate: true
                                },
                                plugins: {
                                    labels: {
                                      render: 'percentage',
                                      fontColor: ['white', 'black', 'yellow', 'white', 'white'],
                                      precision: 1
                                    }
                                }
                            }
                        };";

                        echo "<script defer>";
                        echo "jQuery(document).ready(function($) {";
                        echo    "if( $('#chartjs-finance-vnd-chart').length ) {
                                    var GRAPH = document.getElementById('chartjs-finance-vnd-chart').getContext('2d');
                                    var GRAPH2 = document.getElementById('chartjs-finance-usd-chart').getContext('2d');";
                        echo        $config;
                        echo        $config2;
                        echo        "var GRAPHchartjs = new Chart(GRAPH, GRAPHconfig);";
                        echo        "var GRAPHchartjs2 = new Chart(GRAPH2, GRAPHconfig2);
                                            }";
                        echo "});</script>";
                        ?>
                        <canvas id="chartjs-finance-vnd-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-30">
            <div class="box">
                <div class="box-head">
                    <h4 class="title">Thu chi USD</h4>
                </div>
                <div class="box-body">
                    <div class="chartjs-market-trends-chart">
                        <canvas id="chartjs-finance-usd-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>


    </div>

</div><!-- Content Body End -->

<?php
get_footer();
?>