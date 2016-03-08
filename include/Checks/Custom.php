<?php

namespace ThemeCheck;

class Custom_Checker extends CheckPart
{			
		public function doCheck($php_files, $php_files_filtered, $css_files, $other_files, $themeInfo)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
        $php = implode( ' ', $php_files );
        
        if ( ! preg_match( $this->code[0], $php ) ) {
            $this->messages[] = __all('No reference to <strong>%1$s</strong> was found in the theme.', esc_html($this->code[1]));
            $this->errorLevel = $this->threatLevel;
        }
    }
}

class Custom extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("Custom elements");
			$this->checks = array(
						new Custom_Checker('CUSTOM_HEADER', TT_WORDPRESS, ERRORLEVEL_WARNING, __all("Presence of custom header"), array('#add_theme_support\s?\(\s?[\'|"]custom-header#', 'custom header'), 'ut_custom_add_theme_support_header.zip'),
						new Custom_Checker('CUSTOM_BACKGROUND', TT_WORDPRESS, ERRORLEVEL_WARNING, __all("Presence of custom background"), array('#add_theme_support\s?\(\s?[\'|"]custom-background#', 'custom background'), 'ut_custom_add_theme_support_background.zip')
			);
    }
}