<?php

namespace ThemeCheck;

class Tags_Checker extends CheckPart
{		
		public function doCheck($php_files, $css_files, $other_files)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
        $php = implode( ' ', $php_files );
        
        if ( strpos( $php, 'the_tags' ) === false && strpos( $php, 'get_the_tag_list' ) === false && strpos( $php, 'get_the_term_list' ) === false )
        {
            $this->messages[] = __all('This theme doesn\'t seem to display tags.');
            $this->errorLevel = $this->threatLevel;
        }
                
    }
}

class Tags extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("Tags");
			$this->checks = array(
						new Tags_Checker(TT_WORDPRESS, ERRORLEVEL_CRITICAL, __all('Tags display'), null, 'ut_tags.zip'),
			);
    }
}