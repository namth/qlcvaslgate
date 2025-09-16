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
        },
        // Helper function to get current page URL parameters for Polylang compatibility
        getCurrentPageParams: function(){
            var params = {};
            var search = window.location.search.substring(1);
            if(search) {
                var pairs = search.split('&');
                for(var i = 0; i < pairs.length; i++) {
                    var pair = pairs[i].split('=');
                    params[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1] || '');
                }
            }
            return params;
        }
    });
    
    $('#ajax_filter input[type="submit"]').on('click', function(){
        var data_filter = $('#filter form').serialize();
        var urlParams = $.getCurrentPageParams();

        // console.log(urlParams);

        $.ajax({
            type: "POST",
            url: AJAX.ajax_url,
            data: {
                action: "ajax_filter_jobs",
                data: data_filter,
                type: urlParams.type || '',
                source: urlParams.source || '',
                paged: 1
            },
            beforeSend: function() {
                // Add loading indicator
                $('#data_content').addClass('loading');
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log(xhr.status);
                console.log(xhr.responseText);
                console.log(thrownError);
                $('#data_content').removeClass('loading');
            },
            success: function (resp) {
                console.log(resp);
                $('#data_content').removeClass('loading');
                $('#data_content').html(resp);
            },
        });
        return false;
    });

    /* Xử lý phân trang ajax */
    $(document).on('click', '#job_pagination li a', function(){
        var paged = $(this).data('page');
        var data_filter = $('#filter form').serialize();
        
        // Use improved URL parameter handling for Polylang compatibility
        var urlParams = $.getCurrentPageParams();
        var asltype = urlParams.type;
        var aslsource = urlParams.source;

        var getvarData = {};
        getvarData.action   = "ajax_filter_jobs";
        getvarData.data     = data_filter;
        getvarData.paged    = paged;

        
        if (typeof asltype !== 'undefined' && asltype !== '') {
            getvarData.type = asltype;
        }
        if (typeof aslsource !== 'undefined' && aslsource !== '') {
            getvarData.source = aslsource;
        }

        // console.log(getvarData);

        $.ajax({
            type: "POST",
            url: AJAX.ajax_url,
            data: getvarData,
            beforeSend: function() {
                // Add loading indicator
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

    /* Xử lý phân trang ajax trang author */
    $(document).on('click', '#author_job li a', function(){
        var paged = $(this).data('page');
        var author_id = $("input[name='authorid']").val();
        var numberposts = $("input[name='numberposts']").val();

        var getvarData = {};
        getvarData.action   = "ajax_author_jobs";
        getvarData.paged    = paged;
        getvarData.author   = author_id;
        getvarData.numberposts   = numberposts;

        console.log(getvarData);

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
                $('#author_listjob').html(resp);
            },
        });
        return false;

    });
});
