<?php
namespace ThemeCheck;

class Deregister_Checker extends CheckPart
{	
	public function doCheck($php_files, $php_files_filtered, $css_files, $other_files, $themeInfo)
    {		
        $this->errorLevel = ERRORLEVEL_SUCCESS;
        
		foreach ( $php_files as $file_path => $file_content )
		{
			$filename = tc_filename( $file_path );

			if ( preg_match( '/wp_deregister_script/', $file_content) ) {

				$error = '/wp_deregister_script/';
				$grep = tc_preg( $error, $file_path );

				$this->messages[] = __all( 'Found wp_deregister_script in %1$s. Themes must not deregister core scripts. %2$s', '<strong>' . esc_html($filename) . '</strong>', $grep );
				$this->errorLevel = $this->threatLevel;	
			}
		}
    }
}

class Deregister extends Check
{	
    protected function createChecks()
    {
		$this->title = __all("core scripts deregistered");
		$this->checks = array(
					new Deregister_Checker('DEREGISTER', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Core scripts deregistration'), null, 'ut_deregister.zip'),
		);
    }
}