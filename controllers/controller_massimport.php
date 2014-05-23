<?php
namespace ThemeCheck;
require_once TC_INCDIR."/FileValidator.php";
if (USE_DB) include_once (TC_ROOTDIR.'/DB/History.php');

class Controller_massimport
{
	public $meta = array();
	public $samepage_i18n = array();
	public $importpath;
	
	public function __construct()
	{
		$this->importpath = TC_ROOTDIR.'/../items';
	}
	
	public function prepare()
	{
		$this->meta["title"] = __("Mass import");
		$this->meta["description"] = __("Mass import");
	}
	
	public function render()
	{
		?>
		<div style="text-align:center;">
			<button type="button" id="import-btn" class="btn">
				<?php echo "Import new zip files";?>
			</button>
			<button type="button" id="update-btn" class="btn">
				<?php echo "Update DB files";?>
			</button>
		</div>
		<?php
		$files = listdir( $this->importpath );
		$fileszip = array();
		
		// read themelist files
		$themelist = array();
		foreach ($files as $f)
		{
			if (strpos($f, "themelist.csv")!==false) {
				if (($handle = fopen($f, "r")) !== FALSE) {
						while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
								if (count($data) == 2)
								{
									$zipfilename = $data[0];
									$date = $data[1];
									$themelist[$zipfilename] = $date;
								}
						}
						fclose($handle);
				}
			}
		}
		
		$history = new History();
		$countNew = 0;
		$countTotal = 0;
		foreach ($files as $f)
		{
			$path_parts = pathinfo($f);
			if (isset($path_parts['extension']) && $path_parts['extension'] == "zip") 
			{
				$zipfilename = $path_parts['basename'];
				if (isset($themelist[$zipfilename]))
				{
					$timestamp = strtotime($themelist[$zipfilename]);
					if ($timestamp > 946681200) // filter buggy dates
					{
						$id = $history->getIdFromZipName($zipfilename);
						if ($id === false) // doesn't exist in DB
						{
							$fileszip[$f] = $timestamp;
							$countNew++;
						} 
						$countTotal++;
					}
				}
			}
		}
		
		asort($fileszip);
		
		echo "<br/>Not imported yet : $countNew / $countTotal<hr>";
		
		foreach ($fileszip as $file => $timestamp)
		{
			echo date("Y-m-d", $timestamp).' : '.$file.'<br>';
		}

	/*	$count = 0;
		foreach ($fileszip as $f)
		{
			if ($count > 5) break;
			if(USE_DB)
			{
				$hash_md5 = md5_file($f); 
				$hash_alpha = base_convert($hash_md5, 16, 36); // shorten hash to shorten urls (better looking, less bandwidth)
				while(strlen($hash_alpha) < 25) $hash_alpha = '0'.$hash_alpha;
				$history = new History();
				$themeInfo = $history->loadThemeFromHash($hash_alpha);
				if (!empty($themeInfo)) continue;
			}
			
			$path_parts = pathinfo($f);
			$path_item = $path_parts['dirname'];
			$filename = $path_parts['filename'].'.'.$path_parts['extension'];
		
			$themeInfo = FileValidator::prepareThemeInfo($path_item.'/'.$filename, $filename, 'application/zip', false);

			if (!empty($themeInfo))
			{
				$this->fileValidator = new FileValidator($themeInfo);
				$this->fileValidator->validate();	
				$this->fileValidator->serialize();
				
				$this->validationResults = $this->fileValidator->getValidationResults(I18N::getCurLang());
				$themeInfo = $this->fileValidator->themeInfo;
				echo '<p>'.htmlspecialchars($themeInfo->name).' : '.intval($themeInfo->score).'%</p>';
				$count++;
			}
		}*/
		?>
		<script>
		var zips = new Array();
		var zip_index = 0;
		var theme_id_sart = 145;
		var theme_id = theme_id_sart;
		<?php 
		$a = array_keys($fileszip);
		for ($i = 0; $i< count($a); $i++)
		{
			$unixStylePath = str_replace('\\','/',realpath($a[$i]));
			$index = $a[$i];
			$timestamp = $fileszip[$index];
			echo 'zips['.$i.'] = new Array("'.$unixStylePath.'","'.$timestamp.'");'."\n";
		}
		?>
		
		function importNext()
		{
			$.ajax({
				type: "POST",
				url: "<?php echo TC_HTTPDOMAIN.'/ajax.php?controller=massimport&action=importnext';?>",
				data : {path : zips[zip_index][0], timestamp : zips[zip_index][1]}
			}).done(function( obj ) {
				obj.index = zip_index++;
				console.log(obj);
				importNext();
			}).fail(function() {
				console.log("ajax error");
			})
		}
				
		$('#import-btn').click(importNext);
		
		function updateNext()
		{
			$.ajax({
				type: "POST",
				url: "<?php echo TC_HTTPDOMAIN.'/ajax.php?controller=massimport&action=updatenext';?>",
				data : {id : theme_id, timestamp : zips[zip_index][1]}
			}).done(function( obj ) {
				console.log(obj);
				if (obj.nextId == null) {
					console.log("update done");
					theme_id = theme_id_sart;
				} else {
					theme_id = obj.nextId;
					updateNext();
				}
			}).fail(function() {
				console.log("ajax error");
			})
		}
		$('#update-btn').click(updateNext);
		</script>
		<?php
	}
	
	public function ajax_importnext()
	{
		$time_start = microtime(true);
		$response["error"] = "none";
		$response["file"] = "none";
		
		if (file_exists($_POST["path"]))
		{
			$response["file"] = $_POST["path"];
			if (USE_DB)
			{
				$f = $_POST["path"];
				global $g_creationDate;
				$g_creationDate = intval($_POST["timestamp"]); // bad style, but so much easier

				$hash_md5 = md5_file($f); 
				$hash_alpha = base_convert($hash_md5, 16, 36); // shorten hash to shorten urls (better looking, less bandwidth)
				while(strlen($hash_alpha) < 25) $hash_alpha = '0'.$hash_alpha;
				$history = new History();
				$themeInfo = $history->loadThemeFromHash($hash_alpha);
				
				if (empty($themeInfo)) // don't do anything if already in DB
				{
					$path_parts = pathinfo($f);
					$path_item = $path_parts['dirname'];
					$filename = $path_parts['filename'].'.'.$path_parts['extension'];			
					$themeInfo = FileValidator::prepareThemeInfo($path_item.'/'.$filename, $filename, 'application/zip', false);

					if (!empty($themeInfo))
					{
						$this->fileValidator = new FileValidator($themeInfo);
						$this->fileValidator->validate();	
						if (UserMessage::getCount(ERRORLEVEL_FATAL) > 0)
						{
							$response["error"] = "fatal error:\n";
							foreach(UserMessage::getMessages(ERRORLEVEL_FATAL) as $m)
							{
								$response["error"] .= "\n".$m;
							}
						} else {
						
							if ($this->fileValidator->serialize())
							{
								if (UserMessage::getCount(ERRORLEVEL_FATAL) > 0)
								{
									// at least one error occured while serializing (no thumbnail...)
									$response["error"] = "fatal error, could not serialize validation results:\n";
									foreach(UserMessage::getMessages(ERRORLEVEL_FATAL) as $m)
									{
										$response["error"] .= "\n".$m;
									}
									foreach(UserMessage::getMessages(ERRORLEVEL_CRITICAL) as $m)
									{
										$response["error"] .= "\n".$m;
									}
								} else {
									$this->validationResults = $this->fileValidator->getValidationResults(I18N::getCurLang());
									$themeInfo = $this->fileValidator->themeInfo;
									$response["themeinfo"] = $themeInfo;
								}
							} else {
								// at least one error occured while serializing (no thumbnail...)
								if (UserMessage::getCount(ERRORLEVEL_CRITICAL) > 0)
								$response["error"] = "could not serialize validation results";
								foreach(UserMessage::getMessages(ERRORLEVEL_CRITICAL) as $m)
								{
									$response["error"] .= "\n".$m;
								}
							}
						}
					} else {
						if (UserMessage::getCount(ERRORLEVEL_FATAL) > 0)
						{
							// at least one error occured while serializing (no thumbnail...)
							$response["error"] = "could not execute validation:\n";
							foreach(UserMessage::getMessages(ERRORLEVEL_FATAL) as $m)
							{
								$response["error"] .= "\n".$m;
							}
							foreach(UserMessage::getMessages(ERRORLEVEL_CRITICAL) as $m)
							{
								$response["error"] .= "\n".$m;
							}
						} else {
							$response["error"] = "could not execute validation (unknown error).";
						}
					}
				}
			}			
		}
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		$response["duration"] = $time;
		//ob_clean();
		header('Content-Type: application/json');
		echo json_encode($response);
	}
	
	public function ajax_updatenext()
	{
		$time_start = microtime(true);
		$response["error"] = "none";
		$response["file"] = "none";
		$response["nextId"] = null;
		if (!empty($_POST["id"]))
		{
			$id = intval($_POST["id"]);
			if ($id < 1) $i = 1;
			
			$history = new History();
			$themeInfo = $history->getFewInfo($id);
			if (!empty($themeInfo))
			{	
				$unzippath = TC_VAULTDIR.'/unzip/'.$themeInfo["hash"]."/";
				if (file_exists($unzippath)){
					$nextId = $history->getNextId($themeInfo["id"]);
					$response["nextId"] = $nextId;
					
					$themeInfo = $history->loadThemeFromHash($themeInfo["hash"]);// need an objet and not an array
					$r = $themeInfo->initFromUnzippedArchive($unzippath, $themeInfo->zipfilename, $themeInfo->zipmimetype, $themeInfo->zipfilesize); // merchant...
					$this->fileValidator = new FileValidator($themeInfo);
					$this->fileValidator->validate();	
					if (UserMessage::getCount(ERRORLEVEL_FATAL) > 0)
					{
						$response["error"] = "fatal error:\n";
						foreach(UserMessage::getMessages(ERRORLEVEL_FATAL) as $m)
						{
							$response["error"] .= "\n".$m;
						}
					} else {
						if ($this->fileValidator->serialize(true))
						{
							if (UserMessage::getCount(ERRORLEVEL_FATAL) > 0)
							{
								// at least one error occured while serializing (no thumbnail...)
								$response["error"] = "fatal error, could not serialize validation results:\n";
								foreach(UserMessage::getMessages(ERRORLEVEL_FATAL) as $m)
								{
									$response["error"] .= "\n".$m;
								}
								foreach(UserMessage::getMessages(ERRORLEVEL_CRITICAL) as $m)
								{
									$response["error"] .= "\n".$m;
								}
							} else {
								$this->validationResults = $this->fileValidator->getValidationResults(I18N::getCurLang());
								$themeInfo = $this->fileValidator->themeInfo;
								$response["name"] = $themeInfo->name;
							}
						} else {
							// at least one error occured while serializing (no thumbnail...)
							if (UserMessage::getCount(ERRORLEVEL_CRITICAL) > 0)
							$response["error"] = "could not serialize validation results";
							foreach(UserMessage::getMessages(ERRORLEVEL_CRITICAL) as $m)
							{
								$response["error"] .= "\n".$m;
							}
						}
					}
				} else {
					$response["error"] .= "No zip file ".$unzippath;
				}
			} else {
				if (UserMessage::getCount(ERRORLEVEL_FATAL) > 0)
				{
					// at least one error occured while serializing (no thumbnail...)
					$response["error"] = "could not execute validation:\n";
					foreach(UserMessage::getMessages(ERRORLEVEL_FATAL) as $m)
					{
						$response["error"] .= "\n".$m;
					}
					foreach(UserMessage::getMessages(ERRORLEVEL_CRITICAL) as $m)
					{
						$response["error"] .= "\n".$m;
					}
				} else {
					$response["error"] = "could not execute validation (unknown error).";
				}
			}
		}
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		$response["duration"] = $time;
		//ob_clean();
		header('Content-Type: application/json');
		echo json_encode($response);
	}
}





