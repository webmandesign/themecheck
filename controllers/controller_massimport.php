<?php
namespace ThemeCheck;
require_once TC_INCDIR."/FileValidator.php";
include_once (TC_ROOTDIR.'/DB/History.php');

class Controller_massimport
{
	public $meta = array();
	public $samepage_i18n = array();
	
	public function __construct()
	{
	}
	
	public function prepare()
	{
		/*Route::getInstance()->updateSitemap(1);
		Route::getInstance()->updateSitemap(2);
		Route::getInstance()->updateSitemap(4);*/
		$this->meta["title"] = __("Mass import");
		$this->meta["description"] = __("Mass import");
	}
	
	public function render()
	{
		?><script type="text/javascript"> var page="massimport" </script>
		<section id="content">
			<div class="container" style="background-color:#212121;text-align:center;padding:150px 0 100px 0;color:#FFF">
				<div class="container_massimport">	
					<div style="text-align:center;">
						<button type="button" id="update-btn" class="btn">
							<?php echo "Update DB files";?>
						</button>
					</div>
					<div id="container_results" style="width:60%;margin:auto;text-color:#888;font-size:10px;text-align:left;">
					</div>
				</div>
			</div>
		<script>
		var theme_id_start = <?php $history = new History(); echo $history->getMaxId(); ?>;
		var theme_id = theme_id_start;
		var t_count = 0;
		var percent = 100.0 * (theme_id_start - theme_id) / theme_id_start;
		function updateNext()
		{
			t_count ++;
			console.log("udpating " + theme_id + "...");
			$( "#container_results" ).append( "<span>#"+t_count+" ("+percent+"%) : Updating " + theme_id + "...</span>" );
			$.ajax({
				type: "POST",
				url: "<?php echo TC_HTTPDOMAIN.'/ajax.php?controller=massimport&action=updatenext';?>",
				data : {id : theme_id}
			}).done(function( obj ) {
				console.log(obj);
				var themetype = 'wordpress';
				if (obj.themetype ==2) themetype = 'joomla';
				if (obj.themetype ==4) themetype = 'wordpress child';
				$( "#container_results" ).append( "&nbsp;<span>&quot;"+obj.name+"&quot; ("+themetype+") : "+obj.size+", "+obj.duration+"</span><br/>" );
				if (obj.nextId == null) {
					console.log("update done");
					$( "#container_results" ).append("update done");
					theme_id = theme_id_start;
				} else {
					theme_id = obj.nextId;
					setTimeout(function(){updateNext();}, 5000); // no overload
				}
			}).fail(function() {
				console.log("ajax error");
			})
		}
		$('#update-btn').click(updateNext);
                
		</script>
		<?php
	}
	
	public function ajax_updatenext()
	{ 
		$time_start = microtime(true);
		$response["error"] = "none";
		$response["nextId"] = null;
		if (!empty($_POST["id"]))
		{
			$id = intval($_POST["id"]);
			if ($id < 1) $i = 1;
			
			$history = new History();
			$themeInfo = $history->getFewInfo($id);
			
			if (!empty($themeInfo) && !empty($themeInfo["hash"]))
			{	
				$hash = $themeInfo["hash"];
				$src_path = FileValidator::hashToPathUpload($hash);
				$themeInfo = FileValidator::prepareThemeInfo($src_path, $themeInfo["zipfilename"], 'application/zip', false);

				$nextId = $history->getPrevId($id);
				$response["nextId"] = $nextId;
				
				//$themeInfo = $history->loadThemeFromHash($themeInfo["hash"]);// need an objet and not an array
				//$r = $themeInfo->initFromUnzippedArchive($unzippath, $themeInfo->zipfilename, $themeInfo->zipmimetype, $themeInfo->zipfilesize); // merchant...
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
							$response["name"] = htmlspecialchars($themeInfo->name);
							$response["size"] = number_format($themeInfo->zipfilesize/1000000, 2).' Mb';
							$response["themetype"] = $themeInfo->themetype;
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
				if (function_exists('stats'))  stats($themeInfo);
				$this->fileValidator->cleanUnzippedFiles();
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
		$response["duration"] = number_format($time, 1).' s';
		//ob_clean();
		header('Content-Type: application/json');
		echo json_encode($response);
	}
}





