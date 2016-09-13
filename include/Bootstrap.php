<?php
namespace ThemeCheck;
function isSessionStarted()
{
	$sid = defined('SID') ? constant('SID') : false;
        
	if (false !== $sid && session_id())
	{
			return true;
	}
	
	if (headers_sent()) 
	{
			return true;
	}
	
	return false;
}
if (!isSessionStarted()) session_start();
				
// ****************************************************************************
// Domain and environnement
// ****************************************************************************
$url = $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]; 


if (strpos($url,'localhost/') !== FALSE)
{
	$port = '';
	if ($_SERVER['SERVER_PORT'] != '80') $port = ':'.$_SERVER['SERVER_PORT'];
	define("TC_DOMAIN", 'localhost'.$port.'/themecheck');
	define("TC_ENVIRONMENT", 'dev'); 
} else if (strpos($url,'_themecheck.org') !== FALSE)
{
	$port = '';
	if ($_SERVER['SERVER_PORT'] != '80') $port = ':'.$_SERVER['SERVER_PORT'];
	define("TC_DOMAIN", '_themecheck.org');
	define("TC_ENVIRONMENT", 'dev'); 
}
else if (strpos($url,'192.168.2.89/') !== FALSE)
{
	$port = '';
	if ($_SERVER['SERVER_PORT'] != '80') $port = ':'.$_SERVER['SERVER_PORT'];
	define("TC_DOMAIN", '192.168.2.89'.$port.'/themecheck');
	define("TC_ENVIRONMENT", 'dev');
} else if (strpos($url,'preprod') !== FALSE) {
	define("TC_DOMAIN", 'preprod.themecheck.as44099.com');
	define("TC_ENVIRONMENT", 'preprod');
} else {
	define("TC_DOMAIN", 'themecheck.org');
	define("TC_ENVIRONMENT", 'prod');
}

// ****************************************************************************
// Defines
// ****************************************************************************
define("TC_ROOTDIR", realpath(dirname(__FILE__).'/../'));
define("TC_VAULTDIR", realpath(TC_ROOTDIR.'/../themecheck_vault'));
define("TC_INCDIR", TC_ROOTDIR.'/'.'include');
define("TC_FBAPPID", "541451025891835");
define("TC_GAAPPID", "UA-27721158-1");
global $ExistingLangs;
$ExistingLangs = array("en", "fr");
global $ExistingLocales;
$ExistingLocales = array("en"=>"en_EN", "fr"=>"fr_FR");
define("TC_DEFAULT_LANG", "en");
define("TC_SITE_NAME", "themecheck.org");
define("TC_HTTPDOMAIN", 'http://'.TC_DOMAIN);
define("TC_SALT", '348daf28b36268cf504b066'); // random salt to code file names
define("ERRORLEVEL_NONE", 0);
define("ERRORLEVEL_FATAL", 1); // errors that prevent from running the tests
define("ERRORLEVEL_CRITICAL", 2); // security threats or inacceptable errors
define("ERRORLEVEL_WARNING", 3);
define("ERRORLEVEL_SUCCESS", 4);
define("ERRORLEVEL_INFO", 5);
define("TC_CONTACT_MAIL", "g.baudhuin@peoleo.fr");
define("TC_CONTACT_NAME", "Guillaume Baudhuin");

date_default_timezone_set('UTC');
global $g_creationDate;
$g_creationDate = time();

// ****************************************************************************
// Init
// ****************************************************************************
include_once (TC_ROOTDIR.'/DB/History.php');
include_once (TC_INCDIR.'/Route.php');
include_once (TC_INCDIR.'/I18N.php');

// initialize I18N
$i18n = I18N::getInstance();

// initialize usermessages
include_once (TC_INCDIR.'/UserMessage.php');
$userMessage = UserMessage::getInstance();