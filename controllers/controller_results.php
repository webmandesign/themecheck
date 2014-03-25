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
		if (isset($routeParts["ut"])) // unit tests
		{
			$path_item = TC_ROOTDIR.'/include/unittests/';
			$filename = urldecode($routeParts["ut"]);
			if (!(substr($filename, -4) == ".zip" && file_exists($path_item.$filename)))
			{
				echo $path_item.$filename.' does not exist. Cannot continue';die;
			}

			$themeInfo = FileValidator::prepareThemeInfo($path_item.$filename, $filename, 'application/zip', false);

			$this->fileValidator = new FileValidator($themeInfo);
			$this->fileValidator->validate();	

			$this->validationResults = $this->fileValidator->getValidationResults(I18N::getCurLang());
		
		} else	if (isset($routeParts["hash"])) // already uploaded file
		{
			$hash = $routeParts["hash"];
			$this->fileValidator = FileValidator::unserialize($hash);

			$checkfiles = scandir(TC_INCDIR.'/Checks');
			$youngestCheckTimestamp = 0;
			foreach($checkfiles as $f)
			{
				if ($f == '.' || $f == '..') continue;
				$m = filemtime(TC_INCDIR.'/Checks/'.$f);
				if($youngestCheckTimestamp < $m) $youngestCheckTimestamp = $m;
			}
			if ($this->fileValidator->themeInfo->validationDate < $youngestCheckTimestamp) // if checks changed, revalidate
			{
				$this->fileValidator->validate();	
				if (UserMessage::getCount(ERRORLEVEL_FATAL) == 0) // serialize only if no fatal errors
				$this->fileValidator->serialize(true);
			}
			$this->validationResults = $this->fileValidator->getValidationResults(I18N::getCurLang());
		} else if (count($_FILES)>0 && isset($_FILES["file"]) && !empty($_FILES["file"]["name"])) // uploaded file
		{
			$themeInfo = FileValidator::upload();
			if ($themeInfo)
			{
				$this->fileValidator = new FileValidator($themeInfo);
				$this->fileValidator->validate();	
				
				if (isset($_POST["donotstore"]) || UserMessage::getCount(ERRORLEVEL_FATAL) > 0)
				{
					$this->fileValidator->clean();
				} else {
					$this->fileValidator->serialize();
				}
				
				$this->validationResults = $this->fileValidator->getValidationResults(I18N::getCurLang());
			}
		} else {
			UserMessage::enqueue(__("No file uploaded."), ERRORLEVEL_FATAL);
			$this->meta["title"] = __("No file uploaded");
			$this->meta["description"] = __("No file uploaded");
			return ;
		}
		
		if (!empty($themeinfo))
		{
			if ($themeinfo->themetype == TT_JOOMLA)
			{
				$this->meta["title"] = sprintf(__("Validation results for Joomla template %s"), htmlspecialchars($themeInfo->name));
				$this->meta["description"] = sprintf(__("Security and code quality validation score of Joomla template %s."), htmlspecialchars($themeInfo->name));
			} else {
				$this->meta["title"] = sprintf(__("Validation results for Wordpress theme %s"), htmlspecialchars($themeInfo->name));
				$this->meta["description"] = sprintf(__("Security and code quality validation score of Wordpress theme %s."), htmlspecialchars($themeInfo->name));
			}
		} else {
			$this->meta["title"] = __("Validation results");
			$this->meta["description"] = __("Validation results");
		}
		
		global $ExistingLangs;
		foreach ($ExistingLangs as $l)
		{
			if ($this->fileValidator)
			{
				$themeInfo = $this->fileValidator->themeInfo;
				if (!empty($themeInfo) && $themeInfo->serializable && USE_DB) {
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
		if (UserMessage::getCount(ERRORLEVEL_FATAL) == 0 && $this->fileValidator)
		{
		?>
				<div class="container">
				<br/>
				<?php
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
				
								$img = 'shieldperfect240.png';
								$color = 'a6af11';
								$text = sprintf(__('Validation score : %s%%'),intval($themeInfo->score));
								if ($themeInfo->score<100.0)
								{
									if ($themeInfo->score > 95)
									{
										$img = "shieldgreen240.png";
										$color = 'cbd715';
									} else if ($themeInfo->score > 80)
									{
										$img = "shieldorange240.png";
										$color = 'ff8214';
									} else {
										$img = "shieldred240.png";
										$color = 'ff1427';
									}
			
									if ($themeInfo->criticalCount > 0)
									{
										$text = sprintf(__('Validation score : %s%% (%s critical alerts)'),intval($themeInfo->score),$themeInfo->criticalCount);
									} else {
										$text = sprintf(__('Validation score : %s%%'),intval($themeInfo->score));
									}
								}
								?>
								<div class="shield1" style="width:201px;height:240px;background-image:url(<?php echo TC_HTTPDOMAIN;?>/img/<?php echo $img;?>);" title="<?php echo $text;?>">
										<div class="shield2" style="color:#<?php echo $color;?>;">			
									<?php if ($themeInfo->score<100.0) echo intval($themeInfo->score); ?>
										</div>	
								</div>
								<?php
								echo '<p "color:#'.$color.'">'.__("validation score").' : '.intval($themeInfo->score).' %</p>';
								echo '<p>'.sprintf(__("%s critical alerts. %s warnings."),$themeInfo->criticalCount, $themeInfo->warningsCount).'</p>';
								
								if (!isset($_POST["donotstore"]) && UserMessage::getCount(ERRORLEVEL_FATAL) == 0) {
									?>
									<br/><br/>
									<?php echo __("Share this page with the following link :");?>
									<p>
									<?php 
										echo '<a href="'.$this->samepage_i18n[I18N::getCurLang()].'">'.$this->samepage_i18n[I18N::getCurLang()].'</a>';
									?>
									</p>
									<?php echo __("Display this score on your website with the following HTML code that links to this page :");?>
									<pre style="font-size:11px;width:70%;margin:auto;"><?php echo htmlspecialchars('<iframe src="'.TC_HTTPDOMAIN.'/score.php?lang='.I18N::getCurLang().'&id='.$themeInfo->hash.'&size=big" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:240px; width:200px;" allowTransparency="true"></iframe>');?></pre>		
									
									<button class="btn" data-toggle="collapse" data-target="#moreembedoptions" style="height:20px;padding:1px;font-size:12px">more options</button>
									<div id="moreembedoptions" class="collapse">
									<?php displayShield($themeInfo, I18N::getCurLang(), 80, '#', TC_HTTPDOMAIN.'/'); ?>
									<?php echo __("Medium size icon (default) :");?>
									<pre style="font-size:11px;width:70%;margin:auto;"><?php echo htmlspecialchars('<iframe src="'.TC_HTTPDOMAIN.'/score.php?id='.$themeInfo->hash.'" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:80px; width:67px;" allowTransparency="true"></iframe>');?></pre>	
									<?php displayShield($themeInfo, I18N::getCurLang(), 40, '#', TC_HTTPDOMAIN.'/'); ?>
									<?php echo __("Small size icon :");?>
									<pre style="font-size:11px;width:70%;margin:auto;"><?php echo htmlspecialchars('<iframe src="'.TC_HTTPDOMAIN.'/score.php?id='.$themeInfo->hash.'" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:80px; width:40px;" allowTransparency="true"></iframe>');?></pre>	
									<?php echo htmlspecialchars(__("You can switch language with <strong>lang</strong> parameter in iframe's url. So far <strong>fr</strong> and <strong>en</strong> are supported. Default value is <strong>en</strong>."));?>
									<pre style="font-size:11px;width:70%;margin:auto;"><?php echo htmlspecialchars('<iframe src="'.TC_HTTPDOMAIN.'/score.php?lang='.I18N::getCurLang().'&id='.$themeInfo->hash.'" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:80px; width:67px;" allowTransparency="true"></iframe>');?></pre>	
									</div>
									<br/>
								<?php } else 
								{
									echo '<br>'.__("These results were not saved on themecheck.org servers and will be lost when you quit this page.");
								}?>
						</div>
						<div class="col-md-4" style="border-radius: 3px;background:#444; overflow:hidden; font-size:12px">
							<?php 
							$characteristics = array();
							$characteristics[] = array(__("Theme name"), htmlspecialchars($themeInfo->name));
							if ($themeInfo->themetype == TT_WORDPRESS) 	
								if (empty($themeInfo->cmsVersion)) $characteristics[] = array(__("Theme type"), __("Wordpress theme"));
								else $characteristics[] = array(__("Theme type"), __("Wordpress theme").' '.$themeInfo->cmsVersion);
							else if ($themeInfo->themetype == TT_WORDPRESS_CHILD) 	{
								if (empty($themeInfo->cmsVersion)) $characteristics[] = array(__("Theme type"), __("Wordpress child theme"));
								else $characteristics[] = array(__("Theme type"), __("Wordpress child theme").' '.$themeInfo->cmsVersion);
								if (!empty($themeInfo->parentName))$characteristics[] = array(__("Parent theme name"), htmlspecialchars($themeInfo->parentName));
							}
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
								else $characteristics[] = array(__("License"), '<a href="'.$themeInfo->licenseUri.'" rel="nofollow">'.ThemeInfo::getLicenseName($themeInfo->license).'</a>');
							$characteristics[] = array(__("Files included"), htmlspecialchars($themeInfo->filesIncluded, defined('ENT_HTML5')?ENT_QUOTES | ENT_HTML5:ENT_QUOTES));
							if (!empty($themeInfo->themeUri)) $characteristics[] = array(__("Theme URI"), '<a href="'.htmlspecialchars($themeInfo->themeUri).'" rel="nofollow">'.htmlspecialchars($themeInfo->themeUri).'</a>');
							if (!empty($themeInfo->version)) $characteristics[] = array(__("Version"), htmlspecialchars($themeInfo->version));
							if (!empty($themeInfo->authorUri)) $characteristics[] = array(__("Author URI"), htmlspecialchars($themeInfo->authorUri));
							if (!empty($themeInfo->tags))$characteristics[] = array(__("Tags"), htmlspecialchars($themeInfo->tags));
							if (!empty($themeInfo->copyright))$characteristics[] = array(__("Copyright"), htmlspecialchars($themeInfo->copyright));
							if (!empty($themeInfo->creationDate))$characteristics[] = array(__("Creation date"), date("Y-m-d", $themeInfo->creationDate));
							if (!empty($themeInfo->modificationDate))$characteristics[] = array(__("Last file update"), date("Y-m-d", $themeInfo->modificationDate));
							if (!empty($themeInfo->validationDate))$characteristics[] = array(__("Last validation"), date("Y-m-d H:i", $themeInfo->validationDate));
							
							foreach ($characteristics as $c)
							{
								echo '<p style="text-transform:uppercase;margin:0;margin-top:10px;">'.$c[0].'</p><span style="color:#CCC">'.$c[1].'</span>';
							}
							?>
								
						</div>
					</div>

							<?php
							echo '<div class="row"><div class="col-md-12">';
							
							if (count($this->validationResults->check_critical) > 0)
							{
								echo '<h2 style="line-height:100px;color:#D00;">'.__("Critical alerts").'</h2>';
								echo '<ol>';
								foreach ($this->validationResults->check_critical as $check)
								{
									echo '<h4 style="color:#666;margin-top:40px;"><li>'.$check->title.' : '.$check->hint.'</li></h4>';
									if (!empty($check->messages)) {
										echo '<p style="color:#c94b4b;">'.implode('<br/>',$check->messages).'</p>';
									}
								}
								echo '</ol>';
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
?>
    <div id="disqus_thread" style="margin-top:60px"></div>
    <script type="text/javascript">
        /* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
        var disqus_shortname = 'themecheck'; // required: replace example with your forum shortname
				var disqus_url = '<?php echo $this->samepage_i18n[I18N::getCurLang()];?>';
				
        /* * * DON'T EDIT BELOW THIS LINE * * */
        (function() {
            var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
            dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
            (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
        })();
    </script>
    <noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript" rel="nofollow">comments powered by Disqus.</a></noscript>
    <a href="http://disqus.com" class="dsq-brlink" rel="nofollow">comments powered by <span class="logo-disqus">Disqus</span></a>
    
<?php
if (USE_DB)
{
	echo '<hr>';
	echo '<h2 style="line-height:100px;color:#888">'.__("Other files checked around the same date").'</h2>';
	$history = new History();
	$id = intval($history->getIdFromHash($themeInfo->hash));
	for ($i = 1; $i > -4; $i--)
	{
		if ($i == 0) $i--; // not the current one
		$r = $history->getFewInfo($id + $i);
		if ($r !== false)
		{
			$html = '';
			$namesanitized = $r['namesanitized'];
			$themetype = $r['themetype'];
			$score = $r['score'];
			$themetype_text = sprintf(__("Wordpress %s theme"),$r['cmsVersion']);
			if ($themetype == TT_JOOMLA) $themetype_text = sprintf(__("Joomla %s template"), $r['cmsVersion']);
			$url = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"results", "namesanitized"=>$namesanitized, "themetype"=>$themetype));
			$html .= '<div style="width:220px;height:220px;display:inline-block;text-align:center;margin:10px 32px">';
			$html .= '<a href="'.$url.'" ><img style="box-shadow: 0 0 20px #DDD;" src="'.TC_HTTPDOMAIN.'/'.$r['hash'].'/thumbnail.png"></a>';
			$html .= '<div style="width:220px;height:40px;margin:3px 0 0 0;text-align:left;line-height:18px;padding:0 7px;overflow:hidden;white-space:nowrap;font-size : 12px;">';
			$html .= '<div style="width:33px;height:40px;float:right;">';
			$html .= getShield($r, I18N::getCurLang(), 40, $url, TC_HTTPDOMAIN.'/');
			$html .= '</div>';
			$html .= htmlspecialchars($r['name']).'<br/><span style="font-size : 12px; color:#AAA;">'.$themetype_text.'</span>';
			$html .= '</div>';
			$html .= '</div>';
			
			echo $html;
		}
	}
	//	$themetype = $r['themetype'];
	//	$score = $r['score'];
}

							echo '</div>';

		} else if (UserMessage::getCount(ERRORLEVEL_FATAL) > 0) {
		?>
			<div class="container">
				<br/>
					<div class="row text-center">
							<h1><?php printf(__("Validation results"));?></h1>
					</div>
					<div class="row text-center" style="color:#888;font-weight:normal;margin:30px 0 0 0;background:#F8F8F8;border-radius: 3px;">

						<br/>
						<?php
								$img = 'shieldred240.png';
								$color = 'ff1418';
								$text = __("Validation score : 0 %");
								
								?>
								<div class="shield1" style="width:201px;height:240px;background-image:url(<?php echo TC_HTTPDOMAIN;?>/img/<?php echo $img;?>);" title="<?php echo $text;?>">
										<div class="shield2" style="color:#<?php echo $color;?>;">0</div>	
								</div>
								<?php
								echo '<p "color:#'.$color.'">'.__("Validation score : 0 %").'</p>';
								$userMessage = UserMessage::getInstance();
								echo '<div style="margin:5% 10%">'.__("Fatal error").'<br>'.UserMessage::getInstance()->getMessagesHtml().'</div>';
								?>
								<p><?php echo '<a href="'.TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"index.php")).'">'.__("Go to home page to submit a new version").'</a>'; ?></p>
								<br/>
					</div>
<?php
		} else {
			
			$userMessage = UserMessage::getInstance();
			echo '<div class="container">'.UserMessage::getInstance()->getMessagesHtml().'</div>';
			?>
					<div class="jumbotron">
						<div class="container">
							<h1><?php echo __('Please send us a theme to check.'); ?></h1>
							<p><?php echo '<a href="'.TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"index.php")).'">'.__("Go to home page").'</a>&nbsp;' ?></p>
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





