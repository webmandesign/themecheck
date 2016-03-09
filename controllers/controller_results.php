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
		/*if (isset($routeParts["ut"])) // unit tests
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
			$this->fileValidator->clean();
		} else	*/
		if (isset($routeParts["hash"])) // already uploaded file
		{
			$hash = $routeParts["hash"];
			$fileValidator= FileValidator::unserialize($hash);
			$checkfiles = scandir(TC_INCDIR.'/Checks');
			$youngestCheckTimestamp = 0;
			foreach($checkfiles as $f)
			{
				if ($f == '.' || $f == '..') continue;
				$m = filemtime(TC_INCDIR.'/Checks/'.$f);
				if($youngestCheckTimestamp < $m) $youngestCheckTimestamp = $m;
			}
			if ($fileValidator->themeInfo->validationDate < $youngestCheckTimestamp) // if checks changed, revalidate
			{
				$src_path = FileValidator::hashToPathUpload($hash);
				$themeInfo = FileValidator::prepareThemeInfo($src_path, $fileValidator->themeInfo->zipfilename, 'application/zip', false);
				$this->fileValidator = new FileValidator($themeInfo);
				$this->fileValidator->validate();	
				
				if (UserMessage::getCount(ERRORLEVEL_FATAL) == 0) // serialize only if no fatal errors
					$this->fileValidator->serialize(true);
				
				$themeInfo = $this->fileValidator->themeInfo;
				if (function_exists('stats')) stats($themeInfo);
				
				$this->fileValidator->cleanUnzippedFiles();
			} else  {
				$this->fileValidator = $fileValidator;
				$themeInfo = $this->fileValidator->themeInfo;
			}
			$this->validationResults = $this->fileValidator->getValidationResults(I18N::getCurLang());
		} else if (count($_FILES)>0 && isset($_FILES["file"]) && !empty($_FILES["file"]["name"])) // uploaded file
		{
			if(TC_ENVIRONMENT == "dev" || isset($_SESSION['token_'.$_POST['token']]))
			{
				unset($_SESSION['token_'.$_POST['token']]);
				$themeInfo = FileValidator::upload();
				if ($themeInfo)
				{
					$themeInfo->modificationDate = time(); // set modificationDate only at upload
					$this->fileValidator = new FileValidator($themeInfo);
					$this->fileValidator->validate();
					if (isset($_POST["donotstore"]) || UserMessage::getCount(ERRORLEVEL_FATAL) > 0)
					{
						$this->fileValidator->clean();
					} else {
						$this->fileValidator->serialize(true);
					}
					
					if (function_exists('stats')) stats($themeInfo);
		
					$this->validationResults = $this->fileValidator->getValidationResults(I18N::getCurLang());
					
					if (isset($_POST["donotstore"]))
						$this->inlinescripts[]= "ga('send', 'event', 'theme', 'submit', 'not stored');";
					else 
						$this->inlinescripts[]= "ga('send', 'event', 'theme', 'submit', 'stored');";
					
					$this->fileValidator->cleanUnzippedFiles();
				}
			} else {
				UserMessage::enqueue(__("Invalid form"), ERRORLEVEL_FATAL);
			}
		} else {
			UserMessage::enqueue(__("No file uploaded."), ERRORLEVEL_FATAL);
			$this->meta["title"] = __("No file uploaded");
			$this->meta["description"] = __("No file uploaded");
			$this->meta["robots"] = "noindex";
			return ;
		}

		if (!empty($themeInfo))
		{
			$prefix = '';
			if ($themeInfo->themetype == TT_JOOMLA)
			{
				if (strpos(strtolower($themeInfo->name), 'joomla') === false) $prefix .= " Joomla";
				if (strpos(strtolower($themeInfo->name), 'template') === false) $prefix .= " template";
				if ($prefix == " Joomla template") $prefix = __(" Joomla template");
				$this->meta["title"] = sprintf('%1$s%% : %2$s %3$s', htmlspecialchars($themeInfo->score), $prefix, htmlspecialchars($themeInfo->name));
				$this->meta["title"] = str_replace(__(" Joomla template"), __(" Joomla template"), $this->meta["title"]);
				$this->meta["description"] = sprintf(__("Security and code quality score of Joomla template %s."), htmlspecialchars($themeInfo->name));
				
				// avoid most similar title between language variations
				$this->meta["title"] = str_replace("joomla", "Joomla", $this->meta["title"]);
				$this->meta["title"] = str_replace("Joomla template", __("Joomla template"), $this->meta["title"]);
			} else {
				if (strpos(strtolower($themeInfo->name), 'wordpress') === false) $prefix .= " WordPress";
				if (strpos(strtolower($themeInfo->name), 'theme') === false) $prefix .= " theme";
				if ($prefix == " WordPress theme") $prefix = __(" WordPress theme");				
				
				$this->meta["title"] = sprintf('%1$s%% : %2$s %3$s', htmlspecialchars($themeInfo->score), $prefix, htmlspecialchars($themeInfo->name));
				$this->meta["description"] = sprintf(__("Security and code quality score of WordPress theme %s."), htmlspecialchars($themeInfo->name));
				
				// avoid most similar title between language variations
				$this->meta["title"] = str_replace("Wordpress", "WordPress", $this->meta["title"]);
				$this->meta["title"] = str_replace("wordpress", "WordPress", $this->meta["title"]);
				$this->meta["title"] = str_replace("WordPress theme", __("WordPress theme"), $this->meta["title"]);
				$this->meta["title"] = str_replace("theme", __("theme"), $this->meta["title"]);
			}
			
			if ($themeInfo->score<100.0)
			{
				if ($themeInfo->score > 95)
				{
					$this->meta["favicon"] = "favicon100";
				} else if ($themeInfo->score > 80)
				{
					$this->meta["favicon"] = "favicon95";
				} else {
					$this->meta["favicon"] = "favicon80";
				}
			}
			
			if ($themeInfo->isHigherVersion == 0) $this->meta["robots"] = "noindex";
		} else {
			$this->meta["title"] = __("Check results");
			$this->meta["description"] = __("Security and code quality score");
			$this->meta["robots"] = "noindex";
		}
		
		global $ExistingLangs;
		foreach ($ExistingLangs as $l)
		{
			if ($this->fileValidator)
			{
				$themeInfo = $this->fileValidator->themeInfo;
				if (!empty($themeInfo) && $themeInfo->serializable) {
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
      
?>
        <script type="text/javascript"> var page="results" </script>
<?php 
            if (UserMessage::getCount(ERRORLEVEL_FATAL) == 0 && $this->fileValidator)
            {
            ?>

			     
                   <section id="content">
                        <div class="container_result">	
                <?php
                $themeInfo = $this->fileValidator->themeInfo;
                
                $score = $this->fileValidator->themeInfo->score;
                
                $html =  getShield($themeInfo, I18N::getCurLang(), null, null, TC_HTTPDOMAIN.'/');
                
                
                if ($themeInfo->themetype == TT_JOOMLA) 
                    $cms =  "Joomla template ".htmlspecialchars($themeInfo->cmsVersion);
                else 
                   $cms = "WordPress ".htmlspecialchars($themeInfo->cmsVersion)." theme";
                
                if($themeInfo->isOpenSource)
                {
                    if(preg_match('/\bfr\b/i',$_SERVER['REQUEST_URI']))
                    {
                        $cms = $cms.' '.__('Free');
                    }
                    else 
                    {
                        $cms = __('Free').' '.$cms;
                    }
                }
                ?>
                            <div class="intro_result">
                                <div class="content_intro">
                                    <div class="icon_result">
                                        <?php echo $html; ?>
                                    </div>
                                    <div class="validation_results">
                                            <span class="text_validation_results"><?php echo __('Validation results'); ?></span>
                                    </div>                              
                                    <div class="line"><img src="<?php echo TC_HTTPDOMAIN;?>/img/images/line_content-home.png"/></div>
                                    <h1><?php echo $themeInfo->name; ?></h1>
                                   
                                    <div class="responsive_theme">
                                <?php 
                          
                                    if($themeInfo->layout == 3)
                                       echo '<span class="sprite icon_responsive"></span>';
                                ?>
                                     </div>
                                <?php
                                    
                                    if($themeInfo->isOpenSource)
                                       echo '<div class="open_source_theme"><span>'.$cms.'</span></div>'?>
                                     
                                     <div class="container_alerts">
                                       
                                <?php 
                                if($themeInfo->criticalCount > 0)
                                {
                                ?>
                                        <a href="#criticalAlerts">
                                            <div class="btn_action critical_alerts">					
                                                <label class="critical">
                                                    <?php  
                                                    if($themeInfo->criticalCount == 1)
                                                    {
                                                        echo $themeInfo->criticalCount.__(' CRITICAL ALERT');
                                                    }
                                                    else
                                                    {
                                                        echo $themeInfo->criticalCount.__(' CRITICAL ALERTS');
                                                    }
                                                     ?>
                                                <span class="sprite alert_icon_small"></span>
                                                </label>
                                            </div>
                                        </a>
                                <?php
                                }
                                if($themeInfo->warningsCount > 0)
                                {
                                ?>
                                        <a href="#warningAlerts">
                                            <div class="btn_action warnings_alerts">
                                                <label class="warnings">
                                                    <span class="sprite warning_icon_small"></span>
                                                    <?php 
                                                    if($themeInfo->warningsCount == 1)
                                                    {
                                                        echo __($themeInfo->warningsCount.'  WARNING');
                                                    }
                                                    else
                                                    {
                                                        echo __($themeInfo->warningsCount.'  WARNINGS');
                                                    }
                                                     ?>                
                                                </label>
                                            </div>
                                        </a>
                                <?php
                                }
                                ?>
                                    </div>
									<?php
									$userMessage = UserMessage::getInstance();
                        echo '<div class="title_error_validation" style="margin-top:30px">'.UserMessage::getInstance()->getMessagesHtml().'</div>';
                        ?>
                                      
                          
                                </div>
                                <div class="img_item">
                                        <img src="<?php if ($themeInfo->isNsfw) echo "/img/nsfw.png"; else echo TC_HTTPDOMAIN.'/'.$themeInfo->hash.'/thumbnail.png';?>">
                                </div>
                            </div>
                            
                             <div class="container_details_theme">     
                                <h1><?php echo $themeInfo->name; ?></h1>
                                <div class="open_source_theme">
                                        <span><?php echo $cms; ?></span>
                                </div>
                             
                            
                            <?php 
   
                            if ($this->fileValidator->themeInfo->isThemeForest)	
                            {  
                            ?>
                                <div class="content_list">
					<ul class="list_results">
						<li class="active"><a href="#standard" data-toggle="tab"><?php echo __("Themecheck rules")." : ";
							$score = $this->fileValidator->themeInfo->score;
							$color = 'ff1427';
							if ($score > 95) $color = 'cbd715';
							else if ($score > 80) $color = 'ff8214';
							echo '<span style="color:#'.$color.'">'.intval($score).' %</span>';?></a></li>
						<li><a href="#themeforest" data-toggle="tab"><?php echo __("Themeforest rules")." : ";
							$score = $this->fileValidator->themeInfo_themeforest->score;
							$color = 'ff1427';
							if ($score > 95) $color = 'cbd715';
							else if ($score > 80) $color = 'ff8214';
							echo '<span style="color:#'.$color.'">'.intval($score).' %</span>';?></a></li>
					</ul>
                                </div>
                                            <div class="container_desc_theme active" id="standard">
						<div class="tab-pane">
                                                    <?php $this->renderRulesSet($this->fileValidator->themeInfo, $this->validationResults);?>
                                                </div>
                                            </div>
                                         
                                            <div class="container_desc_theme" id="themeforest">
                                                <div class="tab-pane">
                                                    <p class="info_themeForest">
                                                    <?php 
                                                        echo __('This is a ThemeForest theme. Since Themeforest items are all checked by a human before they appear on their website, ThemeForest verification rules are more permissive than themecheck&#39;s and can give a better verification score ( <a href="http://support.envato.com/index.php?/Knowledgebase/Article/View/472/85/wordpress-theme-submission-requirements" rel="nofollow">Themeforest requirements</a> ).');
                                                     ?>
                                                </p>
                                               <?php echo $this->renderRulesSet($this->fileValidator->themeInfo_themeforest,$this->fileValidator->getValidationResultsThemeForest(I18N::getCurLang())); ?>
                                                </div>
                                            </div>
                                
                                           
                               
                            <?php
                            }
                            else
                            {
                                echo '  <div class="container_desc_theme active">';
                                echo '<div class="tab-pane">';
                                $this->renderRulesSet($this->fileValidator->themeInfo, $this->validationResults); 
                                echo '</div>';
                            }
                            
                            ?>
                                </div>       
     <!-- ok -->                               
                        
                            
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
        
        /* New Integration*/
       
        $('.iframe_result_theme .text_iframe').text(($("#select_banner_style option:selected").val()));//($("#select_banner_style option:selected").innerHtml);
        
        $('#select_banner_style').change(function(){
           
           var valueIframe = $("#select_banner_style option:selected").val(); 
            
           $('.iframe_result_theme .text_iframe').text(valueIframe);
        
        });
        
        
        
        if(!$('.first_part_desc').css('display'))
        {
            $('.second_part_desc').css('margin-left', 0);
        }
        
    </script>
    
                            <?php
                            
                            {
							
                            ?>
							
							
							<?php
							$history = new History();
							
							?>
                                        <div class="container_otherThemes">

                                            <div class="title_alert">
                                                <?php echo __('Other checked themes'); ?>
                                            </div>

                                            <div class="line">
                                               <img src="<?php echo TC_HTTPDOMAIN;?>/img/images/line_content-home.png"/>
                                            </div> 

                                            <div class="container_themes">
                            <?php
                                   
                                    $id = intval($history->getIdFromHash($themeInfo->hash)); 

									$cur_id = $id + 2;

									for ($i = 0; $i < 3; $i++)
                                    {
                                         //   if ($i == 0) $i--; 
                                            $r = $history->getFewInfoPreviousOne($cur_id); 
                                            if ($r !== false)
                                            {
													$cur_id = $r['id'];
													if ($cur_id == $id) {$i --; continue;}// not the current one
                                                    $html = '';
                                                    $namesanitized = $r['namesanitized'];
													$uriNameSeo = $r['uriNameSeo'];
													$uriNameSeoHigherVersion = $r['uriNameSeoHigherVersion'];
                                                    $themetype = $r['themetype'];
                                                    $score = $r['score'];
                                                    $themetype_text = sprintf(__("WordPress %s theme"),$r['cmsVersion']);
                                                    if ($themetype == TT_JOOMLA) $themetype_text = sprintf(__("Joomla %s template"), $r['cmsVersion']);
                                                    //$url = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"results", "hash"=>$r['hash'], "themetype"=>$themetype));
                                                    if (empty($uriNameSeo)) // legacy
														$url = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"results", "namesanitized"=>$namesanitized, "themetype"=>$themetype));
													else {
														if ($r['isHigherVersion'] == 1)
															$url = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"results", "uriNameSeo"=>$uriNameSeoHigherVersion, "themetype"=>$themetype));
														else
															$url = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"results", "uriNameSeo"=>$uriNameSeo, "themetype"=>$themetype));
													}
													
													$html .= '<div class="block_theme">';
                                                        $html .= '<div class="content_theme">';
                                                                $html .= '<div class="bg_theme">';
                                                                    $html .= '<a href="'.$url.'"><img src="'.TC_HTTPDOMAIN.'/'.$r['hash'].'/thumbnail.png" alt="" class="img_theme"/></a>';
                                                                $html .= '</div>';
                                                                $html .= '<div class="footer_theme">';                                      
                                                                    $html .= '<span class="ico_result">';
                                                                        $html .= getShield($r, I18N::getCurLang(), null, $url, TC_HTTPDOMAIN.'/');
                                                                    $html .= '</span>';                                      
                                                                    $html .= '<span class="separ_verti">';
                                                                        $html .= '<span class="ico_result"><img src="'.TC_HTTPDOMAIN.'/img/images/images_theme/separVerti_footer_theme.png" alt=""/></span>';
                                                                    $html .= '</span>';
                                                                    $html .= '<span class="info_theme">';
                                                                        $html .= '<p class="title_theme">'.$namesanitized.'</p>';
                                                                        $html .= '<p class="type_theme">'.$themetype_text.'</p>';
                                                                    $html .= '</span>';
                                                                    $html .= '<div class="container_iconCms">';
                                                                        $html .= '<div class="content_iconCms">';
                                                                        if($r['isOpenSource']) $html .= '<span class="sprite download"></span>';

                                                                        if($r['isTemplateMonster'])
                                                                        {
                                                                            $html .= '<span class="sprite template_monster"></span>';
                                                                            
                                                                        }
                                                                        else if ($r['isThemeForest'])
                                                                        {  
                                                                            $html .= '<span class="sprite theme_forest"></span>';
                                                                            
                                                                        }
                                                                        else if ($r['isCreativeMarket']) 
                                                                        {   
                                                                            $html .= '<span class="sprite creative_market"></span>';
                                                                        }

                                                                        $html .= '</div>';
                                                                    $html .= '</div>';
                                                                $html .= '</div>';
                                                        $html .= '</div>';
                                                    $html .= '</div>';
                                                    
                                                    echo $html;
                                            }
                                    }
                            ?>          
                                            </div>
                                            <div class="container_btnviewAll">
                                                    <div class="btn_action view_all">
<!--                                                            <a href="<?php //echo TC_HTTPDOMAIN.'/index.php#container_home'; ?>" style="text-decoration: none;">-->
                                                        <a href="<?php echo TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"home")).'#theme'; ?>" style="text-decoration: none;">
                                                            <label class="buttonViewAll">
                                                                    <span class="sprite arrow_grey"></span>
                                                                    <?php echo __('VIEW ALL THEMES'); ?>
                                                            </label></a>
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                </section>
                            <?php
                            }

                } 
                else if (UserMessage::getCount(ERRORLEVEL_FATAL) > 0) 
                {
                ?>
                        <section id="content">
                            <div class="container_result error_fatal">
                           
                                <div class="title_error_validation">
                                            <h1><?php printf(__("Validation results"));?></h1>
                                </div>
                                <div class="container_icon_validation">

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
                            </div>
                        </section>
            <?php
                }
                else 
                {

                        $userMessage = UserMessage::getInstance();
                        echo '<section id="content"><div class="container_result error_fatal"> <div class="title_error_validation">'.UserMessage::getInstance()->getMessagesHtml().'</div>';
                        ?>
                                      
                                    <h1><?php echo __('Please send us a theme to check.'); ?></h1>
                                    <p><?php echo '<a href="'.TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"index.php")).'">'.__("Go to home page").'</a>&nbsp;' ?></p>
                        <?php
                        echo '</div></section>';
                }

        }
        
        private function renderRulesSet($themeInfo, $validationResults)
        {
           
                                if(!isset($_POST["donotstore"]))
                                {
        ?>        
                                    
                                    <div class="first_part_desc">
                                        <span class="sprite template_responsive"></span>
                                        
                                        <div class="img_theme">
                                                <img src="<?php if ($themeInfo->isNsfw) echo "/img/nsfw.png"; else echo TC_HTTPDOMAIN.'/'.$themeInfo->hash.'/thumbnail.png';?>" alt=""/>
                                        </div>

                                        <div class="desc_results">
                                            <?php echo getShield($themeInfo, I18N::getCurLang(), null, null, TC_HTTPDOMAIN.'/');?>
                                            <div class="content_desc_results">

                                                <span class="img_separt"><img src="<?php echo TC_HTTPDOMAIN;?>/img/images/images_theme/separVerti_footer_theme.png" alt=""/></span>
<!--                                                <span class="title_desc_results"><?php //echo $themeInfo->name; ?>
                                                        <p class="desc_open_source"><?php //echo $cms; ?></p>
                                                </span>
                                                <div class="view_detail">
                                                        <input type="button" class="fake_input" name="viewDetail">
                                                        <label for="viewDetail" class="viewDetail">
                                                                <span class="sprite arrow_grey"></span>
                                                                VIEW DETAIL ON THEMECHECK.ORG
                                                        </label>
                                                </div>-->
                                            </div>
                                            <div class="iframe_result_theme">
                                                <span class="text_iframe"></span>
                                           </div>
                                        </div>
                              
                                       
                                        <div class="change_banner_style">
                                            <label><?php echo __('CHANGE BANNER STYLE :'); ?></label>
                                            <select id="select_banner_style">
                                                <option value="<?php echo htmlspecialchars('<iframe src="'.TC_HTTPDOMAIN.'/score.php?lang='.I18N::getCurLang().'&id='.$themeInfo->hash.'&size=big" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:240px; width:200px;" allowTransparency="true"></iframe>');?>" selected="selected" >
                                                <?php echo __("Big size icon : (height: 240px, width: 200px)");?></option>
                                                <option value="<?php echo htmlspecialchars('<iframe src="'.TC_HTTPDOMAIN.'/score.php?id='.$themeInfo->hash.'" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:80px; width:67px;" allowTransparency="true"></iframe>');?>">
                                                <?php echo __("Medium size icon (default) : (height: 80px, width: 67px)");?></option>
                                                <option value="<?php echo htmlspecialchars('<iframe src="'.TC_HTTPDOMAIN.'/score.php?id='.$themeInfo->hash.'&size=small" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:40px; width:33px;" allowTransparency="true"></iframe>');?>">
                                                <?php echo __("Small size icon : (height: 40px, width: 33px)");?></option>
<!--                                                <option value="<?php //echo htmlspecialchars('<iframe src="'.TC_HTTPDOMAIN.'/score.php?lang='.I18N::getCurLang().'&id='.$themeInfo->hash.'" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:80px; width:67px;" allowTransparency="true"></iframe>');?>">
                                                <?php //echo htmlspecialchars(__("You can switch language with <strong>lang</strong> parameter in iframe's url. So far <strong>fr</strong> and <strong>en</strong> are supported. Default value is <strong>en</strong>."));?></option>-->
                                            </select>
                                        </div>
                                    </div>
                            <?php
                                }   
                            ?>
                                    
                                    <div class="second_part_desc">
                                        <ul>
                                 <?php 
                                                        
                                    $characteristics = array();
                                    //$characteristics[] = array(__("Theme name"), htmlspecialchars($themeInfo->name));
                                    if ($themeInfo->themetype == TT_WORDPRESS) 	
                                            if (empty($themeInfo->cmsVersion)) $characteristics[] = array(__("Theme type"), __("WordPress theme"));
                                            else $characteristics[] = array(__("Theme type"), __("WordPress theme").' '.$themeInfo->cmsVersion);
                                    else if ($themeInfo->themetype == TT_WORDPRESS_CHILD) 	{
                                            if (empty($themeInfo->cmsVersion)) $characteristics[] = array(__("Theme type"), __("WordPress child theme"));
                                            else $characteristics[] = array(__("Theme type"), __("WordPress child theme").' '.$themeInfo->cmsVersion);
                                            if (!empty($themeInfo->parentName)){
                                                    $url = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"results", "namesanitized"=>$themeInfo->parentUriNameSeo , "themetype"=>$themeInfo->parentThemeType ));
                                                    
													if (empty($themeInfo->parentUriNameSeo)) // legacy
														$url = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"results", "namesanitized"=>$themeInfo->parentNamesanitized, "themetype"=>$themeInfo->parentThemeType));
													else {
														if ($themeInfo->parentIsHigherVersion == 1)
															$url = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"results", "uriNameSeo"=>$themeInfo->parentUriNameSeo, "themetype"=>$themeInfo->parentThemeType));
														else
															$url = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"results", "uriNameSeo"=>$themeInfo->parentUriNameSeoHigherVersion, "themetype"=>$themeInfo->parentThemeType));
													}
													
													$characteristics[] = array(__("Parent theme name"), "<a href='".$url."'>".htmlspecialchars($themeInfo->parentName)."</a>");
                                            }
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
                                            if (!empty($themeInfo->licenseText)) $characteristics[] = array(__("License"), '<a rel="license" href="'.$themeInfo->licenseUri.'" rel="nofollow">'.ThemeInfo::getLicenseName($themeInfo->license).'</a>'.'<br>'.htmlspecialchars($themeInfo->licenseText));
                                            else $characteristics[] = array(__("License"), '<a rel="license" href="'.$themeInfo->licenseUri.'" rel="nofollow">'.ThemeInfo::getLicenseName($themeInfo->license).'</a>');
                                    $characteristics[] = array(__("Files included"), htmlspecialchars($themeInfo->filesIncluded, defined('ENT_HTML5')?ENT_QUOTES | ENT_HTML5:ENT_QUOTES));
                                    if (!empty($themeInfo->themeUri)) {
                                            if (strpos($themeInfo->themeUri,'themeforest.net')!==false)
                                                    $characteristics[] = array(__("Theme URI"), '<a href="'.$themeInfo->themeUri.'?ref=themecheck">'.htmlspecialchars($themeInfo->themeUri).'</a>');
                                            else 
                                                    $characteristics[] = array(__("Theme URI"), '<a href="'.$themeInfo->themeUri.'">'.htmlspecialchars($themeInfo->themeUri).'</a>');
                                    }
                                    if (!empty($themeInfo->version)) $characteristics[] = array(__("Version"), htmlspecialchars($themeInfo->version));
                                    if (!empty($themeInfo->authorUri)) $characteristics[] = array(__("Author URI"), '<a rel="author" href="'.$themeInfo->authorUri.'">'.htmlspecialchars($themeInfo->authorUri).'</a>');
                                    if (!empty($themeInfo->tags))$characteristics[] = array(__("Tags"), htmlspecialchars($themeInfo->tags));
                                    /*if (!empty($themeInfo->layout)) {
                                            if ($themeInfo->layout == 1) $characteristics[] = array(__("Layout"), __("Fixed"));
                                            else if ($themeInfo->layout == 2) $characteristics[] = array(__("Layout"), __("Fluid"));
                                            else if ($themeInfo->layout == 3) $characteristics[] = array(__("Layout"), __("Responsive"));
                                    }*/
                                   // if (!empty($themeInfo->copyright))$characteristics[] = array(__("Copyright"), htmlspecialchars($themeInfo->copyright));
                                    if (!empty($themeInfo->creationDate))$characteristics[] = array(__("Creation date"), date("Y-m-d", $themeInfo->creationDate));
                                    if (!empty($themeInfo->modificationDate))$characteristics[] = array(__("Last file update"), date("Y-m-d", $themeInfo->modificationDate));
                                    if (!empty($themeInfo->validationDate))$characteristics[] = array(__("Last validation"), date("Y-m-d H:i", $themeInfo->validationDate));
									
									$history = new History();
									$otherVersions = $history->getOtherVersions($themeInfo->hash, $themeInfo->themedir, $themeInfo->themetype);
									if (!empty($otherVersions))
									{	
										$data_array = array();
										foreach($otherVersions as $row)
										{
										//	$href = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"results", "uriNameSeo"=>$row[uriNameSeo], "themetype"=>$row[themetype]));
											if ($row['isHigherVersion'] == 1)
												$href = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"results", "uriNameSeo"=>$row["uriNameSeoHigherVersion"], "themetype"=>$row["themetype"]));
											else
												$href = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"results", "uriNameSeo"=>$row["uriNameSeo"], "themetype"=>$row["themetype"]));
											$versiondata = array("href"=>$href,"version"=>$row["version"], "score"=>$row["score"]);
											
											$data_array[] = $versiondata;
											//echo '<p style="margin:20px"><a href="'.$href.'">'.htmlspecialchars($row["name"]).', version '.htmlspecialchars($row["version"]).' : '.$row["score"].'</a></p>';
										}
										$characteristics[] = array(__("Other versions"), $data_array);
									}
                    $i = 1;
            
                    foreach ($characteristics as $c)
                    {   
                        $css_class = '';
                        
                        if($i%2 != 0)
                        {
                            $css_class = 'first_line';
                        }
                        else 
                        {
                            $css_class = 'second_line';
                        }

						if ($c[0] == __("Other versions"))
						{
							echo '<li class="'.$css_class.'"><span class="first_col">'.strtoupper($c[0]).'</span><span class="second_col">';
							$data_array = $c[1];
							foreach($data_array as $versiondata)
							{
								echo '<p style="margin-bottom:10px"><a href="'.$versiondata["href"].'">'.htmlspecialchars($versiondata["version"].' : '.$versiondata["score"].'%').'</a></p>';
							}
							echo '</span></li>';
						} else
							echo '<li class="'.$css_class.'"><span class="first_col">'.strtoupper($c[0]).'</span><span class="second_col">'.$c[1].'</span></li>';

                        $i++;
                    }
                                    ?>
 
                                        </ul>
                                    </div>
                    
                      
                                <?php
                                    if(!isset($_POST["donotstore"]))
                                    {
                                    
                                        if (!empty($themeInfo->isOpenSource))
                                        {

                                        ?>
                                            <div class="theme_is_open"><?php echo __('This theme is open source.'); ?></div>

                                            <div class="button_download">
                                                <a href="<?php echo TC_HTTPDOMAIN.'/download?h='.$themeInfo->hash.'" onclick="trackDL(\''.$themeInfo->name.'\')"'; ?>" style="text-decoration: none;">
                                                <label for="buttonDownload" class="buttonDownload">
                                                        <span class="sprite downloadBold"></span>
                                                        <?php echo __('DOWNLOAD'); ?>
                                                    </label>
                                                </a>
                                            </div>
                                        <?php 
                                        }
                                        else 
                                        {
                                            ?>
                                                 <div class="theme_commercial">
                                            <?php

                                            if ($themeInfo->isTemplateMonster || $themeInfo->isTemplateMonster || $themeInfo->isTemplateMonster) 
                                            {
                                                echo  __("This theme is proprietary. Themecheck doesn't distribute commercial themes.");
                                            } 
                                            else 
                                            {
                                                echo __("This theme seems to be proprietary. Themecheck doesn't distribute commercial themes.");
                                            }

                                            ?>
                                                 </div>
                                            <?php
                                        }
                                    }
                                    else
                                    {
                                        echo '<span class="text_donostore">'.__("These results were not saved on themecheck.org servers and will be lost when you quit this page.").'</span>';
                                    }
                                ?>
                           
                                <div id="criticalAlerts"></div>
                         

                   
                            <?php
                            $search = __("Line");
							$pattern = "/".$search."/i";
                                                           
                            if (count($validationResults->check_critical) > 0)
                            {
                                ?>
                                    <div class="container_critical">      
                                        <div class="sprite alert_icon"></div>
                                            <div class="title_alert"><?php echo __('Critical alerts'); ?></div>
                                            <div class="line">
                                                    <img src="<?php echo TC_HTTPDOMAIN; ?>/img/images/line_content-home.png"/>
                                            </div>
                                            <div class="descript_alert">
                                              <ol>
                                <?php   
                                                              
                                foreach ($validationResults->check_critical as $check)
                                {     
                                ?>
									<li><span class="message_alert"><strong><?php echo $check->title; ?> : </strong><?php echo $check->hint; ?></span>
									<?php
										if (!empty($check->messages)) 
										{                               
											foreach($check->messages as $checkMessage)
											{
												if(preg_match($pattern, $checkMessage))
												{
												   $nameFile = substr($checkMessage, 0, strpos($checkMessage, $search));
												   $ligneFile = substr($checkMessage, strpos($checkMessage, $search));
												   echo '<span class="info_alert">'.$nameFile.'</span><span class="line_default">'.$ligneFile.'</span>';
												}    
												else
												{
													echo $checkMessage;
												}
											  
											}
										}
									echo '<span class="ligne_warning"></span></li>';
                                }
                                
                                         echo '</ol>';
                                   echo '</div>';           
                                echo '</div>';
                            }
                            
                            echo '<div id="warningAlerts"></div>';
                
                            if (count($validationResults->check_warnings) > 0)
                            {
                                ?>
                                    <div class="container_warning">      
                                        <div class="sprite warning_icon"></div>
                                            <div class="title_alert"><?php echo __('Warning'); ?></div>
                                            <div class="line">
                                                    <img src="<?php echo TC_HTTPDOMAIN;?>/img/images/line_content-home.png"/>
                                            </div>
                                            <div class="descript_alert">
                                              <ol>
                                <?php                
                                foreach ($validationResults->check_warnings as $check)
                                {    
                                ?>
									<li><span class="message_alert"><strong><?php echo $check->title.' : </strong>'.$check->hint.'</span>';
										if (!empty($check->messages)) 
										{   
											$checkMessage = "";         
											foreach($check->messages as $checkMessage)
											{
												if(preg_match($pattern, $checkMessage))
												{
												   $nameFile = substr($checkMessage, 0, strpos($checkMessage, $search));
												   $ligneFile = substr($checkMessage, strpos($checkMessage, $search));
												   echo '<span class="info_alert">'.$nameFile.'</span><span class="line_default">'.$ligneFile.'</span>';
												}   
												else
												{
												   echo '<span class="info_alert">'.$checkMessage.'</span>';
												}
											}
										}
									echo '<span class="ligne_warning"></span></li>';
                                }
                                
                                        echo '</ol>';
                                    echo '</div>';
                                echo '</div>';
                            }

                            if (count($validationResults->check_info) > 0)
                            {
                                 ?>
                                    <div class="container_tipOff">      
                                        <div class="sprite tipOff_icon"></div>
                                            <div class="title_alert"><?php echo __('Tip-off'); ?></div>
                                            <div class="line">
                                                    <img src="<?php echo TC_HTTPDOMAIN;?>/img/images/line_content-home.png"/>
                                            </div>
                                            <div class="descript_alert">
                                              <ol>
                                <?php                
                                foreach ($validationResults->check_info as $check)
                                {         
                                ?>
									<li><span class="message_alert"><strong><?php echo $check->title.' : </strong>'.$check->hint.'</span>';
										if (!empty($check->messages)) 
										{                               
											foreach($check->messages as $checkMessage)
											{
												if(preg_match($pattern, $checkMessage))
												{
												   $nameFile = substr($checkMessage, 0, strpos($checkMessage, $search));
												   $ligneFile = substr($checkMessage, strpos($checkMessage, $search));
												   echo '<span class="info_alert">'.$nameFile.'</span><span class="line_default">'.$ligneFile.'</span>';
												}    
												else
												{
												   echo '<span class="info_alert">'.$checkMessage.'</span>';
												}
											}
										}
									echo '<span class="ligne_warning"></span></li>';
                                }
                                
                                        echo '</ol>';
                                   echo '</div>';
                                echo '</div>';
                            }
                                                 
                              
        }
}





