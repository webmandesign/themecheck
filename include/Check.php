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
		
		public function doCheck($php_files, $php_files_filtered, $css_files, $other_files)
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
    }
		
		abstract protected function createChecks();
		
		public function doCheck($php_files, $php_files_filtered, $css_files, $other_files)
		{
			$start_time_checker = microtime(true);
			foreach ($this->checks as &$check)
			{
				$start_time = microtime(true);
				$check->doCheck($php_files, $php_files_filtered, $css_files, $other_files);
				$check->duration = microtime(true) - $start_time; // check duration is calculated outside of the check to simplify check's code
			}	
			$this->duration = microtime(true) - $start_time_checker;
		}
}