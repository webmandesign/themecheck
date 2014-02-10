<?php 
namespace ThemeCheck;
class I18N
{
	private static $instance;
	public $curLang;
	public $curLangMaj;
	private function __construct() 
	{	
		global $ExistingLocales;// defined in bootstrap.php
		global $ExistingLangs; 	// defined in bootstrap.php
		
		// ******************************************************************************************
		// Current language
		// ******************************************************************************************
		$this->curLang = Route::getLangFromUrl();
		$this->curLangMaj = strtoupper($this->curLang);

		// ******************************************************************************************
		// Text translation
		// ******************************************************************************************
		// done with mo file
		
		// ******************************************************************************************
		// URL translation
		// ******************************************************************************************
		
		if (TC_ENVIRONMENT != 'dev' && isset($_SESSION["i18n_urls"])) {
			$I18N_URL = $_SESSION["i18n_urls"];
			$I18N_URL_INV = $_SESSION["i18n_urls_inv"];
			$I18N_TEXTS = $_SESSION["i18n_texts"];
		}
		else
		{
			// translations
			$I18N_TEXTS = array();
			foreach($ExistingLocales as $l => $locale)
			{		
				$filename = TC_ROOTDIR."/lang/".$locale."/LC_MESSAGES/default.mo";
				$I18N_TEXTS[$l] = $this->loadPOfile($filename);
			}
			// save in session
			$_SESSION["i18n_texts"] = $I18N_TEXTS;
			
			// URLs
			$I18N_URL = array();
			$I18N_URL_INV = array();
			foreach($ExistingLangs as $l)
			{		
				$I18N_URL[$l] = array();
				$L = strtoupper($l);
				
				// open translations file
				self::readTransfile(TC_ROOTDIR."/lang/urls_".$L.".txt", $I18N_URL[$l]);
				
				$I18N_URL_INV[$l] = array();
				foreach ($I18N_URL[$l] as $k => $v)
				{
					$I18N_URL_INV[$l][$v] = $k;
				}
			}
			// save in session
			$_SESSION["i18n_urls"] = $I18N_URL;
			$_SESSION["i18n_urls_inv"] = $I18N_URL_INV;
		}
	}

	public static function getInstance() 
	{
			if (!isset(self::$instance)) {
			
					$c = __CLASS__;
					
					self::$instance = new $c;
			}
			
			return self::$instance;
	}

	public function __clone(){trigger_error('Cloning not authorized.', E_USER_ERROR);}
	
	private static function readTransfile($filepath, &$dico)
	{
		if($fh = fopen($filepath, "r")){ 
			while (!feof($fh)){ 
				$line = fgets($fh, 9999);
				$parts = explode("=", $line);
				if (count($parts)==2)
				{
					$k = trim($parts[0]);
					$v = trim($parts[1]);
					$dico[$k] = $v;
				}
			} 
			fclose($fh); 
		}
	}
	
	public static function getCurLang()
	{
		$i18n = I18N::getInstance();
		return $i18n->curLang;
	}
	
	public static function setCurLang($lang)
	{
		$i18n = I18N::getInstance();
		$i18n->curLang = $lang;
	}
	
	public static function __($key, $lang = null)
	{
		$l = $lang;
		global $ExistingLangs;
		if (empty($lang) || !in_array($lang, $ExistingLangs)) {
			$i18n = I18N::getInstance();
			$l = $i18n->curLang;
		}
		
		if (isset($_SESSION["i18n_texts"][$l][$key])) return $_SESSION["i18n_texts"][$l][$key];
		else return $key;
	}
	
	public function url($language, $key)
	{
		return strtolower($_SESSION["i18n_urls"][$language][$key]);
	}
	
	public function url_inv($language, $key)
	{
		if (!isset($_SESSION["i18n_urls_inv"][$language][$key])) return null;
		return strtolower($_SESSION["i18n_urls_inv"][$language][$key]);
	}
	
	/**
	 * @return integer
	 */
	protected function readInteger($file, $littleEndian)
	{
			$format = $littleEndian ? 'Vint' : 'Nint';
			$result = unpack($format, fread($file, 4));

			return $result['int'];
	}

	/**
	 * @param integer $pNum
	 * @return integer
	 */
	protected function readIntegerList($file, $pNum, $littleEndian)
	{
			$format = $littleEndian ? 'V' . $pNum : 'N' . $pNum;
			return unpack($format, fread($file, 4 * $pNum));
	}

	private function loadPOfile($fullname)
	{
		$file = is_file($fullname) ? fopen($fullname, 'rb') : $fullname;
		$magic = fread($file, 4);
		
		$littleEndian = false;
		if($magic == "\x95\x04\x12\xde")
		{
				$littleEndian = false;
		} 
		else if($magic == "\xde\x12\x04\x95") 
		{
				$littleEndian = true;
		} 
		else 
		{
				fclose($file);
				trigger_error('File '.$fullname. ' is not a valid gettext file.', E_USER_ERROR);
		}
		
		$majorRevision = $this->readInteger($file, $littleEndian) >> 16;
		$numStrings = $this->readInteger($file, $littleEndian);

		$originalStringTableOffset = $this->readInteger($file, $littleEndian);
		$translationStringTableOffset = $this->readInteger($file, $littleEndian);
		
		fseek($file, $originalStringTableOffset);
		$originalStringTable = $this->readIntegerList($file, 2 * $numStrings, $littleEndian);
		
		fseek($file, $translationStringTableOffset);
		$translationStringTable = $this->readIntegerList($file, 2 * $numStrings, $littleEndian);
		
		$data = array();
		
		for($i = 0; $i < $numStrings; ++$i) 
		{
				$sizeKey = $i * 2 + 1;
				$offsetKey = $i * 2 + 2;
				$originalStringSize = $originalStringTable[$sizeKey];
				$originalStringOffset = $originalStringTable[$offsetKey];
				$translationStringSize = $translationStringTable[$sizeKey];
				$translationStringOffset = $translationStringTable[$offsetKey];
				$originalString = array('');
				
				if($originalStringSize > 0) 
				{
						fseek($file, $originalStringOffset);
						$originalString = explode("\0", fread($file, $originalStringSize));
				}

				if($translationStringSize > 0) 
				{
						fseek($file, $translationStringOffset);
						$translationString = explode("\0", fread($file, $translationStringSize));

						if(count($originalString) > 1 && count($translationString) > 1) 
						{
								$data[$originalString[0]] = $translationString;
								array_shift($originalString);

								foreach($originalString as $string) 
								{
										$data[$string] = '';
								}
						} 
						else 
						{
								$data[$originalString[0]] = $translationString[0];
						}
				}
		}
		
		unset($data['']);
		
		fclose($file);
		return $data;
	}
}

function __($key, $lang=null)
{
	$i18n = i18n::getinstance();
	if (empty($lang)) $lang = I18N::getCurLang();
	return $i18n->__($key, $lang);
}
