<?php
namespace ThemeCheck;
require_once TC_INCDIR."/FileValidator.php";
require_once TC_INCDIR."/Helpers.php";
if (USE_DB) include_once (TC_ROOTDIR.'/DB/History.php');
$max_size = Helpers::returnBytes(ini_get('upload_max_filesize'));
if ($max_size > Helpers::returnBytes(ini_get('post_max_size'))) $max_size = Helpers::returnBytes(ini_get('post_max_size'));
$max_size_MB = $max_size / (1024*1024);
//ThemeInfo::testLicence();
//die;
?> 
    <div class="jumbotron">
      <div class="container">
        <h1><?php echo __("Validate your web theme or template"); ?></h1>
        <p><?php echo __("Themecheck.org is a quick service that lets you validate web themes or templates. This service is free and compatible with Wordpress themes and Joomla templates."); ?></p>
      </div>
    </div>

    <div class="container">
			<form role="form" class="text-center" action="<?php echo TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(),"phpfile"=>"results.php"));?>" method="post" enctype="multipart/form-data">
				<div class="form-group">
					<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo Helpers::returnBytes(ini_get('upload_max_filesize'));?>" />
					<input type="file" name="file" id="file" class="filestyle" data-buttonText="<?php echo __("Select file"); ?>" data-classButton="btn btn-default btn-lg" data-classInput="input input-lg">
				</div>
				<?php echo __("Maximum file size")." : $max_size_MB MB";?> 
				<br/><br/>
				<button type="submit" class="btn btn-primary btn-lg" ><?php echo __("Submit"); ?></button>
			</form>
<?php
$samepage_i18n = array('en' => TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>"en", "phpfile"=>"index.php")),
											 'fr' => TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>"fr", "phpfile"=>"index.php")));
					
// display recent validated file if	history is available			
if (USE_DB) {
$history = new History();

?>
			<hr>
			<h2><?php echo __("Themes already checked"); ?></h2>

			<?php 
			$pagination = $history->getRecent();
			foreach($pagination as $t)
			{
				$namesanitized = $t['namesanitized'];
				$themetype = $t['themetype'];
				$url = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"results.php", "namesanitized"=>$namesanitized, "themetype"=>$themetype));
				echo '<div style="width:220px;height:180px;display:inline-block;margin:10px">';
				echo '<a href="'.$url.'"><img style="box-shadow: 0 0 20px #DDD;" src="'.TC_HTTPDOMAIN.'/'.$t['hash'].'/thumbnail.png">';
				echo $namesanitized.'</a>';
				echo '</div>';
			}
			?>

<?php } ?>		
		</div> <!-- /container --> 