<?php 
namespace ThemeCheck;
require_once 'Bootstrap.php';
require_once TC_INCDIR.'/Check.php';
require_once TC_INCDIR.'/ThemeInfo.php';

class ValidationResults
{
	public $lang;
	public $themeInfo; // type ThemeInfo
	public $check_fails = array();
	public $check_warnings = array();
	public $check_successes = array();
	public $check_undefined = array();
	public $check_count = 0;
	public $check_countOK = 0; // warnings + successes
	public $score = 0; // %warnings + %successes
	
	public function __construct($lang) 
	{
		$this->lang = $lang;
	}
	
	public function serialize($hash)
	{
		$savedirectory_rslt = ThemeInfo::getReportDirectory($hash);
		if (!file_exists($savedirectory_rslt)) mkdir($savedirectory_rslt, 0775, true);
		$json = json_encode($this);
		file_put_contents($savedirectory_rslt.'/results_'.$this->lang.'.json', $json);
	}
	
	static public function unserialize($hash, $lang)
	{
		$directory = ThemeInfo::getReportDirectory($hash);		
		$fullfilename = $directory.'/results_'.$lang.'.json';
					
		if (!file_exists($fullfilename )) return null;
		$json = file_get_contents($fullfilename);
		$obj = json_decode($json);
		
		$validationResults = new ValidationResults($lang);
		
		$validationResults->check_fails = $obj->check_fails;
		$validationResults->check_warnings = $obj->check_warnings;
		$validationResults->check_successes = $obj->check_successes;
		$validationResults->check_undefined = $obj->check_undefined;
		$validationResults->check_count = $obj->check_count;
		$validationResults->check_countOK = $obj->check_countOK;
		$validationResults->score = $obj->score;	

		return $validationResults;
	}
}