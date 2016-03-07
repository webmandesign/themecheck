<?php
namespace ThemeCheck;

class Controller_error404
{
	public $meta = array();
	public $samepage_i18n = array();
	
	public function __construct()
	{
	}
	
	public function prepare()
	{
		$this->meta["title"] = __("Error 404 (Not Found)");
		$this->meta["description"] = __("Error 404 (Not Found)");
		$this->meta["robots"] = "noindex";
		global $ExistingLangs;
		foreach ($ExistingLangs as $l)
		{
			$this->samepage_i18n[$l] = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>$l, "phpfile"=>"error404"));
		}
	}
	
	public function render()
	{ 
		?>
		<section id="content">
			<div class="container" style="background-color:#212121;text-align:center;padding:150px 0 100px 0;color:#FFF">
				<h1><?php echo __("Error 404."); ?></h1>
				<p style="margin:10px;color:#aaa;"><?php echo __("Sorry, the page you requested doesn't exist."); ?></p>
			</div>
		</section>			
		<?php
	}
}