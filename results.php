<?php
namespace ThemeCheck;
require_once TC_INCDIR."/FileValidator.php";
$themetype = '';
$themeAlreadyOnServer = false;
$unitesting = false;
$fileValidator = null;

$routeParts = Route::getInstance()->match();
// There are 3 types of results to display
// 1 - Display an already evaluated file which results were stored on the server. Just need the id. e.g : results.php?id=162804c3c358267d3a16855686ab1887
// 2 - Display unit test files. Need zip filename of the unit test. e.g : results.php?filename=ut_directories_svn.zip
// 3 - Unknown file. Need $_FILES and $_POST["filetype"]
if (isset($routeParts["hash"])) // already uploaded file
{
	$hash = $routeParts["hash"];
	$fileValidator = FileValidator::unserialize($hash, I18N::getCurLang());
} else if (isset($_GET["filename"])) // unit tests
{	
	// check unit test existence
	$filename = urldecode($_GET["filename"]);

	if (!(substr($filename, -4) == ".zip" && file_exists(TC_ROOTDIR.'/include/unittests/'.$filename)))
	{
		// if file does not exist , we won't do anything : Set $themetype to empty.
		$themetype = '';
	}
	if (!empty($themetype)) $themeAlreadyOnServer = true;
	$unitesting = true;
} else if (count($_FILES)>0 && isset($_FILES["file"]) && isset($_FILES["file"]["name"])) // uploaded file
{
	$archiveInfo = FileValidator::upload();
	
	// validation is actually run in all available languages because error messages adapt to the current language and the context of the error, so translation must be done during the tests.
	$lang_saved = I18N::getCurLang(); // memorize cur lang
	
	// run validation in english
	$i18n = I18N::getInstance();
	$i18n->curLang = 'en';
	$fileValidator_en_EN = new FileValidator($archiveInfo);
	$fileValidator_en_EN->validate();	
	$fileValidator_en_EN->serialize();
	
	// run validation in french
	$i18n->curLang = 'fr';
	$fileValidator_fr_FR = new FileValidator($archiveInfo);
	$fileValidator_fr_FR->validate();	
	$fileValidator_fr_FR->serialize();
	
	// back to user's language
	$i18n->curLang = $lang_saved;
	
	if ($i18n->curLang=='fr')
	{
		$fileValidator = $fileValidator_fr_FR;
	} else {
		$fileValidator = $fileValidator_en_EN;
	}
}

if ($fileValidator)
{
?>
    <div class="container">
		<br/>
		<?php
		if ($themeAlreadyOnServer)
		{
			$hash = $_GET["hash"];
			$fileValidator_tmp = FileValidator::unserialize($hash,I18N::getCurLang());
		}
		
		$userMessage = UserMessage::getInstance();
		echo UserMessage::getInstance()->getMessagesHtml();
		
		
		// check if a file has been submitted
		/*if (!$themeAlreadyOnServer)
		{	
			
		} else {
			if ($unitesting)
			{
				$filename = urldecode($_GET["filename"]);
				$fileValidator = new FileValidator($themetype);
				$fileValidator->filename = $filename;
				$fileValidator->fileid   = null;
				$fileValidator->filepath = 'include/unittests/'.$filename;
				$fileValidator->filetype = 'application/zip';
				$fileValidator->filesize = filesize(TC_ROOTDIR.'/'.$fileValidator->filepath);
				
				$fileValidator->validate();	
				$fileValidator->serialize();			
			} else {
				$fileid = $_GET["id"];
				$fileValidator_tmp = FileValidator::unserialize($fileid,I18N::getCurLang());
				if (true) {
					$fileValidator = new FileValidator($themetype);
					$fileValidator->filename = $fileValidator_tmp->filename;
					$fileValidator->fileid   = $fileValidator_tmp->fileid;
					$fileValidator->filepath = $fileValidator_tmp->filepath;
					$fileValidator->filetype = $fileValidator_tmp->filetype;
					$fileValidator->filesize = $fileValidator_tmp->filesize;
			
					$fileValidator->validate();	
					$fileValidator->serialize();
				}
			}
		}
		*/
		$themeInfo = $fileValidator->themeInfo;
		if ($themeInfo->serializable && USE_HISTORY)  $samepage_i18n = array('en' => TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>"en", "phpfile"=>"results.php", "hash"=>$themeInfo->hash)),
											 'fr' => TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>"fr", "phpfile"=>"results.php", "hash"=>$themeInfo->hash)));
		 else $samepage_i18n = array('en' => null, 'fr' => null);
?>
			<div class="row">
				<div class="col-md-3"><div class="center-block" style="wisth:150px;"><img style="box-shadow: 0 0 20px #DDD;" src="<?php echo TC_HTTPDOMAIN.'/'.$themeInfo->hash.'/thumbnail.png';?>"></div></div>
				<div class="col-md-9">
					<h1><?php printf(__("Validation results for <strong>%s"), htmlspecialchars($themeInfo->zipfilename, defined('ENT_HTML5')?ENT_QUOTES | ENT_HTML5:ENT_QUOTES));?></h1>
					<?php
						$percentclass = 'text-success';
						$picto = '<img style="margin-bottom:20px;margin-right:50px" src="'.TC_HTTPDOMAIN.'/img/pictosuccess.png">';
						if (count($fileValidator->check_fails) > 0)
						{
							$percentclass = "text-danger";
							$picto = '';
						}
					  else if (count($fileValidator->check_warnings) > 0)
						{
							$percentclass = "text-warning";
							$picto = '<img style="margin-bottom:20px;margin-right:50px" src="'.TC_HTTPDOMAIN.'/img/pictowarning.png">';
						}
						echo '<p class="text-center '.$percentclass.'" style="font-size:100px;">'.$picto.number_format($fileValidator->score,2).'&nbsp%</p>';
								?>
				</div>
			</div>
			<div class="row">
				<dl class="dl-horizontal">
					<dt><?php echo __("Theme name");?></dt>
					<dd><?php echo htmlspecialchars($themeInfo->name);?></dd>
				</dl>
				<dl class="dl-horizontal">
					<dt><?php echo __("Theme type");?></dt>
					<dd><?php 
						if ($themeInfo->themetype == TT_WORDPRESS) $filetype = __("Wordpress theme");
						if ($themeInfo->themetype == TT_JOOMLA) $filetype = __("Joomla template");
						echo $filetype.' - '.$themeInfo->cmsVersion;
					?></dd>
				</dl>
				<dl class="dl-horizontal">
					<dt><?php echo __("File name");?></dt>
					<dd><?php echo htmlspecialchars($themeInfo->zipfilename, defined('ENT_HTML5')?ENT_QUOTES | ENT_HTML5:ENT_QUOTES);?></dd>
				</dl>
				<dl class="dl-horizontal">
					<dt><?php echo __("File size");?></dt>
					<dd><?php echo $themeInfo->zipfilesize;?></dd>
				</dl>
				<dl class="dl-horizontal">
					<dt><?php echo __("Permalink");?></dt>
					<dd><?php if (!empty($samepage_i18n[I18N::getCurLang()])) echo $samepage_i18n[I18N::getCurLang()]; else echo __('None');?></dd>
				</dl>
				<dl class="dl-horizontal">
					<dt><?php echo __("License");?></dt>
					<dd><?php if (empty($themeInfo->licenseUri)) echo ThemeInfo::getLicenseName($themeInfo->license);
										else echo '<a href="'.$themeInfo->licenseUri.'">'.ThemeInfo::getLicenseName($themeInfo->license).'</a>'; 
					if (!empty($themeInfo->licenseText)) echo '<br>'.htmlspecialchars($themeInfo->licenseText);?></dd>
				</dl>
				<dl class="dl-horizontal">
					<dt><?php echo __("Files included");?></dt>
					<dd><?php echo htmlspecialchars($themeInfo->filesIncluded, defined('ENT_HTML5')?ENT_QUOTES | ENT_HTML5:ENT_QUOTES);?></dd>
				</dl>
				<dl class="dl-horizontal">
					<dt></dt>
					<dd></dd>
				</dl>
			</div>

					<?php
					echo '<div class="row"><div class="col-md-12">';
					
					
					$panes = array("failures" => $fileValidator->check_fails, "warnings" =>$fileValidator->check_warnings, "successes" => $fileValidator->check_successes);
					if (count($fileValidator->check_undefined)>0) $panes["undefined"] = $fileValidator->check_undefined;
					$glyphicons = array("failures" => "glyphicon-remove-sign", "warnings" =>"glyphicon-exclamation-sign", "successes" => "glyphicon-ok-sign", "undefined" => "glyphicon-question-sign");
					?>

					<ul class="nav nav-tabs">
						<li class="active"><a class="btn-danger" href="#failures" data-toggle="tab"><?php echo __("Failures").' ('.count($fileValidator->check_fails).')';?></a></li>
						<li><a class="btn-warning" href="#warnings" data-toggle="tab"><?php echo __("Warnings").' ('.count($fileValidator->check_warnings).')';?></a></li>
						<li><a class="btn-success" href="#successes" data-toggle="tab"><?php echo __("Successes").' ('.count($fileValidator->check_successes).')';?></a></li>
						<?php if (count($fileValidator->check_undefined)>0) echo '<li><a href="#undefined" data-toggle="tab">'.__("Undefined").' ('.count($fileValidator->check_undefined).')</a></li>';?>
					</ul>
					<div class="tab-content">
						<?php 
						foreach ($panes as $pane => $checks)
						{
							if ($pane == 'failures')
								echo '<div class="tab-pane active" id="'.$pane.'">';
							else 
								echo '<div class="tab-pane" id="'.$pane.'">';
							
								echo '<div class="panel-group" id="accordion">';
								
								$collapseIndex = 1;
								foreach($checks as $check)
								{
									echo '<div class="panel panel-default">';
										echo '<div class="panel-heading">';
											echo '<h4 class="panel-title">';
												echo '<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapse_'.$pane.$collapseIndex.'">';
													echo '<span class="glyphicon '.$glyphicons[$pane].'" style="margin-right:10px"></span>'.$check->title.' : '.$check->hint;
												echo '</a>';
												echo '<span class="badge pull-right">'.number_format($check->duration,3).'s</span>';
												//echo '<span class="badge pull-right"><a href="'.TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>"en", "phpfile"=>"results.php", "themetype"=>$check->themetype, "filename"=>$check->unittest)).'">'.$check->unittest.'</a></span>';
												
											echo '</h4>';
											
										echo '</div>';
										
										if ($pane == 'successes') echo '<div id="collapse_'.$pane.$collapseIndex.'" class="panel-collapse collapse">';
										else echo '<div id="collapse_'.$pane.$collapseIndex.'" class="panel-collapse">';
											echo '<div class="panel-body">';
												if (!empty($check->messages)) echo '<p>'.implode('<br/>',$check->messages).'</p>';
											echo '</div>';
										echo '</div>';
									echo '</div>';
									$collapseIndex ++;
								}
								
								echo '</div>';
							echo '</div>';
						}?>
					</div>
					<?php
					echo '</div></div>';

} else {
	$samepage_i18n = array('en' => TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>"en", "phpfile"=>"results.php")),
											 'fr' => TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>"fr", "phpfile"=>"results.php")));
	?>
			<div class="jumbotron">
				<div class="container">
					<h1><?php echo 'Please send us a theme to check.'; ?></h1>
					<p><?php echo '<a href="'.TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"index.php")).'">Go to home page</a>&nbsp;' ?></p>
				</div>
			</div>

			<div class="container">
	<?php
}
 

?>
</div> <!-- /container --> 
<?php
