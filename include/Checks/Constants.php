<?php

namespace ThemeCheck;

class Constants_Checker extends CheckPart
{
    public function doCheck($php_files, $php_files_filtered, $css_files, $other_files, $themeInfo)
    {		
        $this->errorLevel = ERRORLEVEL_SUCCESS;
				
		foreach ( $php_files as $php_key => $phpfile ) {
            if ( preg_match( '/[\s|]' . $this->code[0] . '/', $phpfile, $matches ) ) {
                $filename = tc_filename( $php_key );
                $error = ltrim( rtrim( $matches[0], '(' ) );
                $grep = tc_grep( $error, $php_key );
                $this->messages[] = __all('Constant <strong>%1$s</strong> was found in the file <strong>%2$s</strong>. Use <strong>%3$s</strong> instead. %4$s', $error, $filename, $this->code[1], $grep );
                $this->errorLevel = $this->threatLevel;
            }
        }
    }
}

class Constants extends Check
{	
    protected function createChecks()
    {
		$this->title = __all("Inapropriate constants");
		$this->checks = array(
					new Constants_Checker('CONSTANTS_STYLESHEETPATH', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Use of STYLESHEETPATH') , array('STYLESHEETPATH', 'get_stylesheet_directory()'), 'ut_constants_get_stylesheet_directory.zip'),
					new Constants_Checker('CONSTANTS_TEMPLATEPATH', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Use of TEMPLATEPATH') , array('TEMPLATEPATH', 'get_template_directory()'), 'ut_constants_get_template_directory.zip'),
					new Constants_Checker('CONSTANTS_PLUGINDIR', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Use of PLUGINDIR') , array('PLUGINDIR','WP_PLUGIN_DIR'), 'ut_constants_wp_plugin_dir.zip'),
					new Constants_Checker('CONSTANTS_MUPLUGINDIR', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Use of MUPLUGINDIR') , array('MUPLUGINDIR','WPMU_PLUGIN_DIR'), 'ut_constants_wpmu_plugin_dir.zip'),
		);
    }
}