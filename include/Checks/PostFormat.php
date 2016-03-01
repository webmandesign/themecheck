<?php

namespace ThemeCheck;

class PostFormat_Checker extends CheckPart
{
		public function doCheck($php_files, $php_files_filtered, $css_files, $other_files, $themeInfo)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
        
        $php = implode( ' ', $php_files );
        $css = implode( ' ', $css_files );
        
        foreach ( $php_files as $php_key => $phpfile ) {
            if ( preg_match( $this->code, $phpfile, $matches ) ) {
                if ( !strpos( $php, 'get_post_format' ) && !strpos( $php, 'has_post_format' ) ) {
                    $css_found = (!strpos( $css, '.format' )) ? ", and no use of formats in the CSS was detected" : "";

                    $filename = tc_filename( $php_key );
                    $matches[0] = str_replace(array('"',"'"),'', $matches[0]);
                    $error = esc_html( rtrim($matches[0], '(' ) );
                    $grep = tc_grep( rtrim($matches[0], '(' ), $php_key);
                    $this->messages[] = __all('<strong>add_theme_support()</strong> was found in the file <strong>%1$s</strong>. However get_post_format and/or has_post_format were not found%2$s.', $filename, $css_found);
                    $this->errorLevel = $this->threatLevel;
                }
            }
        }
    }
}

class PostFormat extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("Registration of theme features");
			$this->checks = array(
						new PostFormat_Checker('POSTFORMAT', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all("Implementation of add_theme_support()"), '/add_theme_support\(\s?("|\')post-formats(,*)?("|\')/m', 'ut_postformat.zip'),
			);
    }
}