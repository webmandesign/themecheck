
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

		
		// MENU MOBILE
		$('#icon_menu_mobile').on('click', switchMenu);

		$('#liste_menu .link_intern').on('click', scrollDown);
                
                $('.container_result .content_intro .container_alerts a').on('click', scrollDown);
                
                // Popup Home
                $('#select_zip a').on('click', openPopup);

		// WINDOW RESIZE
			$(window).resize(function(){
				windowResize();
			});

		// IMAGES FOOTER 

		var imgWordpress = $("img.wordpress")[0].src;
		var imgJoomla = $("img.joomla")[0].src;
		var imgOwasp = $("img.owasp")[0].src;
		var imgGithub = $("img.github")[0].src;

		$("img.wordpress").hover(
			function(){ $("img.wordpress")[0].src = domain_site+'/img/images/footer/wordpress_hover.png';},
			function(){ $("img.wordpress")[0].src = imgWordpress;}
		);
		$("img.joomla").hover(
			function(){ $("img.joomla")[0].src = domain_site+'/img/images/footer/joomla_hover.png';},
			function(){ $("img.joomla")[0].src = imgJoomla;}
		);
		$("img.owasp").hover(
			function(){ $("img.owasp")[0].src = domain_site+'/img/images/footer/owasp_hover.png';},
			function(){ $("img.owasp")[0].src = imgOwasp;}
		);
		$("img.github").hover(
			function(){ $("img.github")[0].src = domain_site+'/img/images/footer/github_hover.png';},
			function(){ $("img.github")[0].src = imgGithub;}
		);
        
               if(page == 'contact')
               {
                   $('#contactPage').css('color', '#cad334');
               }
            
            
            
           
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

	// Affichage background menu 
	
	$(function () {
         var $window = $(window);
         $window.scroll(function () {
             if (($window.scrollTop() == 0) && !$( "#menu" ).hasClass( "open" ))
             {
             	document.getElementById('menu').style.background = "none";
             	
             }
             else 
             {
                document.getElementById('menu').style.background = "black";
             }
       
             // COLOR ITEM ANCHOR
             $('.container_liste_menu a').each(function(){
                var idItem = $(this).attr('href');
                
                if(idItem == "#ancreSubmit" || idItem == "#theme")
                {
                
                    if($window.scrollTop() == $(idItem).offset().top)
                    {
                        $('.container_liste_menu a[href='+idItem+']').addClass('hover_anchor');
                    }
                    else
                    {
                        $('.container_liste_menu a[href='+idItem+']').removeClass('hover_anchor');
                    }
                }
            });
             
         });
     });

	// Affichage menu mobile
	
	switchMenu = function()
	{
		$('#menu').toggleClass('open');
                if($( "#menu" ).hasClass( "open" ))
                {
                    document.getElementById('menu').style.background = "black";
                }
                else
                {
                    document.getElementById('menu').style.background = "none";
                }

	};

	scrollDown = function()
	{ 
	    var urlAnchor = $(this).attr('href');
            var splitUrl = urlAnchor.split('#');
            
            if(splitUrl.length <= 2)
            {
                var anchor = '#'+splitUrl[1];
                var posAnchor = $(anchor).offset().top; 
                if(anchor == "#warningAlerts")
                {
                    posAnchor = posAnchor - 70;
                }
                else if(anchor == "#criticalAlerts")
                {
                    posAnchor = posAnchor - 10;
                }

                $('html, body').animate({
                    scrollTop: posAnchor + 'px'
                }, 500, function()
                {
                    if(checkDevice() == 'isMobile')
                    {
                            $('#menu').removeClass('open');
                    }
                });
            }

	    return false;
	};
        
        
        // Icon interrogation page Home
        openPopup = function()
	{
            $('#select_zip a span ').toggleClass('openPopup');

	};