<?php

namespace ThemeCheck;

class Gravatar_Checker extends CheckPart
{		
		public function doCheck($php_files, $css_files, $other_files)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
        $php = implode( ' ', $php_files );
        
        if ( (strpos( $php, $this->code[0] ) === false) && ( strpos( $php, $this->code[1] ) === false ) )
        {
            $this->messages[] = __all('This theme doesn&#39;t seem to support the standard avatar functions. Use <strong>get_avatar</strong> or <strong>wp_list_comments</strong> to add this support.');
            $this->errorLevel = $this->threatLevel;
        }
    }
}

class Gravatar extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("Avatar");
			$this->checks = array(
						new Gravatar_Checker(TT_WORDPRESS, ERRORLEVEL_ERROR, __all("Support of standard avatar functions"), array('get_avatar','World') , 'ut_gravatar.zip'),
			);
    }
}