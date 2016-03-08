<?php

namespace ThemeCheck;

class Includes_Checker extends CheckPart
{		
	public function doCheck($php_files, $php_files_filtered, $css_files, $other_files, $themeInfo)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
        foreach ( $php_files as $php_key => $phpfile )
        {
            if ( preg_match( $this->code, $phpfile, $matches ) )
            {
                $filename = tc_filename( $php_key );
                $error = '/(?<![a-z0-9_])(?:requir|includ)e(?:_once)?\s?[\'"\(]/';
				$grep = tc_preg( $error, $php_key );
                if ( basename($filename) !== 'functions.php' )
                {
                    $this->messages[] = __all('The theme appears to use include or require : <strong>%1$s</strong> %2$s If these are being used to include separate sections of a template from independent files, then <strong>get_template_part()</strong> should be used instead. Otherwise, use include_once or require_once instead.', esc_html($filename), $grep );
                    $this->errorLevel = $this->threatLevel;
                }
            }
        }
    }
}

class Includes extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("Use of includes");
			$this->checks = array(
						new Includes_Checker('INCLUDES', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_INFO, __all('Use of include or require'), '/(?<![a-z0-9_])(?:requir|includ)e(?:_once)?\s?[\'"\(]/', 'ut_includes.zip'),
			);
    }
}