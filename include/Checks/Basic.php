<?php

namespace ThemeCheck;

class Basic_Checker extends CheckPart
{	
		 public function doCheck($php_files, $css_files, $other_files)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
        
        $php = implode( ' ', $php_files );
        
        $key = $this->code;
        
        if ( !preg_match( '/' . $key . '/i', $php ) ) {
            if ( $key === 'add_theme_support\(\s?("|\')automatic-feed-links("|\')\s?\)' ) $key =  'add_theme_support( \'automatic-feed-links\' )';
            if ( $key === 'wp_enqueue_script\(\s?("|\')comment-reply("|\')' ) $key =  'wp_enqueue_script( \'comment-reply\' )';
            if ( $key === '<body.*body_class\(' ) $key =  'body_class call in body tag';
            if ( $key === 'register_sidebar[s]?\(' ) $key =  'register_sidebar() or register_sidebars()';
            $key = ltrim( trim ( trim( $key, '(' ), '\\' ) );
            $this->messages[] = __all('Could not find <strong>%1$s</strong>', $key );
            $this->errorLevel = $this->threatLevel;
        }
    }
}

class Basic extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("Fundamental theme elements");
			$this->checks = array(
						new Basic_Checker(TT_COMMON, ERRORLEVEL_WARNING, __all('Presence of DOCTYPE'), 'DOCTYPE', 'ut_basic_doctype.zip'),
						new Basic_Checker(TT_WORDPRESS, ERRORLEVEL_WARNING, __all('Presence of <a href="http://codex.wordpress.org/Function_Reference/wp_footer">wp_footer()</a>'), 'wp_footer\(', 'ut_basic_wp_footer.zip'),
						new Basic_Checker(TT_WORDPRESS, ERRORLEVEL_WARNING, __all('Presence of <a href="http://codex.wordpress.org/Function_Reference/wp_head">wp_head()</a>'), 'wp_head\(', 'ut_basic_wp_head.zip'),
						new Basic_Checker(TT_WORDPRESS, ERRORLEVEL_WARNING, __all('Presence of <a href="http://codex.wordpress.org/Function_Reference/language_attributes">language_attributes()</a>'), 'language_attributes', 'ut_basic_language_attributes.zip'),
						new Basic_Checker(TT_COMMON, ERRORLEVEL_WARNING, __all('Definition of a charset'), 'charset', 'ut_basic_charset.zip'),
						new Basic_Checker(TT_WORDPRESS, ERRORLEVEL_WARNING, __all('Presence of <a href="http://codex.wordpress.org/Function_Reference/add_theme_support">add_theme_support()</a>'), 'add_theme_support\(\s?("|\')automatic-feed-links("|\')\s?\)', 'ut_basic_add_theme_support.zip'),
						new Basic_Checker(TT_WORDPRESS, ERRORLEVEL_WARNING, __all('Presence of <a href="http://codex.wordpress.org/Function_Reference/register_sidebar">register_sidebar()</a>'), 'register_sidebar[s]?\(', 'ut_basic_register_sidebar.zip'),
						new Basic_Checker(TT_WORDPRESS, ERRORLEVEL_WARNING, __all('Presence of <a href="http://codex.wordpress.org/Function_Reference/dynamic_sidebar">dynamic_sidebar()</a>'), 'dynamic_sidebar\(', 'ut_basic_dynamic_sidebar.zip'),
						new Basic_Checker(TT_WORDPRESS, ERRORLEVEL_WARNING, __all('Presence of <a href="http://codex.wordpress.org/Template_Tags/comments_template">comments_template()</a>'), 'comments_template\(', 'ut_basic_comments_template.zip'),
						new Basic_Checker(TT_WORDPRESS, ERRORLEVEL_WARNING, __all('Presence of <a href="http://codex.wordpress.org/Template_Tags/wp_list_comments">wp_list_comments()</a>'), 'wp_list_comments\(', 'ut_basic_wp_list_comments.zip'),
						new Basic_Checker(TT_WORDPRESS, ERRORLEVEL_WARNING, __all('Presence of <a href="http://codex.wordpress.org/Template_Tags/comment_form">comment_form()</a>'), 'comment_form\(', 'ut_basic_comment_form.zip'),
						new Basic_Checker(TT_WORDPRESS, ERRORLEVEL_WARNING, __all('Presence of <a href="http://codex.wordpress.org/Template_Tags/body_class">body_class()</a>'), '<body.*body_class\(', 'ut_basic_body_class.zip'),
						new Basic_Checker(TT_WORDPRESS, ERRORLEVEL_WARNING, __all('Presence of <a href="http://codex.wordpress.org/Function_Reference/wp_link_pages">wp_link_pages()</a>'), 'wp_link_pages\(', 'ut_basic_wp_link_pages.zip'),
						new Basic_Checker(TT_WORDPRESS, ERRORLEVEL_WARNING, __all('Presence of <a href="http://codex.wordpress.org/Template_Tags/post_class">post_class()</a>'), 'post_class\(', 'ut_basic_post_class.zip'),
			);
    }
}