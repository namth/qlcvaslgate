(function ($) {
    "use strict";
    
    var pin = '<div class="vmap-pin"><div class="pin"></div><div class="signal"></div><div class="signal2"></div></div>'
    
    /*World*/
    if ($('#vmap-world').length) {
        $('#vmap-world').vectorMap({
            map: 'world_en',
            backgroundColor: 'transparent',
            color: '#428bfa',
            hoverColor: '#136ef8',
            borderColor: '#ffffff',
            enableZoom: false,
            values: sample_data,
            scaleColors: ['#428bfa'],
        });
    }
    
    if ($('#vmap-world-2').length) {
        $('#vmap-world-2').vectorMap({
            map: 'world_en',
            backgroundColor: 'transparent',
            color: '#f4f4f4',
            hoverColor: '#136ef8',
            borderColor: '#ffffff',
            enableZoom: false,
            values: {
                'pk' : '2',
                'us' : '12',
                'ru' : '1',
                'au' : '3',
                'ca' : '2',
                'ci' : '2',
                'bd' : '2',
                'vn' : '10',
            },
            scaleColors: ['#136ef8', '#428bfa'],
            
        });
    }
    
    /*Usa*/
    if ($('#vmap-usa').length) {
        $('#vmap-usa').vectorMap({
            map: 'usa_en',
            backgroundColor: 'transparent',
            color: '#428bfa',
            hoverColor: '#136ef8',
            borderColor: '#ffffff',
            enableZoom: false,
        });
    }
    
    /*Asia*/
    if ($('#vmap-asia').length) {
        $('#vmap-asia').vectorMap({
            map: 'asia_en',
            backgroundColor: 'transparent',
            color: '#428bfa',
            hoverColor: '#136ef8',
            borderColor: '#ffffff',
            enableZoom: false,
        });
    }
    
    /*Australia*/
    if ($('#vmap-australia').length) {
        $('#vmap-australia').vectorMap({
            map: 'australia_en',
            backgroundColor: 'transparent',
            color: '#428bfa',
            hoverColor: '#136ef8',
            borderColor: '#ffffff',
            enableZoom: false,
        });
    }
    
    /*Europe*/
    if ($('#vmap-europe').length) {
        $('#vmap-europe').vectorMap({
            map: 'europe_en',
            backgroundColor: 'transparent',
            color: '#428bfa',
            hoverColor: '#136ef8',
            borderColor: '#ffffff',
            enableZoom: false,
        });
    }
    
    /*Africa*/
    if ($('#vmap-africa').length) {
        $('#vmap-africa').vectorMap({
            map: 'africa_en',
            backgroundColor: 'transparent',
            color: '#428bfa',
            hoverColor: '#136ef8',
            borderColor: '#ffffff',
            enableZoom: false,
        });
    }

})(jQuery);