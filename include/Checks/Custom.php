<?php

namespace ThemeCheck;

class Custom_Checker extends CheckPart
{			
		public function doCheck($php_files, $css_files, $other_files)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
        $php = implode( ' ', $php_files );
        
        if ( ! preg_match( $this->code[0], $php ) ) {
            $this->messages[] = sprintf(__('No reference to <strong>%1$s</strong> was found in the theme.'), $this->code[1]);
            $this->errorLevel = $this->threatLevel;
        }
    }
}

class Custom extends Check
{	
    protected function createChecks()
    {
			$this->title = __("Custom elements");
			$this->checks = array(
						new Custom_Checker(TT_WORDPRESS, ERRORLEVEL_WARNING, __("Presence of custom header"), array('#add_theme_support\s?\(\s?[\'|"]custom-header#', __('custom header')), 'ut_custom_add_theme_support_header.zip'),
						new Custom_Checker(TT_WORDPRESS, ERRORLEVEL_WARNING, __("Presence of custom background"), array('#add_theme_support\s?\(\s?[\'|"]custom-background#', __('custom background')), 'ut_custom_add_theme_support_background.zip')
			);
    }
}