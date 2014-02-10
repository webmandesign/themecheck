<?php

namespace ThemeCheck;

class PostThumb_Checker extends CheckPart
{
		public function doCheck($php_files, $css_files, $other_files)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
        $php = implode( ' ', $php_files );
        
        if ( strpos( $php, $this->code ) === false )
        {
                $this->messages[] = sprintf(__('No reference to <strong>%1$s</strong> was found in the theme.'), $this->code);
                $this->errorLevel = $this->threatLevel;
        }
    }
}

class PostThumb extends Check
{	
    protected function createChecks()
    {
			$this->title = __("Featured image");
			$this->checks = array(
						new PostThumb_Checker(TT_WORDPRESS, ERRORLEVEL_WARNING, __("Use of the_post_thumbnail() instead of custom fields for thumbnails"), 'the_post_thumbnail', 'ut_postthumb_the_post_thumbnail.zip'),
						new PostThumb_Checker(TT_WORDPRESS, ERRORLEVEL_WARNING, __("Use of add_theme_support( 'post-thumbnails' ) in functions.php file"), 'post-thumbnails', 'ut_postthumb_post_thumbnails.zip'),
			);
    }
}