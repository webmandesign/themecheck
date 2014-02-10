<?php
namespace ThemeCheck;
require_once 'include/Bootstrap.php';
?>
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title><?php echo __("Themecheck.org - The page does not exist."); ?></title>
        <meta name="description" content="<?php echo __("Validate your web theme and templates"); ?>">
        <meta name="viewport" content="width=device-width">
				<meta name="robots" content="noindex">
				
        <link rel="stylesheet" href="<?php echo TC_HTTPDOMAIN;?>/css/bootstrap.min.css">
        <style>
            body {
                padding-top: 50px;
                padding-bottom: 20px;
            }
        </style>
        <link rel="stylesheet" href="<?php echo TC_HTTPDOMAIN;?>/css/bootstrap-theme.min.css">
        <link rel="stylesheet" href="<?php echo TC_HTTPDOMAIN;?>/css/main.css">

        <script src="<?php echo TC_HTTPDOMAIN;?>/js/vendor/modernizr-2.6.2-respond-1.1.0.min.js"></script>
    </head>
    <body>
    <div class="navbar navbar-inverse navbar-fixed-top">
		<!--<div class="navbar navbar-inverse">-->
      <div class="container">
        <div class="navbar-header">
        
          <a class="navbar-brand" href="<?php echo TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(),"phpfile"=>"index.php")); ?>"><img src="<?php echo TC_HTTPDOMAIN;?>/img/headerlogo.png"></a>
        </div>
        
      </div>
    </div>

    <!-- Main jumbotron for a primary marketing message or call to action -->
    <div class="jumbotron">
      <div class="container">
        <h1><?php echo __("Error 404."); ?></h1>
        <p><?php echo __("Sorry, the page you requested doesn't exist."); ?></p>
      </div>
    </div>

		<?php 
		$samepage_i18n = array('en' => TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>"en", "phpfile"=>"error404.php")),
											 'fr' => TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>"fr", "phpfile"=>"error404.php")));
		require "footer.php"; ?>
		<!-- /container -->        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="<?php echo TC_HTTPDOMAIN;?>/js/vendor/jquery-1.10.1.min.js"><\/script>')</script>

        <script src="<?php echo TC_HTTPDOMAIN;?>/js/vendor/bootstrap.min.js"></script>
				<script src="<?php echo TC_HTTPDOMAIN;?>/js/vendor/bootstrap-filestyle.min.js"></script>
        <script src="<?php echo TC_HTTPDOMAIN;?>/js/plugins.js"></script>
        <script src="<?php echo TC_HTTPDOMAIN;?>/js/main.js"></script>

        <script>
            var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']];
            (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
            g.src='//www.google-analytics.com/ga.js';
            s.parentNode.insertBefore(g,s)}(document,'script'));
        </script>
    </body>
</html>
