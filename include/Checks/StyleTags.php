<?php

namespace ThemeCheck;

class StyleTags_Checker extends CheckPart
{		
	public function doCheck($php_files, $php_files_filtered, $css_files, $other_files, $themeInfo)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;

		$deprecated_tags = array("flexible-width","fixed-width","black","blue","brown","gray","green","orange","pink","purple","red","silver","tan","white","yellow","dark","light","fixed-layout","fluid-layout","responsive-layout","blavatar","holiday","photoblogging","seasonal");
		$allowed_tags = array('grid-layout',"one-column","two-columns","three-columns","four-columns","left-sidebar","right-sidebar","flexible-header",'footer-widgets',"accessibility-ready","buddypress","custom-background","custom-colors","custom-header","custom-menu","custom-logo","editor-style","featured-image-header","featured-images","front-page-post-form","full-width-template","microformats","post-formats","rtl-language-support","sticky-post","theme-options","threaded-comments","translation-ready",'blog','e-commerce','education','entertainment','food-and-drink','holiday','news','photography','portfolio');
		$subject_tags = array('blog','e-commerce','education','entertainment','food-and-drink','holiday','news','photography','portfolio');
		$subject_tags_count = 0;
		$subject_tags_name = "";
		$tags_array = explode(',', $themeInfo->tags);
		
		if ($this->id == "STYLETAGS")
		{
			foreach( $tags_array as $tag ) {
				$tag = trim($tag);// clean after explode()
				if ( strpos( strtolower( $tag ), "accessibility-ready") !== false ) {
					$this->messages[] = __all('Themes that use the tag accessibility-ready will need to undergo an accessibility review.<br/>See <a href="https://make.wordpress.org/themes/handbook/review/accessibility/">https://make.wordpress.org/themes/handbook/review/accessibility/</a>');
					if ($this->errorLevel == ERRORLEVEL_SUCCESS) $this->errorLevel = ERRORLEVEL_INFO;
				}
				
				if ( !in_array( strtolower( $tag ), $allowed_tags ) ) {
					if ( in_array( strtolower( $tag ), $deprecated_tags ) ) {
						$this->messages[] = __all('The tag %s has been deprecated, it must be removed from style.css header.', '<strong>' . esc_html($tag) . '</strong>' );
						$this->errorLevel = $this->threatLevel;
					} else {
						$this->messages[] = __all('Found wrong tag %s in style.css header.', '<strong>' . esc_html($tag) . '</strong>');
						$this->errorLevel = $this->threatLevel;
					}
				}
			}
		} else if ($this->id == "STYLETAGS_SUBJECT")
		{
			foreach( $tags_array as $tag ) {
				$tag = trim($tag);// clean after explode()
								
				if ( in_array( strtolower( $tag ), $subject_tags ) ) {
					$subject_tags_name .= strtolower( $tag ) . ', ';
					$subject_tags_count++;
				}
			}
			if ( $subject_tags_count > 3 ) {
				$this->error[] = __all('A maximum of 3 subject tags are allowed. The theme has %1$u subjects tags ( %2$s ). Subject tags which do not directly apply to the theme should be removed. <a target="_blank" href="https://make.wordpress.org/themes/handbook/review/required/theme-tags/">See Theme Tags</a>', $subject_tags_count, '<strong>' . rtrim( $subject_tags_name, ', ' ) . '</strong>' );
				$this->errorLevel = $this->threatLevel;
			}
		} else if ($this->id == "STYLETAGS_DUPLICATE")
		{
			foreach( $tags_array as $tag ) {
				$tag = trim($tag);// clean after explode()
								
				if ( in_array( strtolower( $tag ), $allowed_tags ) ) {
					if ( count( array_keys ($tags_array, $tag ) ) > 1) {
						$this->error[] = __all('The tag %s is being used more than once in style.css header.', '<strong>' . $tag . '</strong>' );
						$this->errorLevel = $this->threatLevel;
					}
				}
			}
		}
    }
}

class StyleTags extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("theme tags");
			$this->checks = array(
						new StyleTags_Checker('STYLETAGS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Presence of bad theme tags'), null, 'ut_styletags.zip'),
						new StyleTags_Checker('STYLETAGS_SUBJECT', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Too many subject tags'), null, 'ut_styletagssubject.zip'),
						new StyleTags_Checker('STYLETAGS_DUPLICATE', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Duplicate tags'), null, 'ut_styletagsduplicate.zip'),
			);
    }
}
