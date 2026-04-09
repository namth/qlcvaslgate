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
                        <!-- <a class="button button-secondary" id="updateData" style="color: white;">Update Partner</a> -->
                    </div>
                </form>
                <input type="hidden" name="total_page" value="">
                <input type="hidden" name="current_page" value="">
                <input type="hidden" name="list_object" value="">

                <!-- Resume Modal -->
                <div id="resumeModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.55); z-index:9999; align-items:center; justify-content:center;">
                    <div style="background:#fff; border-radius:12px; padding:32px 36px; max-width:420px; width:90%; box-shadow:0 8px 32px rgba(0,0,0,0.22); text-align:center;">
                        <div style="font-size:2rem; margin-bottom:10px;">⚠️</div>
                        <h3 style="margin:0 0 10px; color:#222;">Phát hiện tiến trình chưa hoàn thành</h3>
                        <p id="resumeModalDesc" style="color:#555; margin-bottom:24px; font-size:0.97rem;"></p>
                        <div style="display:flex; gap:12px; justify-content:center;">
                            <button id="btnContinue" class="button button-primary" style="min-width:130px;">▶ Tiếp tục</button>
                            <button id="btnRestart" class="button" style="min-width:130px; background:#e74c3c; color:#fff; border-color:#e74c3c;">🔄 Bắt đầu lại</button>
                        </div>
                    </div>
                </div>

                <b id="labelimport"></b>
                <div id="process"></div>
                <div id="result"></div>
                <span id="loading">
                    <span id="processbar"></span>
                </span>
                <div id="history">
                    
                </div>
            </div>
        </div>

    </div><!-- Page Headings End -->

</div><!-- Content Body End -->
<script>
    jQuery(document).ready(function($) {

        /* ============================================================
         * RESUME / PROGRESS HELPERS (localStorage)
         * ============================================================ */
        var PROGRESS_KEY = 'asl_export_progress';

        function saveProgress(dataType, functional, total_page, current_page, list_object) {
            var data = {
                dataType: dataType,
                functional: functional,
                total_page: total_page,
                current_page: current_page,
                list_object: list_object,
                savedAt: new Date().toISOString()
            };
            localStorage.setItem(PROGRESS_KEY, JSON.stringify(data));
        }

        function clearProgress() {
            localStorage.removeItem(PROGRESS_KEY);
        }

        function loadProgress() {
            var raw = localStorage.getItem(PROGRESS_KEY);
            return raw ? JSON.parse(raw) : null;
        }

        /* 
        * function to call ajax to start run export data to db with pagination
        */
        function goto_import(functional, total_page, current_page) {
            /* lưu tiến trình trước mỗi lần gọi */
            var dataType = $('select[name="asl_data_type"]').val();
            var listObj  = $('input[name="list_object"]').val();
            saveProgress(dataType, functional, total_page, current_page, listObj);

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
                    /* lỗi mạng – giữ nguyên progress để có thể resume */
                },
                success: function(resp) {
                    var obj = JSON.parse(resp);

                    /* 
                    * check if current page less than total page then continue export
                    */
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

        /* 
        * function to call ajax to start run export data to db
        */
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

        /* 
        * function to check stack if has more data type then continue export
        */
        function checkStack() {
            /* read a list of objects */
            let list_string = $('input[name="list_object"]').val();

            // disable button submit
            $('.main form').find('input[type="submit"]').prop('disabled', true);

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
            } else {
                /* export xong toàn bộ – xóa progress */
                clearProgress();
                // enable button submit
                $('.main form').find('input[type="submit"]').prop('disabled', false);
            }
            return false;
        }

        /* ============================================================
         * SHOW / HIDE RESUME MODAL
         * ============================================================ */
        function showResumeModal(progress, onContinue, onRestart) {
            var savedAt = new Date(progress.savedAt);
            var desc = 'Loại dữ liệu: <strong>' + progress.dataType + '</strong><br>'
                     + 'Trang: <strong>' + progress.current_page + ' / ' + progress.total_page + '</strong><br>'
                     + 'Lưu lúc: <strong>' + savedAt.toLocaleString('vi-VN') + '</strong>';
            $('#resumeModalDesc').html(desc);
            $('#resumeModal').css('display', 'flex');

            $('#btnContinue').off('click').on('click', function() {
                $('#resumeModal').hide();
                onContinue();
            });
            $('#btnRestart').off('click').on('click', function() {
                $('#resumeModal').hide();
                onRestart();
            });
        }

        /* ============================================================
         * SUBMIT FORM – kiểm tra progress trước khi bắt đầu
         * ============================================================ */
        function startFresh(asl_data_type) {
            clearProgress();
            $("#history").html('');
            $("#result").html('');
            $("#process").html('');
            const list_object = JSON.stringify([asl_data_type]);
            $('input[name="list_object"]').val(list_object);
            checkStack();
        }

        $('.main form').submit(function(e) {
            e.preventDefault();
            var asl_data_type = $('select[name="asl_data_type"]').val();
            var progress = loadProgress();

            if (progress && progress.dataType === asl_data_type && progress.current_page < progress.total_page) {
                showResumeModal(
                    progress,
                    /* onContinue */ function() {
                        $("#history").append('<br><em>▶ Tiếp tục từ trang ' + progress.current_page + '/' + progress.total_page + '...</em><br>');
                        $('input[name="list_object"]').val(progress.list_object || '');
                        goto_import(progress.functional, progress.total_page, progress.current_page);
                    },
                    /* onRestart */ function() {
                        startFresh(asl_data_type);
                    }
                );
            } else {
                startFresh(asl_data_type);
            }

            return false;
        });

        $('#importAll').click(function() {
            var progress = loadProgress();
            var allTypes = ["job", "task", "member", "partner", "customer"];

            if (progress && progress.current_page < progress.total_page) {
                showResumeModal(
                    progress,
                    /* onContinue */ function() {
                        $("#history").append('<br><em>▶ Tiếp tục từ trang ' + progress.current_page + '/' + progress.total_page + '...</em><br>');
                        $('input[name="list_object"]').val(progress.list_object || '');
                        goto_import(progress.functional, progress.total_page, progress.current_page);
                    },
                    /* onRestart */ function() {
                        clearProgress();
                        $("#history").html('');
                        $("#result").html('');
                        $("#process").html('');
                        list_object = JSON.stringify(allTypes);
                        $('input[name="list_object"]').val(list_object);
                        checkStack();
                    }
                );
            } else {
                clearProgress();
                $("#history").html('');
                $("#result").html('');
                $("#process").html('');
                list_object = JSON.stringify(allTypes);
                $('input[name="list_object"]').val(list_object);
                checkStack();
            }
            return false;
        });

        $('.lh45').click(function() {
            $("#loading").data("color", 1);
            return false;
        });

        /* 
        * function run update data to db in page
        * put page number to function
        * read data from db with page number
        * update data to db
        */
        function update_data_with_page( total_page, page ) {
            $.ajax({
                type: "POST",
                url: AJAX.ajax_url,
                data: {
                    action: "update_data_with_page",
                    total_page: total_page,
                    page: page
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    console.log(xhr.status);
                    console.log(xhr.responseText);
                    console.log(thrownError);
                },
                success: function(resp) {
                    var obj = JSON.parse(resp);

                    if (obj['current_page'] <= total_page) {
                        update_data_with_page( total_page, obj['current_page'] );
                        var calc = obj['current_page'] / total_page * 100;
                        var percent = Math.round(calc * 100) / 100 + "%";
                        var processbar = (100 - calc) + "%";
                        $("#process").html(percent);
                        $("#history").append(obj['result']);
                        $("#processbar").css('width', processbar);
                    } else {
                        /* check if stack has more data then continue export */
                        $("#history").append("Done.");
                    }
                },
            });
            return false;
        }


        /* 
        * function to call ajax to update data
        */
        $('#updateData').click(function() {
            $.ajax({
                type: "POST",
                url: AJAX.ajax_url,
                data: {
                    action: "setup_page_number",
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    console.log(xhr.status);
                    console.log(xhr.responseText);
                    console.log(thrownError);
                },
                success: function(resp) {
                    console.log(resp);
                    /* put resp to input total_page */
                    $("input[name='total_page']").val(resp);

                    /* call function update_data_with_page */
                    update_data_with_page(resp, 1);
                },
            });
            return false;
        });

    });
</script>

<?php
get_footer();
