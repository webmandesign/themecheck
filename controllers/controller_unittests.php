<?php
namespace ThemeCheck;
require_once TC_INCDIR."/FileValidator.php";
require_once TC_INCDIR."/shield.php";

class Controller_unittests
{
	public $meta = array();
	public $samepage_i18n = array();
	private $fileValidator;
	private $validationResults;
	private $checklist = array();
	
	public function __construct()
	{
		$this->fileValidator = null;
		$this->validationResults = null;
		$this->themeInfo = null;
	}
	
	public function prepare()
	{
		$this->meta["title"] = __("Unit tests");
		$this->meta["description"] = __("Unit tests");
		$this->checklist = FileValidator::getCheckList();
		
	/*	$path_item = TC_ROOTDIR.'/include/unittests/';
		$filename = 'ut_perfect_wordpress.zip';
		if (isset($_GET["filename"])) // unit tests
		{	
			// check unit test existence
			$filename = urldecode($_GET["filename"]);
			
			if (!(substr($filename, -4) == ".zip" && file_exists($path_item.$filename)))
			{
				$filename = '';
			}
		} 
		if (empty($filename)) {trigger_error('empty filename in unit test prepare()', E_USER_ERROR);die;}
		$themeInfo = FileValidator::prepareThemeInfo($path_item.$filename, $filename, 'application/zip', false);

		$this->fileValidator = new FileValidator($themeInfo);
		$this->fileValidator->validate();	

		$this->validationResults = $this->fileValidator->getValidationResults(I18N::getCurLang());
		
		$this->meta["title"] = __("Results unit tests");
		$this->meta["description"] = __("Validation results unit tests");
		
		global $ExistingLangs;
		foreach ($ExistingLangs as $l)
		{
			if ($this->fileValidator)
			{
				$themeInfo = $this->fileValidator->themeInfo;
				if ($themeInfo->serializable && USE_DB) {
					$this->samepage_i18n[$l] = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>$l, "phpfile"=>"unittests", "hash"=>$themeInfo->hash));
				} else {
					$this->samepage_i18n[$l] = null;
				}			
			} else {	
				$this->samepage_i18n[$l] = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>$l, "phpfile"=>"unittests"));
			}
		}*/
	}
	
	public function render()
	{
		?>

		<div style="text-align:center;display:none;" id="upperzone">
			<button type="button" id="stopsampling-btn" class="btn">
				<?php echo "Stop sampling";?>
			</button>
			<div id="divmessage">
			</div>
		</div>
		
		<div id="checklist" style="height:350px;overflow:scroll;padding:10px">
		<?php
		$checkids = array();
		foreach($this->checklist as $check)
		{
			echo '<div>';
			echo '<h2>'.$check->title["en"].'</h2>';
			
			foreach($check->checks as $checkpart)
			{
				// check for non unique checks ids
				if (in_array($checkpart->id, $checkids)) {echo $checkpart->id.' already exists';die;}
				$checkids[] = $checkpart->id;
				
				echo '<a href="#" class="utSampleLink" id="lnk_'.$checkpart->id.'">'.$checkpart->id.'</a>';
				echo ' : ';
				echo $checkpart->hint["en"].' : '.$checkpart->unittest;
				$url = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>"en", "phpfile"=>"results", "ut"=>$checkpart->unittest));
				echo '<a href="'.$url.'">'.$url.'</a>';
				echo '<br/>';
			}
			echo '</div>';
		}
		?>
		</div>
		<div id="messageArea" class="container">
			
		</div>
		<script>
		var startId = <?php $history = new History(); echo $history->getMaxId(); ?>;
		var curId = startId;
		var check_id = null;
		$(".utSampleLink").click(utSample);
		$("#stopsampling-btn").click(utSampleStop);
		function utSample(event)
		{
			if (check_id == null)
			{
				if (event!=null) event.preventDefault(); // no scroll to top
				check_id = event.currentTarget.id.substring(4);
				$("#messageArea").html(" ");
				$("#divmessage").html(" ");
			}
			$("#upperzone").css('display', 'block');
			$("#checklist").css('display', 'none');
			$.ajax({
				type: "POST",
				url: "<?php echo TC_HTTPDOMAIN.'/ajax.php?controller=unittests&action=sample';?>",
				data : {themeid : curId, checkid : check_id}
			}).done(function( obj ) {
				if (check_id == null) return;
				$("#messageArea").append(obj.html);

				curId = obj.next_id;
				if (curId == null)
				{
					$("#messageArea").append("<h2>DONE. NO MORE THEMES TO TEST.</h2>");
					$("#upperzone").css('display', 'none');
					$("#checklist").css('display', 'block');
					curId = startId;
					check_id = null;
				} else {
					$("#divmessage").html("Checking " + obj.next_name + " (id : " + obj.next_id + ") ...");
					utSample(null);
				}
			}).fail(function() {
				console.log("ajax error");
			})
		}
		function utSampleStop(event)
		{
			$("#upperzone").css('display', 'none');
			$("#checklist").css('display', 'block');
			curId = startId;
			check_id = null;
		}
		</script>
		<?php
	}
	
	/*
	* Tests a check with a sample of already uploaded themes
	*/
	public function ajax_sample()
	{
		$time_start = microtime(true);
		$response["error"] = "none";
		$response["html"] = "";
		$themeid = 1;
		if (isset($_POST["themeid"])) $themeid = intval($_POST["themeid"]);
		if ($themeid < 1) $themeid = 1;

		$checkid = $_POST["checkid"];
		if (USE_DB)
		{
			$history = new History();
			$themInfo = $history->getFewInfo($themeid);
			$hash = $themInfo["hash"];
			$fileValidator = FileValidator::unserialize($hash);

			$fileValidator->validate($checkid);	
			//if (UserMessage::getCount(ERRORLEVEL_FATAL) == 0) // serialize only if no fatal errors

			$validationResults = $fileValidator->getValidationResults(I18N::getCurLang());
			
			if (count($validationResults->check_critical) > 0 || count($validationResults->check_warnings) > 0) $html = '<h2 style="color:#D00;">'.$themInfo["name"].'</h2>';
			if (count($validationResults->check_critical) > 0)
			{
				//$html .= '<h2 style="line-height:100px;color:#D00;">'.__("Critical alerts").'</h2>';
				$html .= '<ol>';
				foreach ($validationResults->check_critical as $check)
				{
					$html .= '<h4 style="color:#666;margin-top:40px;"><li>'.$check->title.' : '.$check->hint.'</li></h4>';
					if (!empty($check->messages)) {
						$html .= '<p style="color:#c94b4b;">'.implode('<br/>',$check->messages).'</p>';
					}
				}
				$html .='</ol>';
			}

			if (count($validationResults->check_warnings) > 0)
			{
				//$html .= '<h2 style="line-height:100px;color:#eea43a;">'.__("Warnings").'</h2>';
				$html .= '<ol>';
				foreach ($validationResults->check_warnings as $check)
				{
					$html .= '<h4 style="color:#666;margin-top:40px;"><li>'.$check->title.' : '.$check->hint.'</li></h4>';
					if (!empty($check->messages)) {
						$html .= '<p style="color:#eea43a;">'.implode('<br/>',$check->messages).'</p>';
					}
				}
				$html .= '</ol>';
			}
			$response["html"] = $html;
			
			// get info about next theme to check
			{
				$prevId = $history->getPrevId($themeid );
				if (!empty($prevId))
				{
					$themInfoNext = $history->getFewInfo($prevId);
					$response["next_id"] = $prevId;
					$response["next_name"] = $themInfoNext["name"];
				} else {
					$response["next_id"] = null;
					$response["next_name"] = null;
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