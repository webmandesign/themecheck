<?php
namespace ThemeCheck;

require_once(TC_ROOTDIR.'/DB/Download.php');

// a controller to limit downloads/day/user
class Controller_download
{
	public $meta = array();
	public $samepage_i18n = array();
	
	public function __construct()
	{
	}
	
	public function prepare()
	{
		$this->meta["title"] = __("Download");
     	$this->meta["description"] = __("Download");
		global $ExistingLangs;
		foreach ($ExistingLangs as $l)
		{
			$this->samepage_i18n[$l] = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>$l, "phpfile"=>"download"));
		}
	}
	
	public function convertToMktime($date)
	{
	    $explodeDateHeure = explode(' ',$date);
	    $explodeDate = explode('-',$explodeDateHeure[0]);
	    $explodeHeure = explode(':',$explodeDateHeure[1]);
	   
		return mktime($explodeHeure[0],$explodeHeure[1],$explodeHeure[2],$explodeDate[1], $explodeDate[2],$explodeDate[0]);
	}
		
	public function render()
	{
		if(isset($_SERVER['REMOTE_ADDR']))
		{
			if(($_SERVER['REMOTE_ADDR'])=== '::1')
			{
				$user_ip = '127.0.0.1';
			}
			else
			{
				$user_ip = $_SERVER['REMOTE_ADDR'];
			}
		  
			// Set local timezone
			//date_default_timezone_set('Europe/Belgrade');
			
			// get local time
			$date_now = date("Y-m-d H:i:s");//ok
			$mk_date_now = Controller_download::convertToMktime($date_now);
			
			// -1 day
			$date_yesterday = date("Y-m-d H:i:s",strtotime($date_now." -1 days")); 
			$yesterday = Controller_download::convertToMktime($date_yesterday);
	   
			// get user downloads count
			$instanceDownload = new Download();
			$count = $instanceDownload->CountDownloadByUser($user_ip,$mk_date_now,$yesterday);
		  
			if($count[0]<10)
			{
				 $instanceDownload = new Download();
				 $instanceDownload->InsertNewDownload($user_ip,$mk_date_now);
			
				if(isset($_GET['h'])) // use hash instead of id to harden automatized site scraping
				{
					$hash = $_GET['h'];
					$history = new History();
					$themeInfo = $history->loadThemeFromHash($hash);

					if(!empty($themeInfo))
					{
						$path = TC_VAULTDIR.'/upload/'.$themeInfo->hash.'.zip';

						if (file_exists($path))
						{		
							ob_end_clean();
							ob_start();
							header("Pragma: public");
							header("Expires: 0");
							header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
							header("Cache-Control: public");
							header("Content-Description: File Transfer");
							header("Content-type: application/zip");
							header("Content-Disposition: attachment; filename=\"".$themeInfo->zipfilename."\"");
							header("Content-Transfer-Encoding: binary");
							//header('Content-Length: ' . filesize($path)); // no Content-Length for servers that activate gzip compression (avoids corrupted files)

							readfile($path);
							exit();
						}
					}
					else
					{
						exit("filename does not exist");
					}
				}
			}
			else
			{
				 $dateFirstDownload = $instanceDownload->GetDateMinDownload($user_ip,$mk_date_now,$yesterday);
				 $nextDownload = date("Y-m-d H:i:s",strtotime($dateFirstDownload[0]." +1 days"));
				
				 $explodeDateHour = explode(' ',$nextDownload);
				 $explodeDate = explode('-',$explodeDateHour[0]);
				 $explodeHour = explode(':',$explodeDateHour[1]);
				 $downloadAutho = $explodeDate[2].'-'.$explodeDate[1].' à  '.$explodeHour[0].':'.$explodeHour[1];
				
				  // daily limit reached
				  echo '<span style="text-align:center;"><p><h2 >'.$count[0].' daily downloads allowed'
						  . '</h2></p><br>'
						  . '<p><h3>Next download will be possible at '.$downloadAutho.'</h3></p></span>';
						;
				  header("Refresh: 5;URL=index.php");
			}
	   }
		
		exit();
	}
}