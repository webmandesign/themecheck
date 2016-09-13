<?php

namespace ThemeCheck;

class Constants_Checker extends CheckPart
{
    public function doCheck($php_files, $php_files_filtered, $css_files, $other_files, $themeInfo)
    {		
        $this->errorLevel = ERRORLEVEL_SUCCESS;
				
		foreach ( $php_files as $php_key => $phpfile ) {
            if ( preg_match( '/[\s|\'|\"]' . $this->code[0] . '(?:\'|"|;|\s)/', $phpfile, $matches ) ) {
                $filename = tc_filename( $php_key );
                $error = ltrim( rtrim( $matches[0], '(' ), '\'"' );
                $grep = tc_grep( $error, $php_key );
                $this->messages[] = __all('Constant <strong>%1$s</strong> was found in the file <strong>%2$s</strong>. <strong>%3$s</strong> should be used instead. %4$s', esc_html($error), esc_html($filename), esc_html($this->code[1]), $grep );
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
					new Constants_Checker('CONSTANTS_STYLESHEETPATH', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('Use of STYLESHEETPATH') , array('STYLESHEETPATH', 'get_stylesheet_directory()'), 'ut_constants_get_stylesheet_directory.zip'),
					new Constants_Checker('CONSTANTS_TEMPLATEPATH', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('Use of TEMPLATEPATH') , array('TEMPLATEPATH', 'get_template_directory()'), 'ut_constants_get_template_directory.zip'),
					new Constants_Checker('CONSTANTS_PLUGINDIR', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('Use of PLUGINDIR') , array('PLUGINDIR','WP_PLUGIN_DIR'), 'ut_constants_wp_plugin_dir.zip'),
					new Constants_Checker('CONSTANTS_MUPLUGINDIR', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('Use of MUPLUGINDIR') , array('MUPLUGINDIR','WPMU_PLUGIN_DIR'), 'ut_constants_wpmu_plugin_dir.zip'),
					new Constants_Checker('CONSTANTS_HEADERIMAGE', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('Use of HEADER_IMAGE') , array('HEADER_IMAGE','add_theme_support( \'custom-header\' )'), 'ut_constants_header_image.zip'),
					new Constants_Checker('CONSTANTS_NOHEADERTEXT', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('Use of NO_HEADER_TEXT') , array('NO_HEADER_TEXT','add_theme_support( \'custom-header\' )'), 'ut_constants_no_header_text.zip'),
					new Constants_Checker('CONSTANTS_HEADERTEXTCOLOR', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('Use of HEADER_TEXTCOLOR') , array('HEADER_TEXTCOLOR','add_theme_support( \'custom-header\' )'), 'ut_constants_no_header_textcolor.zip'),
					new Constants_Checker('CONSTANTS_HEADERIMAGEWIDTH', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('Use of HEADER_IMAGE_WIDTH') , array('HEADER_IMAGE_WIDTH','add_theme_support( \'custom-header\' )'), 'ut_constants_header_image_width.zip'),
					new Constants_Checker('CONSTANTS_HEADERIMAGEHEIGHT', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('Use of HEADER_IMAGE_HEIGHT') , array('HEADER_IMAGE_HEIGHT','add_theme_support( \'custom-header\' )'), 'ut_constants_header_image_height.zip'),
					new Constants_Checker('CONSTANTS_BACKGROUNDCOLOR', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('Use of BACKGROUND_COLOR') , array('BACKGROUND_COLOR','add_theme_support( \'custom-background\' )'), 'ut_constants_background_color.zip'),
					new Constants_Checker('CONSTANTS_BACKGROUNDIMAGE', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('Use of BACKGROUND_IMAGE') , array('BACKGROUND_IMAGE','add_theme_support( \'custom-background\' )'), 'ut_constants_background_image.zip'),
		);
    }
}