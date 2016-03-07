<?php
namespace ThemeCheck;

class Customizer_Checker extends CheckPart 
{	
	public function doCheck($php_files, $php_files_filtered, $css_files, $other_files, $themeInfo)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
		$done = false;
		// Check whether every Customizer setting has a sanitization callback set.
		foreach ( $php_files as $file_path => $file_content ) {
			$filename = tc_filename( $file_path );
			// Get the arguments passed to the add_setting method
			if ( preg_match_all( '/\$wp_customize->add_setting\(([^;]+)/', $file_content, $matches ) ) {
				// The full match is in [0], the match group in [1]
				foreach ( $matches[1] as $match ) {
					// Check if we have sanitize_callback or sanitize_js_callback
					if ( false === strpos( $match, 'sanitize_callback' ) && false === strpos( $match, 'sanitize_js_callback' ) ) {
						$this->messages[] = __all( 'Found a Customizer setting that did not have a sanitization callback function in file <strong>%s</strong>. Every call to the <strong>add_setting()</strong> method needs to have a sanitization callback function passed.', esc_html($filename)  );
						$this->errorLevel = $this->threatLevel;
						break;
					} else {
						// There's a callback, check that no empty parameter is passed.
						if ( preg_match( '/[\'"](?:sanitize_callback|sanitize_js_callback)[\'"]\s*=>\s*[\'"]\s*[\'"]/', $match ) ) {
							$this->messages[] = __all( 'Found a Customizer setting that had an empty value passed as sanitization callback in file <strong>%s</strong>. You need to pass a function name as sanitization callback.', esc_html($filename));
							$this->errorLevel = $this->threatLevel;
							break;
						}
					}
				}
			}
			if ($done) break;
		}
    }
}

class Customizer extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("Customizer");
			$this->checks = array(
						new Customizer_Checker('Customizer', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all("Sanitization of Customizer settings"), 
									null , 'ut_customizer.zip')
			);
    }
}