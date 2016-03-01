<?php
namespace ThemeCheck;
require_once TC_INCDIR.'/Bootstrap.php';
require_once TC_INCDIR.'/tc_helpers.php';
define ("ERRORLEVEL_UNDEFINED", 0);
define ("TT_UNDEFINED", 0);
define ("TT_WORDPRESS", 1);
define ("TT_JOOMLA", 2);
define ("TT_WORDPRESS_CHILD", 4);
define ("TT_COMMON", TT_WORDPRESS | TT_JOOMLA | TT_WORDPRESS_CHILD );

// Simple unit of check
abstract class CheckPart
{
		public $duration;		// duration in seconds (float)
		public $threatLevel; // ERRORLEVEL_CRITICAL, ERRORLEVEL_WARNING or ERRORLEVEL_SUCCESS
		public $code;				// code to be tested
		public $messages = array();		// array of messages, variable depending on errors detected while checking
		public $hint = array();				// hint/explanation
		public $unittest;		// zip file of unit tests
		public $themetype;  // TT_UNDEFINED, TT_COMMON, TT_WORDPRESS, TT_JOOMLA...
		public $errorLevel; // errorlelvel of tested check
		public $id; // unique id of the check
		public $checked;
		
		protected $checks;
		protected $unittests;
		
		public static function getChecks()
		{
			return null;
		}
		
		public static function getUnittests()
		{
			return null;
		}
	
    public function __construct($id, $themetype, $threatLevel, $badPraticeDescription, $code, $unittest = null)
    {
			$this->themetype = $themetype;
			$this->duration = 0;
			$this->threatLevel = $threatLevel;
			$this->code = $code;
			$this->hint = $badPraticeDescription; // multilingual array of hints
			$this->unittest = $unittest;
			$this->messages = array(); // array of multilingual messages (array of array)
			$this->errorLevel = ERRORLEVEL_UNDEFINED;	
			$this->id = $id;
    }
		
		public function doCheck($php_files, $php_files_filtered, $css_files, $other_files, $themeInfo)
		{
		}
		
		/**
		* Converts the current multilingual CheckPart to a monolingual CheckPart
		*/
		public function getMonolingual($lang)
		{
			$_hint = (isset($this->hint[$lang]))?$this->hint[$lang]:$this->hint['en'];
			
			$_messages = array();
			foreach ($this->messages as $m)
			{
				$_messages[] = (isset($m[$lang]))?$m[$lang]:$m['en'];
			}

			$ret = clone $this;
			$ret->hint = $_hint;
			$ret->messages = $_messages;
			
			if (isset($this->title))
			{
				$ret->title = (isset($this->title[$lang]))?$this->title[$lang]:$this->title['en'];
			}
			return $ret;
		}
}

// A set of checks related to a specific subject
abstract class Check
{
	public $duration;		// duration in seconds (float)
	public $checkCount; // total number of checks the class is supposed to make
	public $title;			// title (multilingual array)
	public $checks = array(); // checklist
		
    public function __construct()
    {
			$this->title = array();
			$this->createChecks();
			$this->duration = 0;
			$this->checkCount = count($this->checks);
			$this->currentThemetype = TT_UNDEFINED;
			$this->currentCmsVersion = '0';
    }
		
	abstract protected function createChecks();
	
	public function doCheck($php_files, $php_files_filtered, $css_files, $other_files, $themeInfo)
	{
		$start_time_checker = microtime(true);
		foreach ($this->checks as &$check)
		{
			if ($themeInfo->themetype & $check->themetype)
			{
				$start_time = microtime(true);
				$check->doCheck($php_files, $php_files_filtered, $css_files, $other_files, $themeInfo);
				$check->duration = microtime(true) - $start_time; // check duration is calculated outside of the check to simplify check's code
			}
		}	
		$this->duration = microtime(true) - $start_time_checker;
	}
	
	public static function versionCmp($v1, $v2, $themetype)
	{
		$v1_int = intval(str_pad(str_replace(".", "", $v1), 5, "0"));
		$v2_int = intval(str_pad(str_replace(".", "", $v2), 5, "0"));
		if ($v1_int == 0 || $v2_int == 0) return false; // one of the version does not match N.N... pattern
		if ($v1_int < $v2_int) return -1;
		if ($v1_int > $v2_int) return 1;
		
		return 0;
	}
}