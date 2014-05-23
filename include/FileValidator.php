<?php
namespace ThemeCheck;
require_once 'Bootstrap.php';
require_once TC_INCDIR.'/ListDirectoryFiles.php';
require_once TC_INCDIR.'/Check.php';
require_once TC_INCDIR.'/tc_helpers.php';
require_once TC_INCDIR.'/Helpers.php';
require_once TC_INCDIR.'/ThemeInfo.php';
require_once TC_INCDIR.'/ValidationResults.php';
require_once TC_INCDIR.'/UserMessage.php';

function objectToArray($d) {
	if (is_object($d)) {
		// Gets the properties of the given object
		// with get_object_vars function
		$d = get_object_vars($d);
	}

	if (is_array($d)) {
		/*
		* Return array converted to object
		* Using __FUNCTION__ (Magic constant)
		* for recursive call
		*/
		return array_map(__FUNCTION__, $d);
	}
	else {
		// Return array
		return $d;
	}
}
	
class FileValidator
{
	public $themeInfo; // type ThemeInfo
	public $checklist = array();
	public $phpfiles = array();
	public $phpfiles_filtered = array();
	public $cssfiles = array();
	public $otherfiles = array();
					
	public $validationResults = array();// associative array of $lang => ValidationResult
	private $history = null; 

	private static $checklistCommon = array (
            "Badthings",
            "Directories",
            "File",
            "Iframes",
            "Malware",
            "NonPrintable",
            "PHPShort",
            "Worms",
						"MandatoryFiles",
						"OptionalFiles",
						"LineEndings",
						"AdminMenu",
						"Artisteer",
						"Basic",
						"CommentPagination",
						"CommentReply",
						"Constants",
						"ContentWidth",
						"Custom",
						"Deprecated",
						"MoreDeprecated", 
						"EditorStyle",
						"Gravatar",
						"I18NCheck",
						"Includes",
						"NavMenu",
						"PostFormat",
						"PostNav",
						"PostThumb",
						"SearchForm",
						"Style",
					//	"Suggested", //no : not sure this test makes sense
						"Tags",
					//	"TextDomain", //no : not sure this test makes sense 
					  "TimeDate",
						"Screenshot",
						"JManifest"
	);

	public function __construct($themeInfo)
	{
		$this->themeInfo = $themeInfo;
		if (USE_DB) $this->history = new History();
	}
	
	/** 
	*		Gets the upload path of an item from its hash.
	**/
	static public function hashToPathUpload($hash)
	{
		$path = TC_VAULTDIR.'/upload';
		if (!file_exists($path)) trigger_error('Directory TC_VAULTDIR/upload does not exist', E_USER_ERROR);
		
		$fullname = $path.'/'.$hash.'.zip';
		return $fullname;
	}
		
	/** 
	*		Save check results in a JSON file.
	**/
	public function serialize($update = false)
	{
		if($this->themeInfo == null) return false;	
		
		// save thumbnail
		$thumbfile = '';
		$all_images = array();
		
		foreach ($this->otherfiles as $fullpath=>$content)
		{
			$path_parts = pathinfo($fullpath);
			$basename = $path_parts['basename'];
			
			if (($this->themeInfo->themetype == TT_WORDPRESS || $this->themeInfo->themetype == TT_WORDPRESS_CHILD ) && ($basename == "screenshot.png" || $basename == "screenshot.jpg")) {$thumbfile = $fullpath; $all_images[] = $fullpath;}
			if ($this->themeInfo->themetype == TT_JOOMLA)
			{
				if ($basename == "template_thumbnail.png" || $basename == "template_thumbnail.jpg") {$thumbfile = $fullpath; $all_images[] = $fullpath;}
				else if (preg_match("/^template_preview[_0-9a-zA-z\.\-]*\.(gif|jpg|png|jpeg)$/", $basename)) $all_images[] = $fullpath;
			}
		}
			
		if (empty( $thumbfile ))
		{
			if ($this->themeInfo->themetype == TT_WORDPRESS ) { UserMessage::enqueue(__("Mandatory thumbnail file screenshot.png is missing"), ERRORLEVEL_CRITICAL);return false;}
			if ($this->themeInfo->themetype == TT_JOOMLA) {UserMessage::enqueue(__("Mandatory thumbnail file template_thumbnail.png is missing"), ERRORLEVEL_CRITICAL);return false;}
			if ($this->themeInfo->themetype == TT_WORDPRESS_CHILD)
			{
				// thumbnail isn't mandatory in child theme, get the generic one
				$thumbfile = TC_ROOTDIR.'/img/default_wordpress.png';
			}
		}

		// save thumbnail for front-end display (even if not serializable since we want to display a thumbnail on the results page)
		list($width_src, $height_src) = getimagesize($thumbfile);
		$width = 206;
		$height = intval(($height_src * $width) / $width_src);
		$image_p = imagecreatetruecolor($width, $height);
		
		if (pathinfo($thumbfile, PATHINFO_EXTENSION) == 'png') $image_src = imagecreatefrompng($thumbfile);
		else $image_src = imagecreatefromjpeg($thumbfile);
		imagecopyresampled($image_p, $image_src, 0, 0, 0, 0, $width, $height, $width_src, $height_src); // resample and copy image. Since the image is shown on page results, resample even if same size, to avoid potential hacks.
		
		// 1 : save for front-end display (even if not serializable since we want to display a thumbnail on the results page)
		$savedirectory_img = ThemeInfo::getPublicDirectory($this->themeInfo->hash);
		if (!file_exists($savedirectory_img)) mkdir($savedirectory_img, 0774, true);
		imagepng($image_p, $savedirectory_img.'/thumbnail.png');
		
		// if theme is not serializable (duplicate theme name from different users, etc.)
		if (!$this->themeInfo->serializable) return false;
		
		// 2 : save all images in the vault
		$this->themeInfo->images = array();
		foreach($all_images as $img)
		{
			$imginfo = getimagesize($img);
			$path_parts = pathinfo($img);
			if ($imginfo !== false && $imginfo[0] >= 64 && $imginfo[1] >= 64) // check we havee an actual image and it is big enough
			{
				$img_dst = realpath($savedirectory_img).'/'.$path_parts['basename'];
				copy($img, $img_dst);
				$this->themeInfo->images[] = $img_dst;
			}
		}

		// save meta data
		if (USE_DB) {
			$this->history->saveTheme($this->themeInfo, $update);			
		}

		// save validation results
		foreach($this->validationResults as $lang=>$_validationResults)
		{
			$_validationResults->serialize($this->themeInfo->hash);
		}
		
		// we don't serialize themeforest report. They'll be regenerated at unserialization.
		
		return true;
	}
	
	/** 
	*		Restore check results from a JSON file.
	**/
	static public function unserialize($hash)
	{
		if (!USE_DB) return null;
		
		$directory = ThemeInfo::getReportDirectory($hash);
		if (!file_exists($directory )) return null;
		
		$history = new History();
		$themeInfo = $history->loadThemeFromHash($hash);
		if (empty($themeInfo)) return null;

		$fileValidator = new FileValidator($themeInfo);
		
		global $ExistingLangs;
		foreach($ExistingLangs as $l)
		{
			$_validationResults = ValidationResults::unserialize($hash, $l);
			if (empty($_validationResults)) continue;
			$fileValidator->validationResults[$l] = $_validationResults;
		}

		if (!empty($themeInfo->parentId))
		{
			$fewInfo = $history->getFewInfo($themeInfo->parentId);
			if (!empty($fewInfo["id"]))
			$themeInfo->parentNameSanitized = $fewInfo["namesanitized"];
			$themeInfo->parentThemeType = $fewInfo["themetype"];
		}
			
		if ($themeInfo->isThemeForest) $fileValidator->generateThemeForestReport();
			
			
		return $fileValidator;
	}
	
	/** 
	*		Upload an archive and return a ThemeInfo object
	**/
	static public function upload()
	{
		if (count($_FILES)==0 || !isset($_FILES["file"]) || !isset($_FILES["file"]["name"]) ) 
		{
			UserMessage::enqueue(__("No files to upload"), ERRORLEVEL_FATAL);
			return 0;
		}

		if ($_FILES["file"]["size"] == 0)
		{
			$max_size = Helpers::returnBytes(ini_get('upload_max_filesize'));
			if ($max_size > Helpers::returnBytes(ini_get('post_max_size'))) $max_size = Helpers::returnBytes(ini_get('post_max_size'));
			$max_size_MB = $max_size / (1024*1024);
			UserMessage::enqueue(sprintf(__("Could not upload file. File is empty or bigger than maximum upload file size (%s MB)."), $max_size_MB), ERRORLEVEL_FATAL);
			return 0;
		}
		
		$accepted_exts = array("zip");
		$accepted_types = array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed', 'application/octet-stream');
		$filetype = self::fileMimeType($_FILES["file"]["tmp_name"], $_FILES["file"]["name"], false);
		$extension = strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION));

		// check file type
		$filetype_ok = false;
		if(in_array($filetype, $accepted_types))
		{
			$filetype_ok = true;
		}
		
		if (!$filetype_ok)
		{
			if (empty($filetype)) $filetype = '_';
			UserMessage::enqueue(sprintf(__("Bad file type. Mime type %s is not a recognized format for web themes. Uploaded file must be a zip archive."), $filetype), ERRORLEVEL_FATAL);
			return 0;
		}
		
		// check file extension
		if (!in_array($extension, $accepted_exts))
		{
			UserMessage::enqueue(sprintf(__("Bad file extension. File extension %s not recognized. File extension must be \".zip\"."), $_FILES["file"]["name"]), ERRORLEVEL_FATAL);
			return 0;
		}
		
		if ($_FILES["file"]["error"] == UPLOAD_ERR_INI_SIZE || $_FILES["file"]["error"] == UPLOAD_ERR_FORM_SIZE)
		{
			UserMessage::enqueue(sprintf(__("The uploaded file size exceeds %s."), ini_get('upload_max_filesize')), ERRORLEVEL_FATAL);
			return 0;
		}
		
		if ($_FILES["file"]["error"] == UPLOAD_ERR_PARTIAL)
		{
			UserMessage::enqueue(__("The uploaded file was only partially uploaded."), ERRORLEVEL_FATAL);
			return 0;
		}
		
		if ($_FILES["file"]["error"] == UPLOAD_ERR_NO_FILE)
		{
			UserMessage::enqueue(__("No file was uploaded."), ERRORLEVEL_FATAL);
			return 0;
		}
		
		$src_path = $_FILES["file"]["tmp_name"];
		$src_name = $_FILES["file"]["name"];
		$src_type = $_FILES["file"]["type"];
		
		$themeInfo = self::prepareThemeInfo($src_path, $src_name, $src_type, true);
		return $themeInfo;
	}
	
	/**
	* Check a file mime-type
	* to safely check a mime-type, it's recommended to activate fileinfo extension
	* http://www.php.net/manual/en/book.fileinfo.php
	*
	**/
	
	static function fileMimeType($file, $filename=false, $encoding=true) {
		$mime=false;

		// With fileinfo extension, PHP >= 5.3
		if(extension_loaded('fileinfo') && class_exists('\\finfo'))
		{
			$finfo = new \finfo(FILEINFO_MIME);
			$mime = $finfo->file($file);
		}
		// With fileinfo extension, PHP >= 4
		elseif (function_exists('finfo_open'))
		{
			$finfo = finfo_open(FILEINFO_MIME);
			$mime = finfo_file($finfo, $file);
			finfo_close($finfo);
		}
		
		// without fileinfo extension: deprecated/not safe, user input based
		else if(function_exists('mime_content_type'))
		{
			$mime = mime_content_type($pFilePath); 
		}
		
		else if (substr(PHP_OS, 0, 3) == 'WIN') {
			$mime_types = array(

				'txt' => 'text/plain',
				'htm' => 'text/html',
				'html' => 'text/html',
				'php' => 'text/html',
				'css' => 'text/css',
				'js' => 'application/javascript',
				'json' => 'application/json',
				'xml' => 'application/xml',
				'swf' => 'application/x-shockwave-flash',
				'flv' => 'video/x-flv',

				// images
				'png' => 'image/png',
				'jpe' => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'jpg' => 'image/jpeg',
				'gif' => 'image/gif',
				'bmp' => 'image/bmp',
				'ico' => 'image/vnd.microsoft.icon',
				'tiff' => 'image/tiff',
				'tif' => 'image/tiff',
				'svg' => 'image/svg+xml',
				'svgz' => 'image/svg+xml',

				// archives
				'zip' => 'application/zip',
				'rar' => 'application/x-rar-compressed',
				'exe' => 'application/x-msdownload',
				'msi' => 'application/x-msdownload',
				'cab' => 'application/vnd.ms-cab-compressed',

				// audio/video
				'mp3' => 'audio/mpeg',
				'qt' => 'video/quicktime',
				'mov' => 'video/quicktime',

				// adobe
				'pdf' => 'application/pdf',
				'psd' => 'image/vnd.adobe.photoshop',
				'ai' => 'application/postscript',
				'eps' => 'application/postscript',
				'ps' => 'application/postscript',

				// ms office
				'doc' => 'application/msword',
				'rtf' => 'application/rtf',
				'xls' => 'application/vnd.ms-excel',
				'ppt' => 'application/vnd.ms-powerpoint',

				// open office
				'odt' => 'application/vnd.oasis.opendocument.text',
				'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
			);

			$filepart = explode('.',$filename);
			$ext = strtolower(array_pop($filepart));
			if (array_key_exists($ext, $mime_types)) {
				$mime = $mime_types[$ext]."; ";
			}
			
			else {
				$mime = 'application/octet-stream; ';
			}
		}
		else {
			$file = escapeshellarg($file);
			$cmd = "file -iL $file";

			exec($cmd, $output, $r);

			if ($r == 0) {
				$mime = substr($output[0], strpos($output[0], ': ')+2);
			}
		}

		if (!$mime) {
			return false;
		}

		if ($encoding) {
			return $mime;
		}

		return substr($mime, 0, strpos($mime, '; '));
	}
	
	/** 
	*		Generate a hash, move or copy the archive and return a ThemeInfo object
	**/
	static public function prepareThemeInfo($src_path, $src_name, $src_type, $isUpload=false)
	{
		$src_size = filesize($src_path);

		if (!($src_size > 100 || strpos ($src_path, '/unittests/') !== false ))
		{

			$userMessage = UserMessage::getInstance();
			$userMessage->enqueueMessage(__('Files under 100 bytes are not accepted. Operation canceled.'), ERRORLEVEL_CRITICAL);
			return null;
		}
		
		$hash_md5 = md5_file($src_path); 
		$sha1_file = sha1_file($src_path); 
		$hash_alpha = base_convert($hash_md5, 16, 36); // shorten hash to shorten urls (better looking, less bandwidth)
		
		while(strlen($hash_alpha) < 25) $hash_alpha = '0'.$hash_alpha;

		$zipfilepath = self::hashToPathUpload($hash_alpha);
		if ($isUpload)
			move_uploaded_file($src_path, $zipfilepath); // move file to final place (overwrites if already existing)
		else
			copy($src_path, $zipfilepath); // copy the file (overwrites if already existing)
	
		try {
			$zip = new \ZipArchive();
			$path = TC_ROOTDIR.'/../themecheck_vault/unzip';
			if (!file_exists($path)) trigger_error('Directory TC_ROOTDIR/../themecheck_vault/unzip does not exist', E_USER_ERROR);
			$unzippath = $path.'/'.$hash_alpha."/";
			
			$res = $zip->open($zipfilepath);
			if ($res === TRUE) {
			
				if (file_exists($unzippath)) ListDirectoryFiles::recursiveRemoveDir($unzippath); // needed to avoid keeping old files that don't exist anymore in the new archive
				$zip->extractTo($unzippath);

				$zip->close();
			} else {
				UserMessage::enqueue(__("File could not be unzipped."), ERRORLEVEL_FATAL);
			}
		} catch (Exception $e) {
			UserMessage::enqueue(__("Archive extraction failed. The following exception occured : ").$e->getMessage(), ERRORLEVEL_FATAL);
		}
			
		// create a theme info
		$themeInfo = new ThemeInfo($hash_alpha);
		$themeInfo->hash_md5 = $hash_md5;
		$themeInfo->hash_sha1 = $sha1_file;
		$r = $themeInfo->initFromUnzippedArchive($unzippath, $src_name, $src_type, $src_size);
		
		if (!empty($themeInfo->parentName))
		{
		
			$history = new History();
			$fewInfo = $history->getFewInfoFromName($themeInfo->parentName);
			if (!empty($fewInfo["id"]))
			$themeInfo->parentId = intval($fewInfo["id"]);
			$themeInfo->parentNameSanitized = $fewInfo["namesanitized"];
			$themeInfo->parentThemeType = $fewInfo["themetype"];
		}
		if (!$r) return null;
		return $themeInfo;	
	}
	
	/** 
	*		Dispatch files from an uncompressed archive in $this->phpfiles, $this->cssfiles or $this->otherfiles
	*		Used to prepare the call to validate()
	**/
	/*private function extractFiles($unzippedPath)
	{
		$files = listdir( $unzippedPath );


		if ( $files ) {
			foreach( $files as $key => $filename ) {
				if ( substr( $filename, -4 ) == '.php' ) {
					$this->phpfiles[$filename] = php_strip_whitespace( $filename );
				}
				else if ( substr( $filename, -4 ) == '.css' ) {
					$this->cssfiles[$filename] = file_get_contents( $filename );
				}
				else {
					// get all other files : txt, xml, jpg, png
					$sizelimit = 50000;
					$this->otherfiles[$filename] = ( ! is_dir($filename) ) ? file_get_contents( $filename, false, NULL, -1,  $sizelimit) : '';
				}
			}
		}
	}*/
	
	public static function getCheckList()
	{
		$checklist = array();
		// prepare checks
		foreach (self::$checklistCommon as $check)
		{
			require_once(TC_INCDIR."/Checks/$check.php");
			$c = __NAMESPACE__.'\\'.$check;
			$checklist[] = new $c();
		}
		return $checklist;
	}
			
	/** 
	*		Execute all checks 
	**/
	public function validate($checkId = 'ALL')
	{
		$start_time_checker = microtime(true);
		
		// prepare checks
		foreach (self::$checklistCommon as $check)
		{
			require_once(TC_INCDIR."/Checks/$check.php");
			$c = __NAMESPACE__.'\\'.$check;
			$newCheck = new $c();
			$this->checklist[] = $newCheck;
		}
		
		//prepare files
		if (!isset($this->themeInfo)) {trigger_error('themeInfo not set in FileValidator::validate', E_USER_ERROR); die;}
		if (empty($this->themeInfo->hash)) {trigger_error('themeInfo->hash empty in FileValidator::validate', E_USER_ERROR);die;}
		
		$unzippath = TC_ROOTDIR.'/../themecheck_vault/unzip/'.$this->themeInfo->hash;

		// update themeInfo data that is discovefred in initFromUnzippedArchivee
	  $r = $this->themeInfo->initFromUnzippedArchive($unzippath, $this->themeInfo->zipfilename, $this->themeInfo->zipmimetype, $this->themeInfo->zipfilesize);

		$files = listdir( $unzippath );
		if ( $files ) {
			foreach( $files as $key => $filename ) {
				if ( substr( $filename, -4 ) == '.php' ) {
					$this->phpfiles[$filename] = file_get_contents( $filename );
					$this->phpfiles_filtered[$filename] = Helpers::filterPhp( $this->phpfiles[$filename] );
				}
				else if ( substr( $filename, -4 ) == '.css' ) {
					$this->cssfiles[$filename] = file_get_contents( $filename );
				}
				else {
					// get all other files : txt, xml, jpg, png
					$sizelimit = 10000;
					$this->otherfiles[$filename] = ( ! is_dir($filename) ) ? file_get_contents( $filename, false, NULL, -1,  $sizelimit) : '';
				}
			}
		}
		
		$this->themeInfo->validationDate = time();
		$check_critical = array();
		$check_warnings = array();
		$check_successes = array();
		$check_info = array();	
		$check_undefined = array();
		$check_count = 0;
		$score = 0;
		
		$isThemeforest = true;
		
		// run validation. Checks are done in all existing languages and return multilingual arrays in place of strings.
		foreach ($this->checklist as $check)
		{
			$check->setCurrentThemetype($this->themeInfo->themetype);
			$check->setCurrentCmsVersion($this->themeInfo->cmsVersion);
			$check->doCheck($this->phpfiles, $this->phpfiles_filtered, $this->cssfiles, $this->otherfiles);
			foreach($check->checks as $checkpart)
			{
				if ($checkId === 'ALL' || $checkpart->id === $checkId) 
				{
					if ($checkpart->errorLevel !== ERRORLEVEL_UNDEFINED) // avoid checkparts that were not passed in $check->doCheck
					{
						$checkpart->title = $check->title; // a bit dirty
						
						if ($checkpart->errorLevel == ERRORLEVEL_CRITICAL) $check_critical[] = $checkpart;
						else if ($checkpart->errorLevel == ERRORLEVEL_WARNING) $check_warnings[] = $checkpart;
						else if ($checkpart->errorLevel == ERRORLEVEL_SUCCESS) $check_successes[] = $checkpart;
						else if ($checkpart->errorLevel == ERRORLEVEL_INFO) $check_info[] = $checkpart;
						else $check_undefined[] = $checkpart;
						$check_count++;
					}
				}
			}
		}
		
		// score calculation
		$this->themeInfo->check_count = $check_count;
		$this->themeInfo->check_countOK = count($check_successes);
		$this->themeInfo->criticalCount = count($check_critical);
		$this->themeInfo->warningsCount = count($check_warnings);
		$this->themeInfo->infoCount = count($check_info);
		if ($check_count > 0) {
			$this->themeInfo->score = 100 - $this->themeInfo->warningsCount - 20 * $this->themeInfo->criticalCount;
			if ($this->themeInfo->score < 0) $this->themeInfo->score = 0;
		}
		else $this->themeInfo->score = 0.0;
				
		// generate validationResults, one for each existing language. Checks are monolingual : no more multilingual arrays.
		global $ExistingLangs;
		foreach($ExistingLangs as $l)
		{
			$this->validationResults[$l] = new ValidationResults($l);
			foreach($check_critical as $checkpart_multi) $this->validationResults[$l]->check_critical[] = $checkpart_multi->getMonolingual($l);
			foreach($check_warnings as $checkpart_multi) $this->validationResults[$l]->check_warnings[] = $checkpart_multi->getMonolingual($l);
			foreach($check_successes as $checkpart_multi) $this->validationResults[$l]->check_successes[] = $checkpart_multi->getMonolingual($l);
			foreach($check_info as $checkpart_multi) $this->validationResults[$l]->check_info[] = $checkpart_multi->getMonolingual($l);
			foreach($check_undefined as $checkpart_multi) $this->validationResults[$l]->check_undefined[] = $checkpart_multi->getMonolingual($l);
		}
		
		// generate themeforest themes auxiliary report
		if ($isThemeforest) $this->generateThemeForestReport();
		
		$this->duration = microtime(true) - $start_time_checker;
	}
	
	public function getValidationResults($lang)
	{
		if (isset($this->validationResults[$lang])) return $this->validationResults[$lang];
		return $this->validationResults['en'];
	}
	public function getValidationResultsThemeForest($lang)
	{
		if (isset($this->validationResults_themeforest[$lang])) return $this->validationResults_themeforest[$lang];
		return $this->validationResults_themeforest['en'];
	}
	
	public function clean()
	{
		if (!isset($this->themeInfo)) return;
		if (!isset($this->themeInfo->hash)) return;
		
		// don't clean if in DB because it means someone has posted the archive previously, and maybe the current user tries to erase it.
		if (USE_DB)
		{
			$history = new History();
			$id = $history->getIdFromHash($this->themeInfo->hash);
			if (!empty($id)) return;
		}
		
		$zipfilepath = self::hashToPathUpload($this->themeInfo->hash);
		$unzippath = TC_ROOTDIR.'/../themecheck_vault/unzip/'.$this->themeInfo->hash;
		$unzippath_parent = TC_ROOTDIR.'/../themecheck_vault/unzip/'.$this->themeInfo->hash.'_tc_parentzip';
		if (file_exists($zipfilepath)) unlink($zipfilepath);
		if (file_exists($unzippath)) ListDirectoryFiles::recursiveRemoveDir($unzippath);
		if (file_exists($unzippath_parent)) ListDirectoryFiles::recursiveRemoveDir($unzippath_parent);
	}
	
	/**
	* Generate an auxiliary report for themeforest themes. Generated from standard report. Rules taken from themeforest themecheck wordpress plugin.
	*/
	public function generateThemeForestReport()
	{
		global $ExistingLangs;
		$avoidchecks = array( 'INCLUDES',
													'I18N_E',
													'I18N_ALL',
													'I18N_X',
													'I18N_EX',
													'I18N_ESC_ATTR___ALL',
													'I18N_ESC_ATTR_E',
													'I18N_ESC_ATTR_X',
													'I18N_ESC_HTML___ALL',
													'I18N_ESC_HTML_E',
													'I18N_ESC_HTML_X',
													'I18N_UNDERSCORE',
													'I18N_GETTEXT',
													'ADMIN_ADMINPAGES',
													'BADTHINGS_BASE64DEC',
													'BADTHINGS_BASE64ENC_WP',
													'BADTHINGS_BASE64ENC_JO',
													'BADTHINGS_VARIABLEFUNC',
													'MALWARE1',
													'EDITORSTYLE',
													'IFRAMES',
													'CUSTOM_HEADER',
													'CUSTOM_BACKGROUND');
		
		foreach($ExistingLangs as $l)
		{
			$this->validationResults_themeforest[$l] = new ValidationResults($l);
			foreach($this->validationResults[$l]->check_critical as $check) if (!in_array($check->id, $avoidchecks)) $this->validationResults_themeforest[$l]->check_critical[] = $check;
			foreach($this->validationResults[$l]->check_warnings as $check) if (!in_array($check->id, $avoidchecks)) $this->validationResults_themeforest[$l]->check_warnings[] = $check;
			foreach($this->validationResults[$l]->check_successes as $check) if (!in_array($check->id, $avoidchecks)) $this->validationResults_themeforest[$l]->check_successes[] = $check;
			foreach($this->validationResults[$l]->check_info as $check) {
				if ($check->id == 'MANDATORYFILES_COMMENTSPHP') $this->validationResults_themeforest[$l]->check_critical[] = $check; // escalate MANDATORYFILES_COMMENTSPHP to critical if themeforest
				else $this->validationResults_themeforest[$l]->check_info[] = $check;
			}
			foreach($this->validationResults[$l]->check_undefined as $check) if ($check->id != 'MALWARE1') $this->validationResults_themeforest[$l]->check_undefined[] = $check;
		}
		
		$this->themeInfo_themeforest = clone $this->themeInfo;
		// score calculation
		$this->themeInfo_themeforest->check_countOK = count($this->validationResults_themeforest['en']->check_successes);
		$this->themeInfo_themeforest->criticalCount = count($this->validationResults_themeforest['en']->check_critical);
		$this->themeInfo_themeforest->warningsCount = count($this->validationResults_themeforest['en']->check_warnings);
		$this->themeInfo_themeforest->infoCount = count($this->validationResults_themeforest['en']->check_info);
		$this->themeInfo_themeforest->check_count = $this->themeInfo_themeforest->check_countOK + 
																								$this->themeInfo_themeforest->criticalCount + 
																								$this->themeInfo_themeforest->warningsCount + 
																								$this->themeInfo_themeforest->infoCount;
		if ($this->themeInfo_themeforest->check_count > 0) {
			$this->themeInfo_themeforest->score = 100 - $this->themeInfo_themeforest->warningsCount - 20 * $this->themeInfo_themeforest->criticalCount;
			if ($this->themeInfo_themeforest->score < 0) $this->themeInfo_themeforest->score = 0;
		}
		else $this->themeInfo_themeforest->score = 0.0;
	}
}
?>
