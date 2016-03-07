<?php
namespace ThemeCheck;

class Favicon_Checker extends CheckPart
{	
	public function doCheck($php_files, $php_files_filtered, $css_files, $other_files, $themeInfo)
    {		
        $this->errorLevel = ERRORLEVEL_SUCCESS;
        
        foreach ( $php_files as $file_path => $file_content ) 
		{
			$filename = tc_filename( $file_path );

			if ( preg_match( '/(<link rel=[\'"]icon[\'"])|(<link rel=[\'"]apple-touch-icon-precomposed[\'"])|(<meta name=[\'"]msapplication-TileImage[\'"])/', $file_content, $matches ) ) {	
				$this->messages[] = __all( 'Possible Favicon found in %1$s. Favicons are handled by the Site Icon setting in the customizer since version 4.3.', '<strong>' . esc_html($filename) . '</strong>' );
				$this->errorLevel = $this->threatLevel;
			}
		}
    }
}

class Favicon extends Check
{	
    protected function createChecks()
    {
		$this->title = __all("favicon presence");
		$this->checks = array(
					new Favicon_Checker('FAVICON', TT_COMMON, ERRORLEVEL_INFO, __all('Favicon management'), null, 'ut_favicon.zip'),
		);
    }
}