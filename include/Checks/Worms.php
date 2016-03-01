<?php

namespace ThemeCheck;

class Worms_Checker extends CheckPart
{		
		public function doCheck($php_files, $php_files_filtered, $css_files, $other_files, $themeInfo)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
                		
				$files = array_merge( $php_files, $other_files );
				
				foreach ($files as $php_key => $file)
        {
            if (preg_match($this->code, $file, $matches))
            {
                $filename = tc_filename($php_key);
                $error = $matches[0];
                $grep = tc_grep($error, $php_key);
                $this->messages[] = __all('<strong>%1$s</strong> %2$s', $filename, $grep);
                $this->errorLevel = $this->threatLevel;
            }
        }
    }
}

class Worms extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("Worms");
			$this->checks = array(
						new Worms_Checker('WORMS_WSHELL', TT_COMMON, ERRORLEVEL_CRITICAL, __all('Presence of wshell.php. This may be a script used by hackers to get control of a web server'), '/wshell\.php/', "ut_worms_wshell.zip"),
						new Worms_Checker('WORMS_ShellBOT', TT_COMMON, ERRORLEVEL_CRITICAL, __all('Presence of ShellBOT. This may be a script used by hackers to get control of a web server'), '/ShellBOT/', "ut_worms_shellbot.zip"),
						new Worms_Checker('WORMS_UNAME', TT_COMMON, ERRORLEVEL_CRITICAL, __all('Detection of uname -a. Tells a hacker what operating system a server is running'), '/uname -a/', "ut_worms_uname.zip"),
						new Worms_Checker('WORMS_PHARMA1', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('Symptom 1 of the &quot;Pharma&quot; hack <a href="http://blog.sucuri.net/2010/07/understanding-and-cleaning-the-pharma-hack-on-wordpress.html" target="_blank">http://blog.sucuri.net/2010/07/understanding-and-cleaning-the-pharma-hack-on-wordpress.html</a>'), '/php \$[a-zA-Z]*=\'as\';/', "ut_worms_pharma_hack_1.zip"),
						new Worms_Checker('WORMS_PHARMA2', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('Symptom 2 of the &quot;Pharma&quot; hack <a href="http://blog.sucuri.net/2010/07/understanding-and-cleaning-the-pharma-hack-on-wordpress.html" target="_blank">http://blog.sucuri.net/2010/07/understanding-and-cleaning-the-pharma-hack-on-wordpress.html</a>'), '/defined?\(\'wp_class_support/', "ut_worms_pharma_hack_2.zip"),						
			);
    }
}