<?php
namespace ThemeCheck;
require_once TC_INCDIR."/FileValidator.php";
require_once TC_INCDIR."/Helpers.php";
require_once TC_INCDIR."/shield.php";
include_once (TC_ROOTDIR.'/DB/History.php');

class Controller_home
{
	public $meta = array();
	public $samepage_i18n = array();
	
	public function __construct()
	{
	}
	
	public function prepare()
	{
		$l =I18N::getCurLang();
		$this->meta["title"] = __("The WordPress Themes Verification Service");
		$this->meta["description"] = __("A free service that checks WordPress themes for security and code quality.");
		global $ExistingLangs;
		foreach ($ExistingLangs as $l)
		{
			$this->samepage_i18n[$l] = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>$l, "phpfile"=>"index.php"));
		}
		
		if ($l == 'en')
		{
			$this->abtesting_code = "<!-- Google Analytics Content Experiment code -->
<script>function utmx_section(){}function utmx(){}(function(){var
k='99548996-0',d=document,l=d.location,c=d.cookie;
if(l.search.indexOf('utm_expid='+k)>0)return;
function f(n){if(c){var i=c.indexOf(n+'=');if(i>-1){var j=c.
indexOf(';',i);return escape(c.substring(i+n.length+1,j<0?c.
length:j))}}}var x=f('__utmx'),xx=f('__utmxx'),h=l.hash;d.write(
'<sc'+'ript src=\"'+'http'+(l.protocol=='https:'?'s://ssl':
'://www')+'.google-analytics.com/ga_exp.js?'+'utmxkey='+k+
'&utmx='+(x?x:'')+'&utmxx='+(xx?xx:'')+'&utmxtime='+new Date().
valueOf()+(h?'&utmxhash='+escape(h.substr(1)):'')+
'\" type=\"text/javascript\" charset=\"utf-8\"><\/sc'+'ript>')})();
</script><script>utmx('url','A/B');</script>
<!-- End of Google Analytics Content Experiment code -->
";
		}
	}
	
	private function getThumb($themeInfo)
	{
		$html = '';
		$namesanitized = $themeInfo['namesanitized'];
		$uriNameSeo = $themeInfo['uriNameSeo'];
		$uriNameSeoHigherVersion = $themeInfo['uriNameSeoHigherVersion'];
		$themetype = $themeInfo['themetype'];
		$score = $themeInfo['score'];
		$cmsVersion = $themeInfo['cmsVersion'];
		$themetype_text = '';
		//sprintf(__("Wordpress %s theme"),$themeInfo['cmsVersion']);
	//	if ($themetype == TT_JOOMLA) $themetype_text = sprintf(__("Joomla %s template"), $themeInfo['cmsVersion']);
		
		if ($themetype == TT_WORDPRESS) 	
			if (empty($cmsVersion)) $themetype_text = __("WordPress theme");
			else $themetype_text = sprintf(__("WordPress %s theme"), $cmsVersion);
		else if ($themetype == TT_WORDPRESS_CHILD)
			if (empty($cmsVersion)) $themetype_text = __("WordPress child theme");
			else $themetype_text = sprintf(__("WordPress %s child theme"), $cmsVersion);
		else if ($themetype == TT_JOOMLA)
			if (empty($cmsVersion)) $themetype_text = __("Joomla template");
			else $themetype_text = sprintf(__("Joomla %s template"), $cmsVersion);		
		
		if (empty($uriNameSeo)) // legacy
			$url = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"results", "namesanitized"=>$namesanitized, "themetype"=>$themetype));
		else {
			if ($themeInfo['isHigherVersion'] == 1)
				$url = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"results", "uriNameSeo"=>$uriNameSeoHigherVersion, "themetype"=>$themetype));
			else
				$url = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"results", "uriNameSeo"=>$uriNameSeo, "themetype"=>$themetype));
		}
		
		$imgSize = getimagesize(TC_HTTPDOMAIN.'/'.$themeInfo['hash'].'/thumbnail.png');
		$imgWidth = $imgSize[0];
		$imgHeight = $imgSize[1];
		
		$html .= '<div class="block_theme" data-id="'.$themeInfo['id'].'">';
			$html .= '<div class="content_theme">';
				$html .= '<div class="bg_theme">';
				if ($themeInfo['isNsfw']==true) 
				{
					$html .= '<a href="'.$url.'" ><img src="/img/nsfw.png"></a>';
				}
				else 
				{
					$html .= '<a href="'.$url.'" ><img  src="'.TC_HTTPDOMAIN.'/'.$themeInfo['hash'].'/thumbnail.png"></a>';
				}
				$html .= '</div>';
					
				$html .= '<div class="footer_theme">';
					$html .= '<span class="ico_result">';
                    $html .= getShield($themeInfo, I18N::getCurLang(), null, $url, TC_HTTPDOMAIN.'/');
					$html .='</span>';
					$html .= '<a href="'.$url.'" ><span class="separ_verti">';
					$html .= '<img src= "'.TC_HTTPDOMAIN.'/img/images/images_theme/separVerti_footer_theme.png" alt="">';
					$html .='</span></a>';
					$html .= '<a href="'.$url.'" ><span class="info_theme">';
					$html .= '<p class="title_theme">'.htmlspecialchars($themeInfo['name']).' '.htmlspecialchars($themeInfo['version']).'</p>';
					$html .= '<p class="type_theme">'.$themetype_text.'</p>';
					$html .= '</span></a>';
					
					$html .='<div class="container_iconCms">';
						$html .= '<div class="content_iconCms"><a href="'.$url.'" >';
							if ($themeInfo["isOpenSource"])
							{
								$html .= '<a style="display:inline" href="'.TC_HTTPDOMAIN.'/download?h='.$themeInfo['hash'].'" '
                                . 'onclick="trackDL(\''.$themeInfo['uriNameSeo'].'\');"><span class="sprite download" title="'.__("Quick download").'"></span></a>';
									
								if(preg_match('/\bfr\b/i',$_SERVER['REQUEST_URI']))
								{
									$themetype_text = $themetype_text.' '.__('Free');
								}
								else 
								{
									$themetype_text = __('Free').' '.$themetype_text;
								}
							}
							else if ($themeInfo["isThemeForest"]){
								$html .= '<span class="sprite theme_forest" title="'.__("Themeforest theme").'"></span>';
							}
							else if ($themeInfo["isCreativeMarket"]){
								$html .= '<span class="sprite creative_market" title="'.__("Creative Market theme").'"></span>';
							}
							else if ($themeInfo["isTemplateMonster"]){
								$html .= '<span class="sprite template_monster" title="'.__("Template Monster theme").'"></span>';
							}
						$html .= '</a></div>';
					$html .= '</div>';
				$html .= '</div>';
		    $html .= '</div>';

		$html .= '</div>';
		
		return $html;
	}
	
	public function render()
	{   
?>
        <script type="text/javascript"> var page="home" </script>
<?php
            
            $max_size = Helpers::returnBytes(ini_get('upload_max_filesize')); 
            if ($max_size > Helpers::returnBytes(ini_get('post_max_size'))) $max_size = Helpers::returnBytes(ini_get('post_max_size'));
            $max_size_MB = $max_size / (1024*1024);

            $token = uniqid(true);
            $_SESSION['token_'.$token] = time();

            $nbreTheme = new History();
            $data = $nbreTheme->getNumberOfTheme();

            ?> 
            <section id="content">
                <div class="container">
                    <div class="bg_home">
                        <h1><?php 
							if (isset($_GET["v"]) && $_GET["v"]==1)
								echo __("The WordPress themes verification platform"); 
							else 
								echo __("Verify your WordPress themes"); 
							?></h1>

                        <p class="description">
                        <?php echo __("Themecheck.org is a quick service that lets you verify WordPress themes for security and code quality."); ?><br>
                        <?php echo __("This service is free and compatible with Joomla templates."); ?>
                        </p>
                        <div id="ancreSubmit"></div>

                        <div class="line"><img src="<?php echo TC_HTTPDOMAIN;?>/img/images/line_content-home.png"/></div>


                        <h2><?php echo __("Upload a zip file and get its verification score"); ?></h2>

                        <div class="container_submit">
<!-- select file -->            
                            <form role="form" class="text-center" action="<?php echo TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(),"phpfile"=>"results"));?>" method="post" enctype="multipart/form-data">
                                <div class="content_select" id="content_select">
                                    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo Helpers::returnBytes(ini_get('upload_max_filesize'));?>" />
                                    <input type="file" name="file" id="file" class="fake_input" data-buttonText="<?php echo __("Select file"); ?>" data-classButton="btn btn-default btn-lg" data-classInput="input input-lg">
                                    <label for="file"  class="select">
                                            <span class="sprite arrow_white"></span>
                                            <?php echo __("SELECT FILE.ZIP"); ?>
                                    </label>
                                </div>

                                <div class="container_file_submit" id="container_file_submit">
                                    <div class="content_file">
                                            <input type="text" id="selected_file" class="selected_file" disabled="disabled" value=""/>
                                            <!-- <input type="file" name="new_file" id="new_file" class="fake_input"/> -->
                                            <label for="file" class="new_file"><span class="sprite grey_cross"></span></label>
                                    </div>
                                    <div class="content_submit">
                                        <input type="submit" id="submit" class="fake_input"/>
                                        <input type="hidden" name="token" value="<?php echo $token;?>"/>
                                        <label for="submit" class="submit" >
                                            <span class="sprite arrow_white"></span>
                                            <?php echo __("SUBMIT"); ?>
                                        </label>
                                    </div>
                                </div>
                                <div id="select_zip" class="select_zip">
                                    <label for="check_data">
                                        <span id="sprite_check" class="sprite check"></span>                                         
                                        <input type="checkbox" name="donotstore" value="donotstore" id="check_data" class="fake_input" onclick="check(this);"><?php echo __('Forget uploaded data after results').'&nbsp;<a id="forgetresultsmoreinfo"  data-container="body" data-toggle="popover" data-placement="bottom" data-content="'.__("<ul><li>No data will be kept on themecheck.org servers (or any other)<li>Validation won't be visible to the public<li>If you want to see the results in the future, you'll have to re-submit your file</ul>").'" href="#!"><span class="sprite interrogation_green"></span></a>';?>
                                    </label>
                                </div>
                                <p>
                                        <?php echo __("Maximum file size")." : $max_size_MB MB";?>
                                </p>
                            </form>
                        </div>
                    </div>	

        <!-- themes already -->

                    <div class="bg_themesAlready">
                        <div class="bg_first_part">
                                <div class="themes_verified">
                                    <div class="img_separVerti">
                                        <img src="<?php echo TC_HTTPDOMAIN;?>/img/images/separation_vertical.png"/>
                                    </div>
                                    <h4 class="nbreTheme"><strong><?php echo $data[0].' '; ?></strong>
                                    <?php echo __('THEMES ALREADY VERIFIED !'); ?></h4>
                                </div>
                        </div>
                        <div class="bg_second_part">
                            <div class="themes_for">
                                <div class="content_website_owners">

                                    <div class="website">
                                        <span class="sprite website_owners"></span>
                                    </div>

                                    <div class="description_website">
                                        <h2 class="subtitle"><?php echo __("Website owners"); ?></h2>
                                        <div class="img_separHori">
                                            <img src="<?php echo TC_HTTPDOMAIN;?>/img/images/separation_horizontal.png">
                                        </div>
                                        <div class="descript">
                                                <?php echo __("Check the themes you find or buy before installing them on your site"); ?>
                                        </div>
                                        <div class="liste">
                                            <ul> 	
                                                <li><?php echo __("Check code quality"); ?></li>
                                                <li><?php echo __("Check presence of malware"); ?></li>
                                            </ul>
                                        </div>	
                                    </div>
                                </div>
                                <div class="content_developers">
                                    <div class="dev">
                                            <span class="sprite developers"></span>
                                    </div>
                                    <div class="description_developers">
                                        <h2 class="subtitle"><?php echo __("Developers"); ?></h2>

                                        <div class="img_separHori">
                                                <img src="<?php echo TC_HTTPDOMAIN;?>/img/images/separation_horizontal.png"/>
                                        </div>

                                        <div class="descript">
                                                <?php echo __("Your create or distribute themes ?"); ?>
                                        </div>

                                        <div class="liste">
                                            <ul> 	
                                                <li>
                                                    <?php echo __("Themecheck.org helps you verify they satisfy WordPress standards and common users needs."); ?>
                                                </li>
                                                <li class="shareVerif">
                                                    <?php echo __("Share verification score on your site with ThemeCheck.org widget"); ?>
                                                    <span class="sprite themeCkeck_white"></span>
                                                </li>
                                            </ul>
                                        </div>	
                                    </div>
                                </div>
                                <div id="theme"></div>
                            </div>
                        </div>
                    </div>

                    <div class="container_page_home" id="container_home">
                        <div class="content_home">
                            <h2 class="recently"><?php echo __("Recently checked themes"); ?></h2>

                            <div class="line_content-home">
                                    <img src="<?php echo TC_HTTPDOMAIN;?>/img/images/line_content-home.png" width="303" class="img_line_content-home">
                            </div>

                            <div class="filter_themes">
                                <form id="sortform">
                                    <label><?php echo __("FILTER BY : "); ?></label>
                <!-- SELECT CMS -->
                                    <div class="selec_filters">	
                                        <div class="select_cms">
                                            <span class='selected'></span>
                                            <span class="selectArrow"><span class="sprite arrow_bottom"></span></span>
    
                                            <input type='checkbox' name='theme[]' value='wordpress' id="wordpress"<?php if(isset($_SESSION['theme'])){if(in_array("wordpress",$_SESSION['theme'])){echo 'checked="checked"';}} else {echo 'checked="checked"';};?> class="sortdropdown fake_input"/>
                                            <input type='checkbox' name='theme[]' value='joomla' id="joomla"<?php if(isset($_SESSION['theme'])){if(in_array("joomla",$_SESSION['theme'])){echo 'checked="checked"';}} else {echo 'checked="checked"';};?> class="sortdropdown fake_input"/>
                                            
                                            <div class="selectOptions" id="filterThemes">
                                                <span class="selectOption" value="All theme"><?php echo __("All theme");?></span>
                                                <span class="selectOption" value='wordpress'>Wordpress themes</span>
                                                <span class="selectOption" value='joomla' >Joomla themes</span>
                                            </div>
                                        </div>
                 <!-- SELECT FIRST -->
                                        <div class="select_first">
                                            <span class='selected'></span>
                                            <span class="selectArrow"><span class="sprite arrow_bottom"></span></span>
                                             <select name='sort' class='sortdropdown fake_input' id="select_hidden">
                                                <option value='modificationDate' <?php if(isset($_SESSION['sort']) && $_SESSION['sort']=='creationDate'){echo 'selected="selected"';}?>><?php echo __("Newer first");?>></option>
                                                <option value='score' <?php if(isset($_SESSION['sort']) && $_SESSION['sort']=='score'){echo 'selected="selected"';}?>><?php echo __("Higher scores first");?>></option>
                                            </select>
                                            <div class="selectOptions" id="selectOptionsFirst">
                                                <span class="selectOption"><?php echo __("Newer first");?></span>
                                                <span class="selectOption"><?php echo __("Higher scores first");?></span>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <script type="text/javascript"> 
                                var sessionTheme = "";
                                var sessionSort = "";
                            </script>
                
                       <?php  if(isset($_SESSION['theme']))
                               { 
                                    if(count($_SESSION['theme']) < 2)
                                    {  
                              
                                ?>
                                        <script type="text/javascript"> 
                                          
                                        sessionTheme = <?php echo $_SESSION['theme'][0]; ?>; 
                                       
                                        $('div.select_cms .selectOptions .selectOption').each(function(){

                                            if($(this).attr('value') == sessionTheme['value'])
                                            {
                                                $(this).closest('div.select_cms').attr('value',$(this).attr('value'));
                                                $(this).parent().siblings('span.selected').html($(this).html());
                                            }
                                        });
    
                                        </script>
                        <?php
                                    }
                               }
                               
                               if(isset($_SESSION['sort']) && $_SESSION['sort'] != 'id')
                               {

                            ?>
                                        <script type="text/javascript">                              

                                        $('div.select_first .selectOptions .selectOption').each(function(){

                                            if($(this).html() == __("Higher scores first") )
                                            {
                                                $(this).closest('div.select_first').attr('value',$(this).attr('value'));
                                                $(this).parent().siblings('span.selected').html($(this).html());

                                                sessionSort = $(this).html();
                                            }
                                        });
    
                                        
                                        </script>
                            <?php
                               
                               }
                        ?>
                        
                            <div class="block_container_themes">
                                <div class="container_themes">
                <?php

                // display recent validated file if	history is available			
                
                {
					$history = new History();
					$history->booom();
                ?>

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
                                </div>
                            </div>
        <?php   } ?>		
                        </div>
                        
                         <div class="container_seemore">
                          
                            <label for="seemore-btn" class="seemore"><span class="sprite arrow_grey"></span><?php echo __("SEE MORE");?> <input type="button" class="fake_input" name="seemore" id="seemore-btn">
                            </label>
                        </div>
                    </div>
                </div>	
            </section>

            <script src="<?php echo TC_HTTPDOMAIN;?>/scripts/Home-dist.js"></script>

			<script>
				  $('#seemore-btn').click(function () { 
						$.ajax({
							type: "POST",
							url: "<?php echo TC_HTTPDOMAIN.'/ajax.php?controller=home&action=seemore';?>",
							data: { olderthan: $("#alreadyvalidated div.block_theme").last().data("id"), lang: "<?php echo I18N::getCurLang();?>" }
						}).done(function( obj ) {
							$("#alreadyvalidated").append(obj.html); 
							if (obj.html == '') $("#container_home .seemore").css('display', 'none');
							smallestid = obj.smallestid; 
						}).fail(function() {
							console.log("ajax error");
						})

				  });
				  
//				$('.sortdropdown').on("change", function(){ console.log('ok');
//					$.ajax({
//						type: "POST",
//						url: "<?php //echo TC_HTTPDOMAIN.'/ajax.php?controller=home&action=sort';?>",
//						data: $("#sortform").serialize()
//					}).done(function(obj){
//						$("#alreadyvalidated").html(obj.html);
//					}); 
//      				});
                                
                                function ajaxSelectItem()
                                {
                                    $.ajax({
						type: "POST",
						url: "<?php echo TC_HTTPDOMAIN.'/ajax.php?controller=home&action=sort';?>",
						data: $("#sortform").serialize()
					}).done(function(obj){
						$("#alreadyvalidated").html(obj.html);
					}); 
                                }
                                
                                $('#selectOptionsFirst .selectOption').on("click", function(){ 
                                  
                                    if(($(this).html() == 'Higher scores first') || ($(this).html() == 'Meilleurs scores en premier'))
                                    {
                                        $('#select_hidden option[value="score"]').attr('selected', true);
                                        $('#select_hidden option[value="modificationDate"]').attr('selected', false);
                                    }
                                    else
                                    {
                                        $('#select_hidden option[value="modificationDate"]').attr('selected', true);
                                        $('#select_hidden option[value="score"]').attr('selected', false);
                                    }
                                 
                                    ajaxSelectItem();
                                });
                                
                                
                                 $('#filterThemes .selectOption').on("click", function(){ 
                                   
                                   var selected = $(this).attr('value'); //theme selected
                                   var select_cms = $('.select_cms input[type=checkbox]'); //input hidden
                             
                                   if(selected == 'wordpress' )
                                   {
                                       select_cms[0]['checked'] = true;  
                                       select_cms[1]['checked'] = false;
                                   }
                                   else if (selected == 'joomla')
                                   {
                                       select_cms[1]['checked'] = true;
                                       select_cms[0]['checked'] = false;  
                                   }
                                   else
                                   {
                                       select_cms[0]['checked'] = true;
                                       select_cms[1]['checked'] = true;
                                   }
                                   
                                   ajaxSelectItem();
                                });
                                
                                
				</script>
				<?php
	}
		
	public function ajax_seemore()
	{
		$lang = $_POST["lastid"];
		$olderthan = intval($_POST["olderthan"]);
		$response = null;
		
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