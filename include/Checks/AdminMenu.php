<?php
namespace ThemeCheck;

class AdminMenu_Checker extends CheckPart
{		
		public function doCheck($php_files, $css_files, $other_files)
    {		
        $this->errorLevel = ERRORLEVEL_SUCCESS;
               
        foreach ( $php_files as $php_key => $phpfile ) 
				{
            if ( preg_match( $this->code, $phpfile, $matches ) ) {
                $filename = tc_filename( $php_key );
                $error = ltrim( rtrim( $matches[0], '(' ) );
                $grep = tc_grep( $error, $php_key );
                $this->messages[] = __all('File <strong>%1$s</strong> : %2$s', $filename, $grep);
                $this->errorLevel = $this->threatLevel;
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
						new AdminMenu_Checker('ADMIN_ADMINPAGES', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('Themes that support admin pages should use <strong>add_theme_page()</strong> instead of other functions (add_admin_page, add_submenu_page...)'), '/([^_]add__all(admin|submenu|menu|dashboard|posts|media|links|pages|comments|plugins|users|management|options)_page\()/', 'ut_adminmenu_addadminpage.zip'),
						new AdminMenu_Checker('ADMIN_USERLEVELS1', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Detection of user levels that were deprecated in Worpress 2.0. <a href="http://codex.wordpress.org/Roles_and_Capabilities">Wordpress codex</a>'), '/([^_](add__all(admin|submenu|menu|dashboard|posts|media|links|pages|comments|theme|plugins|users|management|options)_page)\s?\([^,]*,[^,]*,\s[\'|"]?(level_[0-9]|[0-9])[^;|\r|\r\n]*)/', 'ut_adminmenu_addmenupage.zip'),
						new AdminMenu_Checker('ADMIN_USERLEVELS2', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Detection of user levels that were deprecated in Worpress 2.0. <a href="http://codex.wordpress.org/Roles_and_Capabilities">Wordpress codex</a>'), '/[^a-z0-9](current_user_can\s?\(\s?[\'\"]level_[0-9][\'\"]\s?\))[^\r|\r\n]*/', 'ut_adminmenu_current_user_can.zip'),
			);
    }
}