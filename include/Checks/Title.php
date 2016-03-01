<?php
namespace ThemeCheck;

/**
 * Checks for the title:
 * Are there <title> and </title> tags?
 * Is there a call to wp_title()?
 * There can't be any hardcoded text in the <title> tag.
 *
 * See: http://make.wordpress.org/themes/guidelines/guidelines-theme-check/
 */
class Title_Checker extends CheckPart
{	
	public function doCheck($php_files, $php_files_filtered, $css_files, $other_files, $themeInfo)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
		
		$php = implode( ' ', $php_files );
		
		// Look for add_theme_support( 'title-tag' ) first
		$titletag = true;
		if ( ! preg_match( '#add_theme_support\s?\(\s?[\'|"]title-tag#', $php ) ) {
			$this->messages[] = __all('No reference to <strong>add_theme_support( "title-tag" )</strong> was found in the theme. It is recommended that the theme implement this functionality for WordPress 4.1 and above.' );
			$this->errorLevel = ERRORLEVEL_WARNING;
			$titletag = false;
		}
		
		// Look for <title> and </title> tags.
		if ( ( false === strpos( $php, '<title>' ) || false === strpos( $php, '</title>' ) ) && !$titletag  ) {
			$this->messages[] = __all( 'The theme needs to have <strong>&lt;title&gt;</strong> tags, ideally in the <strong>header.php</strong> file.');
			$this->errorLevel = ERRORLEVEL_CRITICAL;
		}

		// Check whether there is a call to wp_title()
		if ( false === strpos( $php, 'wp_title(' ) && !$titletag ) {
			$this->messages[] = __all( 'The theme needs to have a call to <strong>wp_title()</strong>, ideally in the <strong>header.php</strong> file.');
			$this->errorLevel = ERRORLEVEL_CRITICAL;
		}

		//Check whether the the <title> tag contains something besides a call to wp_title()
		foreach ( $php_files as $file_path => $file_content ) {
			// Look for anything that looks like <svg>...</svg> and exclude it (inline svg's have titles too)
			$file_content = preg_replace('/<svg>.*<\/svg>/s', '', $file_content);
			
			// First looks ahead to see of there's <title>...</title>
			// Then performs a negative look ahead for <title> wp_title(...); </title>
			if ( preg_match( '/(?=<title>(.*)<\/title>)(?!<title>\s*<\?php\s*wp_title\([^\)]*\);?\s*\?>\s*<\/title>)/s', $file_content ) ) {
				$this->messages[] = __all( 'The <strong>&lt;title&gt;</strong> tags can only contain a call to <strong>wp_title()</strong>. Use the  <strong>wp_title filter</strong> to modify the output.');
				$this->errorLevel = ERRORLEVEL_CRITICAL;
			}
		}
    }
}

class Title extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("Title");
			$this->checks = array(
						new Title_Checker('Title_Checker', TT_WORDPRESS, ERRORLEVEL_CRITICAL, __all("Title"), 
									null , 'ut_pluginterritory.zip')
			);
    }
}