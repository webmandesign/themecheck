<?php
namespace ThemeCheck;

class PluginTerritory_Checker extends CheckPart
{	
	public function doCheck($php_files, $php_files_filtered, $css_files, $other_files, $themeInfo)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
		
		$php = implode( ' ', $php_files );

		foreach ( $this->code as $forbidden_function)
		{
			if ( false !== strpos( $php, $forbidden_function )) // optimization : strpos is faster than preg_match, and since the condition is rarely true, it is globally faster to use strpos as a filter before preg_match
			{
				if ( preg_match( '/\s' . $forbidden_function . '\s*\(/', $php)) {

					$this->messages[] = __all( 'The theme uses the %s function, which is plugin-territory functionality.', '<strong>' . esc_html( $forbidden_function ) . '()</strong>' ) ;
					$this->errorLevel = $this->threatLevel;
					break;
				}
			}
		}
		
		// Shortcodes can't be used in the post content, so warn about them.
		if ( false !== strpos( $php, 'add_shortcode' )) // optimization : strpos is faster than preg_match, and since the condition is rarely true, it is globally faster to use strpos as a filter before preg_match
		{
			if ( preg_match( '/\sadd_shortcode\s*\(/', $php)) {
				$this->messages[] = __all( 'The theme uses the %s function. Custom post-content shortcodes are plugin-territory functionality.', '<strong>add_shortcode()</strong>' ) ;
				$this->errorLevel = $this->threatLevel;
			}
		}
    }
}

class PluginTerritory extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("Plugin territory");
			$this->checks = array(
						new PluginTerritory_Checker('PluginTerritory_Checker', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all("Plugin territory functionalities"), 
									array(
										'register_post_type',
										'register_taxonomy',
									) , 'ut_pluginterritory.zip')
			);
    }
}