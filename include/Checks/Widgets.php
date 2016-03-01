<?php
namespace ThemeCheck;

class Widgets_Checker extends CheckPart
{	
	public function doCheck($php_files, $php_files_filtered, $css_files, $other_files, $themeInfo)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
		
		$php = implode( ' ', $php_files );
		
		// no widgets registered or used...
		if ( strpos( $php, 'register_sidebar' ) === false && strpos( $php, 'dynamic_sidebar' ) === false ) {
			$this->messages[] = __all( "This theme contains no sidebars/widget areas. See <a href='https://codex.wordpress.org/Widgets_API'>Widgets API</a>");
			$this->errorLevel = ERRORLEVEL_WARNING;
		}

		if ( strpos( $php, 'register_sidebar' ) !== false && strpos( $php, 'dynamic_sidebar' ) === false ) {
			$this->messages[] = __all( "The theme appears to use <strong>register_sidebar()</strong> but no <strong>dynamic_sidebar()</strong> was found. See: <a href='https://codex.wordpress.org/Function_Reference/dynamic_sidebar'>dynamic_sidebar</a><pre> &lt;?php dynamic_sidebar( \$index ); ?&gt;</pre>" );
			$this->errorLevel = ERRORLEVEL_CRITICAL;
		}

		if ( strpos( $php, 'register_sidebar' ) === false && strpos( $php, 'dynamic_sidebar' ) !== false ) {
			$this->messages[] = __all( "The theme appears to use <strong>dynamic_sidebars()</strong> but no <strong>register_sidebar()</strong> was found. See: <a href='https://codex.wordpress.org/Function_Reference/register_sidebar'>register_sidebar</a><pre> &lt;?php register_sidebar( \$args ); ?&gt;</pre>" );
			$this->errorLevel = ERRORLEVEL_CRITICAL;
		}

		/**
		 * There are widgets registered, is the widgets_init action present?
		 */
		if ( strpos( $php, 'register_sidebar' ) !== false && preg_match( '/add_action\s*\(\s*("|\')widgets_init("|\')\s*,/', $php ) == false ) {
			$this->messages[] = __all( "Sidebars need to be registered in a custom function hooked to the <strong>widgets_init</strong> action. See: %s.", '<a href="https://codex.wordpress.org/Function_Reference/register_sidebar">register_sidebar()</a>' );
			$this->errorLevel = ERRORLEVEL_CRITICAL;
		}
    }
}

class Widgets extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("Widgets");
			$this->checks = array(
						new Widgets_Checker('Widgets_Checker', TT_WORDPRESS, ERRORLEVEL_CRITICAL, __all("Widgets"), 
									null , 'ut_widgets.zip')
			);
    }
}