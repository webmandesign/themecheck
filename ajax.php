<?php
namespace ThemeCheck;

require_once 'include/Bootstrap.php';

function ErrorHandler($errLevel, $errMsg, $errFile, $errLine)
{	
	if ($errLevel & E_USER_ERROR) {
		$response["error"] = $errMsg;
		ob_clean();
		header('Content-Type: application/json');
		echo json_encode($response);
		die;
	}
}

set_error_handler(__NAMESPACE__."\ErrorHandler");

// Multilingual url un-rewriting
$routeParts = Route::getInstance()->match();

if (empty($_GET["controller"]) || empty($_GET["action"])) die;
$controller = $_GET["controller"];
$action = $_GET["action"];
if (($controller == "home" && $action == "seemore") || 
		($controller == "home" && $action == "sort")||
		($controller == "massimport" && $action == "updatenext") ||
		($controller == "unittests" && $action == "sample")
		)
{
	include (TC_ROOTDIR.'/controllers/controller_'.$controller.'.php');
	$classname = '\\ThemeCheck\\Controller_'.$controller;
	$controller = new $classname();
	$action = "ajax_".$action;
	$controller->$action();
}