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