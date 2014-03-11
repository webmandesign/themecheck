<?php
namespace ThemeCheck;
require_once TC_INCDIR."/FileValidator.php";
require_once TC_INCDIR."/Helpers.php";
require_once TC_INCDIR."/shield.php";
if (USE_DB) include_once (TC_ROOTDIR.'/DB/History.php');

class Controller_home
{
	public $meta = array();
	public $samepage_i18n = array();
	
	public function __construct()
	{
	}
	
	public function prepare()
	{
		$this->meta["title"] = __("The Web Template Validation Service");
		$this->meta["description"] = __("A free service that checks web templates and themes for security and code quality.");
		global $ExistingLangs;
		foreach ($ExistingLangs as $l)
		{
			$this->samepage_i18n[$l] = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>$l, "phpfile"=>"index.php"));
		}
	}
	
	private function getThumb($themeInfo)
	{
		$html = '';
		$namesanitized = $themeInfo['namesanitized'];
		$themetype = $themeInfo['themetype'];
		$score = $themeInfo['score'];
		$themetype_text = sprintf(__("Wordpress %s theme"),$themeInfo['cmsVersion']);
		if ($themetype == TT_JOOMLA) $themetype_text = sprintf(__("Joomla %s template"), $themeInfo['cmsVersion']);
		$url = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"results", "namesanitized"=>$namesanitized, "themetype"=>$themetype));
		$html .= '<div style="width:220px;height:220px;display:inline-block;text-align:center;margin:10px 32px">';
		$html .= '<a href="'.$url.'" ><img style="box-shadow: 0 0 20px #DDD;" src="'.TC_HTTPDOMAIN.'/'.$themeInfo['hash'].'/thumbnail.png"></a>';
		$html .= '<div style="width:220px;height:40px;margin:3px 0 0 0;text-align:left;line-height:18px;padding:0 7px;overflow:hidden;white-space:nowrap;font-size : 12px;">';
		$html .= '<div style="width:33px;height:40px;float:right;">';
		$html .= getShield($themeInfo, I18N::getCurLang(), 40, $url, '');
		$html .= '</div>';
		$html .= htmlspecialchars($themeInfo['name']).'<br/><span style="font-size : 12px; color:#AAA;">'.$themetype_text.'</span>';
		$html .= '</div>';
		$html .= '</div>';
		
		return $html;
	}
	
	public function render()
	{
		$max_size = Helpers::returnBytes(ini_get('upload_max_filesize'));
		if ($max_size > Helpers::returnBytes(ini_get('post_max_size'))) $max_size = Helpers::returnBytes(ini_get('post_max_size'));
		$max_size_MB = $max_size / (1024*1024);
		?> 
				<div class="jumbotron">
					<div class="container">
						<h1><?php echo __("Validate your web theme or template"); ?></h1>
						<h2><?php echo __("And make it trustable"); ?></h2>
						<p><?php echo __("Themecheck.org is a quick service that lets you validate web themes or templates for security and code quality. This service is free and compatible with Wordpress themes and Joomla templates."); ?></p>
						<p><?php echo __("Share validation score on your site with ThemeCheck.org widget :"); ?>&nbsp;<img src="img/pictosuccess40.png"></p>
					</div>
				</div>

				<div class="container">
					<form role="form" class="text-center" action="<?php echo TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(),"phpfile"=>"results"));?>" method="post" enctype="multipart/form-data">
						<div class="form-group">
							<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo Helpers::returnBytes(ini_get('upload_max_filesize'));?>" />
							<input type="file" name="file" id="file" class="filestyle" data-buttonText="<?php echo __("Select file"); ?>" data-classButton="btn btn-default btn-lg" data-classInput="input input-lg">
						</div>
						<?php echo __("Maximum file size")." : $max_size_MB MB";?> 
						<br/><br/>
						
						<input type="checkbox" name="donotstore" value="donotstore">&nbsp;<?php echo __('Forget uploaded data after results').'&nbsp;<a id="forgetresultsmoreinfo" data-container="body" data-toggle="popover" data-placement="right" data-content="'.__("<ul><li>No data will be kept on themecheck.org servers (or any other)<li>Validation won't be visible to the public<li>If you want to see the results in the future, you'll have to re-submit your file</ul>").'" href="#">( ? )</a>';?><br>

						<br/>
						<button type="submit" class="btn btn-primary btn-lg" ><?php echo __("Submit"); ?></button>
					</form>
		<?php
		
							
		// display recent validated file if	history is available			
		if (USE_DB) {
		$history = new History();

		?>
					<hr>
					<h2><?php echo __("Themes recently checked"); ?></h2>
					<div id="alreadyvalidated">
					<?php 
					$pagination = $history->getRecent();
					$smallestid = 0;
					foreach($pagination as $t)
					{
						echo $this->getThumb($t);
						if ($smallestid == 0 || $smallestid > intval($t['id'])) $smallestid = intval($t['id']);
					}
					?>
					</div>
			<div style="text-align:center;"><button type="button" id="seemore-btn" class="btn">
				<?php echo __("See more");?>
			</button>
			</div>
		<?php } ?>		
				</div> <!-- /container --> 
				<script>
				var smallestid = <?php echo $smallestid;?>;
  $('#seemore-btn').click(function () {
		$.ajax({
			type: "POST",
			url: "<?php echo TC_HTTPDOMAIN.'/ajax.php?controller=home&action=seemore';?>",
			data: { olderthan: smallestid, lang: "<?php echo I18N::getCurLang();?>" }
		}).done(function( obj ) {
			$("#alreadyvalidated").append(obj.html);
			if (obj.nomore) $("#seemore-btn").hide();
			smallestid = obj.smallestid; 
		}).fail(function() {
			console.log("ajax error");
		})

  });
</script>
				<?php
	}
		
	public function ajax_seemore()
	{
		$lang = $_POST["lastid"];
		$olderthan = intval($_POST["olderthan"]);
		$response = null;
		if (USE_DB)
		{
			$history = new History();
			$pagination = $history->getRecent($olderthan);
			
			$smallestid = 0;
			$html = '';
			foreach($pagination as $t)
			{
				$html .= $this->getThumb($t);
				if ($smallestid == 0 || $smallestid > intval($t['id'])) $smallestid = intval($t['id']);
			}
			
			$response["html"] = $html;
			if ($smallestid == 1) $response["nomore"] = true;
			else $response["nomore"] = false;
			$response["smallestid"] = $smallestid;
		}
		ob_clean();
		header('Content-Type: application/json');
		echo json_encode($response);
	}
}