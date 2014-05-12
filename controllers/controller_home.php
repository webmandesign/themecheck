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
		$this->meta["title"] = __("The Web Template Verification Service");
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
		$cmsVersion = $themeInfo['cmsVersion'];
		$themetype_text = '';
		//sprintf(__("Wordpress %s theme"),$themeInfo['cmsVersion']);
	//	if ($themetype == TT_JOOMLA) $themetype_text = sprintf(__("Joomla %s template"), $themeInfo['cmsVersion']);
		
		if ($themetype == TT_WORDPRESS) 	
			if (empty($cmsVersion)) $themetype_text = __("Wordpress theme");
			else $themetype_text = sprintf(__("Wordpress %s theme"), $cmsVersion);
		else if ($themetype == TT_WORDPRESS_CHILD)
			if (empty($cmsVersion)) $themetype_text = __("Wordpress child theme");
			else $themetype_text = sprintf(__("Wordpress %s child theme"), $cmsVersion);
		else if ($themetype == TT_JOOMLA)
			if (empty($cmsVersion)) $themetype_text = __("Joomla template");
			else $themetype_text = sprintf(__("Joomla %s template"), $cmsVersion);		
		
		$url = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"results", "namesanitized"=>$namesanitized, "themetype"=>$themetype));
		
		$html .= '<div class="validated-theme" data-id="'.$themeInfo['id'].'">';
		$html .= '<a href="'.$url.'" ><img style="box-shadow: 0 0 20px #DDD;" src="'.TC_HTTPDOMAIN.'/'.$themeInfo['hash'].'/thumbnail.png"></a>';
		$html .= '<div class="vts">';
		if ($themeInfo["isThemeForest"]){
			$html .= '<img src="img/logo_themeforest18.png" style="margin-right:2px;float:left;" title="'.__("Themeforest theme").'" alt="'.__("Themeforest icon").'">';
		}
		$html .= '<div class="dshield">';
		$html .= getShield($themeInfo, I18N::getCurLang(), 40, $url, TC_HTTPDOMAIN.'/');
		$html .= '</div>';
		if ($themeInfo["isThemeForest"]){
			$html .= '<span class="stext">'.htmlspecialchars($themeInfo['name']).'</span><br/><span style="font-size : 12px; color:#AAA;">'.$themetype_text.'</span>';
		} else {
			$html .= '<span class="stext" style="width:170px;">'.htmlspecialchars($themeInfo['name']).'</span><br/><span style="font-size : 12px; color:#AAA;">'.$themetype_text.'</span>';
		}
		$html .= '</div>';
		$html .= '</div>';
		
		return $html;
	}
	
	public function render()
	{
		$max_size = Helpers::returnBytes(ini_get('upload_max_filesize'));
		if ($max_size > Helpers::returnBytes(ini_get('post_max_size'))) $max_size = Helpers::returnBytes(ini_get('post_max_size'));
		$max_size_MB = $max_size / (1024*1024);
		
		$token = uniqid(true);
		$_SESSION['token_'.$token] = time();
		
		?> 
				<div class="jumbotron">
					<div class="container">
						<h1><?php echo __("Verify web themes and templates"); ?></h1>
						<p><?php echo __("Themecheck.org is a quick service that lets you verify web themes or templates for security and code quality. This service is free and compatible with Wordpress themes and Joomla templates."); ?>
						<a data-toggle="collapse" data-parent="#accordion" href="#collapseOne"><?php echo __("More..."); ?></a>
						</p>
						<div class="row">
							
							<div id="collapseOne" class="panel-collapse collapse">
								<div class="panel-body">
									<div class="col-lg-6">
										<h2><?php echo __("Website owners"); ?></h2>
										<p><?php echo __("Check themes or templates you download before installing them on your site"); ?></p>
										<ul>
										<li><?php echo __("Check code quality"); ?></li>
										<li><?php echo __("Check presence of malware"); ?></li>
										</ul>
									</div>
									<div class="col-lg-6">
										<h2><?php echo __("Developers"); ?></h2>
										<p><?php echo __("Your create or distribute themes ?"); ?></p>
										<ul>
										<li><?php echo __("Themecheck.org helps you verify they satisfy CMS standards and common users needs."); ?></li>
										<li><?php echo __("Share verification score on your site with ThemeCheck.org widget  "); ?>&nbsp;<img src="<?php echo TC_HTTPDOMAIN;?>/img/pictosuccess40.png"></li>
										</ul>
									</div>
								</div>
							</div>
							
						</div>
					</div>
				</div>

				<div class="container text-center">
					<h2 ><?php echo __("Upload a zip file and get its verification score :"); ?></h2><br/>
					<form role="form" class="text-center" action="<?php echo TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(),"phpfile"=>"results"));?>" method="post" enctype="multipart/form-data">
						<div class="form-group">
							<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo Helpers::returnBytes(ini_get('upload_max_filesize'));?>" />
							<input type="file" name="file" id="file" class="filestyle" data-buttonText="<?php echo __("Select file"); ?>" data-classButton="btn btn-default btn-lg" data-classInput="input input-lg">
						</div>
						<?php echo __("Maximum file size")." : $max_size_MB MB";?> 
						<br/><br/>
						
						<input type="checkbox" name="donotstore" value="donotstore">&nbsp;<?php echo __('Forget uploaded data after results').'&nbsp;<a id="forgetresultsmoreinfo" data-container="body" data-toggle="popover" data-placement="right" data-content="'.__("<ul><li>No data will be kept on themecheck.org servers (or any other)<li>Validation won't be visible to the public<li>If you want to see the results in the future, you'll have to re-submit your file</ul>").'" href="#!">( ? )</a>';?><br>

						<br/>
						<button type="submit" class="btn btn-primary btn-lg" ><?php echo __("Submit"); ?></button>
						<input type="hidden" name="token" value="<?php echo $token;?>"/>
					</form>
		<?php
		
							
		// display recent validated file if	history is available			
		if (USE_DB) {
		$history = new History();

		?>
					<hr>
					<h2><?php echo __("Recently checked themes"); ?></h2>
					<div class="row">
						<form id="sortform">
							<div style="font-size:20px;" class="col-sm-4 col-sm-offset-4">
								wordpress <input type='checkbox' name='theme[]' value='wordpress' <?php if(isset($_SESSION['theme'])){if(in_array("wordpress",$_SESSION['theme'])){echo 'checked="checked"';}} else {echo 'checked="checked"';};?> class='sortdropdown'/>
								&nbsp;&nbsp;&nbsp;joomla <input type='checkbox' name='theme[]' value='joomla' <?php if(isset($_SESSION['theme'])){if(in_array("joomla",$_SESSION['theme'])){echo 'checked="checked"';}} else {echo 'checked="checked"';};?> class='sortdropdown'/>
							</div>
							<div class="col-sm-3 col-sm-offset-1 col-xs-12">
								<select name='sort' class='sortdropdown form-control' style="width:180px;margin:auto">
									<option value='id' <?php if(isset($_SESSION['sort']) && $_SESSION['sort']=='creationDate'){echo 'selected="selected"';}?>><?php echo __("Newer first");?></option>
									<option value='score' <?php if(isset($_SESSION['sort']) && $_SESSION['sort']=='score'){echo 'selected="selected"';}?>><?php echo __("Higher scores first");?></option>
								</select>
							</div>
						</form>
					</div>
					<div id="alreadyvalidated">
					<?php 
					if(isset($_SESSION['sort']) && isset($_SESSION['theme']))
					{
						$pagination = $history->getSorted($_SESSION['sort'], $_SESSION['theme']);
					}
					else
					{
						$pagination = $history->getRecent();
					}
					foreach($pagination as $t)
					{
						echo $this->getThumb($t);
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
				  $('#seemore-btn').click(function () {
						$.ajax({
							type: "POST",
							url: "<?php echo TC_HTTPDOMAIN.'/ajax.php?controller=home&action=seemore';?>",
							data: { olderthan: $("#alreadyvalidated div.validated-theme").last().data("id"), lang: "<?php echo I18N::getCurLang();?>" }
						}).done(function( obj ) {
							$("#alreadyvalidated").append(obj.html);
							if (obj.nomore) $("#seemore-btn").hide();
							smallestid = obj.smallestid; 
						}).fail(function() {
							console.log("ajax error");
						})

				  });
				  
				$('.sortdropdown').on("change", function(){
					$.ajax({
						type: "POST",
						url: "<?php echo TC_HTTPDOMAIN.'/ajax.php?controller=home&action=sort';?>",
						data: $("#sortform").serialize()
					}).done(function(obj){
						$("#alreadyvalidated").html(obj.html);
					});
				})
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
			if(isset($_SESSION['sort']) && isset($_SESSION['theme']))
			{
				$pagination = $history->getSorted($_SESSION['sort'], $_SESSION['theme'], $olderthan);
			}
			else
			{
				$pagination = $history->getRecent($olderthan);
			}
			
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
	
	public function ajax_sort()
	{
		$data = array();
		$data['success'] = false;
		
		if(!empty($_POST['sort']) && !empty($_POST['theme']))
		{
			$_SESSION['sort'] = $_POST['sort'];
			$_SESSION['theme'] = $_POST['theme'];
		
			$history = new History();
			
			$pagination = $history->getSorted($_POST['sort'], $_POST['theme']);
			$smallestid = 0;
			$html = '';
			foreach($pagination as $t)
			{
				$html .= $this->getThumb($t);
				if ($smallestid == 0 || $smallestid > intval($t['id'])) $smallestid = intval($t['id']);
			}
			
			$data['success'] = true;
		}
		$data['html'] = $html;

		header('Content-Type: application/json');
		echo json_encode($data);
	}
}