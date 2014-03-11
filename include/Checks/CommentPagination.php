<?php
namespace ThemeCheck;

class CommentPagination_Checker extends CheckPart
{		
		public function doCheck($php_files, $css_files, $other_files)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
        $php = implode( ' ', $php_files );
				$parts = explode (' ', $this->code);
				$fail = true;
				foreach($parts as $p) if (strpos( $php, $p ) !== false) $fail = false;
				
        if ($fail)
        {
            $this->messages[] = __all('The theme doesn\'t have comment pagination code in it. Use <strong>paginate_comments_links()</strong> to add comment pagination.');
            $this->errorLevel = $this->threatLevel;
        }
    }
}

class CommentPagination extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("Comment pagination");
			$this->checks = array(
						new CommentPagination_Checker(TT_WORDPRESS, ERRORLEVEL_CRITICAL, __all("Declaration of comment pagination"), "paginate_comments_links previous_comments_link next_comments_link", 'ut_commentpagination.zip')
			);
    }
}