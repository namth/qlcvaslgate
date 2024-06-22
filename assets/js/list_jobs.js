jQuery(document).ready(function ($) {
    
    $.extend({
        getUrlVars: function(){
            var vars = [], hash;
            var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
            for(var i = 0; i < hashes.length; i++)
            {
                hash = hashes[i].split('=');
                vars.push(hash[0]);
                vars[hash[0]] = hash[1];
            }
            return vars;
        },
        getUrlVar: function(name){
            return $.getUrlVars()[name];
        }
    });
    
    $('#ajax_filter input[type="submit"]').on('click', function(){
        var data_filter = $('#filter form').serialize();
        const searchParams = new URLSearchParams(window.location.search);

        // console.log(searchParams.get('type'));

        $.ajax({
            type: "POST",
            url: AJAX.ajax_url,
            data: {
                action: "ajax_filter_jobs",
                data: data_filter,
                type: searchParams.get('type'),
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
    $(document).on('click', '#job_pagination li a', function(){
        var paged = $(this).data('page');
        var data_filter = $('#filter form').serialize();
        var asltype = $.getUrlVar("type");
        var aslsource = $.getUrlVar("source");

        var getvarData = {};
        getvarData.action   = "ajax_filter_jobs";
        getvarData.data     = data_filter;
        getvarData.paged    = paged;

        
        if (typeof asltype !== 'undefined') {
            getvarData.type = asltype;
        }
        if (typeof aslsource !== 'undefined') {
            getvarData.source = aslsource;
        }

        // console.log(getvarData);

        $.ajax({
            type: "POST",
            url: AJAX.ajax_url,
            data: getvarData,
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
