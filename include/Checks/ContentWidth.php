<?php

namespace ThemeCheck;

class ContentWidth_Checker extends CheckPart
{
    public function doCheck($php_files, $php_files_filtered, $css_files, $other_files)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
        
        // combine all the php files into one string to make it easier to search
        $php = implode( ' ', $php_files );
        if ( strpos( $php, $this->code ) === false && !preg_match( '/add_filter\(\s?("|\')embed_defaults/', $php ) && !preg_match( '/add_filter\(\s?("|\')content_width/', $php ) ) {
            $this->messages[] =  __all('No content width has been defined. Example: <pre>if ( ! isset( $content_width ) ) $content_width = 900;</pre>');
            $this->errorLevel = $this->threatLevel;
        }
    }
}

class ContentWidth extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("Content width");
			$this->checks = array(
						new ContentWidth_Checker('CONTENT_WIDTH', TT_WORDPRESS, ERRORLEVEL_CRITICAL, __all('Proper definition of content_width') , '$content_width', 'ut_badthings_eval.zip')
			);
    }
}