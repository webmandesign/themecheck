<?php

namespace ThemeCheck;

class TimeDate_Checker extends CheckPart
{
		public function doCheck($php_files, $css_files, $other_files)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
        
        foreach ( $php_files as $php_key => $phpfile )
        {
            if ( preg_match( $this->code, $phpfile, $matches ) )
            {
                $filename = tc_filename( $php_key );
                $matches[0] = str_replace(array('"',"'"),'', $matches[0]);
                $error = trim( esc_html( rtrim( $matches[0], '(' ) ) );
                $this->messages[] = __all('At least one hard coded date was found in the file <strong>%s</strong>. Function get_option( &#39;date_format&#39; ) should be used instead.', $filename );
                $this->errorLevel = $this->threatLevel;
            }
        }
    }
}

class TimeDate extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("Date and time implementation");
			$this->checks = array(
						new TimeDate_Checker(TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Use of date_i18n()'), '/\sdate_i18n\s?\(\s?["|\'][A-Za-z\s]+["|\']\s?\)/', 'ut_timedate_date_i18n.zip'),
						new TimeDate_Checker(TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Use of the_date()'), '/[^get_]the_date\s?\(\s?["|\'][A-Za-z\s]+["|\']\s?\)/', 'ut_timedate_the_date.zip'),
						new TimeDate_Checker(TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Use of the_time()'), '/[^get_]the_time\s?\(\s?["|\'][A-Za-z\s]+["|\']\s?\)/', 'ut_timedate_the_time.zip'),
			);
    }
}