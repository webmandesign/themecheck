<?php

namespace ThemeCheck;

class I18NCheck_Checker extends CheckPart
{
	public function doCheck($php_files, $php_files_filtered, $css_files, $other_files)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
        
        $error = '';

        // make sure the tokenizer is available
        if ( !function_exists( 'token_get_all' ) ) return true;
        foreach ( $php_files as $php_key => $phpfile )
        {
            $error = '';
            $stmts = array();
            
            $search = $phpfile;

			while ( preg_match( '/' . $this->code . '\(/', $search, $matches, PREG_OFFSET_CAPTURE ) ) {
				$pos = $matches[0][1];
                $search = substr($search,$pos);
				
                $open=1;
                $i=strpos($search,'(')+1;
                while( $open>0 ) {
                    switch($search[$i]) {
                    case '(':
                            $open++; break;
                    case ')':
                            $open--; break;
                    }
                    $i++;
                }
                $stmts[] = substr($search,0,$i);
                $search = substr($search,$i);
            }

            foreach ( $stmts as $match ) {
                $tokens = @token_get_all('<?php '.$match.';');

				if (!empty($tokens)) {
					foreach ($tokens as $token) {
						if (is_array($token) && in_array( $token[0], array( T_VARIABLE ) ) ) {
							$filename = tc_filename( $php_key );
							$grep = tc_grep( ltrim( $match ), $php_key );
							preg_match( '/[^\s]*\s[0-9]+/', $grep, $line);
							$error = '';
							if ( isset( $line[0] ) ) {
								$error = ( !strpos( $error, $line[0] ) ) ? $grep : '';
							}

							$var_name = $token[1];
							$this->messages[] = __all('Possible variable %1$s found in translation function in <strong>%2$s</strong>. Translation function calls should not contain PHP variables. %3$s', '<strong>'.$var_name.'</strong>', $filename, $error);
                            $this->errorLevel = $this->threatLevel;
                            
							break; // stop looking at the tokens on this line once a variable is found
						}
					}
				}
            }
        }
    }
}

class I18NCheck extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("I18N implementation");
			$this->checks = array(
						new I18NCheck_Checker('I18N_E', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Proper use of _e(') , '_e', "ut_i18n__e.zip"),
						new I18NCheck_Checker('I18N_ALL', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Proper use of ___all(') , '__', "ut_i18n___.zip"),
						new I18NCheck_Checker('I18N_X', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Proper use of _x(') , '_x', "ut_i18n__x.zip"),
						new I18NCheck_Checker('I18N_EX', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Proper use of _ex(') , '_ex', "ut_i18n__ex.zip"),
						new I18NCheck_Checker('I18N_ESC_ATTR___ALL', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Proper use of esc_attr___all(') , 'esc_attr__', "ut_i18n_esc_attr__.zip"),
						new I18NCheck_Checker('I18N_ESC_ATTR_E', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Proper use of esc_attr_e(') , 'esc_attr_e', "ut_i18n_esc_attr_e.zip"),
						new I18NCheck_Checker('I18N_ESC_ATTR_X', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Proper use of esc_attr_x(') , 'esc_attr_x', "ut_i18n_esc_attr_x.zip"),
						new I18NCheck_Checker('I18N_ESC_HTML___ALL', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Proper use of esc_html___all(') , 'esc_html__', "ut_i18n_esc_html__.zip"),
						new I18NCheck_Checker('I18N_ESC_HTML_E', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Proper use of esc_html_e(') , 'esc_html_e', "ut_i18n_esc_html_e.zip"),
						new I18NCheck_Checker('I18N_ESC_HTML_X', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Proper use of esc_html_x(') , 'esc_html_x', "ut_i18n_esc_html_x.zip"),
						new I18NCheck_Checker('I18N_UNDERSCORE', TT_COMMON, ERRORLEVEL_WARNING, __all('Proper use of __all(') , '__all', "ut_i18n__.zip"),
						new I18NCheck_Checker('I18N_GETTEXT', TT_COMMON, ERRORLEVEL_WARNING, __all('Proper use of gettext(') , 'gettext', "ut_i18n_gettext.zip"),
			);
    }
	
	public function doCheck($php_files, $php_files_filtered, $css_files, $other_files)
	{
		$start_time_checker = microtime(true);
		foreach ($this->checks as &$check)
		{
			if ($this->currentThemetype & $check->themetype)
			{
				$start_time = microtime(true);
				$check->doCheck($php_files, $php_files_filtered, $css_files, $other_files);
				$check->duration = microtime(true) - $start_time; // check duration is calculated outside of the check to simplify check's code
			}
		}	
		$this->duration = microtime(true) - $start_time_checker;
	}
}