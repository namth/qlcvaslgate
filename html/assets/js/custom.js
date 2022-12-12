jQuery(document).ready(function ($) {
	
	// xử lý ajax khi click vào nút đăng ký partner mới
	$('#create_partner input[type="submit"]').click( function() {
        // lấy dữ liệu từ form và mã hoá thành chuỗi
        var $data = $('#create_partner form').serialize();
        $.ajax({
            type : 'POST',
            url : AJAX.ajax_url,
            data : {
                'action'    : 'add_user',
                'data'      : $data,
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log(xhr.status);
                console.log(xhr.responseText);
                console.log(thrownError);
            },
            success : function (resp){
                // alert(resp);
                var obj = JSON.parse( resp );
                // console.log(obj);
                if ( obj['status'] == 'success' ) {
                    $( obj['hide_form'] ).html( obj['notification'] );
                    $( obj['select_element'] ).prepend( obj['content'] ).show(200);
                } else {
                    $( obj['div_notification'] ).html( obj['notification'] );
                    $('html, body').animate({
                        scrollTop: $( '#create_new_job' ).offset().top
                    }, 1000);
                }
            }
        });
        return false;
	});
	
    // xử lý ajax khi click vào nút đăng ký khách hàng (customer) mới
    $('#create_customer input[type="submit"]').click( function() {
        // lấy dữ liệu từ form và mã hoá thành chuỗi
        var $data = $('#create_customer form').serialize();
        $.ajax({
            type : 'POST',
            url  : AJAX.ajax_url,
            data : {
                'action'    : 'add_customer',
                'data'      : $data,
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log(xhr.status);
                console.log(xhr.responseText);
                console.log(thrownError);
            },
            success : function (resp){
                // alert(resp);
                var obj = JSON.parse( resp );
                // console.log(obj);
                if ( obj['status'] == 'success' ) {
                    $( obj['hide_form'] ).html( obj['notification'] );
                    $( obj['select_element'] ).prepend( obj['content'] ).show(200);
                } else {
                    $( obj['div_notification'] ).html( obj['notification'] );
                    $('html, body').animate({
                        scrollTop: $( '#create_new_job' ).offset().top
                    }, 1000);
                }
            }
        });
        return false;
    });
    
    // xử lý khi bấm hoàn thành form tạo đầu việc mới
    $('.finish_newjob').click( function(){
        // get data from previous steps on smart wizard
        var $data_partner   = $('select[name="partner"]').val();
        var $data_customer  = $('select[name="customer"]').val();
        var $data_job       = $('form#new_job')[0];
        var $data_manager   = $('select[name="manager"]').val();
        //khởi tạo đối tượng form data
        var form_data = new FormData($data_job);

        form_data.append('action', 'add_new_job');
        form_data.append('data_partner', $data_partner);
        form_data.append('data_customer', $data_customer);
        // form_data.append('data_job', $data_job);
        form_data.append('data_manager', $data_manager);

        $.ajax({
            type : 'POST',
            url  : AJAX.ajax_url,
            data : form_data,
            contentType: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)
            processData: false, // NEEDED, DON'T OMIT THIS
            error: function (xhr, ajaxOptions, thrownError) {
                console.log(xhr.status);
                console.log(xhr.responseText);
                console.log(thrownError);
            },
            success : function (resp){
                var obj = JSON.parse( resp );
                console.log(obj);
                if ( obj['status'] == 'success' ) {
                    $( obj['div_notification'] ).html( obj['notification'] );
                } else {
                    $( obj['div_notification'] ).prepend( obj['notification'] );
                    $('html, body').animate({
                        scrollTop: $( '#create_new_job' ).offset().top
                    }, 1000);
                }
            }
        });

        return false;
    });


    /* khi click vào chọn danh mục trong phần tạo job mới thì set giá trị cho hidden form ['danh_muc'] */
    $('#choose_group li a').click( function(){
        var danh_muc = $(this).data('group');
        $('input[name="danh_muc"]').val(danh_muc);
    });

    // when range change, ajax sent
    $('#list_task_by_date').change( function() {
        //khởi tạo đối tượng form data
        var date_value = $(this).val();
        var form_data = new FormData();

        form_data.append('action', 'list_task_by_date');
        form_data.append('date_value', date_value);
        // gọi ajax để lọc thông tin
        $.ajax({
            type : 'POST',
            url  : AJAX.ajax_url,
            data : form_data,
            contentType: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)
            processData: false, // NEEDED, DON'T OMIT THIS
            error: function (xhr, ajaxOptions, thrownError) {
                console.log(xhr.status);
                console.log(xhr.responseText);
                console.log(thrownError);
            },
            success : function (resp){
                console.log(resp);
                $( 'tbody' ).html( resp );
            }
        });

        return false;
    });

});
