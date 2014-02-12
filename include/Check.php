<?php
namespace ThemeCheck;
require_once TC_INCDIR.'/Bootstrap.php';
require_once TC_INCDIR.'/tc_helpers.php';
define ("ERRORLEVEL_UNDEFINED", 0);
define ("TT_UNDEFINED", 1);
define ("TT_WORDPRESS", 1);
define ("TT_JOOMLA", 2);
define ("TT_COMMON", TT_WORDPRESS | TT_JOOMLA );

// Simple unit of check
abstract class CheckPart
{
		public $duration;		// duration in seconds (float)
		public $threatLevel; // ERRORLEVEL_ERROR, ERRORLEVEL_WARNING or ERRORLEVEL_SUCCESS
		public $code;				// code to be tested
		public $messages;		// array of messages, variable depending on errors detected while checking
		public $hint = array();				// hint/explanation
		public $unittest;		// zip file of unit tests
		public $themetype;  // TT_UNDEFINED, TT_COMMON, TT_WORDPRESS, TT_JOOMLA
		public $errorLevel; // errorlelvel of tested check
		
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
	
    public function __construct($themetype, $threatLevel, $badPraticeDescription, $code, $unittest = null)
    {
			$this->themetype = $themetype;
			$this->duration = 0;
			$this->threatLevel = $threatLevel;
			$this->code = $code;
			$this->hint = $badPraticeDescription; // multilingual array of hints
			$this->unittest = $unittest;
			$this->messages = array(); // array of multilingual messages (array of array)
			$this->errorLevel = ERRORLEVEL_UNDEFINED;			
    }
		
		public function doCheck($php_files, $css_files, $other_files)
		{
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
    }
		
		abstract protected function createChecks();
		
		public function doCheck($php_files, $css_files, $other_files)
		{
			$start_time_checker = microtime(true);
			foreach ($this->checks as &$check)
			{
				$start_time = microtime(true);
				$check->doCheck($php_files, $css_files, $other_files);
				$check->duration = microtime(true) - $start_time; // check duration is calculated outside of the check to simplify check's code
			}	
			$this->duration = microtime(true) - $start_time_checker;
		}
}