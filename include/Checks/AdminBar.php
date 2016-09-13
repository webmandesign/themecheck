<?php
namespace ThemeCheck;

class AdminBar_Checker extends CheckPart
{	
	public function doCheck($php_files, $php_files_filtered, $css_files, $other_files, $themeInfo)
    {
		$this->errorLevel = ERRORLEVEL_SUCCESS;
		
		if ($this->id == 'ADMINBAR_PHP')
		{
			//Check php files for filter show_admin_bar and show_admin_bar()
			foreach ( $php_files as $file_path => $file_content ) {

				$filename = tc_filename( $file_path );

				if ( preg_match( $this->code, $file_content, $matches ) ) {
					$this->messages[] = __all('Themes should not hide admin bar. Detected in file : %s.', '<strong>' . $filename . '</strong>' );
					$this->errorLevel = $this->threatLevel;
					break;
				}
			}
		} else if ($this->id == 'ADMINBAR_CSS')
		{
			//Check CSS Files for #wpadminbar
			foreach ( $css_files as $file_path => $file_content ) {
				
				$filename = tc_filename( $file_path );

				if ( preg_match( $this->code, $file_content, $matches ) ) {
					$this->messages[] = __all('Themes should not hide admin bar. Detected in file %s.', '<strong>' . $filename . '</strong>' );
					$this->errorLevel = $this->threatLevel;
					break;		
				}
			}
		}
    }
}

class AdminBar extends Check
{	
    protected function createChecks()
    {
		$this->title = __all("Hidden admin bar");
		$this->checks = array(
					new AdminBar_Checker('ADMINBAR_PHP', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('Hidden admin Bar'), "/(add_filter(\s*)\((\s*)(\"|')show_admin_bar(\"|')(\s*)(.*))|(([^\S])show_admin_bar(\s*)\((.*))/", 'ut_adminbar_1.zip'),
					new AdminBar_Checker('ADMINBAR_CSS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Hidden admin Bar in CSS'), "/(#wpadminbar)/", 'ut_adminbar_1.zip'),
		);
    }
}