<?php

namespace ThemeCheck;

class NavMenu_Checker extends CheckPart
{		
		public function doCheck($php_files, $css_files, $other_files)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
        $php = implode( ' ', $php_files );
        
        if ( strpos( $php, $this->code ) === false )
        {
            $this->messages[] = __('No reference to nav_menu was found in the theme.');
            $this->errorLevel = $this->threatLevel;
        }
    }
}

class NavMenu extends Check
{	
    protected function createChecks()
    {
			$this->title = __("Nav menu");
			$this->checks = array(
						new NavMenu_Checker(TT_WORDPRESS, ERRORLEVEL_WARNING, __("Correct integration"), 'nav_menu' , 'ut_navmenu.zip'),
			);
    }
}