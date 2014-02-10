<?php

namespace ThemeCheck;

class PHPShort_Checker extends CheckPart
{		
		public function doCheck($php_files, $css_files, $other_files)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
                
        foreach ( $php_files as $name => $content )
        {
            if ( preg_match($this->code, $content, $matches ) )
            {
                $filename = tc_filename( $name );
                $non_print = tc_preg( $this->code , $name );
                $this->messages[] = sprintf(__('PHP short tags were found in file <strong>%1$s</strong>. &quot;This practice is discouraged because they are only available if enabled with short_open_tag php.ini configuration file directive, or if PHP was configured with the --enable-short-tags option&quot; (php.net), which is not the case on many servers.%2$s'), $filename, $non_print);
                $this->errorLevel = $this->threatLevel;
            }
        }
    }
}

class PHPShort extends Check
{	
    protected function createChecks()
    {
			$this->title = __("PHP short tags");
			$this->checks = array(
						new PHPShort_Checker(TT_COMMON, ERRORLEVEL_WARNING, __('Presence of PHP short tags'), '/<\?(\=?)(?!php|xml)/', 'ut_phpshort.zip')
			);
    }
}