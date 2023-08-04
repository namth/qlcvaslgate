<?php
/*
    Template Name: Báo cáo theo phân loại công việc
*/
get_header();

get_sidebar();

$args_job = array(
    'post_type' => 'job',
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
    }
} else {
    $args_job['date_query'] = array(
        array(
            'after'     => date('Ymd', strtotime('-1 month')),
            'inclusive' => true,
        ),
    );
}

$query = new WP_Query($args_job);
$data['total_job'] = $query->post_count;
$data['label'] = [];

$data = [
    'total_job' => $query->post_count,
    'Tổng tiềm năng' => 0,
    'Tổng đã chốt'      => 0,
    'label' => [],
    'group' => [
        'Tiềm năng' => [],
        'Đã chốt'   => []
    ],
    'agency' => [],
];

if ($query->have_posts()) {
    while ($query->have_posts()) {
        $query->the_post();

        $potential = false;

        # phân loại theo loại công việc
        $terms = wp_get_post_terms(get_the_ID(), 'group');
        $term_names = wp_list_pluck($terms, 'name');
        foreach ($term_names as $term_name) {
            if ($term_name == "Tiềm năng") {
                $potential = true;
            } else {
                if (!in_array($term_name, $data['label'])) {
                    $data['label'][] = $term_name;
                }
            }
        }

        # phân loại theo chi nhánh
        $agency = wp_get_post_terms(get_the_ID(), 'agency');
        $agency_name = $agency[0]->name;
        $data['agency'][$agency_name]['total']++;

        if ($potential) {
            # Ghi nhận đầu việc này là tiềm năng
            $data['Tổng tiềm năng']++;
            foreach ($terms as $term) {
                if ($term->name != "Tiềm năng") {
                    # Nếu không phải tiềm năng thì thêm vào
                    $data['group']['Tiềm năng'][$term->name]++;
                    $data['agency'][$agency_name]['Tiềm năng'][$term->name]++;
                }
            }
        } else {
            # Ghi nhận đầu việc này là đã chốt
            $data['Tổng đã chốt']++;
            foreach ($terms as $term) {
                $data['group']['Đã chốt'][$term->name]++;
                $data['agency'][$agency_name]['Đã chốt'][$term->name]++;
            }
        }
    }
    wp_reset_postdata();
}

?>

<!-- Content Body Start -->
<div class="content-body">

    <!-- Page Headings Start -->
    <div class="row justify-content-between align-items-center mb-10">

        <!-- Page Heading Start -->
        <div class="col-12 col-lg-auto mb-20">
            <div class="page-heading">
                <h3><?php _e('Báo cáo', 'qlcv'); ?> <span>/ <?php _e('Kết quả công việc', 'qlcv'); ?></span></h3>
            </div>
        </div><!-- Page Heading End -->

        <!-- Page Button Group Start -->
        <div class="col-12 col-lg-auto mb-20">
            <div class="page-date-range">
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="text" class="form-control input-date" name="filter_date" value="<?php if ($_POST['filter_date']) {
                                                                                                        echo $_POST['filter_date'];
                                                                                                    } else echo date('m/d/Y', strtotime('-1 month')) . ' - ' . date('m/d/Y'); ?>">
                    <input type="submit" class="button button-primary" value="<?php _e('Lọc', 'qlcv'); ?>">
                </form>
            </div>
        </div><!-- Page Button Group End -->

    </div><!-- Page Headings End -->

    <div class="row mb-30">
        <div class="col-xlg-6 col-md-6 col-12">
            <div class="top-report">
                <!-- Head -->
                <div class="head">
                    <h4><?php _e('Tổng số việc tiềm năng', 'qlcv'); ?></h4>
                </div>

                <!-- Content -->
                <div class="content">
                    <?php
                    echo "<h2>" . number_format($data['Tổng tiềm năng']) . "</h2>";
                    ?>
                </div>

            </div>
        </div>
        <div class="col-xlg-6 col-md-6 col-12">
            <div class="top-report">
                <!-- Head -->
                <div class="head">
                    <h4><?php _e('Tổng số việc đã chốt', 'qlcv'); ?></h4>
                </div>

                <!-- Content -->
                <div class="content">
                    <?php
                    echo "<h2>" . number_format($data['Tổng đã chốt']) . "</h2>";
                    ?>
                </div>

            </div>
        </div>
    </div>

    <div class="row">
        <!-- Bar Vertical Start -->
        <div class="col-12 mb-30">
            <div class="box">
                <div class="box-head">
                    <h4 class="title">Chi tiết công việc theo phân nhóm</h4>
                    <?php
                    foreach ($data['label'] as $label) {
                        $labels[]   = "'" . $label . "'";

                        $tiemnang[] = $data["group"]["Tiềm năng"][$label] ? $data["group"]["Tiềm năng"][$label] : 0;
                        $dachot[]   = $data["group"]["Đã chốt"][$label] ? $data["group"]["Đã chốt"][$label] : 0;
                    }
                    $list_labels = implode(', ', $labels);
                    $list_tiemnang = implode(', ', $tiemnang);
                    $list_dachot = implode(', ', $dachot);
                    ?>
                </div>
                <div class="box-body">
                    <div class="aslgate-chartjs">
                        <canvas id="aslgate-chartjs-barV" height="450px"></canvas>
                    </div>
                </div>
            </div>
        </div><!-- Bar Vertical End -->
        
    </div>

    <!-- Ve bieu do so cong viec cua tung chi nhanh 
    -- Su dung vong lap de lay du lieu
    -- Co bao nhieu chi nhanh thi ve bay nhieu bang
    -->

    <div class="row mb-30">
        <?php
        $i = 0;
        foreach ($data['agency'] as $agency => $agencyValue) {
            # khai bao bien cho data du lieu tung chi nhanh
            $varConfig = 'agency' . $i++;
            
            # Xu ly du lieu de ve bieu do
            $tiemnang = [];
            $dachot = [];
            $labels = [];
            $total_tiemnang = 0;
            $total_dachot = 0;
            foreach ($data['label'] as $label) {
                $temp1 = $agencyValue["Tiềm năng"][$label] ? $agencyValue["Tiềm năng"][$label] : 0;
                $temp2 = $agencyValue["Đã chốt"][$label] ? $agencyValue["Đã chốt"][$label] : 0;
                # Neu co it nhat 1 du lieu thi moi luu vao mang
                if ($temp1 || $temp2) {
                    $labels[]   = "'" . $label . "'";
    
                    $tiemnang[] = $temp1;
                    $dachot[]   = $temp2;
                    $total_tiemnang += $temp1;
                    $total_dachot += $temp2;
                }
            }
        ?>
            <div class="col-6 mb-30">
                <div class="box">
                    <div class="box-head">
                        <h4 class="title"><?php echo $agencyValue['total'] ?> công việc tại <?php echo $agency; ?></h4>
                        <div class="row">
                            <div class="col-6 mb-10"><?php echo $total_tiemnang; ?> công việc tiềm năng</div>
                            <div class="col-6 mb-10"><?php echo $total_dachot; ?> công việc đã chốt</div>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="aslgate-chartjs">
                            <canvas id="aslgate-chartjs-<?php echo $varConfig; ?>" height="300px"></canvas>
                        </div>
                    </div>
                </div>
            </div><!-- Bar Vertical End -->
        <?php
            $jscode .= "if ($('#aslgate-chartjs-" . $varConfig . "').length) {
                            var ECBV" . $varConfig . " = document.getElementById('aslgate-chartjs-" . $varConfig . "').getContext('2d');
                            var " . $varConfig . " = {
                                ...ECBVconfig
                            };
                            " . $varConfig . ".data = {
                                labels: [" . implode(', ', $labels) . "],
                                datasets: [{
                                        label: 'Tiềm năng',
                                        data: [" .  implode(', ', $tiemnang) . "],
                                        backgroundColor: '#fe0090',
                                        fill: false,
                                    },
                                    {
                                        label: 'Đã chốt',
                                        data: [" .  implode(', ', $dachot) . "],
                                        backgroundColor: '#0a34bc',
                                        fill: false,
                                    }
                                ]
                            };
                            " . $varConfig . ".options.scales.xAxes[0].barThickness = 30;
                            console.log(" . $varConfig . ");
                            var " . $varConfig . "chartjs = new Chart(ECBV" . $varConfig . ", " . $varConfig . ");
                        }";
        }
        ?>
    </div>
</div><!-- Content Body End -->

<script>
    jQuery(document).ready(function($) {
        if ($('#aslgate-chartjs-barV').length) {
            var ECBV = document.getElementById('aslgate-chartjs-barV').getContext('2d');
            var ECBVconfig = {
                type: 'bar',
                data: {
                    labels: [<?php echo $list_labels; ?>],
                    datasets: [{
                            label: 'Tiềm năng',
                            data: [<?php echo $list_tiemnang; ?>],
                            backgroundColor: '#fb7da4',
                            fill: false,
                        },
                        {
                            label: 'Đã chốt',
                            data: [<?php echo $list_dachot; ?>],
                            backgroundColor: '#428bfa',
                            fill: false,
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    legend: {
                        labels: {
                            fontColor: '#333333',
                        }
                    },
                    plugins: {
                        labels: {
                            render: 'value',
                        },
                    },
                    scales: {
                        xAxes: [{
                            display: true,
                            stacked: false,
                            gridLines: {
                                color: 'rgba(136,136,136,0.1)',
                                lineWidth: 1,
                                drawBorder: false,
                                zeroLineWidth: 1,
                                zeroLineColor: 'rgba(136,136,136,0.1)',
                            },
                            ticks: {
                                fontColor: '#333333',
                            },
                        }],
                        yAxes: [{
                            display: false,
                            stacked: false,
                            gridLines: {
                                color: 'rgba(136,136,136,0.1)',
                                lineWidth: 1,
                                drawBorder: false,
                                zeroLineWidth: 1,
                                zeroLineColor: 'rgba(136,136,136,0.1)',
                            },
                            ticks: {
                                fontColor: '#333333',
                            },
                        }]
                    }
                }
            };
            var ECBVchartjs = new Chart(ECBV, ECBVconfig);
        }
        <?php echo $jscode; ?>
    });
</script>
<?php
get_footer();
?>