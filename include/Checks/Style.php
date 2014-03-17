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
			$this->title = __all("CSS files");
			$this->checks = array(
						new Style_Checker(TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('Presence of theme name'), array('[ \t\/*#]*Theme Name:', __all('<strong>Theme name</strong> is missing from style.css header.')), 'ut_style_theme_name.zip'),
						new Style_Checker(TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Presence of theme description'), array('[ \t\/*#]*Description:', __all('<strong>Description</strong> is missing from style.css header.')), "ut_style_description.zip"),
						new Style_Checker(TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Presence of theme author'), array('[ \t\/*#]*Author:',__all('<strong>Author</strong> is missing from style.css header.')), "ut_style_author.zip"),
						new Style_Checker(TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Presence of theme version'), array('[ \t\/*#]*Version', __all('<strong>Version</strong> is missing from style.css header.')), "ut_style_version.zip"),
						new Style_Checker(TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Presence of license type'), array('[ \t\/*#]*License:', __all('<strong>License</strong> is missing from style.css header.')), "ut_style_license.zip"),
						new Style_Checker(TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Presence of license url'), array('[ \t\/*#]*License URI:', __all('<strong>License URI</strong> is missing from style.css header.')), "ut_style_license_uri.zip"),
						new Style_Checker(TT_WORDPRESS, ERRORLEVEL_CRITICAL, __all('Presence of .sticky class'), array('\.sticky', __all('<strong>.sticky</strong> css class is needed in theme css.')), "ut_style_sticky.zip"),
						new Style_Checker(TT_WORDPRESS, ERRORLEVEL_CRITICAL, __all('Presence of .bypostauthor class'), array('\.bypostauthor', __all('<strong>.bypostauthor</strong> css class is needed in theme css.')), "ut_style_bypostauthor.zip"),
						new Style_Checker(TT_WORDPRESS, ERRORLEVEL_CRITICAL, __all('Presence of .alignleft class'), array('\.alignleft', __all('<strong>.alignleft</strong> css class is needed in theme css.')), "ut_style_alignleft.zip"),
						new Style_Checker(TT_WORDPRESS, ERRORLEVEL_CRITICAL, __all('Presence of .alignright class'), array('\.alignright', __all('<strong>.alignright</strong> css class is needed in theme css.')), "ut_style_alignright.zip"),
						new Style_Checker(TT_WORDPRESS, ERRORLEVEL_CRITICAL, __all('Presence of .aligncenter class'), array('\.aligncenter', __all('<strong>.aligncenter</strong> css class is needed in theme css.')), "ut_style_aligncenter.zip"),
						new Style_Checker(TT_WORDPRESS, ERRORLEVEL_CRITICAL, __all('Presence of .wp-caption class'), array('\.wp-caption', __all('<strong>.wp-caption</strong> css class is needed in theme css.')), "ut_style_wp_caption.zip"),
						new Style_Checker(TT_WORDPRESS, ERRORLEVEL_CRITICAL, __all('Presence of .wp-caption-text class'), array('\.wp-caption-text', __all('<strong>.wp-caption-text</strong> css class is needed in theme css.')), "ut_style_wp_caption_text.zip"),
						new Style_Checker(TT_WORDPRESS, ERRORLEVEL_CRITICAL, __all('Presence of .gallery-caption class'), array('\.gallery-caption', __all('<strong>.gallery-caption</strong> css class is needed in theme css.')), "ut_style_gallery_caption.zip"),
						new Style_Checker(TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Presence of Theme URI'), array('[ \t\/*#]*Theme URI:', __all('Could not find <strong>Theme URL</strong>.')), "ut_style_theme_uri.zip"),
						new Style_Checker(TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Presence of Author URI'), array('[ \t\/*#]*Author URI:', __all('Could not find <strong>Author URI</strong>.')), "ut_style_author_uri.zip")
			);
    }
}