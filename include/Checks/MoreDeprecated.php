<?php

namespace ThemeCheck;

class MoreDeprecated_Checker extends CheckPart
{		
		public function doCheck($php_files, $php_files_filtered, $css_files, $other_files)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
         
				$key = $this->code[0];
				$key_instead = $this->code[1];
				$key_version = $this->code[2];				 
        foreach ( $php_files as $php_key => $phpfile )
        {
            if ( preg_match( '/[\s|]' . $key . '/', $phpfile, $matches ) )
            {
                $filename = tc_filename( $php_key );
                $error = ltrim( rtrim( $matches[0], '(' ) );
                $grep = tc_grep( $error, $php_key );
								$this->messages[] = __all('<strong>%1$s</strong> found in file <strong>%2$s</strong>. Deprecated since version <strong>%3$s</strong>. Use <strong>%4$s</strong> instead.%5$s', $error, $filename, $key_version, $key_instead, $grep );

                $this->errorLevel = ERRORLEVEL_CRITICAL;
            }
        }
    }
}

class MoreDeprecated extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("Deprecated functions");
			$this->checks = array(
						new MoreDeprecated_Checker('MOREDEPRECATED_GET_BLOGINFO', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_bloginfo'), array('get_bloginfo\(\s?("|\')home("|\')\s?\)', 'home_url()', '2.2' ), 't_moredeprecatedwordpress_get_bloginfo.zip'),
						new MoreDeprecated_Checker('MOREDEPRECATED_BLOGINFO', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('bloginfo'), array('bloginfo\(\s?("|\')home("|\')\s?\)', 'echo home_url()', '2.2' ), 'ut_moredeprecatedwordpress_bloginfo.zip'),
						new MoreDeprecated_Checker('MOREDEPRECATED_GET_BLOGINFO_SITE_URL', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_bloginfo'), array('get_bloginfo\(\s?("|\')site_url("|\')\s?\)', 'home_url()', '2.2' ), 'ut_moredeprecatedwordpress_get_bloginfo_site_url.zip'),
						new MoreDeprecated_Checker('MOREDEPRECATED_BLOGINFO_SITE_URL', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('bloginfo'), array('bloginfo\(\s?("|\')site_url("|\')\s?\)', 'echo home_url()', '2.2' ), 'ut_moredeprecatedwordpress_bloginfo_site_url.zip'),
			);
    }
}