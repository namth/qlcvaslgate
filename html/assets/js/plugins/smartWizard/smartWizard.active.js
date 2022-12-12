(function ($) {
    "use strict";
    
    /*Smart Wizard*/
    if( $('.smart-wizard').length ) {
        $('.smart-wizard').smartWizard({
            showStepURLhash: false,
            toolbarSettings: {
		      	toolbarPosition: 'bottom', // none, top, bottom, both
				toolbarButtonPosition: 'center', // left, right, center
				showNextButton: true, // show/hide a Next button
				showPreviousButton: true, // show/hide a Previous button
		  	},
		  	lang: { // Language variables for button
			    next: 'Tiếp theo',
			    previous: 'Quay lại'
			},
        });
    }
    
})(jQuery);