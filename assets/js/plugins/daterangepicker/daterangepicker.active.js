(function ($) {
    "use strict";
    
    /*Input Date*/
    if( $('.input-date').length ) {
        $('.input-date').daterangepicker();
    }
    
    /*Input Date & Time*/
    if( $('.input-date-time').length ) {
        $('.input-date-time').daterangepicker({
            timePicker: true,
        });
    }
    
    /*Input Date Single*/
    if( $('.input-date-single').length ) {
        $('.input-date-single').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            locale: {
              format: 'DD/MM/YYYY'
            }
        });
    }
    
    /*Input Date Empty*/
    if( $('.input-date-empty').length ) {
        $('.input-date-empty').daterangepicker({
            autoUpdateInput: false,
        });
    }

    /*Input Date Predefined*/
    if( $('.input-date-predefined').length ) {
        var start = moment().subtract(30, 'days');
        var end = moment();
        function cb(start, end) {
            $('.input-date-predefined').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        }
        $('.input-date-predefined').daterangepicker({
            autoUpdateInput: false,
            startDate: start,
            endDate: end,
            ranges: {
               'Hai năm trước': [moment().subtract(2, 'year').startOf('year'), moment().subtract(2, 'year').endOf('year')],
               'Một năm trước': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
               'Năm nay': [moment().startOf('year'), moment()],
               'Hôm nay': [moment(), moment()],
               'Hôm qua': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
               '7 ngày gần đây': [moment().subtract(6, 'days'), moment()],
               '30 ngày gần đây': [moment().subtract(29, 'days'), moment()],
               'Tháng này': [moment().startOf('month'), moment().endOf('month')],
               'Tháng trước': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
        }, cb);
        cb(start, end);
        
        $('.input-date-predefined').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
        });
      
        $('.input-date-predefined').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
    }

    
})(jQuery);