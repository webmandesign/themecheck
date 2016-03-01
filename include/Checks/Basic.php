<?php
namespace ThemeCheck;

class Basic_Checker extends CheckPart
{	
		public function doCheck($php_files, $php_files_filtered, $css_files, $other_files, $themeInfo)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
        
        $php = implode( ' ', $php_files );
        
        $key = $this->code;
        
        if ( !preg_match( '/' . $key . '/i', $php ) ) {
            if ( $this->id == 'BASIC_WP_ADD_THEME_SUPPORT' ) $key =  'add_theme_support( \'automatic-feed-links\' )';
            else if ( $this->id == 'BASIC_WP_BODY_CLASS' ) $key =  'body_class call in body tag';
			else if ( $key === 'charset' ) {
				if (preg_match( '/encoding/i', $php )) return; // in xhtml charset can be declared with <?xml version="1.0" encoding="UTF-8"...
			}
            else $key = substr($key,0,strpos($key, '\s*\('));
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
						new Basic_Checker('BASIC_DOCTYPE', TT_WORDPRESS | TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('Presence of DOCTYPE'), 'DOCTYPE', 'ut_basic_doctype.zip'),
						new Basic_Checker('BASIC_WP_FOOTER', TT_WORDPRESS, ERRORLEVEL_WARNING, __all('Presence of <a href="https://codex.wordpress.org/Function_Reference/wp_footer">wp_footer()</a>'), 'wp_footer\s*\(', 'ut_basic_wp_footer.zip'),
						new Basic_Checker('BASIC_WP_HEAD', TT_WORDPRESS, ERRORLEVEL_WARNING, __all('Presence of <a href="https://codex.wordpress.org/Function_Reference/wp_head">wp_head()</a>'), 'wp_head\s*\(', 'ut_basic_wp_head.zip'),
						new Basic_Checker('BASIC_WP_LANG', TT_WORDPRESS, ERRORLEVEL_WARNING, __all('Presence of <a href="https://codex.wordpress.org/Function_Reference/language_attributes">language_attributes()</a>'), 'language_attributes', 'ut_basic_language_attributes.zip'),
						new Basic_Checker('BASIC_CHARSET', TT_WORDPRESS | TT_JOOMLA, ERRORLEVEL_WARNING, __all('Definition of a charset'), 'charset', 'ut_basic_charset.zip'),
						new Basic_Checker('BASIC_WP_ADD_THEME_SUPPORT', TT_WORDPRESS, ERRORLEVEL_WARNING, __all('Presence of <a href="https://codex.wordpress.org/Function_Reference/add_theme_support">add_theme_support()</a>'), 'add_theme_support\s*\(\s?("|\')automatic-feed-links("|\')\s?\)', 'ut_basic_add_theme_support.zip'),
						new Basic_Checker('BASIC_WP_COMMENTS_TEMPLATE', TT_WORDPRESS, ERRORLEVEL_WARNING, __all('Presence of <a href="https://codex.wordpress.org/Template_Tags/comments_template">comments_template()</a>'), 'comments_template\s*\(', 'ut_basic_comments_template.zip'),
						new Basic_Checker('BASIC_WP_LIST_COMMENTS', TT_WORDPRESS, ERRORLEVEL_WARNING, __all('Presence of <a href="https://codex.wordpress.org/Template_Tags/wp_list_comments">wp_list_comments()</a>'), 'wp_list_comments\s*\(', 'ut_basic_wp_list_comments.zip'),
						new Basic_Checker('BASIC_WP_COMMENT_FORM', TT_WORDPRESS, ERRORLEVEL_WARNING, __all('Presence of <a href="https://codex.wordpress.org/Template_Tags/comment_form">comment_form()</a>'), 'comment_form\s*\(', 'ut_basic_comment_form.zip'),
						new Basic_Checker('BASIC_WP_BODY_CLASS', TT_WORDPRESS, ERRORLEVEL_WARNING, __all('Presence of <a href="https://codex.wordpress.org/Template_Tags/body_class">body_class()</a>'), '<body.*body_class\s*\(', 'ut_basic_body_class.zip'),
						new Basic_Checker('BASIC_WP_LINK_PAGES', TT_WORDPRESS, ERRORLEVEL_WARNING, __all('Presence of <a href="https://codex.wordpress.org/Function_Reference/wp_link_pages">wp_link_pages()</a>'), 'wp_link_pages\s*\(', 'ut_basic_wp_link_pages.zip'),
						new Basic_Checker('BASIC_WP_POST_CLASS', TT_WORDPRESS, ERRORLEVEL_WARNING, __all('Presence of <a href="https://codex.wordpress.org/Template_Tags/post_class">post_class()</a>'), 'post_class\s*\(', 'ut_basic_post_class.zip'),
			);
    }
}