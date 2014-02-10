<?php

namespace ThemeCheck;

class Style_Checker extends CheckPart
{		
		public function doCheck($php_files, $css_files, $other_files)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
        $css = implode( ' ', $css_files );
               
        if ( !preg_match( '/' . $this->code[0] . '/i', $css, $matches ) )
        {
            $this->messages[] = $this->code[1];
            $this->errorLevel = $this->threatLevel;
        }
    }
}

class Style extends Check
{	
    protected function createChecks()
    {
			$this->title = __("CSS files");
			$this->checks = array(
						new Style_Checker(TT_WORDPRESS, ERRORLEVEL_ERROR, __('Presence of theme name'), array('[ \t\/*#]*Theme Name:', __('<strong>Theme name:</strong> is missing from style.css header.')), 'ut_style_theme_name.zip'),
						new Style_Checker(TT_WORDPRESS, ERRORLEVEL_ERROR, __('Presence of theme description'), array('[ \t\/*#]*Description:', __('<strong>Description:</strong> is missing from style.css header.')), "ut_style_description.zip"),
						new Style_Checker(TT_WORDPRESS, ERRORLEVEL_ERROR, __('Presence of theme author'), array('[ \t\/*#]*Author:',__('<strong>Author:</strong> is missing from style.css header.')), "ut_style_author.zip"),
						new Style_Checker(TT_WORDPRESS, ERRORLEVEL_ERROR, __('Presence of theme version'), array('[ \t\/*#]*Version', __('<strong>Version:</strong> is missing from style.css header.')), "ut_style_version.zip"),
						new Style_Checker(TT_WORDPRESS, ERRORLEVEL_ERROR, __('Presence of license type'), array('[ \t\/*#]*License:', __('<strong>License:</strong> is missing from style.css header.')), "ut_style_license.zip"),
						new Style_Checker(TT_WORDPRESS, ERRORLEVEL_ERROR, __('Presence of license url'), array('[ \t\/*#]*License URI:', __('<strong>License URI:</strong> is missing from style.css header.')), "ut_style_license_uri.zip"),
						new Style_Checker(TT_WORDPRESS, ERRORLEVEL_ERROR, __('Presence of .sticky class'), array('\.sticky', __('<strong>.sticky</strong> css class is needed in theme css.')), "ut_style_sticky.zip"),
						new Style_Checker(TT_WORDPRESS, ERRORLEVEL_ERROR, __('Presence of .bypostauthor class'), array('\.bypostauthor', __('<strong>.bypostauthor</strong> css class is needed in theme css.')), "ut_style_bypostauthor.zip"),
						new Style_Checker(TT_WORDPRESS, ERRORLEVEL_ERROR, __('Presence of .alignleft class'), array('\.alignleft', __('<strong>.alignleft</strong> css class is needed in theme css.')), "ut_style_alignleft.zip"),
						new Style_Checker(TT_WORDPRESS, ERRORLEVEL_ERROR, __('Presence of .alignright class'), array('\.alignright', __('<strong>.alignright</strong> css class is needed in theme css.')), "ut_style_alignright.zip"),
						new Style_Checker(TT_WORDPRESS, ERRORLEVEL_ERROR, __('Presence of .aligncenter class'), array('\.aligncenter', __('<strong>.aligncenter</strong> css class is needed in theme css.')), "ut_style_aligncenter.zip"),
						new Style_Checker(TT_WORDPRESS, ERRORLEVEL_ERROR, __('Presence of .wp-caption class'), array('\.wp-caption', __('<strong>.wp-caption</strong> css class is needed in theme css.')), "ut_style_wp_caption.zip"),
						new Style_Checker(TT_WORDPRESS, ERRORLEVEL_ERROR, __('Presence of .wp-caption-text class'), array('\.wp-caption-text', __('<strong>.wp-caption-text</strong> css class is needed in theme css.')), "ut_style_wp_caption_text.zip"),
						new Style_Checker(TT_WORDPRESS, ERRORLEVEL_ERROR, __('Presence of .gallery-caption class'), array('\.gallery-caption', __('<strong>.gallery-caption</strong> css class is needed in theme css.')), "ut_style_gallery_caption.zip"),
						new Style_Checker(TT_WORDPRESS, ERRORLEVEL_WARNING, __('Presence of Theme URI'), array('[ \t\/*#]*Theme URI:', __('Could not find <strong>Theme URL:</strong>.')), "ut_style_theme_uri.zip"),
						new Style_Checker(TT_WORDPRESS, ERRORLEVEL_WARNING, __('Presence of Author URI'), array('[ \t\/*#]*Author URI:', __('Could not find <strong>Author URI:</strong>.')), "ut_style_author_uri.zip")
			);
    }
}