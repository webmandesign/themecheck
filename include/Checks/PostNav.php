<?php

namespace ThemeCheck;

class PostNav_Checker extends CheckPart
{
		public function doCheck($php_files, $php_files_filtered, $css_files, $other_files)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
        $php = implode( ' ', $php_files );
				
        if ( strpos( $php, 'posts_nav_link' ) === false && strpos( $php, 'paginate_links' ) === false && strpos( $php, 'the_posts_pagination' ) === false && strpos( $php, 'the_posts_navigation' ) === false &&
              strpos( $php, 'previous_posts_link' ) === false && strpos( $php, 'next_posts_link' ) === false 
           ) {
                $this->messages[] = __all('The theme doesn&#39;t have post pagination code in it. Use <strong>posts_nav_link()</strong> or <strong>paginate_links()</strong> or <strong>the_posts_pagination()</strong> or <strong>the_posts_navigation()</strong> or <strong>next_posts_link()</strong> and <strong>previous_posts_link()</strong> to add post pagination.');
                $this->errorLevel = $this->threatLevel;
        }
    }
}

class PostNav extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("Post pagination");
			$this->checks = array(
						new PostNav_Checker('POSTNAV', TT_WORDPRESS, ERRORLEVEL_CRITICAL, __all("Implementation"), null, 'ut_postnav.zip'),
			);
    }
}