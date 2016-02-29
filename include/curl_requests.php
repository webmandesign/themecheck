<?php
namespace ThemeCheck;

/**
 * Url creatoin
 * @param  $testUrl string
 * @return curl type
 */
function requestsCurl($testUrl)
{
    $ch = curl_init(); 
    curl_setopt($ch,CURLOPT_URL,$testUrl);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    return $ch;
}


/*
* Check if theme exists on wordpress.org
* @param string
* @return booleen
*/
function isOnWordpressOrg($nomTheme)
{
   
    $themeExist = true;
    $testUrl = 'https://wordpress.org/themes/'.$nomTheme;
 
    $ch = requestsCurl($testUrl);
    curl_exec($ch);

    $test = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
    
    if($test != 200)
    {
        $themeExist = false;
    }
  
    return $themeExist;
}        

/**
 *  Check if theme exists on joomla24.com
 * @param $name name of theme
 * @param $zipfilename type string
 * @return boolean
 */
function isOnJoomla24($name, $zipfilename)
{
    $themeExists = false;
    $j24Url = 'http://www.joomla24.com/rd_sitemap.html';
    
    $explodeZip = explode('.',$zipfilename);
    $zipname = $explodeZip[0];
    $name = str_replace(' ','_',$name);
    $zipname = str_replace(' ','_',$zipname);
		
    // open joomla24 cache file
	$file_path = TC_ROOTDIR."/dyn/joomla24_cache.txt";
	$needs_refresh = true;
	
	if (file_exists($file_path)) 
	{
		$lastupdate = filemtime($file_path);
		if (time() < $lastupdate + 86400*7) $needs_refresh = false; // 7 days
	}

	if ($needs_refresh)
	{
		// get http page content
		$ch = requestsCurl($j24Url);
		$buffer = curl_exec($ch); 
	  
		if((!empty($name) && preg_match("/\b$name\b/i", $buffer)) || preg_match("/\b$zipname\b/i", $buffer))
		{
			$themeExists = true;
		}
		
		// save in cache
		file_put_contents($file_path, $buffer);
	} else {
		$file = @fopen($file_path, "r+");
		
		while (($buffer = fgets($file)) !== false)
		{
			if((!empty($name) && preg_match("/\b$name\b/i", $buffer)) || preg_match("/\b$zipname\b/i", $buffer))
			{
				$themeExists = true;
				break;
			}
		}
		fclose($file);
	}

    return $themeExists;
}   
?>

