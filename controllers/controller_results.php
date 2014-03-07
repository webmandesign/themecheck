<?php
namespace ThemeCheck;
require_once TC_INCDIR."/FileValidator.php";
require_once TC_INCDIR."/shield.php";

class Controller_results
{
	public $meta = array();
	public $samepage_i18n = array();
	private $fileValidator;
	private $validationResults;
	
	public function __construct()
	{
		$this->fileValidator = null;
		$this->validationResults = null;
		$this->themeInfo = null;
	}
	
	public function prepare()
	{
		$routeParts = Route::getInstance()->match();
		// There are 2 types of results to display
		// 1 - Display an already evaluated file which results were stored on the server. Just need the id. e.g : results?id=162804c3c358267d3a16855686ab1887
		// 2 - Unknown file. Need $_FILES and $_POST["filetype"]
		if (isset($routeParts["hash"])) // already uploaded file
		{
			$hash = $routeParts["hash"];
			$this->fileValidator = FileValidator::unserialize($hash);
			$this->validationResults = $this->fileValidator->getValidationResults(I18N::getCurLang());
		} else if (count($_FILES)>0 && isset($_FILES["file"]) && !empty($_FILES["file"]["name"])) // uploaded file
		{
			$themeInfo = FileValidator::upload();
			if ($themeInfo)
			{
				$this->fileValidator = new FileValidator($themeInfo);
				$this->fileValidator->validate();	
				$this->fileValidator->serialize();

				$this->validationResults = $this->fileValidator->getValidationResults(I18N::getCurLang());
			}
		} else {
			trigger_error(__("No file uploaded."), E_USER_ERROR);
			$this->meta["title"] = __("No file uploaded");
			$this->meta["description"] = __("No file uploaded");
			return ;
		}
		
		$this->meta["title"] = __("Results");
		$this->meta["description"] = __("Validation results");
		
		global $ExistingLangs;
		foreach ($ExistingLangs as $l)
		{
			if ($this->fileValidator)
			{
				$themeInfo = $this->fileValidator->themeInfo;
				if ($themeInfo->serializable && USE_DB) {
					$this->samepage_i18n[$l] = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>$l, "phpfile"=>"results", "hash"=>$themeInfo->hash));
				} else {
					$this->samepage_i18n[$l] = null;
				}			
			} else {	
				$this->samepage_i18n[$l] = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>$l, "phpfile"=>"results"));
			}
		}
	}
	
	public function render()
	{
		if ($this->fileValidator)
		{
		?>
				<div class="container">
				<br/>
				<?php
						
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
				$themeInfo = $this->fileValidator->themeInfo;
				
		?>
					<div class="row text-center">
						<div><img style="box-shadow: 0 0 20px #DDD;" src="<?php echo TC_HTTPDOMAIN.'/'.$themeInfo->hash.'/thumbnail.png';?>"></div>
							<h1><?php printf(__("Validation results for <strong>%s</strong>"), htmlspecialchars($themeInfo->zipfilename, defined('ENT_HTML5')?ENT_QUOTES | ENT_HTML5:ENT_QUOTES));?></h1>
					</div>
					<div class="row" style="color:#888;font-weight:normal;margin:30px 0 0 0;background:#F8F8F8;border-radius: 3px;">
						<div class="col-md-8 text-center" style="">
						<br/>
						<?php
								$userMessage = UserMessage::getInstance();
								echo UserMessage::getInstance()->getMessagesHtml();
				
								$img = 'shieldgreen240.png';
								$color = 'a6af11';
								$text = sprintf(__('Themecheck.org validation score : %s%%'),intval($themeInfo->score));
								if ($themeInfo->score<100.0)
								{
									if ($themeInfo->failuresCount > 0)
									{
										$img = 'shieldred240.png';
										$color = 'ff1418';
										$text = sprintf(__('Themecheck.org validation score : %s%% (%s checks failed)'),intval($themeInfo->score),$themeInfo->failuresCount);
									} else {
										$img = 'shieldorange240.png';
										$color = 'd96f11';
										$text = sprintf(__('Themecheck.org validation score : %s%%'),intval($themeInfo->score));
									}
								}
								?>
								<div class="shield1" style="width:201px;height:240px;background-image:url(<?php echo TC_HTTPDOMAIN;?>/img/<?php echo $img;?>);" title="<?php echo $text;?>">
										<div class="shield2" style="color:#<?php echo $color;?>;">			
									<?php if ($themeInfo->score<100.0) echo intval($themeInfo->score); ?>
										</div>	
								</div>
								<?php
								echo '<p "color:#'.$color.'">validation score : '.intval($themeInfo->score).' %</p>';
								echo '<p>'.$themeInfo->failuresCount.' checks failed. '.$themeInfo->warningsCount.' warnings.</p>';
								?>
								<br/><br/>
								Share this page with the following link : 
								<p>
								<?php 
									echo '<a href="'.$this->samepage_i18n[I18N::getCurLang()].'">'.$this->samepage_i18n[I18N::getCurLang()].'</a>';
								?>
								</p>
								Display this score on your website with the following HTML code that links to this page :
								<pre style="font-size:11px;width:70%;margin:auto;"><?php echo htmlspecialchars('<iframe src="'.TC_HTTPDOMAIN.'/score.php?lang='.I18N::getCurLang().'&id='.$themeInfo->hash.'&size=big" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:240px; width:200px;" allowTransparency="true"></iframe>');?></pre>		
								
								<button class="btn" data-toggle="collapse" data-target="#moreembedoptions" style="height:20px;padding:1px;font-size:12px">more options</button>
								<div id="moreembedoptions" class="collapse">
								<?php displayShield($themeInfo, I18N::getCurLang(), 80, '#', TC_HTTPDOMAIN.'/'); ?>
								Medium size icon (default) :
								<pre style="font-size:11px;width:70%;margin:auto;"><?php echo htmlspecialchars('<iframe src="'.TC_HTTPDOMAIN.'/score.php?id='.$themeInfo->hash.'" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:80px; width:67px;" allowTransparency="true"></iframe>');?></pre>	
								<?php displayShield($themeInfo, I18N::getCurLang(), 40, '#', TC_HTTPDOMAIN.'/'); ?>
								Small size icon :
								<pre style="font-size:11px;width:70%;margin:auto;"><?php echo htmlspecialchars('<iframe src="'.TC_HTTPDOMAIN.'/score.php?id='.$themeInfo->hash.'" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:80px; width:40px;" allowTransparency="true"></iframe>');?></pre>	
								You can switch language with <strong>lang</strong> parameter in iframe&#39;s url. So far <strong>fr</strong> and <strong>en</strong> are supported. Default value is <strong>en</strong>.
								<pre style="font-size:11px;width:70%;margin:auto;"><?php echo htmlspecialchars('<iframe src="'.TC_HTTPDOMAIN.'/score.php?lang='.I18N::getCurLang().'&id='.$themeInfo->hash.'" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:80px; width:67px;" allowTransparency="true"></iframe>');?></pre>	
								</div>
								<br/>
						</div>
						<div class="col-md-4" style="border-radius: 3px;background:#444; overflow:hidden; font-size:12px">
							<?php 
							$characteristics = array();
							$characteristics[] = array(__("Theme name"), htmlspecialchars($themeInfo->name));
							if ($themeInfo->themetype == TT_WORDPRESS) 	
								if (empty($themeInfo->cmsVersion)) $characteristics[] = array(__("Theme type"), __("Wordpress theme"));
								else $characteristics[] = array(__("Theme type"), __("Wordpress theme").' '.$themeInfo->cmsVersion);
							else if ($themeInfo->themetype == TT_JOOMLA)
								if (empty($themeInfo->cmsVersion)) $characteristics[] = array(__("Theme type"), __("Joomla template"));
								else $characteristics[] = array(__("Theme type"), __("Joomla template").' '.$themeInfo->cmsVersion);		
							$characteristics[] = array(__("File name"), htmlspecialchars($themeInfo->zipfilename, defined('ENT_HTML5')?ENT_QUOTES | ENT_HTML5:ENT_QUOTES));
							$characteristics[] = array(__("File size"), $themeInfo->zipfilesize.' '.__('bytes'));
							$characteristics[] = array(__("MD5"), strtolower($themeInfo->hash_md5));
							$characteristics[] = array(__("SHA1"), strtolower($themeInfo->hash_sha1));
							if (empty($themeInfo->licenseUri)) 
								if (!empty($themeInfo->licenseText)) $characteristics[] = array(__("License"), ThemeInfo::getLicenseName($themeInfo->license).'<br>'.htmlspecialchars($themeInfo->licenseText));
								else $characteristics[] = array(__("License"), ThemeInfo::getLicenseName($themeInfo->license));
							else 
								if (!empty($themeInfo->licenseText)) $characteristics[] = array(__("License"), '<a href="'.$themeInfo->licenseUri.'" rel="nofollow">'.ThemeInfo::getLicenseName($themeInfo->license).'</a>'.'<br>'.htmlspecialchars($themeInfo->licenseText));
								else $characteristics[] = array(__("License"), '<a href="'.$themeInfo->licenseUri.'">'.ThemeInfo::getLicenseName($themeInfo->license).'</a>');
							$characteristics[] = array(__("Files included"), htmlspecialchars($themeInfo->filesIncluded, defined('ENT_HTML5')?ENT_QUOTES | ENT_HTML5:ENT_QUOTES));
							if (!empty($themeInfo->themeUri)) $characteristics[] = array(__("Theme URI"), '<a href="'.htmlspecialchars($themeInfo->themeUri).'" rel="nofollow">'.htmlspecialchars($themeInfo->themeUri).'</a>');
							if (!empty($themeInfo->version)) $characteristics[] = array(__("Version"), htmlspecialchars($themeInfo->version));
							if (!empty($themeInfo->authorUri)) $characteristics[] = array(__("Author URI"), htmlspecialchars($themeInfo->authorUri));
							if (!empty($themeInfo->tags))$characteristics[] = array(__("Tags"), htmlspecialchars($themeInfo->tags));
							if (!empty($themeInfo->copyright))$characteristics[] = array(__("Copyright"), htmlspecialchars($themeInfo->copyright));
							if (!empty($themeInfo->creationDate))$characteristics[] = array(__("Creation date"), htmlspecialchars($themeInfo->creationDate));
							if (!empty($themeInfo->modificationDate))$characteristics[] = array(__("Mast update"), htmlspecialchars($themeInfo->modificationDate));

							foreach ($characteristics as $c)
							{
								echo '<p style="text-transform:uppercase;margin:0;margin-top:10px;">'.$c[0].'</p><span style="color:#CCC">'.$c[1].'</span>';
							}
							?>
								
						</div>
					</div>

							<?php
							echo '<div class="row"><div class="col-md-12">';
							
							if (count($this->validationResults->check_fails) > 0)
							{
								echo '<div style="padding:20px;margin-top:20px;"><h2 style="line-height:100px;color:#D00;">'.__("Failed checks").'</h2>';
								echo '<ol>';
								foreach ($this->validationResults->check_fails as $check)
								{
									echo '<h4 style="color:#666;margin-top:40px;"><li>'.$check->title.' : '.$check->hint.'</li></h4>';
									if (!empty($check->messages)) {
										echo '<p style="color:#c94b4b;">'.implode('<br/>',$check->messages).'</p>';
									}
								}
								echo '</ol></div>';
							}

							if (count($this->validationResults->check_warnings) > 0)
							{
								echo '<h2 style="line-height:100px;color:#eea43a;">'.__("Warnings").'</h2>';
								echo '<ol>';
								foreach ($this->validationResults->check_warnings as $check)
								{
									echo '<h4 style="color:#666;margin-top:40px;"><li>'.$check->title.' : '.$check->hint.'</li></h4>';
									if (!empty($check->messages)) {
										echo '<p style="color:#eea43a;">'.implode('<br/>',$check->messages).'</p>';
									}
								}
								echo '</ol>';
							}

							echo '</div></div>';

		} else {
			
			$userMessage = UserMessage::getInstance();
			echo '<div class="container">'.UserMessage::getInstance()->getMessagesHtml().'</div>';
			?>
					<div class="jumbotron">
						<div class="container">
							<h1><?php echo 'Please send us a theme to check.'; ?></h1>
							<p><?php echo '<a href="'.TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"index.php")).'">Go to home page</a>&nbsp;' ?></p>
						</div>
					</div>

					<div class="container">
				<br/>
				<?php
				
		}
		 

		?>
		</div> <!-- /container --> 
		<?php
	}
}





