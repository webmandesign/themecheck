<?php
namespace ThemeCheck;
?>
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
	<head>
	<?php if (!empty($controller->abtesting_code)) echo $controller->abtesting_code;?>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title><?php echo $controller->meta["title"]; ?></title>
		<meta name="description" content="<?php echo $controller->meta["description"]; ?>"/>
		<meta content="width=device-width, initial-scale=1, maximum-scale=1, minimal-ui" name="viewport">
		<meta name="viewport" content="width=device-width">

		<meta property="og:image" content="<?php echo TC_HTTPDOMAIN;?>/img/logo.jpg" />
		<meta property="og:url" content="<?php echo TC_HTTPDOMAIN;?>" />
		<meta property="og:title" content="<?php echo $controller->meta["title"]; ?>" />
		<meta property="og:description" content="<?php echo $controller->meta["description"]; ?>" />
		<?php if (isset($controller->meta["robots"])) echo '<meta property="robots" content="'.$controller->meta["robots"].'"/>';?>
		
		<?php 
		if (!empty($controller->samepage_i18n[I18N::getCurLang()])){
			foreach ($controller->samepage_i18n as $l=>$url) {
				echo '<link rel="alternate" hreflang="'.$l.'" href="'.$url.'"/>';
			}
		}?>			
		<link rel="stylesheet" href="<?php echo TC_HTTPDOMAIN;?>/styles/css/bootstrap-dist.css"/>
<!--		<link rel="stylesheet" href="<?php //echo TC_HTTPDOMAIN;?>/css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="<?php //echo TC_HTTPDOMAIN;?>/css/main.css"> -->
		<link rel="stylesheet" href="<?php echo TC_HTTPDOMAIN;?>/styles/css/styles.css"/>

		<link href='https://fonts.googleapis.com/css?family=Roboto:300,100,bold' rel='stylesheet' type='text/css'/>
                
		<link href='http://fonts.googleapis.com/css?family=Arimo&subset=latin,latin-ext' rel='stylesheet' type='text/css'/>
		<link rel="icon" href="<?php echo TC_HTTPDOMAIN;?>/<?php if(isset($controller->meta["favicon"])){echo $controller->meta["favicon"];} else { echo "favicon"; };?>.ico" />
		<link rel="icon" type="image/png" href="<?php echo TC_HTTPDOMAIN;?>/<?php if(isset($controller->meta["favicon"])){echo $controller->meta["favicon"];} else { echo "favicon"; };?>.png" />
		<script src="<?php echo TC_HTTPDOMAIN;?>/scripts/vendor/modernizr-2.6.2-respond-1.1.0.min.js"></script>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
		<script>window.jQuery || document.write('<script src="<?php echo TC_HTTPDOMAIN;?>/scripts/jquery/jquery-1.11.1.min.js"><\/script>')</script>

		
		<script>
			var domain_site = "<?php echo TC_HTTPDOMAIN; ?>"; 
		</script>
		
	</head>
	<body>
            <script>
		(function(d, s, id) {
		  var js, fjs = d.getElementsByTagName(s)[0];
		  if (d.getElementById(id)) return;
		  js = d.createElement(s); js.id = id;
		  js.src = "//connect.facebook.net/fr_FR/sdk.js#xfbml=1&version=v2.5";
		  fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));
		</script>
            
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
 
<div id="container">
   		<header id="header">
		 	<div id="menu" class="menu">
				<a href="<?php echo TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(),"phpfile"=>"index.php")); ?>" class="logoThemecheck">
					<span class="logo">
						<img src="<?php echo TC_HTTPDOMAIN;?>/img/images/header/logo.png"/>
					</span>
				</a>
				<div class="container_liste_menu">	
					<ul id="liste_menu" class="liste_menu">
						
                                    <?php                            
                                
                                       echo '<li><a href="'.TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"home")).'#ancreSubmit'.'" class="link_intern">SUBMIT</a></li>';
                                       echo '<li><a href="'.TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"home")).'#theme'.'" class="link_intern">THEMES</a></li>';
                                       
                                    ?>
                                               
						
                                               
						<li>
							<a href="<?php echo TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"contact")); ?>" id="contactPage"><?php echo __("CONTACT"); ?></a>
						</li>
					</ul>
			    </div>
				<a href="#" id="icon_menu_mobile">
					<span class="line line1"></span>
					<span class="line line2"></span>
					<span class="line line3"></span>
				</a>
			</div>
		</header>
   
