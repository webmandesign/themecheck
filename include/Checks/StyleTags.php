<?php

namespace ThemeCheck;

class StyleTags_Checker extends CheckPart
{		
	public function doCheck($php_files, $php_files_filtered, $css_files, $other_files, $themeInfo)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;

		$allowed_tags = array("black","blue","brown","gray","green","orange","pink","purple","red","silver","tan","white","yellow","dark","light","one-column","two-columns","three-columns","four-columns","left-sidebar","right-sidebar","fixed-layout","fluid-layout","responsive-layout","flexible-header","accessibility-ready","blavatar","buddypress","custom-background","custom-colors","custom-header","custom-menu","editor-style","featured-image-header","featured-images","front-page-post-form","full-width-template","microformats","post-formats","rtl-language-support","sticky-post","theme-options","threaded-comments","translation-ready","holiday","photoblogging","seasonal");
		$tags_array = explode(',', $themeInfo->tags);
		
		foreach( $tags_array as $tag ) {
			$tag = trim($tag);// clean after explode()
			if ( strpos( strtolower( $tag ), "accessibility-ready") !== false ) {
				$this->messages[] = __all('Themes that use the tag accessibility-ready will need to undergo an accessibility review.<br/>See <a href="https://make.wordpress.org/themes/handbook/review/accessibility/">https://make.wordpress.org/themes/handbook/review/accessibility/</a>');
				if ($this->errorLevel == ERRORLEVEL_SUCCESS) $this->errorLevel = ERRORLEVEL_INFO;
			}

			if ( !in_array( strtolower( $tag ), $allowed_tags ) ) {
				if ( in_array( strtolower( $tag ), array("flexible-width","fixed-width") ) ) {
					$this->messages[] = __all('The flexible-width and fixed-width tags changed to fluid-layout and fixed-layout tags in WordPress 3.8. Additionally, the responsive-layout tag was added.');
					$this->errorLevel = $this->threatLevel;
				} else {
					$this->messages[] = __all('Found wrong tag %s in style.css header.', '<strong>' . esc_html($tag) . '</strong>');
					$this->errorLevel = $this->threatLevel;
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
			);
    }
}