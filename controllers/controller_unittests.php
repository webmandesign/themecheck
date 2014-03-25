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
		<div class="container">
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
				
				echo $checkpart->id.' : ';
				echo $checkpart->hint["en"].' : '.$checkpart->unittest;
				$url = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>"en", "phpfile"=>"results", "ut"=>$checkpart->unittest));
				echo '<a href="'.$url.'">'.$url.'</a>';
				echo '<br/>';
			}
			echo '</div>';
		}
		?>
		</div> <!-- /container --> 
		<?php
	}
}