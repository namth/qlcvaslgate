<?php
/*
    Template Name: Báo cáo đối tác
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
                <h3>Báo cáo <span>/ Đối tác, khách hàng</span></h3>
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
    $args_partner  = array(
        'role__in'      => array('partner', 'foreign_partner'),
        'number'    => 999999,
    );

    $args_customer = array(
        'post_type' => 'customer',
        'posts_per_page' => -1,
    );



    if (isset($_POST['filter_date'])) {

        $date_value = explode(' - ', $_POST['filter_date']);
        $date_1 = date('Ymd', strtotime(($date_value[0])));
        $date_2 = date('Ymd', strtotime($date_value[1]));

        if ($date_1 && $date_2) {
            $args_customer['date_query'] = array(
                array(
                    'after'     => $date_1,
                    'before'    => $date_2,
                    'inclusive' => true,
                ),
            );
            $args_partner['date_query'] = array(
                array(
                    'after'     => $date_1,
                    'before'    => $date_2,
                    'inclusive' => true,
                ),
            );

        }
    }

    $query = new WP_Query($args_customer);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $quoc_gia = trim(get_field('quoc_gia'));

            $customer[$quoc_gia]++;
        }
        wp_reset_postdata();
    }


    $query = new WP_User_Query($args_partner);
    $users = $query->get_results();

    if (!empty($users)) {
        $role_arr = array('Tổng số đối tác');
        foreach ($users as $user) {
            $quoc_gia   = trim(get_field('quoc_gia' , 'user_' . $user->ID));
            $vip        = get_field('vip' , 'user_' . $user->ID);
            $worked     = get_field('worked' , 'user_' . $user->ID);

            $partner[$quoc_gia]['Tổng số đối tác']++;
            foreach ( $user->roles as $role ){
                $role_name = translate_user_role($wp_roles->roles[$role]['name']);
                $partner[$quoc_gia][$role_name]++;

                if (!in_array($role_name, $role_arr)) {
                    $role_arr[] = $role_name;
                } 
            }

            if ($worked) {
                $p_worked[$vip]++;
            } else $p_not_worked[$vip]++;
        }
    }
?>
    <div class="row mbn-30">

        <!-- Đối tác trên mỗi quốc gia -->
        <div class="col-md-12 mb-30">
            <div class="box">
                <div class="box-head">
                    <h4 class="title">Đối tác trên mỗi quốc gia</h4>
                </div>
                <div class="box-body">
                    <div class="chartjs-market-trends-chart">
                        <?php
                        $label = array();
                        $data_value = array();
                        foreach ($partner as $country => $roles) {
                            $label[] = "'" . $country . "'";

                            foreach ($role_arr as $key => $role) {
                                if ($roles[$role]) {
                                    $data_value[$role][$country] = $roles[$role];
                                } else $data_value[$role][$country] = "";
                            }
                        }

                        $color = ['#428bfa', '#fb7da4', '#ff9666', '#17a2b8', '#ee2f2f', '#8727df'];
                        $i = 0;
                        foreach ($data_value as $key => $value) {
                            $dynamic_data .= "{
                                label: '" . $key . "',
                                data: [" . implode(',', $value) . "],
                                backgroundColor: '" . $color[$i++] . "',
                                fill: false,
                            },";
                        }

                        $config = "var MTCconfig = {
                                        type: 'bar',
                                        data: {
                                            labels: [" . implode(',', $label) . "],
                                            datasets: [" . $dynamic_data . "]
                                        },
                                        options: {
                                            maintainAspectRatio: false,
                                            legend: {
                                                labels: {
                                                    fontColor: '#aaaaaa',
                                                }
                                            },
                                            scales: {
                                                xAxes: [{
                                                    display: true,
                                                    gridLines: {
                                                        color: 'rgba(136,136,136,0.1)',
                                                        lineWidth: 1,
                                                        drawBorder: false,
                                                        zeroLineWidth: 1,
                                                        zeroLineColor: 'rgba(136,136,136,0.1)',
                                                    },
                                                    ticks: {
                                                        fontColor: '#aaaaaa',
                                                    },
                                                }],
                                                yAxes: [{
                                                    display: true,
                                                    gridLines: {
                                                        color: 'rgba(136,136,136,0.1)',
                                                        lineWidth: 1,
                                                        drawBorder: false,
                                                        zeroLineWidth: 1,
                                                        zeroLineColor: 'rgba(136,136,136,0.1)',
                                                    },
                                                    ticks: {
                                                        fontColor: '#aaaaaa',
                                                    },
                                                }]
                                            },
                                            plugins: {
                                                labels: {
                                                  render: 'value',
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

        <!-- Khách hàng trên mỗi quốc gia -->
        <div class="col-md-12 mb-30">
            <div class="box">
                <div class="box-head">
                    <h4 class="title">Khách hàng trên mỗi quốc gia</h4>
                </div>
                <div class="box-body">
                    <div class="chartjs-market-trends-chart">
                        <?php

                        $label = array();
                        $data_value = array();
                        foreach ($customer as $key => $value) {
                            $label[] = "'" . $key . "'";
                            $data_value[] = $value;
                        }

                        $config = "var GRAPHconfig = {
                            type: 'bar',
                            data: {
                                labels: [" . implode(',', $label) . "],
                                datasets: [{
                                    label: 'Khách hàng',
                                    data: [" . implode(',', $data_value) . "],
                                    backgroundColor: '#428bfa',
                                }]
                            },
                            options: {
                                maintainAspectRatio: false,
                                legend: {
                                    labels: {
                                        fontColor: '#aaaaaa',
                                    }
                                },
                                scales: {
                                    xAxes: [{
                                        display: true,
                                        gridLines: {
                                            color: 'rgba(136,136,136,0.1)',
                                            lineWidth: 1,
                                            drawBorder: false,
                                            zeroLineWidth: 1,
                                            zeroLineColor: 'rgba(136,136,136,0.1)',
                                        },
                                        ticks: {
                                            fontColor: '#aaaaaa',
                                        },
                                    }],
                                    yAxes: [{
                                        display: true,
                                        gridLines: {
                                            color: 'rgba(136,136,136,0.1)',
                                            lineWidth: 1,
                                            drawBorder: false,
                                            zeroLineWidth: 1,
                                            zeroLineColor: 'rgba(136,136,136,0.1)',
                                        },
                                        ticks: {
                                            fontColor: '#aaaaaa',
                                        },
                                    }]
                                },
                                plugins: {
                                    labels: {
                                      render: 'value',
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

        <!-- Tỷ lệ khách tiềm năng và đã chốt -->
        <div class="col-md-4 mb-30">
            <div class="box">
                <div class="box-head">
                    <h4 class="title">Tỷ lệ đối tác</h4>
                </div>
                <div class="box-body">
                    <div class="chartjs-market-trends-chart">
                        <?php
                        $config = "var MTCconfig = {
                                        type: 'pie',
                                        data: {
                                            labels: ['Đã chốt', 'Tiềm năng'],
                                            datasets: [{
                                                data: [" . implode(',', array(array_sum($p_worked), array_sum($p_not_worked))) . "],
                                                backgroundColor: [
                                                    '#fb7da4',
                                                    '#428bfa',
                                                    '#7dfb9b',
                                                    '#ff9666',
                                                    '#ee2f2f',
                                                ],
                                            }]
                                        },
                                        options: {
                                            maintainAspectRatio: false,
                                            legend: {
                                                position: 'top',
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
                                                  fontColor: ['white', 'white', 'yellow', 'white', 'white'],
                                                  precision: 1
                                                }
                                            }
                                        }
                                    };";

                        echo "<script defer>";
                        echo "jQuery(document).ready(function($) {";
                        echo    "if( $('#chartjs-p_total-chart').length ) {
                                    var MTC = document.getElementById('chartjs-p_total-chart').getContext('2d');";
                        echo        $config;
                        echo        "var MTCchartjs = new Chart(MTC, MTCconfig);
                                            }";
                        echo "});</script>";
                        ?>
                        <canvas id="chartjs-p_total-chart"></canvas>
                    </div>
                </div>
            </div>
        </div><!-- Market Trends Chart End -->

        <!-- Tỷ lệ khách vip đã chốt -->
        <div class="col-md-4 mb-30">
            <div class="box">
                <div class="box-head">
                    <h4 class="title">Tỷ lệ đối tác đã chốt</h4>
                </div>
                <div class="box-body">
                    <div class="chartjs-market-trends-chart">
                        <?php
                        $label = array();
                        $data_value = array();
                        foreach ($p_worked as $key => $value) {
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
                                                    '#7dfb9b',
                                                    '#ee2f2f',
                                                    '#ff9666',
                                                    '#fb7da4',
                                                    '#428bfa',
                                                ],
                                            }]
                                        },
                                        options: {
                                            maintainAspectRatio: false,
                                            legend: {
                                                position: 'top',
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
                                                  fontColor: ['black', 'white', 'white', 'white', 'white'],
                                                  precision: 1
                                                }
                                            }
                                        }
                                    };";

                        echo "<script defer>";
                        echo "jQuery(document).ready(function($) {";
                        echo    "if( $('#chartjs-p_worked-chart').length ) {
                                    var MTC = document.getElementById('chartjs-p_worked-chart').getContext('2d');";
                        echo        $config;
                        echo        "var MTCchartjs = new Chart(MTC, MTCconfig);
                                            }";
                        echo "});</script>";
                        ?>
                        <canvas id="chartjs-p_worked-chart"></canvas>
                    </div>
                </div>
            </div>
        </div><!-- Market Trends Chart End -->

        <!-- Tỷ lệ đối tác vip tiềm năng -->
        <div class="col-md-4 mb-30">
            <div class="box">
                <div class="box-head">
                    <h4 class="title">Tỷ lệ đối tác tiềm năng</h4>
                </div>
                <div class="box-body">
                    <div class="chartjs-market-trends-chart">
                        <?php
                        $label = array();
                        $data_value = array();
                        foreach ($p_not_worked as $key => $value) {
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
                                                    '#ff9666',
                                                    '#fb7da4',
                                                    '#ee2f2f',
                                                    '#428bfa',
                                                    '#7dfb9b',
                                                ],
                                            }]
                                        },
                                        options: {
                                            maintainAspectRatio: false,
                                            legend: {
                                                position: 'top',
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
                                                  fontColor: ['white', 'white', 'white', 'white', 'white'],
                                                  precision: 1
                                                }
                                            }
                                        }
                                    };";

                        echo "<script defer>";
                        echo "jQuery(document).ready(function($) {";
                        echo    "if( $('#chartjs-p_not_worked-chart').length ) {
                                    var MTC = document.getElementById('chartjs-p_not_worked-chart').getContext('2d');";
                        echo        $config;
                        echo        "var MTCchartjs = new Chart(MTC, MTCconfig);
                                            }";
                        echo "});</script>";
                        ?>
                        <canvas id="chartjs-p_not_worked-chart"></canvas>
                    </div>
                </div>
            </div>
        </div><!-- Market Trends Chart End -->

        <!-- Tỷ lệ công việc tiềm năng -->
        <!-- <div class="col-md-12 mb-30">
            <div class="box">
                <div class="box-head">
                    <h4 class="title">Tỷ lệ công việc tiềm năng</h4>
                </div>
                <div class="box-body">
                    <div class="chartjs-market-trends-chart">
                        <?php
                        // $label = array('Tiềm năng','Đã chốt');
                        // $data_value = array( $data['group']['Tiềm năng'], $data['sign_date'] );

                        
                        echo "<script defer>";
                        echo "jQuery(document).ready(function($) {";
                        echo    "alert('alo');
                        if( $('#chartjs-potential-chart').length ) {
                                    $('#chartjs-potential-chart').vectorMap({
                                        map: 'world_en',
                                        backgroundColor: 'transparent',
                                        color: '#f4f4f4',
                                        hoverColor: '#136ef8',
                                        borderColor: '#ffffff',
                                        enableZoom: false,
                                        values: {
                                            'pk' : '2',
                                            'us' : '12',
                                            'ru' : '1',
                                            'au' : '3',
                                            'ca' : '2',
                                            'ci' : '2',
                                            'bd' : '2',
                                            'vn' : '10',
                                        },
                                        scaleColors: ['#136ef8', '#428bfa'],
                                        
                                    });
                                }";
                        echo "});</script>";
                        ?>
                        <div id="chartjs-potential-chart" class="vmap vmap-world"></div>
                    </div>
                </div>
            </div>
        </div> -->

    </div>

</div><!-- Content Body End -->

<?php
get_footer();
?>