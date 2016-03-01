<?php

namespace ThemeCheck;

class Uri_Checker extends CheckPart
{		
	public function doCheck($php_files, $php_files_filtered, $css_files, $other_files, $themeInfo)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;

		$authorURI = $themeInfo->authorUri;
		$authorName = $themeInfo->author;
		$URI = $themeInfo->themeUri;
		
		if ( !empty( $authorURI  ) && !empty( $URI ) ) {
			if ( strtolower( preg_replace('/https?:\/\/|www./i', '', trim( $URI , '/' ) ) ) == strtolower( preg_replace('/https?:\/\/|www./i', '', trim( $authorURI, '/' ) ) ) )  {
				$this->messages[] = __all('Theme URI and Author URI should not be the same.');
				$this->errorLevel = $this->threatLevel;
			}
	
			//We allow .org user profiles as Author URI, so only check the Theme URI. We also allow WordPress.com links.
			if ( stripos( $URI, 'wordpress.org' ) && $authorName <> "the WordPress team" || stripos( $URI, 'w.org' ) && $authorName <> "the WordPress team" ) {
				$this->messages[] = __all('Using a WordPress.org Theme URI is reserved for official themes.');
				$this->errorLevel = $this->threatLevel;
			}
		}
    }
}

class Uri extends Check
{	
    protected function createChecks()
    {
		$this->title = __all("special URIs");
		$this->checks = array(
					new Uri_Checker('URI', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Presence of bad theme tags'), null, 'ut_uri.zip'),
		);
    }
}