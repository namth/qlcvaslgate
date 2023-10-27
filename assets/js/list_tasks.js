jQuery(document).ready(function ($) {
    $('#ajax_filter input[type="submit"]').on('click', function(){
        var data_filter = $('#filter form').serialize();
        console.log(data_filter);

        $.ajax({
            type: "POST",
            url: AJAX.ajax_url,
            data: {
                action: "ajax_filter_tasks",
                data: data_filter,
                paged: 1
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log(xhr.status);
                console.log(xhr.responseText);
                console.log(thrownError);
            },
            success: function (resp) {
                // console.log(resp);
                $('#data_content').html(resp);
            },
        });
        return false;
    });

    /* Xử lý phân trang ajax */
    $(document).on('click', '#task_pagination li a', function(){
        var paged = $(this).data('page');
        var data_filter = $('#filter form').serialize();
        console.log(paged);

        $.ajax({
            type: "POST",
            url: AJAX.ajax_url,
            data: {
                action: "ajax_filter_tasks",
                data: data_filter,
                paged: paged
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log(xhr.status);
                console.log(xhr.responseText);
                console.log(thrownError);
            },
            success: function (resp) {
                // console.log(resp);
                $('#data_content').html(resp);
            },
        });
        return false;

    });
});
