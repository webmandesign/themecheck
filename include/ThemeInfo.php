<?php
namespace ThemeCheck;
require_once 'Bootstrap.php';
require_once TC_INCDIR.'/ListDirectoryFiles.php';
require_once TC_INCDIR.'/Check.php';
require_once TC_INCDIR.'/tc_helpers.php';
define("TC_LICENSE_NONE", 						0);
define("TC_LICENSE_CUSTOM", 					1);
define("TC_LICENSE_CREATIVE_COMMONS", 2);
define("TC_LICENSE_CC_BY", 						3);
define("TC_LICENSE_CC_BY_SA", 				4);
define("TC_LICENSE_CC_BY_ND", 				5);
define("TC_LICENSE_CC_BY_NC", 				6);
define("TC_LICENSE_CC_BY_NC_SA", 			7);
define("TC_LICENSE_CC_BY_NC_ND", 			8);
define("TC_LICENSE_MIT_X11", 					9);
define("TC_LICENSE_BSD", 							10);
define("TC_LICENSE_NEWBSD", 					11);
define("TC_LICENSE_FREEBSD", 					12);
define("TC_LICENSE_LGPL2", 						13);
define("TC_LICENSE_LGPL3", 						14);
define("TC_LICENSE_GPL2", 						15);
define("TC_LICENSE_GPL3",							16);
define("TC_LICENSE_APACHE", 					17);
define("TC_LICENSE_CDDL",							18);
define("TC_LICENSE_ECLIPSE", 					19);

/** 
*		Store theme info and metadata
**/
class ThemeInfo
{
	public $themetype;// "joomla", "wordpress"
	public $hash; 	  // hash is the md5 file hash in base36
	public $hash_md5;
	public $hash_sha1;
	public $zipfilename; // the name of the original file
	public $zipmimetype;
	public $zipfilesize; // size of archive
	public $userIp;// ip of poster
	public $name;
	public $author;
	public $description;
	public $themeUri;
	public $version;
	public $authorUri;
	public $authorMail;
	public $tags;
	
	public $copyright;
	public $creationDate;
	public $serializable;
	
	public $license;
	public $licenseUri;
	public $licenseText;
	public $cmsVersion;
	public $hasBacklinKey;
	public $filesIncluded;
	public $modulePositions;
	public $templateParameters;
	public $imagePath;//path of the snaphsot

	public $validation_timestamp; // Unix timestamp
	
	public function __construct($hash)
	{
		$this->hash = $hash;
		$this->serializable = true;
	}
			
	/** 
	*		Analyze an unzipped theme and get metadata
	**/
	public function initFromUnzippedArchive($unzippath, $zipfilename, $zipmimetype, $zipfilesize)
	{
		$this->themetype = $this->detectThemetype($unzippath);
		$this->zipfilename = $zipfilename;
		$this->zipmimetype = $zipmimetype;
		$this->zipfilesize = $zipfilesize;
		$this->userIp = $_SERVER['REMOTE_ADDR'];
		if ($this->userIp == '::1') $this->userIp = '127.0.0.1';
		
		$rawlicense = '';
		
		// list of meaningful files in themes
		// txt, csv, etc. are meaningless
		$filetypes = array(	'css'=>false,
												'php'=>false,
												'html'=>false,
												'phtml'=>false,
												'htm'=>false,
												'xml'=>false,
												'gif'=>false,
												'jpeg'=>false,
												'png'=>false,
												'psd'=>false,
												'ai'=>false,
												'doc'=>false,
												'docx'=>false,
												'rtf'=>false);
		
		
		if ($this->themetype == TT_WORDPRESS)
		{
			$files = listdir( $unzippath );
			if ( $files ) {
				foreach( $files as $key => $filename ) {
					$path_parts = pathinfo($filename);
					$basename = $path_parts['basename'];
					if ($basename == 'style.css')
					{
						$file_content = file_get_contents($filename);
						
						if ( preg_match('/[ \t\/*#]*Theme Name:(.*)$/mi', 	$file_content, $match) && !empty($match) && count($match)==2) $this->name = trim($match[1]);
						if ( preg_match('/[ \t\/*#]*Description:(.*)$/mi', 	$file_content, $match) && !empty($match) && count($match)==2) $this->description = trim($match[1]);
						if ( preg_match('/[ \t\/*#]*Author:(.*)$/mi', 			$file_content, $match) && !empty($match) && count($match)==2) $this->author = trim($match[1]);
						if ( preg_match('/[ \t\/*#]*Theme URI:(.*)$/mi', 		$file_content, $match) && !empty($match) && count($match)==2) $this->themeUri = trim($match[1]);
						if ( preg_match('/[ \t\/*#]*Author URI:(.*)$/mi', 	$file_content, $match) && !empty($match) && count($match)==2) $this->authorUri = trim($match[1]);
						if ( preg_match('/[ \t\/*#]*Version:(.*)$/mi', 			$file_content, $match) && !empty($match) && count($match)==2) $this->version = trim($match[1]);
						if ( preg_match('/[ \t\/*#]*License:(.*)$/mi', 			$file_content, $match) && !empty($match) && count($match)==2) $rawlicense = trim($match[1]);
						if ( preg_match('%[ \t\/*#]*License URI:.*(https?://[A-Za-z0-9-\./_~:?#@!$&\'()*+,;=]*)%mi', 	$file_content, $match) && !empty($match) && count($match)==2) $this->licenseUri = trim($match[1]);
						if ( preg_match('/[ \t\/*#]*Tags:(.*)$/mi', 				$file_content, $match) && !empty($match) && count($match)==2) $this->tags = trim($match[1]);
					}
					
					if (isset($path_parts['extension'])) {
						$ext = strtolower(trim($path_parts['extension']));
						if (isset($filetypes[$ext])) $filetypes[$ext] = true;
					}
				}
			}
			$this->cmsVersion = "3.4+";
		}
		
		if ($this->themetype == TT_JOOMLA)
		{
			$files = listdir( $unzippath );
			if ( $files ) {
				foreach( $files as $key => $filename ) {
					$path_parts = pathinfo($filename);
					$basename = $path_parts['basename'];
					if ($basename == 'templateDetails.xml')
					{					
						$xml = simplexml_load_file($filename);
						if (!empty($xml))
						{
								if ($xml->getName() == 'extension' || $xml->getName() == 'install')
								{
									if(!empty($xml->name)) $this->name = (string)$xml->name;
									if(!empty($xml->description)) $this->description = (string)$xml->description;
									if(!empty($xml->author)) $this->author = (string)$xml->author;
									if(!empty($xml->authorUrl)) $this->authorUri = (string)$xml->authorUri;
									if(!empty($xml->authorMail)) $this->authorUri = (string)$xml->authorMail;
									if(!empty($xml->version)) $this->version = (string)$xml->version;
									if(!empty($xml->copyright)) $this->copyright = (string)$xml->copyright;
									if(!empty($xml->creationDate)) $this->creationDate = (string)$xml->creationDate;
									if(!empty($xml->license)) {
										$rawlicense = (string)$xml->license;
									}
									
									if ($xml->getName() == 'extension') {
										$attrs = $xml->attributes();
										if (isset($attrs["version"])) $this->cmsVersion = (string)$attrs["version"];
										else $this->cmsVersion = "3.0";
									}
									if ($xml->getName() == 'install') $this->cmsVersion = "1.5";
								} 
						}
					}
					if (isset($path_parts['extension'])) {
						$ext = strtolower(trim($path_parts['extension']));
						if (isset($filetypes[$ext])) $filetypes[$ext] = true;
					}
				}
			}
		}
		
		$this->license = self::getLicense($rawlicense);
		if (preg_match('%(https?://[A-Za-z0-9-\./_~:?#@!$&\'()*+,;=])%i', $rawlicense, $match) && !empty($match) && count($match)==2) // if contains an url
		{
			$this->licenseUri = trim($match[1]);
		}
		if (empty($this->licenseUri)) $this->licenseUri = self::getLicenseUri($this->license);
		if ($this->license == TC_LICENSE_CUSTOM) $this->licenseText = $rawlicense;
		
		$this->hasBacklinKey = false;
		
		if ($filetypes['css']) $this->filesIncluded .= 'CSS, ';
		if ($filetypes['php']) $this->filesIncluded .= 'PHP, ';
		if ($filetypes['html'] || $filetypes['phtml'] || $filetypes['htm']) $this->filesIncluded .= 'HTML, ';
		if ($filetypes['xml']) $this->filesIncluded .= 'XML, ';
		if ($filetypes['gif'] || $filetypes['jpeg'] || $filetypes['png']) $this->filesIncluded .= 'Bitmap images, ';
		if ($filetypes['psd']) $this->filesIncluded .= 'Adobe Photoshop, ';
		if ($filetypes['xml']) $this->filesIncluded .= 'Adobe Illustrator, ';
		if ($filetypes['doc'] || $filetypes['docx']) $this->filesIncluded .= 'MS Word, ';
		if ($filetypes['rtf']) $this->filesIncluded .= 'RTF, ';

		$this->filesIncluded = trim($this->filesIncluded, " ,");
	//	public $filesIncluded;
	//	public $modulePositions;
	//	public $templateParameters;
	//	public $imagePath;
	}
		
	/** 
	*		Auto detect theme type
	**/
	static public function detectThemetype($unzippath)
	{
		$files = listdir( $unzippath );

		$score_wordpress = 0;
		$score_joomla = 0;
		
		if ( $files ) {
			foreach( $files as $key => $filename ) {
				$path_parts = pathinfo($filename);
				$basename = $path_parts['basename'];
				if ($basename == 'style.css')
				{
					$file_content = file_get_contents($filename);
					
					if ( preg_match('/[ \t\/*#]*Theme Name:/i', $file_content) | preg_match('/[ \t\/*#]*Description:/i', $file_content) | preg_match('/[ \t\/*#]*Author:/i', $file_content) )
					{
						$score_wordpress ++;
					}
				}
				if ($basename == 'screenshot.png') $score_wordpress ++;
				if ($basename == 'templateDetails.xml') $score_joomla ++;
				if ($basename == 'template_thumbnail.png') $score_joomla ++;
				if ($basename == 'template_preview.png') $score_joomla ++;
				if ($basename == 'index.php') 
				{
					$file_content = file_get_contents($filename);
					if ( preg_match('/get_header\s?\(\s?\)/', $file_content) && preg_match('/get_footer\s?\(\s?\)/', $file_content) ) $score_wordpress ++;
					if ( strpos($file_content, "'_JEXEC'") !== false) $score_joomla ++;
				}
			}
		}
		
		if ($score_joomla > $score_wordpress) return TT_JOOMLA;
		if ($score_joomla < $score_wordpress) return TT_WORDPRESS;
		return TT_UNDEFINED;
	}
	
	/** 
	*		Gets the report path of an item from its hash.
	**/
	static public function getReportDirectory($hash)
	{
		$path = TC_VAULTDIR.'/reports';
		
		// split directory tree to avoid huge directories that are so slow in FTP
		$path1 = substr($hash, 0, 2);
		$path2 = substr($hash, 2, 2);
		$path3 = substr($hash, 4);
		$path = $path.'/'.$path1.'/'.$path2.'/'.$path3;

		return $path;
	}	
	
	/** 
	*		Gets the public path of an item from its hash.
	**/
	static public function getPublicDirectory($hash)
	{
		$path = TC_ROOTDIR.'/dyn';
		
		// split directory tree to avoid huge directories that are so slow in FTP
		$path1 = substr($hash, 0, 2);
		$path2 = substr($hash, 2, 2);
		$path3 = substr($hash, 4);
		$path = $path.'/'.$path1.'/'.$path2.'/'.$path3;

		return $path;
	}	
	
	/** 
	*		Test license recognition width different patterns
	**/
	static public function testLicence()
	{
		$t = array('creative commons' => TC_LICENSE_CREATIVE_COMMONS,
							 'creative-commons' => TC_LICENSE_CREATIVE_COMMONS,
							 'ygyg creative-commons' => TC_LICENSE_CREATIVE_COMMONS,
							 'ygyg Creative-commons efce' => TC_LICENSE_CREATIVE_COMMONS,
							 'ygyg creativecommons efce' => TC_LICENSE_CREATIVE_COMMONS,
							 'CC-BY' => TC_LICENSE_CC_BY,
							 'CC_BY' => TC_LICENSE_CC_BY,
							 'CC BY' => TC_LICENSE_CC_BY,
							 'http://creativecommons.org/licenses/by/1.0/' => TC_LICENSE_CC_BY,
							 'Creative-commons BY' => TC_LICENSE_CC_BY,
							 'CC-BY-SA' => TC_LICENSE_CC_BY_SA,
							 'CC_BY-SA' => TC_LICENSE_CC_BY_SA,
							 'CC BY SA' => TC_LICENSE_CC_BY_SA,
							 'http://creativecommons.org/licenses/by-sa/2.0/' => TC_LICENSE_CC_BY_SA,
							 'Creative-commons BY SA' => TC_LICENSE_CC_BY_SA,
							 'CC-BY-NC-SA' => TC_LICENSE_CC_BY_NC_SA,
							 'CC_BY-NC-SA' => TC_LICENSE_CC_BY_NC_SA,
							 'CC BY NC SA' => TC_LICENSE_CC_BY_NC_SA,
							 'Creative-commons BY NC SA' => TC_LICENSE_CC_BY_NC_SA,
							 'CC-BY-NC-ND' => TC_LICENSE_CC_BY_NC_ND,
							 'CC_BY-NC-ND' => TC_LICENSE_CC_BY_NC_ND,
							 'CC BY NC ND' => TC_LICENSE_CC_BY_NC_ND,
							 'Creative-commons BY NC ND' => TC_LICENSE_CC_BY_NC_ND,
							 'CC-BY-NC' => TC_LICENSE_CC_BY_NC,
							 'CC_BY-NC' => TC_LICENSE_CC_BY_NC,
							 'CC BY NC' => TC_LICENSE_CC_BY_NC,
							 'Creative-commons BY NC' => TC_LICENSE_CC_BY_NC,
							 'CC-BY-ND' => TC_LICENSE_CC_BY_ND,
							 'MIT' => TC_LICENSE_MIT_X11,
							 'X11' => TC_LICENSE_MIT_X11,
							 'MIT X11' => TC_LICENSE_MIT_X11,
							 'MIT_X11' => TC_LICENSE_MIT_X11,
							 'X11-MIT' => TC_LICENSE_MIT_X11,
							 'http://opensource.org/licenses/MIT' => TC_LICENSE_MIT_X11,
							 'BSD' => TC_LICENSE_BSD,
							 'blabla BSD' => TC_LICENSE_BSD,
							 'New BSD' => TC_LICENSE_NEWBSD,
							 'revised BSD' => TC_LICENSE_NEWBSD,
							 'revised_BSD' => TC_LICENSE_NEWBSD,
							 'revised BSD 3' => TC_LICENSE_NEWBSD,
							 'BSD 3.0' => TC_LICENSE_NEWBSD,
							 'Free BSD' => TC_LICENSE_FREEBSD,
							 'simplified BSD' => TC_LICENSE_FREEBSD,
							 'simplified_BSD' => TC_LICENSE_FREEBSD,
							 'simplified BSD 2' => TC_LICENSE_FREEBSD,
							 'BSD 2.0' => TC_LICENSE_FREEBSD,
							 'GPL' => TC_LICENSE_GPL2,
							 'GNUGPL' => TC_LICENSE_GPL2,
							 'http://opensource.org/licenses/GPL-2.0' => TC_LICENSE_GPL2,
							 'LGPL' => TC_LICENSE_LGPL2,
							 'LGPL 3' => TC_LICENSE_LGPL3,
							 'LGPL-2' => TC_LICENSE_LGPL2,
							 'GNU-LGPL' => TC_LICENSE_LGPL2,
							 'GNU LGPL' => TC_LICENSE_LGPL2,
							 'gnu_lgpl' => TC_LICENSE_LGPL2,
							 'GNU' => TC_LICENSE_GPL2,
							 'general public license' => TC_LICENSE_GPL2,
							 'GNU LGPL 2' => TC_LICENSE_LGPL2,
							 'Apache' => TC_LICENSE_APACHE,
							 'cddl' => TC_LICENSE_CDDL,
							 'COMMON DEVELOPMENT AND DISTRIBUTION LICENSE' => TC_LICENSE_CDDL,
							 'eclipse' => TC_LICENSE_ECLIPSE,
							 'epl-1.0' => TC_LICENSE_ECLIPSE,
							 'my own license' => TC_LICENSE_CUSTOM,
							 '' => TC_LICENSE_NONE,
							);	
		foreach ($t as $k => $v)
		{
			$a = self::getLicense($k);
			if ($a == $v) echo 'OK';
			else echo '--';
			echo '&nbsp;'.self::getLicenseName($a).' -> '.$k.'<br>';
		}
	}
	
	/** 
	*		Search a string for a regular license
	**/
	static public function getLicense($rawString)
	{
		$ret = TC_LICENSE_CUSTOM;
		if (empty($rawString)) $ret = TC_LICENSE_NONE;
		else if ( preg_match('/\b(CC|Creative[-_ \t]?Common[s]?).*BY[-_ \t]?NC[-_ \t]?SA\b/i', $rawString, $match)) $ret = TC_LICENSE_CC_BY_NC_SA; 
		else if ( preg_match('/\b(CC|Creative[-_ \t]?Common[s]?).*BY[-_ \t]?NC[-_ \t]?ND\b/i', $rawString, $match)) $ret = TC_LICENSE_CC_BY_NC_ND;
		else if ( preg_match('/\b(CC|Creative[-_ \t]?Common[s]?).*BY[-_ \t]?SA\b/i', $rawString, $match)) $ret = TC_LICENSE_CC_BY_SA;
		else if ( preg_match('/\b(CC|Creative[-_ \t]?Common[s]?).*BY[-_ \t]?NC\b/i', $rawString, $match)) $ret = TC_LICENSE_CC_BY_NC;
		else if ( preg_match('/\b(CC|Creative[-_ \t]?Common[s]?).*BY[-_ \t]?ND\b/i', $rawString, $match)) $ret = TC_LICENSE_CC_BY_ND;
		else if ( preg_match('/\b(CC|Creative[-_ \t]?Common[s]?).*BY\b/i', $rawString, $match)) $ret = TC_LICENSE_CC_BY;
		else if ( preg_match('/\bCreative[-_ \t]?Common[s]?\b/i', $rawString, $match)) $ret = TC_LICENSE_CREATIVE_COMMONS;
		else if ( preg_match('/((\bMIT\b)|(\bX11\b))|(MIT_X11)|(X11_MIT)/i', $rawString, $match)) $ret = TC_LICENSE_MIT_X11;
		else if ( preg_match('/((new|revised)[-_ \t]?BSD)|(BSD.*3)/i', $rawString, $match)) $ret = TC_LICENSE_NEWBSD;
		else if ( preg_match('/((simplified|free)[-_ \t]?BSD)|(BSD.*2)/i', $rawString, $match)) $ret = TC_LICENSE_FREEBSD;
		else if ( preg_match('/BSD/i', $rawString, $match)) $ret = TC_LICENSE_BSD;
		else if ( preg_match('/(LGPL.*3)|(lesser general[-_ ]public[-_ ]licen(c|s)e.*3)/i', $rawString, $match)) $ret = TC_LICENSE_LGPL3;
		else if ( preg_match('/(LGPL.*2)|(lesser general[-_ ]public[-_ ]licen(c|s)e.*2)/i', $rawString, $match)) $ret = TC_LICENSE_LGPL2;
		else if ( preg_match('/(LGPL|lesser[-_ ]general[-_ ]public)/i', $rawString, $match)) $ret = TC_LICENSE_LGPL2;
		else if ( preg_match('/(GPL.*3)|(general[-_ ]public[-_ ]licen(c|s)e.*3)/i', $rawString, $match)) $ret = TC_LICENSE_GPL3;
		else if ( preg_match('/(GPL.*2)|(general[-_ ]public[-_ ]licen(c|s)e.*2)/i', $rawString, $match)) $ret = TC_LICENSE_GPL2;
		else if ( preg_match('/(GNU|GPL|General[-_ ]Public[-_ ]Licen(c|s)e)/i', $rawString, $match)) $ret = TC_LICENSE_GPL2;
		else if ( preg_match('/apache/i', $rawString, $match)) $ret = TC_LICENSE_APACHE;
		else if ( preg_match('/(CDDL|Common[-_ ]Development[-_ ]and[-_ ]Distribution)/i', $rawString, $match)) $ret = TC_LICENSE_CDDL;
		else if ( preg_match('/(epl|eclipse)/i', $rawString, $match)) $ret = TC_LICENSE_ECLIPSE;

		return $ret;
	}
	
	/** 
	*		Return URL of a license from license id
	**/	
	static public function getLicenseUri($licenseId)
	{
		$uris = array(TC_LICENSE_NONE 						=> '',
									TC_LICENSE_CUSTOM 					=> '',
									TC_LICENSE_CREATIVE_COMMONS => 'http://creativecommons.org/licenses/by/2.0/',
									TC_LICENSE_CC_BY 						=> 'http://creativecommons.org/licenses/by/2.0/',
									TC_LICENSE_CC_BY_SA 				=> 'http://creativecommons.org/licenses/by-sa/2.0/',
									TC_LICENSE_CC_BY_ND 				=> 'http://creativecommons.org/licenses/by-nd/2.0/',
									TC_LICENSE_CC_BY_NC 				=> 'http://creativecommons.org/licenses/by-nc/2.0/',
									TC_LICENSE_CC_BY_NC_SA 			=> 'http://creativecommons.org/licenses/by-nc-sa/2.0/',
									TC_LICENSE_CC_BY_NC_ND 			=> 'http://creativecommons.org/licenses/by-nc-nd/2.0/',
									TC_LICENSE_MIT_X11 					=> 'http://opensource.org/licenses/MIT',
									TC_LICENSE_BSD 							=> 'http://en.wikisource.org/wiki/BSD_License',
									TC_LICENSE_NEWBSD 					=> 'http://opensource.org/licenses/BSD-3-Clause',
									TC_LICENSE_FREEBSD 					=> 'http://opensource.org/licenses/BSD-2-Clause',
									TC_LICENSE_LGPL2 						=> 'http://opensource.org/licenses/LGPL-2.1',
									TC_LICENSE_LGPL3 						=> 'http://opensource.org/licenses/LGPL-3.0',
									TC_LICENSE_GPL2 						=> 'http://opensource.org/licenses/GPL-2.0',
									TC_LICENSE_GPL3 						=> 'http://opensource.org/licenses/GPL-3.0',
									TC_LICENSE_APACHE 					=> 'http://opensource.org/licenses/Apache-2.0',
									TC_LICENSE_CDDL 						=> 'http://opensource.org/licenses/CDDL-1.0',
									TC_LICENSE_ECLIPSE 					=> 'http://opensource.org/licenses/EPL-1.0');
									
		if (isset($uris[$licenseId])) return $uris[$licenseId];
		return '';
	}
	
	/** 
	*		Return the name of a license from license id
	**/	
	static public function getLicenseName($licenseId)
	{
		$names = array(TC_LICENSE_NONE 						=> __('None'),
									TC_LICENSE_CUSTOM 					=> __('Custom'),
									TC_LICENSE_CREATIVE_COMMONS => __('Creative Commons'),
									TC_LICENSE_CC_BY 						=> __('Creative Commons BY'),
									TC_LICENSE_CC_BY_SA 				=> __('Creative Commons BY SA'),
									TC_LICENSE_CC_BY_ND 				=> __('Creative Commons BY ND'),
									TC_LICENSE_CC_BY_NC 				=> __('Creative Commons BY NC'),
									TC_LICENSE_CC_BY_NC_SA 			=> __('Creative Commons BY NC SA'),
									TC_LICENSE_CC_BY_NC_ND 			=> __('Creative Commons BY NC ND'),
									TC_LICENSE_MIT_X11 					=> __('MIT X11'),
									TC_LICENSE_BSD 							=> __('BSD'),
									TC_LICENSE_NEWBSD 					=> __('New BSD'),
									TC_LICENSE_FREEBSD 					=> __('Free BSD'),
									TC_LICENSE_LGPL2 						=> __('GNU LGPL 2'),
									TC_LICENSE_LGPL3 						=> __('GNU LGPL 3'),
									TC_LICENSE_GPL2 						=> __('GNU GPL 2'),
									TC_LICENSE_GPL3 						=> __('GNU GPL 3'),
									TC_LICENSE_APACHE 					=> __('Apache'),
									TC_LICENSE_CDDL 						=> __('CDDL'),
									TC_LICENSE_ECLIPSE 					=> __('Eclipse'));
									
		if (isset($names[$licenseId])) return $names[$licenseId];
		return '';
	}
}