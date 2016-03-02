<?php

namespace ThemeCheck;

class Wpvulndb_Checker extends CheckPart
{	
	public function doCheck($php_files, $php_files_filtered, $css_files, $other_files)
    {
        $this->errorLevel = $this->threatLevel;

        $vuln = $this->code;

		$content = "&quot;".$vuln->title."&quot;";

		foreach ($vuln->references as $url)
		{
			$content .= '<br/>More on Wordpress Vulnerability Scanner site : <a target="_blank" rel="nofollow" href="https://wpvulndb.com/vulnerabilities/'.$vuln->id.'">https://wpvulndb.com/vulnerabilities/'.$vuln->id.'</a>';
		}
		
		$this->messages[] = array('en'=>$content);//__all('WPScan Vulnerability <strong>%1$s</strong>', $content );
		$this->errorLevel = $this->threatLevel;
    }
}

class Wpvulndb extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("WPScan Vulnerability Database");
			$this->checks = array(
						new Wpvulndb_Checker('WPVULNDB', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('This theme is vulnerable to security breach'), null, 'ut_wpvulndb.zip'),
			);
    }
	
	public function doCheck($php_files, $php_files_filtered, $css_files, $other_files)
	{
		$start_time_checker = microtime(true);
	
		$history = new History();
	
		foreach ($this->checks as &$check)
		{
			$url = 'https://wpvulndb.com/api/v2/themes/'.urlencode($this->currentThemeName);
		
			$headers = get_headers($url);
			$response_code = substr($headers[0], 9, 3);
			if ($response_code != 200) continue;
			
			$content = file_get_contents($url);
			$wpvulnObject = json_decode($content);
			$wpvuln = WpVuln::fromJson($wpvulnObject, $this->currentThemeName);
			
			if ($this->currentThemetype & $check->themetype)
			{
				foreach ($wpvuln->vulnerabilities as $v)
				{						
					$cmp = Check::versionCmp($this->currentThemeVersion, $v->fixed_in, null);
					if ($cmp < 0)
					{
						$history->upsertWpVuln($this->currentThemeHash, $v);
						
						$check->code = $v;
						$start_time = microtime(true);
						$check->doCheck($php_files, $php_files_filtered, $css_files, $other_files);
						$check->duration = microtime(true) - $start_time; // check duration is calculated outside of the check to simplify check's code
					}
				}
			}
		}	
		$this->duration = microtime(true) - $start_time_checker;
	}
}