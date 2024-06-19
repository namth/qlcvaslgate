<?php
/*
    Template Name: Export to MongoDB
*/
get_header();

get_sidebar();

require_once (__DIR__ . "/datacenter/mongodb_connection.php");

?>
<!-- Content Body Start -->
<div class="content-body">
    <!-- Page Headings Start -->
    <div class="row justify-content-between align-items-center mb-10">
        <div class="col-12 mb-30">
            <div class="box main">
                <form action="#" method="POST" class="row">
                    <div class="col-lg-3 form_title lh45">Loại dữ liệu</div>
                    <div class="col-lg-6 col-12 mb-20">
                        <select class="form-control select2-tags mb-20" name="asl_data_type">
                            <option value="customer">Khách hàng</option>
                            <option value="partner">Đối tác</option>
                            <option value="member">Nhân sự</option>
                            <option value="job">Jobs</option>
                            <option value="task">Tasks</option>
                        </select>
                    </div>
                    <div class="col-lg-3"></div>

                    <div class="col-lg-3 form_title lh45">Loại hình</div>
                    <div class="col-lg-6 col-12 mb-20">
                        <select class="form-control select2-tags mb-20" name="mongo_method">
                            <option value="insertMany">Insert</option>
                            <option value="update">Update</option>
                        </select>
                    </div>
                    <div class="col-lg-3"></div>

                    <div class="col-lg-3 form_title lh45">Số trang bắt đầu</div>
                    <div class="col-lg-6 col-12 mb-20"><input type="number" class="form-control" name="start_page" value="1"></div>
                    <div class="col-lg-3"></div>

                    <div class="col-lg-3"></div>
                    <div class="col-lg-6 col-12 mb-20"><input type="submit" class="button button-primary" value="<?php _e('Start', 'qlcv'); ?>"></div>

                </form>
                <input type="hidden" name="total_page" value="">
                <input type="hidden" name="current_page" value="">
                <div id="process"></div>
                <div id="result">
                    <h3>Kết quả</h3>
                    <?php 
                        $date = "14/7/2024";

                        if (preg_match("/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}$/", $date)) {
                            echo "Đúng";
                        } else echo "Sai format";
                        // global $wpdb;

                        // $aslTable = $wpdb->prefix . 'asljob';
                        // $aslHistory = $wpdb->prefix . 'asljobhistory';
                        // $aslSupervisor = $wpdb->prefix . 'aslsupervisor';
                        // $posts_per_page = 20;
                        // $args   = array(
                        //     'post_type'     => 'job',
                        //     'paged'         => 170,
                        //     'posts_per_page'=> $posts_per_page,
                        // );
                    
                        // $query = new WP_Query( $args );
                    
                        // if( $query->have_posts() ) {
                        //     while ( $query->have_posts() ) {
                        //         $query->the_post();
                    
                        //         $jobID          = get_the_ID();
                        //         $our_ref        = get_field('our_ref');
                        //         $customer       = get_field('customer');
                        //         $phan_loai      = get_field('phan_loai');
                        //         $partner_1      = get_field('partner_1');
                        //         $partner_2      = get_field('partner_2');
                        //         $foreign_partner = get_field('foreign_partner');
                        //         $member         = get_field('member');
                        //         $manager        = get_field('manager');
                        //         $tags_obj       = get_the_tags();
                        //         $tagname_arr    = array();
                        //         if ($tags_obj) {
                        //             foreach ($tags_obj as $key => $value) {
                        //                 $tagname_arr[] = $value->name;
                        //             }
                        //         }

                        //         # check partner isset?
                        //         if (is_array($foreign_partner)) {
                        //             $foreign_partner_id = $foreign_partner['ID'];
                        //         } else $foreign_partner_id = NULL;

                        //         if (is_array($partner_1)) {
                        //             $partner_1_id = $partner_1['ID'];
                        //         } else $partner_1_id = NULL;


                        //         # get field cash in
                        //         $total_value    = get_field('total_value');
                        //         $paid           = get_field('paid');
                        //         $remainning     = get_field('remainning');
                        //         $currency       = get_field('currency');
                        //         # get field cash out
                        //         $total_cost     = get_field('total_cost');
                        //         $advance_money  = get_field('advance_money');
                        //         $debt           = get_field('debt');
                        //         $currency_out   = get_field('currency_out');
                        //         $payment_status = get_field('payment_status');
                    
                        //         if (get_field('contract_sign_date')) {
                        //             $tmp = DateTime::createFromFormat('d/m/Y', get_field('contract_sign_date'));
                        //             $contract_sign_date = $tmp->format('Y-m-d H:i:s');
                        //         } else $contract_sign_date = "";
                    
                        //         # save history to export
                        //         $work_list  = get_field('lich_su_cong_viec');
                        //         if ($work_list) {
                        //             foreach ($work_list as $key => $value) {
                        //                 if (preg_match("/^\d{1,2}/\d{1,2}/\d{4}$/", $value['ngay_thang'])) {
                        //                     $tmp = DateTime::createFromFormat('d/m/Y', $value['ngay_thang']);
                        //                     $ngay_thang = $tmp->format('Y-m-d H:i:s');
                        //                 } else $ngay_thang = "";
                        //                 $data_arr = [
                        //                     'jobid' => $jobID,
                        //                     'name'  => $value['mo_ta'],
                        //                     'date'  => $ngay_thang
                        //                 ];
                    
                        //                 $wpdb->insert(
                        //                     $aslHistory,
                        //                     $data_arr
                        //                 );
                        //             }
                        //         }
                    
                        //         # save supervisor to export
                        //         $data_supervisor = get_field('supervisor');
                        //         if ( $data_supervisor ) {
                        //             $supervisors = explode("|", $data_supervisor);
                        //             if(!empty($supervisors)){
                        //                 foreach ($supervisors as $supervisor) {
                        //                     $data_arr = [
                        //                         'jobid' => $jobID,
                        //                         'supervisorid' => $supervisor
                        //                     ];
                    
                        //                     $wpdb->insert(
                        //                         $aslSupervisor,
                        //                         $data_arr
                        //                     );
                        //                 }
                        //             }
                        //         }
                    
                        //         $brand = array();
                        //         $agency = get_the_terms(get_the_ID(), 'agency');
                        //         foreach ($agency as $id_chi_nhanh) {
                        //             $term = get_term($id_chi_nhanh);
                    
                        //             $brand[] = $term->name;
                        //         }
                    
                        //         if (is_array($brand)) {
                        //             $agency_hn = in_array('ha-noi', $brand)?1:0;
                        //             $agency_hcm = in_array('ho-chi-minh', $brand)?1:0;
                        //         }
                    
                        //         $date = DateTime::createFromFormat('m/d/Y', get_the_date('m/d/Y'));
                                
                        //         $job = [
                        //             'id'        => get_the_ID(),
                        //             'title'     => get_the_title(),
                        //             'type'      => $phan_loai,
                        //             'our_ref'   => $our_ref,
                        //             'customerid'        => $customer->ID,
                        //             'first_partnerid'   => $partner_1_id,
                        //             'partnerid'         => $partner_2['ID'],
                        //             'partner_out_id'    => $foreign_partner_id,
                        //             'memberid'      => $member['ID'],
                        //             'managerid'     => $manager['ID'],
                        //             'currency'      => $currency,
                        //             'total_value'   => $total_value,
                        //             'paid'          => $work_list,
                        //             'remainning'    => $remainning,
                        //             'total_cost'    => $total_cost,
                        //             'currency_out'  => $currency_out,
                        //             'advance_money' => $advance_money,
                        //             'debt'          => $debt,
                        //             'payment_status'=> $payment_status,
                        //             'source'        => implode(",", $tagname_arr),
                        //             'date'          => $date->format('Y-m-d H:i:s'),
                        //             'contract_sign_date' => $contract_sign_date,
                        //             'agency_hn'     => $agency_hn,
                        //             'agency_hcm'    => $agency_hcm
                        //         ];
                    
                        //         // print_r($job);
                        //         $sent = $wpdb->insert(
                        //             $aslTable,
                        //             $job
                        //         );
                        //     } 
                        //     wp_reset_postdata();
                        // }
                    ?>
                </div>
            </div>
        </div>

    </div><!-- Page Headings End -->

</div><!-- Content Body End -->
<script>
    function goto_import(functional,total_page, current_page){
        $.ajax({
            type: "POST",
            url: AJAX.ajax_url,
            data: {
                action: "js_export",
                functional: functional,
                // total_page: total_page,
                current_page: current_page
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log(xhr.status);
                console.log(xhr.responseText);
                console.log(thrownError);
            },
            success: function (resp) {
                var obj = JSON.parse(resp);
                
                if (obj['current_page']<= total_page) {
                    goto_import(functional, total_page, obj['current_page']);
                    $("#process").html(obj['current_page']/total_page*100 + "%");
                    $("#result").append(obj['result']);
                }
                console.log(resp);
            },
        });    
    }

    jQuery(document).ready(function ($) {

        $('.main form').submit(function(){
            var asl_data_type   = $('select[name="asl_data_type"]').val();
            var mongo_method = $('select[name="mongo_method"]').val();
            var start_page  = $('input[name="start_page"]').val();

            $.ajax({
                type: "POST",
                url: AJAX.ajax_url,
                data: {
                    action: "run_export_mongo",
                    asl_data_type: asl_data_type,
                    mongo_method: mongo_method,
                    start_page: start_page
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log(xhr.status);
                    console.log(xhr.responseText);
                    console.log(thrownError);
                },
                success: function (resp) {
                    console.log(resp);
                    var obj = JSON.parse(resp);
                    $("input[name='total_page']").val(obj['total_page']);
                    
                    if (obj['current_page']<= obj['total_page']) {
                        goto_import(obj['function'], obj['total_page'], obj['current_page']);
                        $("#process").html(obj['current_page']/obj['total_page']*100 + "%");
                    }
                    console.log(resp);
                },
            });    

            return false;
        });

    });
</script>

<?php
get_footer();