<?php
namespace ThemeCheck;
class Version_Checker extends CheckPart
{
    public function doCheck($php_files, $php_files_filtered, $css_files, $other_files, $themeInfo)
    {		
		$this->errorLevel = ERRORLEVEL_SUCCESS;
		
		$version = '';
		$filename = '';
		
		if ($this->id == "VERSION_SYNTAX_WP")
		{
			foreach ( $css_files as $css_file => $file_content ) 
			{
				$filename = tc_filename( $css_file );

				if ( $filename == 'style.css') 
				{
					if (preg_match('/[ \t\/*#]*Version:(.*)$/mi', $file_content, $match) && !empty($match) && count($match)==2)
					{
						$version = trim($match[1]);
						break; // break only if found. Multiple style.css files is possible.
					}
				}
			}
		}
		else if ($this->id == "VERSION_SYNTAX_J")
		{
			foreach ( $other_files as $other_file => $file_content ) 
			{
				$filename = tc_filename( $other_file );
				
				if ($filename == 'templateDetails.xml')
				{
					libxml_use_internal_errors(true);
					$xml = simplexml_load_file($other_file);
					if (count(libxml_get_errors()) > 0 ) return;
					if (empty($xml)) return;

					if ($xml->getName() == 'extension' || $xml->getName() == 'install' || $xml->getName() == 'mosinstall')
					{
						if(!empty($xml->version)) $version = (string)$xml->version;
					}  else {
						return;
					}
					break;
				}
			}
		}
		 
		if (empty($version))
		{
			if ($this->id == "VERSION_SYNTAX_J") $this->messages[] = __all('Could not find template version. A template version must be given in templateDetails.xml file.');
			if ($this->id == "VERSION_SYNTAX_WP") $this->messages[] = __all('Could not find theme version. A theme version must be given in style.css file.');
			$this->errorLevel = $this->threatLevel;
		} else 
		{
			if ( !preg_match('/^[0-9]{1,4}(\.[0-9]{1,4}){0,3}$/', $version, $match))
			{
				$this->messages[] = __all('Version syntax does not match one of the following patterns : "n", "n.n", "n.n.n", or "n.n.n.n" where n can be 1 to 4 digits. Detected theme version was %1$s in file %2$s.', 
											'<strong>'.$version.'</strong>', '<strong>'.$filename.'</strong>');
				$this->messages[] = __all('Themecheck.org does not accept themes with malformed version number.');
				$this->errorLevel = $this->threatLevel;
			}
		}
    }
}

class Version extends Check
{	
    protected function createChecks()
    {
		$this->title = __all("Version syntax");
		$this->checks = array(
					// these are fatal checks. Execution will stop when positive.
					new Version_Checker('VERSION_SYNTAX_WP',  TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_FATAL, __all('Incorrect theme version.'), null, 'ut_version_syntax_wp.zip'),
					new Version_Checker('VERSION_SYNTAX_J',  TT_JOOMLA, ERRORLEVEL_FATAL, __all('Incorrect theme version.'), null, 'ut_version_syntax_j.zip'),
		);
    }
}