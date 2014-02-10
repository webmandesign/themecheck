<?php

namespace ThemeCheck;

class NonPrintable_Checker extends CheckPart
{		
		public function doCheck($php_files, $css_files, $other_files)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
                
        foreach ( $php_files as $name => $content )
        {
            // 09 = tab
            // 0A = line feed
            // 0D = new line
            if ( preg_match($this->code, $content, $matches ) )
            {
                $filename = tc_filename( $name );
                $non_print = tc_preg( $this->code , $name );
                $this->messages[] = sprintf(__('Non-printable characters were found in file <strong>%1$s</strong>. This is an indicator of potential errors in PHP code.%2$s'), $filename, $non_print);
                $this->errorLevel = $this->threatLevel;
            }
        }
    }
}

class NonPrintable extends Check
{	
    protected function createChecks()
    {
			$this->title = __("Non-printable characters");
			$this->checks = array(
						new NonPrintable_Checker(TT_COMMON, ERRORLEVEL_WARNING, __('Presence of non-printable characters in PHP files')	, '/[\x00-\x08\x0B-\x0C\x0E-\x1F\x80-\xFF]/', 'ut_nonprintable.zip')
			);
    }
}
