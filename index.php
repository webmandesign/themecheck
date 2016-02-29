<?php
namespace ThemeCheck;

require_once 'include/Bootstrap.php';

// Error handling
function ErrorHandler($errLevel, $errMsg, $errFile, $errLine)
{
	$errorLevelClass = "";
	if ($errLevel & E_ERROR) 				$errorLevelClass = " alert-danger";
	if ($errLevel & E_WARNING) 			$errorLevelClass = " alert-warning";
	if ($errLevel & E_CORE_ERROR) 	$errorLevelClass = " alert-danger";
	if ($errLevel & E_CORE_WARNING) $errorLevelClass = " alert-warning";
	if ($errLevel & E_USER_ERROR) 	$errorLevelClass = " alert-danger";
	if ($errLevel & E_USER_WARNING) $errorLevelClass = " alert-warning";
	if ($errLevel & E_USER_NOTICE) 	$errorLevelClass = " alert-warning";
	if ($errLevel & E_NOTICE) 			$errorLevelClass = " alert-danger";
	if ($errLevel & E_COMPILE_ERROR)$errorLevelClass = " alert-danger";
	
	if ($errLevel & E_USER_ERROR)
		echo '<div class="alert'.$errorLevelClass.'">'.__('Error')." : ".htmlentities($errMsg).'</div>';
	else if($errLevel & E_USER_WARNING)
		echo '<div class="alert'.$errorLevelClass.'">'.__('Warning')." : ".htmlentities($errMsg).'</div>';
	else 
		echo '<div class="alert'.$errorLevelClass.'">'.__('Error').' '.$errLevel." : ".htmlentities($errMsg).'<br/>In '.$errFile.' line '.$errLine.'</div>';
}

set_error_handler(__NAMESPACE__."\ErrorHandler");
 
// Multilingual url un-rewriting
$routeParts = Route::getInstance()->match();

if (empty($routeParts) || empty($routeParts["phpfile"]) || $routeParts["phpfile"] == "error404.php") 
{
	include_once (TC_ROOTDIR.'/error404.php');
     	die;
} else 
{ 
	include (TC_ROOTDIR.'/controllers/controller_'.$routeParts["phpfile"].'.php');
	$classname = '\\ThemeCheck\\Controller_'.$routeParts["phpfile"];
	$controller = new $classname();
	$controller->prepare();
	if ($routeParts["phpfile"] == 'download')
	{
		$controller->render();
	} else {
		require "header.php";
		$controller->render();
		require "footer.php";
	}
}

