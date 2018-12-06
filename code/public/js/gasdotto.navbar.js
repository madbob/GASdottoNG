$(document).ready(function() {
	/*dynamic padding top on body element when resizing window*/
	var additionalPadding = 10;
	$(window).resize(function () { 
	    $('body').css('padding-top', parseInt($('.navbar').css("height"))+additionalPadding);
	});

	$(window).load(function () { 
	    $('body').css('padding-top', parseInt($('.navbar').css("height"))+additionalPadding);        
	});
})
