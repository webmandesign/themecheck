<?php

namespace ThemeCheck;

class CommentReply_Checker extends CheckPart
{	
		public function doCheck($php_files, $css_files, $other_files)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
        
        $php = implode( ' ', $php_files );
        
        if ( ! preg_match( $this->code[0], $php ) ) {
            if ( ! preg_match( $this->code[1], $php ) ) {
                $this->messages[] = __all('Could not find the <strong>comment-reply</strong> script enqueued.');
                $this->errorLevel = $this->threatLevel;
            }
            else
            {
                $this->messages[] = __all('Could not find the <strong>comment-reply</strong> script enqueued, however a reference to \'comment-reply\' was found. Make sure that the comment-reply script is being enqueued properly on singular pages.');
								$this->errorLevel = ERRORLEVEL_WARNING;
						}
        }
    }
}

class CommentReply extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("Comment reply");
			$this->checks = array(
						new CommentReply_Checker('COMMENT_REPLY', TT_WORDPRESS, ERRORLEVEL_CRITICAL, __all("Declaration of comment reply"), array('/wp_enqueue_script\(\s?("|\')comment-reply("|\')/i','/comment-reply/'), 'ut_commentreply_enqueue_script.zip')
			);
    }
}