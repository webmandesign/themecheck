<?php
namespace ThemeCheck;

class Deprecated_Checker extends CheckPart
{
		public function doCheck($php_files, $php_files_filtered, $css_files, $other_files)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
        
				$key = $this->code[0];
				$key_instead = $this->code[1];
				$deprecatedSinceVersion = $this->code[2];
		
        foreach ( $php_files_filtered as $php_key => $phpfile )
        {
						if (strpos($phpfile, $key) !== false)// optimization : strpos is faster than preg_match, and since the condition is rarely true, it is globally faster to use strpos as a filter before preg_match
						{ 
								if ( preg_match( '/[\s]+' . $key . '[\s-]*\(/', $phpfile, $matches ) )
								{
										$filename = tc_filename( $php_key );
										$error = ltrim( rtrim( $matches[0], '(' ) );
										$grep = tc_grep( $error, $php_key );
										$this->messages[] = __all('<strong>%1$s</strong> found in file <strong>%2$s</strong>. Deprecated since version <strong>%3$s</strong>. Use <strong>%4$s</strong> instead.%5$s', $error, $filename, $deprecatedSinceVersion, htmlspecialchars($key_instead), $grep );
										
										$this->errorLevel = $this->threatLevel;
								}
						}
        }
    }
}

class Deprecated extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("Deprecated functions");
			$this->checks = array(
					new Deprecated_Checker('DEPRECATED_GET_POST_DATA', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_post_data'), array('get_post_data', 'get_post()', '1.5.1' ), 'ut_deprecatedwordpress_get_post_data.zip'),
					new Deprecated_Checker('DEPRECATED_START_WP', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('start_wp'), array('start_wp', 'Use the Loop', '1.5' ),'ut_deprecatedwordpress_start_wp.zip'),
					new Deprecated_Checker('DEPRECATED_THE_CATEGORY_ID', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('the_category_id'), array('the_category_id', 'get_the_category()', '0.71' ),'ut_deprecatedwordpress_the_category_id.zip'),
					new Deprecated_Checker('DEPRECATED_THE_CATEGORY_HEAD', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('the_category_head'), array('the_category_head', 'get_the_category_by_ID()', '0.71' ),'ut_deprecatedwordpress_the_category_head.zip'),
					new Deprecated_Checker('DEPRECATED_PREVIOUS_POST', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('previous_post'), array('previous_post', 'previous_post_link()', '2.0' ),'ut_deprecatedwordpress_previous_post.zip'),
					new Deprecated_Checker('DEPRECATED_NEXT_POST', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('next_post'), array('next_post', 'next_post_link()', '2.0' ),'ut_deprecatedwordpress_next_post.zip'),
					new Deprecated_Checker('DEPRECATED_USER_CAN_CREATE_POST', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('user_can_create_post'), array('user_can_create_post', 'current_user_can()', '2.0' ),'ut_deprecatedwordpress_user_can_create_post.zip'),
					new Deprecated_Checker('DEPRECATED_USER_CAN_CREATE_DRAFT', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('user_can_create_draft'), array('user_can_create_draft', 'current_user_can()', '2.0' ),'ut_deprecatedwordpress_user_can_create_draft.zip'),
					new Deprecated_Checker('DEPRECATED_USER_CAN_EDIT_POST', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('user_can_edit_post'), array('user_can_edit_post', 'current_user_can()', '2.0' ),'ut_deprecatedwordpress_user_can_edit_post.zip'),
					new Deprecated_Checker('DEPRECATED_USER_CAN_DELETE_POST', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('user_can_delete_post'), array('user_can_delete_post', 'current_user_can()', '2.0' ),'ut_deprecatedwordpress_user_can_delete_post.zip'),
					new Deprecated_Checker('DEPRECATED_USER_CAN_SET_POST_DATE', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('user_can_set_post_date'), array('user_can_set_post_date', 'current_user_can()', '2.0' ),'ut_deprecatedwordpress_user_can_set_post_date.zip'),
					new Deprecated_Checker('DEPRECATED_USER_CAN_EDIT_POST_COMMENTS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('user_can_edit_post_comments'), array('user_can_edit_post_comments', 'current_user_can()', '2.0' ),'ut_deprecatedwordpress_user_can_edit_post_comments.zip'),
					new Deprecated_Checker('DEPRECATED_USER_CAN_DELETE_POST_COMMENTS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('user_can_delete_post_comments'), array('user_can_delete_post_comments', 'current_user_can()', '2.0' ), 'ut_deprecatedwordpress_user_can_delete_post_comments.zip'),
					new Deprecated_Checker('DEPRECATED_USER_CAN_EDIT_USER', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('user_can_edit_user'), array('user_can_edit_user', 'current_user_can()', '2.0' ), 'ut_deprecatedwordpress_user_can_edit_user.zip'),
					new Deprecated_Checker('DEPRECATED_GET_LINKSBYNAME', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_linksbyname'), array('get_linksbyname', 'get_bookmarks()', '2.1' ),'ut_deprecatedwordpress_get_linksbyname.zip'),
					new Deprecated_Checker('DEPRECATED_WP_GET_LINKSBYNAME', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('wp_get_linksbyname'), array('wp_get_linksbyname', 'wp_list_bookmarks()', '2.1' ),'ut_deprecatedwordpress_wp_get_linksbyname.zip'),
					new Deprecated_Checker('DEPRECATED_GET_LINKOBJECTSBYNAME', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_linkobjectsbyname'), array('get_linkobjectsbyname', 'get_bookmarks()', '2.1' ),'ut_deprecatedwordpress_get_linkobjectsbyname.zip'),
					new Deprecated_Checker('DEPRECATED_GET_LINKOBJECTS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_linkobjects'), array('get_linkobjects', 'get_bookmarks()', '2.1' ),'ut_deprecatedwordpress_get_linkobjects.zip'),
					new Deprecated_Checker('DEPRECATED_GET_LINKSBYNAME_WITHRATING', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_linksbyname_withrating'), array('get_linksbyname_withrating', 'get_bookmarks()', '2.1' ),'ut_deprecatedwordpress_get_linksbyname_withrating.zip'),
					new Deprecated_Checker('DEPRECATED_GET_LINK_WITHRATING', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_links_withrating'), array('get_links_withrating', 'get_bookmarks()', '2.1' ),'ut_deprecatedwordpress_get_links_withrating.zip'),
					new Deprecated_Checker('DEPRECATED_GET_AUTOTOGGLE', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_autotoggle'), array('get_autotoggle', 'none available', '2.1' ), 'ut_deprecatedwordpress_get_autotoggle.zip'),
					new Deprecated_Checker('DEPRECATED_LIST_CATS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('list_cats'), array('list_cats', 'wp_list_categories', '2.1' ),'ut_deprecatedwordpress_list_cats.zip'),
					new Deprecated_Checker('DEPRECATED_WP_LIST_CATS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('wp_list_cats'), array('wp_list_cats', 'wp_list_categories', '2.1' ),'ut_deprecatedwordpress_wp_list_cats.zip'),
					new Deprecated_Checker('DEPRECATED_DROPDOWN_CATS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('dropdown_cats'), array('dropdown_cats', 'wp_dropdown_categories()', '2.1' ),'ut_deprecatedwordpress_dropdown_cats.zip'),
					new Deprecated_Checker('DEPRECATED_LIST_AUTHORS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('list_authors'), array('list_authors', 'wp_list_authors()', '2.1' ),'ut_deprecatedwordpress_list_authors.zip'),
					new Deprecated_Checker('DEPRECATED_WP_GET_POST_CATS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('wp_get_post_cats'), array('wp_get_post_cats', 'wp_get_post_categories()', '2.1' ),'ut_deprecatedwordpress_wp_get_post_cats.zip'),
					new Deprecated_Checker('DEPRECATED_WP_SET_POST_CATS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('wp_set_post_cats'), array('wp_set_post_cats', 'wp_set_post_categories()', '2.1' ),'ut_deprecatedwordpress_wp_set_post_cats.zip'),
					new Deprecated_Checker('DEPRECATED_GET_ARCHIVES', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_archives'), array('get_archives', 'wp_get_archives', '2.1' ),'ut_deprecatedwordpress_get_archives.zip'),
					new Deprecated_Checker('DEPRECATED_GET_AUTHOR_LINK', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_author_link'), array('get_author_link', 'get_author_posts_url()', '2.1' ),'ut_deprecatedwordpress_get_author_link.zip'),
					new Deprecated_Checker('DEPRECATED_LINK_PAGES', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('link_pages'), array('link_pages', 'wp_link_pages()', '2.1' ),'ut_deprecatedwordpress_link_pages.zip'),
					new Deprecated_Checker('DEPRECATED_GET_SETTINGS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_settings'), array('get_settings', 'get_option()', '2.1' ),'ut_deprecatedwordpress_get_settings.zip'),
					new Deprecated_Checker('DEPRECATED_PERMALINK_LINK', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('permalink_link'), array('permalink_link', 'the_permalink()', '1.2' ),'ut_deprecatedwordpress_permalink_link.zip'),
					new Deprecated_Checker('DEPRECATED_PERMALINK_SINGLE_RSS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('permalink_single_rss'), array('permalink_single_rss', 'permalink_rss()', '2.3' ),'ut_deprecatedwordpress_permalink_single_rss.zip'),
					new Deprecated_Checker('DEPRECATED_WP_GET_LINKS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('wp_get_links'), array('wp_get_links', 'wp_list_bookmarks()', '2.1' ),'ut_deprecatedwordpress_wp_get_links.zip'),
					new Deprecated_Checker('DEPRECATED_GET_LINKS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_links'), array('get_links', 'get_bookmarks()', '2.1' ),'ut_deprecatedwordpress_get_links.zip'),
					new Deprecated_Checker('DEPRECATED_GET_LINKS_LIST', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_links_list'), array('get_links_list', 'wp_list_bookmarks()', '2.1' ), 'ut_deprecatedwordpress_get_links_list.zip'),
					new Deprecated_Checker('DEPRECATED_LINKS_POPUP_SCRIPT', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('links_popup_script'), array('links_popup_script', 'none available', '2.1' ),'ut_deprecatedwordpress_links_popup_script.zip'),
					new Deprecated_Checker('DEPRECATED_GET_LINKRATING', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_linkrating'), array('get_linkrating', 'sanitize_bookmark_field()', '2.1' ),'ut_deprecatedwordpress_get_linkrating.zip'),
					new Deprecated_Checker('DEPRECATED_GET_LINKCATNAME', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_linkcatname'), array('get_linkcatname', 'get_category()', '2.1' ),'ut_deprecatedwordpress_get_linkcatname.zip'),
					new Deprecated_Checker('DEPRECATED_COMMENTS_RSS_LINK', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('comments_rss_link'), array('comments_rss_link', 'post_comments_feed_link()', '2.5' ),'ut_deprecatedwordpress_comments_rss_link.zip'),
					new Deprecated_Checker('DEPRECATED_GET_CATEGORY_RSS_LINK', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_category_rss_link'), array('get_category_rss_link', 'get_category_feed_link()', '2.5' ),'ut_deprecatedwordpress_get_category_rss_link.zip'),
					new Deprecated_Checker('DEPRECATED_GET_AUTHOR_RSS_LINK', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_author_rss_link'), array('get_author_rss_link', 'get_author_feed_link()', '2.5' ),'ut_deprecatedwordpress_get_author_rss_link.zip'),
					new Deprecated_Checker('DEPRECATED_COMMENTS_RSS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('comments_rss'), array('comments_rss', 'get_post_comments_feed_link()', '2.2' ),'ut_deprecatedwordpress_comments_rss.zip'),
					new Deprecated_Checker('DEPRECATED_CREATE_USER', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('create_user'), array('create_user', 'wp_create_user()', '2.0' ),'ut_deprecatedwordpress_create_user.zip'),
					new Deprecated_Checker('DEPRECATED_GZIP_COMPRESSION', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('gzip_compression'), array('gzip_compression', 'none available', '2.5' ),'ut_deprecatedwordpress_gzip_compression.zip'),
					new Deprecated_Checker('DEPRECATED_GET_COMMENTDATA', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_commentdata'), array('get_commentdata', 'get_comment()', '2.7' ),'ut_deprecatedwordpress_get_commentdata.zip'),
					new Deprecated_Checker('DEPRECATED_GET_CATNAME', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_catname'), array('get_catname', 'get_cat_name()', '2.8' ),'ut_deprecatedwordpress_get_catname.zip'),
					new Deprecated_Checker('DEPRECATED_GET_CATEGORY_CHILDREN', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_category_children'), array('get_category_children', 'get_term_children', '2.8' ),'ut_deprecatedwordpress_get_category_children.zip'),
					new Deprecated_Checker('DEPRECATED_GET_THE_AUTHOR_DESCRIPTION', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_the_author_description'), array('get_the_author_description', 'get_the_author_meta(&#39;description&#39;)', '2.8' ),'ut_deprecatedwordpress_get_the_author_description.zip'),
					new Deprecated_Checker('DEPRECATED_THE_AUTHOR_DESCRIPTION', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('the_author_description'), array('the_author_description', 'the_author_meta(&#39;description&#39;)', '2.8' ),'ut_deprecatedwordpress_the_author_description.zip'),
					new Deprecated_Checker('DEPRECATED_GET_THE_AUTHOR_LOGIN', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_the_author_login'), array('get_the_author_login', 'the_author_meta(&#39;login&#39;)', '2.8' ),'ut_deprecatedwordpress_get_the_author_login.zip'),
					new Deprecated_Checker('DEPRECATED_GET_THE_AUTHOR_FIRSTNAME', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_the_author_firstname'), array('get_the_author_firstname', 'get_the_author_meta(&#39;first_name&#39;)', '2.8' ),'ut_deprecatedwordpress_get_the_author_firstname.zip'),
					new Deprecated_Checker('DEPRECATED_THE_AUTHOR_FIRSTNAME', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('the_author_firstname'), array('the_author_firstname', 'the_author_meta(&#39;first_name&#39;)', '2.8' ),'ut_deprecatedwordpress_the_author_firstname.zip'),
					new Deprecated_Checker('DEPRECATED_GET_THE_AUTHOR_LASTNAME', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_the_author_lastname'), array('get_the_author_lastname', 'get_the_author_meta(&#39;last_name&#39;)', '2.8' ),'ut_deprecatedwordpress_get_the_author_lastname.zip'),
					new Deprecated_Checker('DEPRECATED_THE_AUTHOR_LASTNAME', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('the_author_lastname'), array('the_author_lastname', 'the_author_meta(&#39;last_name&#39;)', '2.8' ),'ut_deprecatedwordpress_the_author_lastname.zip'),
					new Deprecated_Checker('DEPRECATED_GET_THE_AUTHOR_NICKNAME', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_the_author_nickname'), array('get_the_author_nickname', 'get_the_author_meta(&#39;nickname&#39;)', '2.8' ),'ut_deprecatedwordpress_get_the_author_nickname.zip'),
					new Deprecated_Checker('DEPRECATED_THE_AUTHOR_NICKNAME', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('the_author_nickname'), array('the_author_nickname', 'the_author_meta(&#39;nickname&#39;)', '2.8' ),'ut_deprecatedwordpress_the_author_nickname.zip'),
					new Deprecated_Checker('DEPRECATED_GET_THE_AUTHOR_EMAIL', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_the_author_email'), array('get_the_author_email', 'get_the_author_meta(&#39;email&#39;)', '2.8' ),'ut_deprecatedwordpress_get_the_author_email.zip'),
					new Deprecated_Checker('DEPRECATED_THE_AUTHOR_EMAIL', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('the_author_email'), array('the_author_email', 'the_author_meta(&#39;email&#39;)', '2.8' ),'ut_deprecatedwordpress_the_author_email.zip'),
					new Deprecated_Checker('DEPRECATED_GET_THE_AUTHOR_ICQ', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_the_author_icq'), array('get_the_author_icq', 'get_the_author_meta(&#39;icq&#39;)', '2.8' ),'ut_deprecatedwordpress_get_the_author_icq.zip'),
					new Deprecated_Checker('DEPRECATED_THE_AUTHOR_ICQ', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('the_author_icq'), array('the_author_icq', 'the_author_meta(&#39;icq&#39;)', '2.8' ),'ut_deprecatedwordpress_the_author_icq.zip'),
					new Deprecated_Checker('DEPRECATED_GET_THE_AUTHOR_YIM', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_the_author_yim'), array('get_the_author_yim', 'get_the_author_meta(&#39;yim&#39;)', '2.8' ),'ut_deprecatedwordpress_get_the_author_yim.zip'),
					new Deprecated_Checker('DEPRECATED_THE_AUTHOR_YIM', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('the_author_yim'), array('the_author_yim', 'the_author_meta(&#39;yim&#39;)', '2.8' ),'ut_deprecatedwordpress_the_author_yim.zip'),
					new Deprecated_Checker('DEPRECATED_GET_THE_AUTHOR_MSN', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_the_author_msn'), array('get_the_author_msn', 'get_the_author_meta(&#39;msn&#39;)', '2.8' ),'ut_deprecatedwordpress_get_the_author_msn.zip'),
					new Deprecated_Checker('DEPRECATED_THE_AUTHOR_MSN', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('the_author_msn'), array('the_author_msn', 'the_author_meta(&#39;msn&#39;)', '2.8' ),'ut_deprecatedwordpress_the_author_msn.zip'),
					new Deprecated_Checker('DEPRECATED_GET_THE_AUTHOR_AIM', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_the_author_aim'), array('get_the_author_aim', 'get_the_author_meta(&#39;aim&#39;)', '2.8' ),'ut_deprecatedwordpress_get_the_author_aim.zip'),
					new Deprecated_Checker('DEPRECATED_THE_AUTHOR_AIM', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('the_author_aim'), array('the_author_aim', 'the_author_meta(&#39;aim&#39;)', '2.8' ),'ut_deprecatedwordpress_the_author_aim.zip'),
					new Deprecated_Checker('DEPRECATED_GET_AUTHOR_NAME', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_author_name'), array('get_author_name', 'get_the_author_meta(&#39;display_name&#39;)', '2.8' ),'ut_deprecatedwordpress_get_author_name.zip'),
					new Deprecated_Checker('DEPRECATED_GET_THE_AUTHOR_URL', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_the_author_url'), array('get_the_author_url', 'get_the_author_meta(&#39;url&#39;)', '2.8' ),'ut_deprecatedwordpress_get_the_author_url.zip'),
					new Deprecated_Checker('DEPRECATED_THE_AUTHOR_URL', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('the_author_url'), array('the_author_url', 'the_author_meta(&#39;url&#39;)', '2.8' ),'ut_deprecatedwordpress_the_author_url.zip'),
					new Deprecated_Checker('DEPRECATED_GET_THE_AUTHOR_ID', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_the_author_ID'), array('get_the_author_ID', 'get_the_author_meta(&#39;ID&#39;)', '2.8' ),'ut_deprecatedwordpress_get_the_author_ID.zip'),
					new Deprecated_Checker('DEPRECATED_THE_AUTHOR_ID', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('the_author_ID'), array('the_author_ID', 'the_author_meta(&#39;ID&#39;)', '2.8' ),'ut_deprecatedwordpress_the_author_ID.zip'),
					new Deprecated_Checker('DEPRECATED_THE_CONTENT_RSS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('the_content_rss'), array('the_content_rss', 'the_content_feed()', '2.9' ),'ut_deprecatedwordpress_the_content_rss.zip'),
					new Deprecated_Checker('DEPRECATED_MAKE_URL_FOOTNOTE', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('make_url_footnote'), array('make_url_footnote', 'none available', '2.9' ),'ut_deprecatedwordpress_make_url_footnote.zip'),
					new Deprecated_Checker('DEPRECATED_C', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('_c'), array('_c', '_x()', '2.9' ),'ut_deprecatedwordpress__c.zip'),
					new Deprecated_Checker('DEPRECATED_TRANSLATE_WITH_CONTEXT', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('translate_with_context'), array('translate_with_context', '_x()', '3.0' ),'ut_deprecatedwordpress_translate_with_context.zip'),
					new Deprecated_Checker('DEPRECATED_NC', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('nc'), array('nc', 'nx()', '3.0' ),'ut_deprecatedwordpress_nc.zip'),
					new Deprecated_Checker('DEPRECATED_NGETTEXT', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('__ngettext'), array('__ngettext', '_n_noop()', '2.8' ), 'ut_deprecatedwordpress___ngettext.zip'),
					new Deprecated_Checker('DEPRECATED_NGETTEXT_NOOP', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('__ngettext_noop'), array('__ngettext_noop', '_n_noop()', '2.8' ),'ut_deprecatedwordpress___ngettext_noop.zip'),
					new Deprecated_Checker('DEPRECATED_GET_ALLOPTIONS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_alloptions'), array('get_alloptions', 'wp_load_alloptions()', '3.0' ),'ut_deprecatedwordpress_get_alloptions.zip'),
					new Deprecated_Checker('DEPRECATED_GET_THE_ATTACHMENT_LINK', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_the_attachment_link'), array('get_the_attachment_link', 'wp_get_attachment_link()', '2.5' ),'ut_deprecatedwordpress_get_the_attachment_link.zip'),
					new Deprecated_Checker('DEPRECATED_GET_ATTACHMENT_ICON_SRC', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_attachment_icon_src'), array('get_attachment_icon_src', 'wp_get_attachment_image_src()', '2.5' ),'ut_deprecatedwordpress_get_attachment_icon_src.zip'),
					new Deprecated_Checker('DEPRECATED_GET_ATTACHMENT_ICON', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_attachment_icon'), array('get_attachment_icon', 'wp_get_attachment_image()', '2.5' ),'ut_deprecatedwordpress_get_attachment_icon.zip'),
					new Deprecated_Checker('DEPRECATED_GET_ATTACHMENT_INNERHTML', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_attachment_innerhtml'), array('get_attachment_innerhtml', 'wp_get_attachment_image()', '2.5' ),'ut_deprecatedwordpress_get_attachment_innerhtml.zip'),
					new Deprecated_Checker('DEPRECATED_GET_LINK', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_link'), array('get_link', 'get_bookmark()', '2.1' ),'ut_deprecatedwordpress_get_link.zip'),
					new Deprecated_Checker('DEPRECATED_SANITIZE_URL', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('sanitize_url'), array('sanitize_url', 'esc_url()', '2.8' ),'ut_deprecatedwordpress_sanitize_url.zip'),
					new Deprecated_Checker('DEPRECATED_CLEAN_URL', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('clean_url'), array('clean_url', 'esc_url()', '3.0' ),'ut_deprecatedwordpress_clean_url.zip'),
					new Deprecated_Checker('DEPRECATED_JS_ESCAPE', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('js_escape'), array('js_escape', 'esc_js()', '2.8' ), 'ut_deprecatedwordpress_js_escape.zip'),
					new Deprecated_Checker('DEPRECATED_WP_SPECIALCHARS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('wp_specialchars'), array('wp_specialchars', 'esc_html()', '2.8' ),'ut_deprecatedwordpress_wp_specialchars.zip'),
					new Deprecated_Checker('DEPRECATED_ATTRIBUTE_ESCAPE', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('attribute_escape'), array('attribute_escape', 'esc_attr()', '2.8' ), 'ut_deprecatedwordpress_attribute_escape.zip'),
					new Deprecated_Checker('DEPRECATED_REGISTER_SIDEBAR_WIDGET', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('register_sidebar_widget'), array('register_sidebar_widget', 'wp_register_sidebar_widget()', '2.8' ),'ut_deprecatedwordpress_register_sidebar_widget.zip'),
					new Deprecated_Checker('DEPRECATED_UNREGISTER_SIDEBAR_WIDGET', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('unregister_sidebar_widget'), array('unregister_sidebar_widget', 'wp_unregister_sidebar_widget()', '2.8' ), 'ut_deprecatedwordpress_unregister_sidebar_widget.zip'),
					new Deprecated_Checker('DEPRECATED_REGISTER_WIDGET_CONTROL', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('register_widget_control'), array('register_widget_control', 'wp_register_widget_control()', '2.8' ),'ut_deprecatedwordpress_register_widget_control.zip'),
					new Deprecated_Checker('DEPRECATED_UNREGISTER_WIDGET_CONTROL', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('unregister_widget_control'), array('unregister_widget_control', 'wp_unregister_widget_control()', '2.8' ),'ut_deprecatedwordpress_unregister_widget_control.zip'),
					new Deprecated_Checker('DEPRECATED_DELETE_USERMETA', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('delete_usermeta'), array('delete_usermeta', 'delete_user_meta()', '3.0' ),'ut_deprecatedwordpress_delete_usermeta.zip'),
					new Deprecated_Checker('DEPRECATED_GET_USERMETA', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_usermeta'), array('get_usermeta', 'get_user_meta()', '3.0' ),'ut_deprecatedwordpress_get_usermeta.zip'),
					new Deprecated_Checker('DEPRECATED_UPDATE_USERMETA', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('update_usermeta'), array('update_usermeta', 'update_user_meta()', '3.0' ),'ut_deprecatedwordpress_update_usermeta.zip'),
					new Deprecated_Checker('DEPRECATED_AUTOMATIC_FEED_LINKS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('automatic_feed_links'), array('automatic_feed_links', 'add_theme_support( &#39;automatic-feed-links&#39; )', '3.0' ),'ut_deprecatedwordpress_automatic_feed_links.zip'),
					new Deprecated_Checker('DEPRECATED_GET_PROFILE', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_profile'), array('get_profile', 'get_the_author_meta()', '3.0' ),'ut_deprecatedwordpress_get_profile.zip'),
					new Deprecated_Checker('DEPRECATED_GET_USERNUMPOSTS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_usernumposts'), array('get_usernumposts', 'count_user_posts()', '3.0' ),'ut_deprecatedwordpress_get_usernumposts.zip'),
					new Deprecated_Checker('DEPRECATED_FUNKY_JAVASCRIPT_CALLBACK', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('funky_javascript_callback'), array('funky_javascript_callback', 'none available', '3.0' ), 'ut_deprecatedwordpress_funky_javascript_callback.zip'),
					new Deprecated_Checker('DEPRECATED_FUNKY_JAVASCRIPT_FIX', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('funky_javascript_fix'), array('funky_javascript_fix', 'none available', '3.0' ),'ut_deprecatedwordpress_funky_javascript_fix.zip'),
					new Deprecated_Checker('DEPRECATED_IS_TAXONOMY', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('is_taxonomy'), array('is_taxonomy', 'taxonomy_exists()', '3.0' ),'ut_deprecatedwordpress_is_taxonomy.zip'),
					new Deprecated_Checker('DEPRECATED_IS_TERM', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('is_term'), array('is_term', 'term_exists()', '3.0' ),'ut_deprecatedwordpress_is_term.zip'),
					new Deprecated_Checker('DEPRECATED_IS_PLUGIN_PAGE', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('is_plugin_page'), array('is_plugin_page', '$plugin_page and/or get_plugin_page_hookname() hooks', '3.1' ),'ut_deprecatedwordpress_is_plugin_page.zip'),
					new Deprecated_Checker('DEPRECATED_UPDATE_CATEGORY_CACHE', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('update_category_cache'), array('update_category_cache', 'No alternatives', '3.1' ),'ut_deprecatedwordpress_update_category_cache.zip'),
					new Deprecated_Checker('DEPRECATED_GET_USERS_OF_BLOG', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_users_of_blog'), array('get_users_of_blog', 'get_users()', '3.1' ),'ut_deprecatedwordpress_get_users_of_blog.zip'),
					new Deprecated_Checker('DEPRECATED_WP_TIMEZONE_SUPPORTED', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('wp_timezone_supported'), array('wp_timezone_supported', 'None available', '3.2' ),'ut_deprecatedwordpress_wp_timezone_supported.zip'),
					new Deprecated_Checker('DEPRECATED_THE_EDITOR', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('the_editor'), array('the_editor', 'wp_editor', '3.3' ),'ut_deprecatedwordpress_the_editor.zip'),
					new Deprecated_Checker('DEPRECATED_GET_USER_METAVALUES', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_user_metavalues'), array('get_user_metavalues', 'none available', '3.3' ),'ut_deprecatedwordpress_get_user_metavalues.zip'),
					new Deprecated_Checker('DEPRECATED_SANITIZE_USER_OBJECT', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('sanitize_user_object'), array('sanitize_user_object', 'none available', '3.3' ), 'ut_deprecatedwordpress_sanitize_user_object.zip'),
					new Deprecated_Checker('DEPRECATED_GET_BOUNDARY_POST_REL_LINK', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_boundary_post_rel_link'), array('get_boundary_post_rel_link', 'none available', '3.3' ),'ut_deprecatedwordpress_get_boundary_post_rel_link.zip'),
					new Deprecated_Checker('DEPRECATED_START_POST_REL_LINK', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('start_post_rel_link'), array('start_post_rel_link', 'none available', '3.3' ),'ut_deprecatedwordpress_start_post_rel_link.zip'),
					new Deprecated_Checker('DEPRECATED_GET_INDEX_REL_LINK', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_index_rel_link'), array('get_index_rel_link', 'none available', '3.3' ),'ut_deprecatedwordpress_get_index_rel_link.zip'),
					new Deprecated_Checker('DEPRECATED_INDEX_REL_LINK', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('index_rel_link'), array('index_rel_link', 'none available', '3.3' ),'ut_deprecatedwordpress_index_rel_link.zip'),
					new Deprecated_Checker('DEPRECATED_GET_PARENT_POST_REL_LINK', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_parent_post_rel_link'), array('get_parent_post_rel_link', 'none available', '3.3' ),'ut_deprecatedwordpress_get_parent_post_rel_link.zip'),
					new Deprecated_Checker('DEPRECATED_PARENT_POST_REL_LINK', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('parent_post_rel_link'), array('parent_post_rel_link', 'none available', '3.3' ),'ut_deprecatedwordpress_parent_post_rel_link.zip'),
					new Deprecated_Checker('DEPRECATED_WP_ADMIN_BAR_DASHBOARD_VIEW_SITE_MENU', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('wp_admin_bar_dashboard_view_site_menu'), array('wp_admin_bar_dashboard_view_site_menu', 'none available', '3.3' ),'ut_deprecatedwordpress_wp_admin_bar_dashboard_view_site_menu.zip'),
					new Deprecated_Checker('DEPRECATED_IS_BLOG_USER', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('is_blog_user'), array('is_blog_user', 'is_member_of_blog()', '3.3' ),'ut_deprecatedwordpress_is_blog_user.zip'),
					new Deprecated_Checker('DEPRECATED_DEBUG_FOPEN', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('debug_fopen'), array('debug_fopen', 'error_log()', '3.3' ),'ut_deprecatedwordpress_debug_fopen.zip'),
					new Deprecated_Checker('DEPRECATED_DEBUG_FWRITE', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('debug_fwrite'), array('debug_fwrite', 'error_log()', '3.3' ),'ut_deprecatedwordpress_debug_fwrite.zip'),
					new Deprecated_Checker('DEPRECATED_DEBUG_FCLOSE', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('debug_fclose'), array('debug_fclose', 'error_log()', '3.3' ),'ut_deprecatedwordpress_debug_fclose.zip'),
					// wp-admin deprecated
					new Deprecated_Checker('DEPRECATED_TINYMCE_INCLUDE', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('tinymce_include'), array('tinymce_include', 'wp_editor()', '2.1' ),'ut_deprecatedwordpress_tinymce_include.zip'),
					new Deprecated_Checker('DEPRECATED_DOCUMENTATION_LINK', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('documentation_link'), array('documentation_link', 'None available', '2.5' ),'ut_deprecatedwordpress_documentation_link.zip'),
					new Deprecated_Checker('DEPRECATED_WP_SHRINK_DIMENSIONS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('wp_shrink_dimensions'), array('wp_shrink_dimensions', 'wp_constrain_dimensions()', '3.0' ),'ut_deprecatedwordpress_wp_shrink_dimensions.zip'),
					new Deprecated_Checker('DEPRECATED_DROPDOWN_CATEGORIES', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('dropdown_categories'), array('dropdown_categories', 'wp_category_checklist()', '2.6' ),'ut_deprecatedwordpress_dropdown_categories.zip'),
					new Deprecated_Checker('DEPRECATED_DROPDOWN_LINK_CATEGORIES', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('dropdown_link_categories'), array('dropdown_link_categories', 'wp_link_category_checklist()', '2.6' ),'ut_deprecatedwordpress_dropdown_link_categories.zip'),
					new Deprecated_Checker('DEPRECATED_WP_DROPDOWN_CATS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('wp_dropdown_cats'), array('wp_dropdown_cats', 'wp_dropdown_categories()', '3.0' ),'ut_deprecatedwordpress_wp_dropdown_cats.zip'),
					new Deprecated_Checker('DEPRECATED_ADD_OPTION_UPDATE_HANDLER', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('add_option_update_handler'), array('add_option_update_handler', 'register_setting()', '3.0' ),'ut_deprecatedwordpress_add_option_update_handler.zip'),
					new Deprecated_Checker('DEPRECATED_REMOVE_OPTION_UPDATE_HANDLER', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('remove_option_update_handler'), array('remove_option_update_handler', 'unregister_setting()', '3.0' ),'ut_deprecatedwordpress_remove_option_update_handler.zip'),
					new Deprecated_Checker('DEPRECATED_CODEPRESS_GET_LANG', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('codepress_get_lang'), array('codepress_get_lang', 'None available', '3.0' ),'ut_deprecatedwordpress_codepress_get_lang.zip'),
					new Deprecated_Checker('DEPRECATED_CODEPRESS_FOOTER_JS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('codepress_footer_js'), array('codepress_footer_js', 'None available', '3.0' ), 'ut_deprecatedwordpress_codepress_footer_js.zip'),
					new Deprecated_Checker('DEPRECATED_USE_CODEPRESS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('use_codepress'), array('use_codepress', 'None available', '3.0' ),'ut_deprecatedwordpress_use_codepress.zip'),
					new Deprecated_Checker('DEPRECATED_GET_AUTHOR_USER_IDS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_author_user_ids'), array('get_author_user_ids', 'None available', '3.1' ),'ut_deprecatedwordpress_get_author_user_ids.zip'),
					new Deprecated_Checker('DEPRECATED_GET_EDITABLE_AUTHORS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_editable_authors'), array('get_editable_authors', 'None available', '3.1' ), 'ut_deprecatedwordpress_get_editable_authors.zip'),
					new Deprecated_Checker('DEPRECATED_GET_EDITABLE_USER_IDS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_editable_user_ids'), array('get_editable_user_ids', 'None available', '3.1' ),'ut_deprecatedwordpress_get_editable_user_ids.zip'),
					new Deprecated_Checker('DEPRECATED_GET_NONAUTHOR_USER_IDS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_nonauthor_user_ids'), array('get_nonauthor_user_ids', 'None available', '3.1' ),'ut_deprecatedwordpress_get_nonauthor_user_ids.zip'),
					new Deprecated_Checker('DEPRECATED_WP_USER_SEARCH', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('WP_User_Search'), array('WP_User_Search', 'WP_User_Query', '3.1' ),'ut_deprecatedwordpress_WP_User_Search.zip'),
					new Deprecated_Checker('DEPRECATED_GET_OTHERS_UNPUBLISHED_POSTS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_others_unpublished_posts'), array('get_others_unpublished_posts', 'None available', '3.1' ),'ut_deprecatedwordpress_get_others_unpublished_posts.zip'),
					new Deprecated_Checker('DEPRECATED_GET_OTHERS_DRAFTS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_others_drafts'), array('get_others_drafts', 'None available', '3.1' ), 'ut_deprecatedwordpress_get_others_drafts.zip'),
					new Deprecated_Checker('DEPRECATED_GET_OTHERS_PENDING', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('get_others_pending'), array('get_others_pending', 'None available', '3.1' ),'ut_deprecatedwordpress_get_others_pending.zip'),
					new Deprecated_Checker('DEPRECATED_WP_DASHBOARD_QUICK_PRESS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('wp_dashboard_quick_press'), array('wp_dashboard_quick_press()', 'None available', '3.2' ),'ut_deprecatedwordpress_wp_dashboard_quick_press.zip'),
					new Deprecated_Checker('DEPRECATED_WP_TINY_MCE', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('wp_tiny_mce'), array('wp_tiny_mce', 'wp_editor', '3.2' ),'ut_deprecatedwordpress_wp_tiny_mce.zip'),
					new Deprecated_Checker('DEPRECATED_WP_PRELOAD_DIALOGS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('wp_preload_dialogs'), array('wp_preload_dialogs', 'wp_editor()', '3.2' ),'ut_deprecatedwordpress_wp_preload_dialogs.zip'),
					new Deprecated_Checker('DEPRECATED_WP_PRINT_EDITOR_JS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('wp_print_editor_js'), array('wp_print_editor_js', 'wp_editor()', '3.2' ),'ut_deprecatedwordpress_wp_print_editor_js.zip'),
					new Deprecated_Checker('DEPRECATED_WP_QUICKTAGS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('wp_quicktags'), array('wp_quicktags', 'wp_editor()', '3.2' ),'ut_deprecatedwordpress_wp_quicktags.zip'),
					new Deprecated_Checker('DEPRECATED_FAVORITE_ACTIONS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('favorite_actions'), array('favorite_actions', 'WP_Admin_Bar', '3.2' ),'ut_deprecatedwordpress_favorite_actions.zip'),
					new Deprecated_Checker('DEPRECATED_SCREEN_LAYOUT', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('screen_layout'), array('screen_layout', '$current_screen->render_screen_layout()', '3.3' ),'ut_deprecatedwordpress_screen_layout.zip'),
					new Deprecated_Checker('DEPRECATED_SCREEN_OPTIONS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('screen_options'), array('screen_options', '$current_screen->render_per_page_options()', '3.3' ),'ut_deprecatedwordpress_screen_options.zip'),
					new Deprecated_Checker('DEPRECATED_SCREEN_META', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('screen_meta'), array('screen_meta', '$current_screen->render_screen_meta()', '3.3' ),'ut_deprecatedwordpress_screen_meta.zip'),
					new Deprecated_Checker('DEPRECATED_MEDIA_UPLOAD_IMAGE', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('media_upload_image'), array('media_upload_image', 'wp_media_upload_handler()', '3.3' ),'ut_deprecatedwordpress_media_upload_image.zip'),
					new Deprecated_Checker('DEPRECATED_MEDIA_UPLOAD_AUDIO', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('media_upload_audio'), array('media_upload_audio', 'wp_media_upload_handler()', '3.3' ), 'ut_deprecatedwordpress_media_upload_audio.zip'),
					new Deprecated_Checker('DEPRECATED_MEDIA_UPLOAD_VIDEO', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('media_upload_video'), array('media_upload_video', 'wp_media_upload_handler()', '3.3' ), 'ut_deprecatedwordpress_media_upload_video.zip'),
					new Deprecated_Checker('DEPRECATED_MEDIA_UPLOAD_FILE', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('media_upload_file'), array('media_upload_file', 'wp_media_upload_handler()', '3.3' ),'ut_deprecatedwordpress_media_upload_file.zip'),
					new Deprecated_Checker('DEPRECATED_TYPE_URL_FORM_IMAGE', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('type_url_form_image'), array('type_url_form_image', 'wp_media_insert_url_form( &#39;image&#39; )', '3.3' ), 'ut_deprecatedwordpress_type_url_form_image.zip'),
					new Deprecated_Checker('DEPRECATED_TYPE_URL_FORM_AUDIO', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('type_url_form_audio'), array('type_url_form_audio', 'wp_media_insert_url_form( &#39;audio&#39; )', '3.3' ),'ut_deprecatedwordpress_type_url_form_audio.zip'),
					new Deprecated_Checker('DEPRECATED_TYPE_URL_FORM_VIDEO', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('type_url_form_video'), array('type_url_form_video', 'wp_media_insert_url_form( &#39;video&#39; )', '3.3' ),'ut_deprecatedwordpress_type_url_form_video.zip'),
					new Deprecated_Checker('DEPRECATED_TYPE_URL_FORM_FILE', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('type_url_form_file'), array('type_url_form_file', 'wp_media_insert_url_form( &#39;file&#39; )', '3.3' ), 'ut_deprecatedwordpress_type_url_form_file.zip'),
					new Deprecated_Checker('DEPRECATED_ADD_CONTEXTUAL_HELP', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('add_contextual_help'), array('add_contextual_help', 'get_current_screen()->add_help_tab()', '3.3' ),'ut_deprecatedwordpress_add_contextual_help.zip'),

					// recently deprecated : warning
					new Deprecated_Checker('DEPRECATED_GET_THEMES', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('get_themes'), array('get_themes', 'wp_get_themes()', '3.4' ),'ut_deprecatedrecommendedwordpress_get_themes.zip'),
					new Deprecated_Checker('DEPRECATED_GET_THEME', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('get_theme'), array('get_theme', 'wp_get_theme()', '3.4' ),'ut_deprecatedrecommendedwordpress_get_theme.zip'),
					new Deprecated_Checker('DEPRECATED_GET_CURRENT_THEME', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('get_current_theme'), array('get_current_theme', 'wp_get_theme()', '3.4' ),'ut_deprecatedrecommendedwordpress_get_current_theme.zip'),
					new Deprecated_Checker('DEPRECATED_CLEAN_PRE', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('clean_pre'), array('clean_pre', 'none available', '3.4' ),'ut_deprecatedrecommendedwordpress_clean_pre.zip'),
					new Deprecated_Checker('DEPRECATED_ADD_CUSTOM_IMAGE_HEADER', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('add_custom_image_header'), array('add_custom_image_header', 'add_theme_support( &#39;custom-header&#39;, $args )', '3.4' ),'ut_deprecatedrecommendedwordpress_add_custom_image_header.zip'),
					new Deprecated_Checker('DEPRECATED_REMOVE_CUSTOM_IMAGE_HEADER', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('remove_custom_image_header'), array('remove_custom_image_header', 'remove_theme_support( &#39;custom-header&#39; )', '3.4' ),'ut_deprecatedrecommendedwordpress_remove_custom_image_header.zip'),
					new Deprecated_Checker('DEPRECATED_ADD_CUSTOM_BACKGROUND', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('add_custom_background'), array('add_custom_background', 'add_theme_support( &#39;custom-background&#39;, $args )', '3.4' ),'ut_deprecatedrecommendedwordpress_add_custom_background.zip'),
					new Deprecated_Checker('DEPRECATED_REMOVE_CUSTOM_BACKGROUND', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('remove_custom_background'), array('remove_custom_background', 'remove_theme_support( &#39;custom-background&#39; )', '3.4' ),'ut_deprecatedrecommendedwordpress_remove_custom_background.zip'),
					new Deprecated_Checker('DEPRECATED_GET_THEME_DATA', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('get_theme_data'), array('get_theme_data', 'wp_get_theme()', '3.4' ),'ut_deprecatedrecommendedwordpress_get_theme_data.zip'),
					new Deprecated_Checker('DEPRECATED_UPDATE_PAGE_CACHE', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('update_page_cache'), array('update_page_cache', 'update_post_cache()', '3.4' ),'ut_deprecatedrecommendedwordpress_update_page_cache.zip'),
					new Deprecated_Checker('DEPRECATED_CLEAN_PAGE_CACHE', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('clean_page_cache'), array('clean_page_cache', 'clean_post_cache()', '3.4' ),'ut_deprecatedrecommendedwordpress_clean_page_cache.zip'),
					new Deprecated_Checker('DEPRECATED_WP_EXPLAIN_NONCE', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('wp_explain_nonce'), array('wp_explain_nonce', 'wp_nonce_ays', '3.4.1' ),'ut_deprecatedrecommendedwordpress_wp_explain_nonce.zip'),
					new Deprecated_Checker('DEPRECATED_STICKY_CLASS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('sticky_class'), array('sticky_class', 'post_class()', '3.5' ),'ut_deprecatedrecommendedwordpress_sticky_class.zip'),
					new Deprecated_Checker('DEPRECATED_GET_POST_ANCESTORS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('_get_post_ancestors'), array('_get_post_ancestors', 'none', '3.5' ),'ut_deprecatedrecommendedwordpress__get_post_ancestors.zip'),
					new Deprecated_Checker('DEPRECATED_WP_LOAD_IMAGE', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('wp_load_image'), array('wp_load_image', 'wp_get_image_editor()', '3.5' ),'ut_deprecatedrecommendedwordpress_wp_load_image.zip'),
					new Deprecated_Checker('DEPRECATED_IMAGE_RESIZE', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('image_resize'), array('image_resize', 'wp_get_image_editor()', '3.5' ),'ut_deprecatedrecommendedwordpress_image_resize.zip'),
					new Deprecated_Checker('DEPRECATED_WP_GET_SINGLE_POST', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('wp_get_single_post'), array('wp_get_single_post', 'get_post()', '3.5' ),'ut_deprecatedrecommendedwordpress_wp_get_single_post.zip'),
					new Deprecated_Checker('DEPRECATED_USER_PASS_OK', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('user_pass_ok'), array('user_pass_ok', 'wp_authenticate()', '3.5' ),'ut_deprecatedrecommendedwordpress_user_pass_ok.zip'),
					new Deprecated_Checker('DEPRECATED_SAVE_POST_HOOK', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('_save_post_hook'), array('_save_post_hook', 'none', '3.5' ),'ut_deprecatedrecommendedwordpress__save_post_hook.zip'),
					new Deprecated_Checker('DEPRECATED_GD_EDIT_IMAGE_SUPPORT', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('gd_edit_image_support'), array('gd_edit_image_support', 'wp_image_editor_supports()', '3.5' ),'ut_deprecatedrecommendedwordpress_gd_edit_image_support.zip'),
					new Deprecated_Checker('DEPRECATED_WP_CONVERT_BYTES_TO_HR', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('wp_convert_bytes_to_hr'), array('wp_convert_bytes_to_hr', 'size_format()', '3.6' ),'ut_deprecatedrecommendedwordpress_gd_edit_image_support.zip'),
					new Deprecated_Checker('DEPRECATED_SEARCH_TERMS_TIDY', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('_search_terms_tidy'), array('_search_terms_tidy', 'none', '3.7' ),'ut_deprecatedrecommendedwordpress_gd_edit_image_support.zip'),

					// wp-admin recently deprecated warning
					new Deprecated_Checker('DEPRECATED_GET_ALLOWED_THEMES', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('get_allowed_themes'), array('get_allowed_themes', 'wp_get_themes( array( &#39;allowed&#39; => true ) )', '3.4' ),'ut_deprecatedrecommendedwordpress_get_allowed_themes.zip'),
					new Deprecated_Checker('DEPRECATED_GET_BROKEN_THEMES', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('get_broken_themes'), array('get_broken_themes', 'wp_get_themes( array( &#39;errors&#39; => true )', '3.4' ),'ut_deprecatedrecommendedwordpress_get_broken_themes.zip'),
					new Deprecated_Checker('DEPRECATED_CURRENT_THEME_INFO', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('current_theme_info'), array('current_theme_info', 'wp_get_theme()', '3.4' ),'ut_deprecatedrecommendedwordpress_current_theme_info.zip'),
					new Deprecated_Checker('DEPRECATED_INSERT_INTO_POST_BUTTON', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('_insert_into_post_button'), array('_insert_into_post_button', 'none', '3.5' ),'ut_deprecatedrecommendedwordpress__insert_into_post_button.zip'),
					new Deprecated_Checker('DEPRECATED_MEDIA_BUTTON', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('_media_button'), array('_media_button', 'none', '3.5' ),'ut_deprecatedrecommendedwordpress__media_button.zip'),
					new Deprecated_Checker('DEPRECATED_GET_POST_TO_EDIT', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('get_post_to_edit'), array('get_post_to_edit', 'get_post()', '3.5' ),'ut_deprecatedrecommendedwordpress_get_post_to_edit.zip'),
					new Deprecated_Checker('DEPRECATED_GET_DEFAULT_PAGE_TO_EDIT', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('get_default_page_to_edit'), array('get_default_page_to_edit', 'get_default_post_to_edit()', '3.5' ),'ut_deprecatedrecommendedwordpress_get_default_page_to_edit.zip'),
					new Deprecated_Checker('DEPRECATED_WP_CREATE_THUMBNAIL', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('wp_create_thumbnail'), array('wp_create_thumbnail', 'image_resize()', '3.5' ),'ut_deprecatedrecommendedwordpress_wp_create_thumbnail.zip'),
					new Deprecated_Checker('DEPRECATED_WP_NAV_MENU_LOCATIONS_META_BOX', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('wp_nav_menu_locations_meta_box'), array('wp_nav_menu_locations_meta_box', 'Manage Locations tab', '3.6' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_WP_UPDATE_CORE', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('wp_update_core'), array('wp_update_core', 'Core_Upgrader::upgrade()', '3.7' ),'tobedefined.zip'),		
					new Deprecated_Checker('DEPRECATED_WP_UPDATE_PLUGIN', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('wp_update_plugin'), array('wp_update_plugin', 'Plugin_Upgrader::upgrade()', '3.7' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_WP_UPDATE_THEME', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('wp_update_theme'), array('wp_update_theme', 'Theme_Upgrader::upgrade()', '3.7' ),'tobedefined.zip'),	
					new Deprecated_Checker('DEPRECATED_THE_ATTACHMENT_LINKS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('the_attachment_links'), array('the_attachment_links', 'none', '3.7' ),'tobedefined.zip'),	
					new Deprecated_Checker('DEPRECATED_SCREEN_ICON', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('screen_icon'), array('screen_icon', 'none', '3.8' ),'tobedefined.zip'),	
					new Deprecated_Checker('DEPRECATED_GET_SCREEN_ICON', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('get_screen_icon'), array('get_screen_icon', 'none', '3.8' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_WP_DASHBOARD_INCOMING_LINKS_OUTPUT', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('wp_dashboard_incoming_links_output'), array('wp_dashboard_incoming_links_output', 'none', '3.8' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_WP_DASHBOARD_SECONDARY_OUTPUT', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('wp_dashboard_secondary_output'), array('wp_dashboard_secondary_output', 'none', '3.8' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_WP_DASHBOARD_INCOMING_LINKS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('wp_dashboard_incoming_links'), array('wp_dashboard_incoming_links', 'none', '3.8' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_WP_DASHBOARD_INCOMING_LINKS_CONTROL', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('wp_dashboard_incoming_links_control'), array('wp_dashboard_incoming_links_control', 'none', '3.8' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_WP_DASHBOARD_PLUGINS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('wp_dashboard_plugins'), array('wp_dashboard_plugins', 'none', '3.8' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_WP_DASHBOARD_PRIMARY_CONTROL', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('wp_dashboard_primary_control'), array('wp_dashboard_primary_control', 'none', '3.8' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_WP_DASHBOARD_RECENT_COMMENTS_CONTROL', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('wp_dashboard_recent_comments_control'), array('wp_dashboard_recent_comments_control', 'none', '3.8' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_WP_DASHBOARD_SECONDARY', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('wp_dashboard_secondary'), array('wp_dashboard_secondary', 'none', '3.8' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_WP_DASHBOARD_SECONDARY_CONTROL', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('wp_dashboard_secondary_control'), array('wp_dashboard_secondary_control', 'none', '3.8' ),'tobedefined.zip'),
					
					// joomla 1.5
					new Deprecated_Checker('DEPRECATED_AMPREPLACE', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('ampReplace'), array('ampReplace', 'none', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_DELDIR', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('deldir'), array('deldir', 'JFolder::delete()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_DOGZIP', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('doGzip'), array('doGzip', 'JDocument Zlib outputfilter', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_EDITORAREA', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('editorArea'), array('editorArea', 'JEditor::display()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_GETEDITORCONTENTS', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('getEditorContents'), array('getEditorContents', 'JEditor::save() or JEditor::getContent()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_INITEDITOR', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('initEditor'), array('initEditor', 'JEditor::init()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_INITGZIP', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('initGzip'), array('initGzip', 'JDocument Zlib outputfilter', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_JOSGETARRAYINTS', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('josGetArrayInts'), array('josGetArrayInts', 'JRequest::getVar()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_JOSSPOOFCHECK', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('josSpoofCheck'), array('josSpoofCheck', 'none', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_JOSSPOOFVALUE', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('josSpoofValue'), array('josSpoofValue', 'JUtility::getToken()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_LOADOVERLIB', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('loadOverlib'), array('loadOverlib', 'none', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSARRAYTOINTS', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosArrayToInts'), array('mosArrayToInts', 'JArrayHelper::toInteger()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSBACKTRACE', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosBackTrace'), array('mosBackTrace', 'JException->getTrace()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSBINDARRAYTOOBJECT', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosBindArrayToObject'), array('mosBindArrayToObject', 'JArrayHelper->toObject()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSCHMOD', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosChmod'), array('mosChmod', 'JPath::setPermissions()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSCHMODRECURSIVE', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosChmodRecursive'), array('mosChmodRecursive', 'JPath::setPermissions()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSCOUNTADMINMODULES', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosCountAdminModules'), array('mosCountAdminModules', '<jdoc:exists>', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSCOUNTMODULES', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosCountModules'), array('mosCountModules', '<jdoc:exists type="modules" condition="{POSITION}" />', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSCREATEMAIL', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosCreateMail'), array('mosCreateMail', 'JFactory::getMailer()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSCURRENTDATE', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosCurrentDate'), array('mosCurrentDate', "JHTML::_('date', )", '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSERRORALERT', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosErrorAlert'), array('mosErrorAlert', 'JError', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSFORMATDATE', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosFormatDate'), array('mosFormatDate', "JHTML::_('date', )", '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSGETBROWSER', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosGetBrowser'), array('mosGetBrowser', ' JBrowser::getInstance()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSGETORDERINGLIST', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosGetOrderingList'), array('mosGetOrderingList', " JHTML::_('list.genericordering', )", '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSGETOS', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosGetOS'), array('mosGetOS', 'JApplication::getBrowser()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSGETPARAM', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosGetParam'), array('mosGetParam', 'JArrayHelper::getValue()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSHASH', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosHash'), array('mosHash', 'JUtility::getHash()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSISCHMODABLE', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosIsChmodable'), array('mosIsChmodable', 'JPath::canChmod()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSLOADADMINMODULE', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosLoadAdminModule'), array('mosLoadAdminModule', '<jdoc:include type="module" />', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSLOADCOMPONENT', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosLoadComponent'), array('mosLoadComponent', 'none', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSLOADMODULE', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosLoadModule'), array('mosLoadModule', '<jdoc:include type="module" />', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSMAIL', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosMail'), array('mosMail', 'JUtility::sendMail()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSMAINBODY', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosMainBody'), array('mosMainBody', '<jdoc:include type="component" />', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSMAKEHTMLSAFE', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosMakeHtmlSafe'), array('mosMakeHtmlSafe', 'JFilterOutput::objectHTMLSafe()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSMAKEPASSWORD', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosMakePassword'), array('mosMakePassword', 'JUserHelper::genRandomPassword()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSMAKEPATH', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosMakePath'), array('mosMakePath', 'JFolder::create()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSMENUCHECK', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosMenuCheck'), array('mosMenuCheck', 'JMenu::authorize()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSNOTAUTH', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosNotAuth'), array('mosNotAuth', 'none', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSOBJECTTOARRAY', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosObjectToArray'), array('mosObjectToArray', 'JArrayHelper::fromObject()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSPATHNAME', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosPathName'), array('mosPathName', 'JPath::clean()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSPATHWAY', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosPathWay'), array('mosPathWay', "mosLoadModule( 'breadcrumb', -1 )", '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSREADDIRECTORY', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosReadDirectory'), array('mosReadDirectory', 'JFolder::files() or JFolder::folders()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSREDIRECT', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosRedirect'), array('mosRedirect', 'JApplication->redirect()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSSENDADMINMAIL', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosSendAdminMail'), array('mosSendAdminMail', 'JUtility::sendAdminMail()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSSHOWHEAD', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosShowHead'), array('mosShowHead', '<jdoc:include type="head" />', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSSHOWSOURCE', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosShowSource'), array('mosShowSource', 'geshi', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSSTRIPSLASHES', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosStripslashes'), array('mosStripslashes', 'JRequest::getVar()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSTOOLTIP', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosToolTip'), array('mosToolTip', 'none', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSTREERECURSE', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosTreeRecurse'), array('mosTreeRecurse', 'JApplication->redirect()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_MOSWARNING', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('mosWarning'), array('mosWarning', 'JHTML::tooltip()', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_SEFRELTOABS', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('sefRelToAbs'), array('sefRelToAbs', 'none', '1.5' ),'tobedefined.zip'),
					new Deprecated_Checker('DEPRECATED_SORTARRAYOBJECTS', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('SortArrayObjects'), array('SortArrayObjects', 'JArrayHelper::sortObjects()', '1.5' ),'tobedefined.zip'),
					);
					
					// http://api.joomla.fr/joomla25/da/d58/deprecated.html
					// 1.6 -> 2.5 : http://docs.joomla.org/J2.5:What's_new_in_Joomla_2.5
					// http://docs.joomla.org/Category:Migration
					// http://docs.joomla.org/Category:Compatibility
    }
		
		public function doCheck($php_files, $php_files_filtered, $css_files, $other_files)
		{
			$start_time_checker = microtime(true);
			foreach ($this->checks as &$check)
			{
				$deprecatedSinceVersion = $check->code[2];
				if ($this->currentThemetype & $check->themetype)
				{
					$cmp = Check::versionCmp($this->currentCmsVersion, $deprecatedSinceVersion, $check->themetype);

					if ($cmp === false || $cmp >= 0)
					{
						$start_time = microtime(true);
						$check->doCheck($php_files, $php_files_filtered, $css_files, $other_files);
						$check->duration = microtime(true) - $start_time; // check duration is calculated outside of the check to simplify check's code
					}
				}
				
			}	
			$this->duration = microtime(true) - $start_time_checker;
		}
}