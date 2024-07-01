<?php
/*
    Template Name: Export to MongoDB
*/
get_header();

get_sidebar();

require_once(__DIR__ . "/datacenter/mongodb_connection.php");

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

                    <!-- <div class="col-lg-3 form_title lh45">Loại hình</div>
                    <div class="col-lg-6 col-12 mb-20">
                        <select class="form-control select2-tags mb-20" name="mongo_method">
                            <option value="insertMany">Insert</option>
                            <option value="update">Update</option>
                        </select>
                    </div>
                    <div class="col-lg-3"></div>

                    <div class="col-lg-3 form_title lh45">Số trang bắt đầu</div>
                    <div class="col-lg-6 col-12 mb-20"><input type="number" class="form-control" name="start_page" value="1"></div>
                    <div class="col-lg-3"></div> -->

                    <div class="col-lg-3"></div>
                    <div class="col-lg-6 col-12 mb-20">
                        <input type="submit" class="button button-primary" value="<?php _e('Start', 'qlcv'); ?>">
                        <a class="button button-primary" id="importAll" style="color: white;">Import All</a>
                    </div>

                </form>
                <input type="hidden" name="total_page" value="">
                <input type="hidden" name="current_page" value="">
                <input type="hidden" name="list_object" value="">
                <b id="labelimport"></b>
                <div id="process"></div>
                <div id="result"></div>
                <span id="loading">
                    <span id="processbar"></span>
                </span>
                <div id="history"></div>
            </div>
        </div>

    </div><!-- Page Headings End -->

</div><!-- Content Body End -->
<script>
    jQuery(document).ready(function($) {
        function goto_import(functional, total_page, current_page) {
            $.ajax({
                type: "POST",
                url: AJAX.ajax_url,
                data: {
                    action: "js_export",
                    functional: functional,
                    // total_page: total_page,
                    current_page: current_page
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    console.log(xhr.status);
                    console.log(xhr.responseText);
                    console.log(thrownError);
                },
                success: function(resp) {
                    var obj = JSON.parse(resp);

                    if (obj['current_page'] <= total_page) {
                        goto_import(functional, total_page, obj['current_page']);
                        var calc = obj['current_page'] / total_page * 100;
                        var percent = Math.round(calc * 100) / 100 + "%";
                        var processbar = (100 - calc) + "%";
                        $("#process").html(percent);
                        $("#result").append(obj['result']);
                        $("#processbar").css('width', processbar);
                    } else {
                        /* check if stack has more data then continue export */
                        $("#history").append("Done.");
                        checkStack();
                    }
                    console.log(resp);
                },
            });
        }

        async function run_export_ajax(asl_data_type) {
            const response = await $.ajax({
                type: "POST",
                url: AJAX.ajax_url,
                data: {
                    action: "run_export_mongo",
                    asl_data_type: asl_data_type
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    console.log(xhr.status);
                    console.log(xhr.responseText);
                    console.log(thrownError);
                },
                success: function(resp) {
                    // console.log(resp);
                    var obj = JSON.parse(resp);
                    $("input[name='total_page']").val(obj['total_page']);

                    goto_import(obj['function'], obj['total_page'], 1);
                    $("#process").html(1 / obj['total_page'] * 100 + "%");
                    // $("#labelimport").html("Importing " + obj['data_table'] + "...");
                },
            });

            console.log(response);
        }

        function checkStack() {
            /* read a list of objects */
            let list_string = $('input[name="list_object"]').val();
            if (list_string != "") {
                let list_object = JSON.parse(list_string);
                /* get out one item */
                let process_item = list_object.pop();

                /* check if array not empty, then convert to json string and put in the input field */
                if (list_object.length !== 0) {
                    const jsonString = JSON.stringify(list_object);
                    $('input[name="list_object"]').val(jsonString);
                } else $('input[name="list_object"]').val("");

                $("#history").append("<br>Importing " + process_item + " ... ");

                let color = $("#loading").data("color");
                // alert(color);
                if (color == 1) {
                    /* set color to loading bar */
                    const r1 = Math.floor(Math.random() * 256);
                    const r2 = Math.floor(Math.random() * 256);
                    const r3 = Math.floor(Math.random() * 256);
                    const r4 = Math.floor(Math.random() * 256);
                    const r5 = Math.floor(Math.random() * 256);
                    const r6 = Math.floor(Math.random() * 256);
                    const r7 = Math.floor(Math.random() * 256);
                    const r8 = Math.floor(Math.random() * 256);
                    const r9 = Math.floor(Math.random() * 256);
                    $("#processbar").css('width', '100%');
                    $('#loading').css('background', 'linear-gradient(90deg, rgba('+r1+','+r2+','+r3+',1) 0%, rgba('+r4+','+r5+','+r6+',1) 50%, rgba('+r7+','+r8+','+r9+',1) 100%)');
                }
                
                /* call function to process export data to db */
                run_export_ajax(process_item);
            }
            return false;
        }

        $('.main form').submit(function(e) {
            e.preventDefault();
            var asl_data_type = $('select[name="asl_data_type"]').val();
            const list_object = JSON.stringify([asl_data_type]);
            $('input[name="list_object"]').val(list_object);

            checkStack();
            return false;
        });

        $('#importAll').click(function() {
            list_object = JSON.stringify(["job", "task", "member", "partner", "customer"]);
            $('input[name="list_object"]').val(list_object);

            checkStack();
            return false;
        });

        $('.lh45').click(function() {
            $("#loading").data("color", 1);
            return false;
        });

    });
</script>

<?php
get_footer();
