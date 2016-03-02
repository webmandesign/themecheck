<?php

namespace ThemeCheck;

class MoreDeprecated_Checker extends CheckPart
{		
	public function doCheck($php_files, $php_files_filtered, $css_files, $other_files)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
         		 
		$function = $this->code[0];
		$data = $this->code[1];
        foreach ( $php_files as $php_key => $phpfile )
        {
			foreach ( $data as $parameter => $replacement ) {

					if ( preg_match( '/\s' . $function . '\(\s*("|\')' . $parameter . '("|\')\s*\)/', $phpfile, $matches ) ) {
					$filename      = tc_filename( $php_key );
					$error         = ltrim( rtrim( $matches[0], '(' ) );
					$grep          = tc_grep( $error, $php_key );
					$this->messages[] =  __all( '<strong>%1$s</strong> was found in the file <strong>%2$s</strong>. Use <strong>%3$s</strong> instead.%4$s', $error, $filename, $replacement, $grep );
					$this->errorLevel = $this->threatLevel;
				}
			}
        }
    }
}

class MoreDeprecated extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("Deprecated functions");
			$this->checks = array(
						new MoreDeprecated_Checker('MOREDEPRECATED_GET_BLOGINFO', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_bloginfo'), array('get_bloginfo', array(
				'home'                 => 'home_url()',
				'url'                  => 'home_url()',
				'wpurl'                => 'site_url()',
				'stylesheet_directory' => 'get_stylesheet_directory_uri()',
				'template_directory'   => 'get_template_directory_uri()',
				'template_url'         => 'get_template_directory_uri()',
				'text_direction'       => 'is_rtl()',
				'feed_url'             => "get_feed_link( 'feed' ), where feed is rss, rss2 or atom")), 't_moredeprecatedwordpress_get_bloginfo.zip'),

						new MoreDeprecated_Checker('MOREDEPRECATED_BLOGINFO', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('bloginfo'), array('bloginfo',array(
				'home'                 => 'echo esc_url( home_url() )',
				'url'                  => 'echo esc_url( home_url() )',
				'wpurl'                => 'echo esc_url( site_url() )',
				'stylesheet_directory' => 'echo esc_url( get_stylesheet_directory_uri() )',
				'template_directory'   => 'echo esc_url( get_template_directory_uri() )',
				'template_url'         => 'echo esc_url( get_template_directory_uri() )',
				'text_direction'       => 'is_rtl()',
				'feed_url'             => "echo esc_url( get_feed_link( 'feed' ) ), where feed is rss, rss2 or atom")), 'ut_moredeprecatedwordpress_bloginfo.zip'),
						new MoreDeprecated_Checker('MOREDEPRECATED_GETOPTION', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_option'), array('get_option',array(
				'home'                 => 'home_url()',
				'url'                  => 'home_url()')), 'ut_moredeprecatedwordpress_getoption.zip'),
			);
    }
}