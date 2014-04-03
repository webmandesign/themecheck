<?php
namespace ThemeCheck;
?>
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title><?php echo $controller->meta["title"]; ?></title>
		<meta name="description" content="<?php echo $controller->meta["description"]; ?>">
		
		<meta name="viewport" content="width=device-width">

		<link rel="stylesheet" href="<?php echo TC_HTTPDOMAIN;?>/css/bootstrap.min.css">
		<style>
				body {
						padding-bottom: 20px;
				}
		</style>
		<link rel="stylesheet" href="<?php echo TC_HTTPDOMAIN;?>/css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="<?php echo TC_HTTPDOMAIN;?>/css/main.css">
		<link href='http://fonts.googleapis.com/css?family=Arimo&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
		<link rel="icon" href="<?php echo TC_HTTPDOMAIN;?>/favicon.ico" />
		<link rel="icon" type="image/png" href="<?php echo TC_HTTPDOMAIN;?>/favicon.png" />
		<script src="<?php echo TC_HTTPDOMAIN;?>/js/vendor/modernizr-2.6.2-respond-1.1.0.min.js"></script>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
		<script>window.jQuery || document.write('<script src="<?php echo TC_HTTPDOMAIN;?>/js/vendor/jquery-1.11.0.min.js"><\/script>')</script>
	</head>
	<body>
<?php include_once("analyticstracking.php");
if (isset($controller->inlinescripts))
{
	foreach ($controller->inlinescripts as $script)
	{
		echo '<script>'."\n";
		echo $script."\n";
		echo '</script>'."\n";
	}
	
}
 ?>
    <div class="navbar navbar-inverse">
		<!--<div class="navbar navbar-inverse">-->
      <div class="container">
        <div class="navbar-header">
					<a class="navbar-brand" href="<?php echo TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(),"phpfile"=>"index.php")); ?>"><img src="<?php echo TC_HTTPDOMAIN;?>/img/headerlogo.png"></a>
        </div>
				<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
					<ul	class="nav navbar-nav navbar-right">
						<li><a href="<?php echo TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"contact"));?>"><?php echo __("Contact us");?></a></li>
					</ul>
				</div>	
      </div>
    </div>
