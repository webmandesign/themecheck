
// CHECK DEVICE
	checkDevice = function()
	{
		var widthDevice = (window.innerWidth > 0) ? window.innerWidth : screen.width;

	    $('body').removeClass('isMobile isTablet isDesktop');

	    if(navigator.userAgent.match(/iphone|ipod|ipad/i) || navigator.userAgent.match(/Android/i) || navigator.userAgent.match(/iemobile/i))
	    {
	        $('body').removeClass('no_touch');
	    }

	    if(widthDevice <= 768)
	    {
	        $('body').addClass('isMobile');
	        return 'isMobile';
	    }
	    else if(widthDevice > 768 && widthDevice <= 992)
	    {
	        $('body').addClass('isTablet');
	        return 'isTablet';
	    }
	    else
	    {
	        $('body').addClass('isDesktop');
	        return 'isDesktop';
	    }
	};


// ----------------------------------- DOCUMENT READY ----------------------------------- 
// --------------------------------------------------------------------------------------
	$(document).ready(function()
	{
		// CHECK DEVICE
			checkDevice();

		// INIT CUIR
			$('html').cuir();
		

		// WINDOW RESIZE
			$(window).resize(function(){
				windowResize();
			});
	});


// ----------------------------------- WINDOW LOAD -------------------------------------- 
// --------------------------------------------------------------------------------------
	$(window).load(function()
	{
	});


// ----------------------------------- FUNCTIONS ----------------------------------------
// --------------------------------------------------------------------------------------
	// WINDOW RESIZE
		windowResize = function(){
		}