jQuery(document).ready(function ($) {

    $('.finance input').on('keyup', function(){
        var new_value = parseInt($(this).val());
        new_value = isNaN(new_value)?0:new_value;
        
        $(this).val(new_value);
    });

    /* Tu dong tinh tien con lai can thanh toan */
    $('input[name="total_value"],input[name="paid"]').on('change', function(){
        var total_value = $('input[name="total_value"]').val();
        var paid = $('input[name="paid"]').val();

        $('input[name="remainning"]').val(total_value - paid);
    });

    /* Tu dong tinh chi phi nuoc ngoai */
    $('input[name="total_cost"],input[name="advance_money"]').on('change', function(){
        var total_cost = $('input[name="total_cost"]').val();
        var advance_money = $('input[name="advance_money"]').val();

        $('input[name="debt"]').val(total_cost - advance_money);
    });
});
