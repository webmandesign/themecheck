<?php
namespace ThemeCheck;

class AdminMenu_Checker extends CheckPart
{		
	public function doCheck($php_files, $php_files_filtered, $css_files, $other_files, $themeInfo)
    {		
        $this->errorLevel = ERRORLEVEL_SUCCESS;
        
		if ( $this->id == 'ADMIN_ADMINPAGES' ) 
		{
			foreach ( $php_files as $php_key => $phpfile ) 
			{				
				if ( preg_match_all( $this->code, $phpfile, $matches ) ) {
					foreach ($matches[1] as $match) {
						if ($match == 'add_theme_page') {
							continue;
						}
						$filename = tc_filename( $php_key );
						$error = ltrim( rtrim( $match, '(' ) );
						$grep = tc_grep( $error, $php_key );
						$this->messages[] = __all('File <strong>%1$s</strong> : %2$s', $filename, $grep);
					$this->errorLevel = $this->threatLevel;
					}
				}
			}
		} else {
			foreach ( $php_files as $php_key => $phpfile ) 
			{
				if ( preg_match( $this->code, $phpfile, $matches ) ) {
					$filename = tc_filename( $php_key );
					$grep = ( isset( $matches[2] ) ) ? tc_grep( $matches[2], $php_key ) : tc_grep( $matches[1], $php_key );
					$this->messages[] = __all('File <strong>%1$s</strong> : %2$s', $filename, $grep);
					$this->errorLevel = $this->threatLevel;
				}
			}
		}
    }
}

class AdminMenu extends Check
{
	protected function createChecks()
    {
		$this->title = __all("Admin menu");
		$this->checks = array(
					new AdminMenu_Checker('ADMIN_ADMINPAGES', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('Themes should use <strong>add_theme_page()</strong> for adding admin pages.'), '/(?<!function)[^_>:](add_[^_\'",();]+?_page)/', 'ut_adminmenu_addadminpage.zip'),
					new AdminMenu_Checker('ADMIN_USERLEVELS1', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Detection of user levels deprecated in WordPress 2.0. See <a href="https://codex.wordpress.org/Roles_and_Capabilities">Wordpress codex</a>.'),'/([^_](add_(admin|submenu|menu|dashboard|posts|media|links|pages|comments|theme|plugins|users|management|options)_page)\s?\([^,]*,[^,]*,\s[\'|"]?(level_[0-9]|[0-9])[^;|\r|\r\n]*)/', 'ut_adminmenu_addmenupage.zip'),
					new AdminMenu_Checker('ADMIN_USERLEVELS2', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Detection of user levels deprecated in WordPress 2.0. See <a href="https://codex.wordpress.org/Roles_and_Capabilities">Wordpress codex</a>.'), '/[^a-z0-9](current_user_can\s?\(\s?[\'\"]level_[0-9][\'\"]\s?\))[^\r|\r\n]*/', 'ut_adminmenu_current_user_can.zip'),
		);
    }
}