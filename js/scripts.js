jQuery.noConflict();
(function($) {
	// Submenus
	var config = {    
		 over: function(){ $('ul', this).fadeIn(200); },  
		 timeout: 300,
		 out: function(){ $('ul', this).fadeOut(300); }  
	};
	$('#head ul > li').not("#head ul li li").hoverIntent(config);
})(jQuery);