jQuery(document).ready(function ($) {
    
    // Add loading CSS styles
    if (!$('#ajax-loading-styles').length) {
        $('head').append(`
            <style id="ajax-loading-styles">
                .loading {
                    position: relative;
                    opacity: 0.6;
                }
                .loading::before {
                    content: '';
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    width: 40px;
                    height: 40px;
                    margin: -20px 0 0 -20px;
                    border: 4px solid #f3f3f3;
                    border-top: 4px solid #3498db;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                    z-index: 1000;
                }
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            </style>
        `);
    }
    
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
            beforeSend: function() {
                $('#data_content').addClass('loading');
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log(xhr.status);
                console.log(xhr.responseText);
                console.log(thrownError);
                $('#data_content').removeClass('loading');
            },
            success: function (resp) {
                // console.log(resp);
                $('#data_content').removeClass('loading');
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
            beforeSend: function() {
                $('#data_content').addClass('loading');
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log(xhr.status);
                console.log(xhr.responseText);
                console.log(thrownError);
                $('#data_content').removeClass('loading');
            },
            success: function (resp) {
                // console.log(resp);
                $('#data_content').removeClass('loading');
                $('#data_content').html(resp);
                
                // Scroll to top of content after pagination
                $('html, body').animate({
                    scrollTop: $('#data_content').offset().top - 100
                }, 500);
            },
        });
        return false;

    });
});
