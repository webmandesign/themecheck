<?php

namespace ThemeCheck;

class Iframes_Checker extends CheckPart
{
    public function doCheck($php_files, $php_files_filtered, $css_files, $other_files, $themeInfo)
    {		
        $this->errorLevel = ERRORLEVEL_SUCCESS;
        $grep = '';
				
        foreach ( $php_files as $php_key => $phpfile ) {
            if ( preg_match( $this->code, $phpfile, $matches ) ) {
                $filename = tc_filename( $php_key );
                $error = ltrim( trim( $matches[0], '(' ) );
                $grep = tc_grep( $error, $php_key );
                $this->messages[] = __all('Found <strong>%1$s</strong> in file <strong>%2$s</strong>. %3$s', esc_html($error), esc_html($filename), $grep );
                $this->errorLevel = $this->threatLevel;
            }
        }
    }
}

class Iframes extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("Presence of iframes");
			$this->checks = array(
						new Iframes_Checker('IFRAMES', TT_COMMON, ERRORLEVEL_CRITICAL, __all('iframes are sometimes used to load unwanted adverts and malicious code on another site'), '/<(iframe +.*src=)[^>]*>/', 'ut_iframes.zip')
			);
    }
}