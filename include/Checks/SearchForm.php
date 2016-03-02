<?php

namespace ThemeCheck;

class SearchForm_Checker extends CheckPart
{
		public function doCheck($php_files, $php_files_filtered, $css_files, $other_files)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;

        foreach ( $php_files as $php_key => $phpfile )
        {
            if ( preg_match( $this->code, $phpfile, $out ) )
            {
                $grep = tc_preg( $this->code, $php_key );
                $filename = tc_filename( $php_key );
                $this->messages[] = __all('File <strong>%1$s</strong> :%2$s Use <strong>get_search_form()</strong> instead of including searchform.php directly.', $filename, $grep);
                $this->errorLevel = $this->threatLevel;
            }
        }
    }
}

class SearchForm extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("Search form");
			$this->checks = array(
						new SearchForm_Checker('SEARCHFORM', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('Implementation'), '/(include\s?\(\s?TEMPLATEPATH\s?\.?\s?["|\']\/searchform.php["|\']\s?\))/', 'ut_searchform.zip'),
			);
    }
}