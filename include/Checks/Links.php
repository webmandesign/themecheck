<?php
namespace ThemeCheck;

class Links_Checker extends CheckPart
{	
	public function doCheck($php_files, $php_files_filtered, $css_files, $other_files, $themeInfo)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
        $authorURI = $themeInfo->authorUri;
		$URI = $themeInfo->themeUri;
		foreach ( $php_files as $php_key => $phpfile ) {
			$grep = '';
			// regex borrowed from TAC
			$url_re = '([[:alnum:]\-\.])+(\\.)([[:alnum:]]){2,4}([[:blank:][:alnum:]\/\+\=\%\&\_\\\.\~\?\-]*)';
			$title_re = '[[:blank:][:alnum:][:punct:]]*';	// 0 or more: any num, letter(upper/lower) or any punc symbol
			$space_re = '(\\s*)';
			if ( preg_match_all( "/(<a)(\\s+)(href" . $space_re . "=" . $space_re . "\"" . $space_re . "((http|https|ftp):\\/\\/)?)" . $url_re . "(\"" . $space_re . $title_re . $space_re . ">)" . $title_re . "(<\\/a>)/is", $phpfile, $out, PREG_SET_ORDER ) ) {
				$filename = tc_filename( $php_key );
				foreach( $out as $key ) {
					if ( preg_match( '/\<a\s?href\s?=\s?["|\'](.*?)[\'|"](.*?)\>(.*?)\<\/a\>/is', $key[0], $stripped ) ) {
						if ( !empty( $authorURI ) && !empty( $URI ) && $stripped[1] && !strpos( $stripped[1], $URI ) && !strpos( $stripped[1], $authorURI ) && !strpos( $stripped[1], 'wordpress.' ) ) {
						$grep .= tc_grep( $stripped[1], $php_key );
						}
					}
				}
				if ( $grep ) {
					$this->messages[] = __all('Possible hard-coded links were found in the file %1$s.%2$s', '<strong>' . esc_html($filename) . '</strong>', $grep );
					$this->errorLevel = $this->threatLevel;
				}
			}
		}
    }
}

class Links extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("Static links");
			$this->checks = array(
						new Links_Checker('LINKS_STATIC', TT_COMMON, ERRORLEVEL_INFO, __all('Presence of hard-coded links'), null, 'ut_links_static.zip'),
			);
    }
}