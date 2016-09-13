<?php
namespace ThemeCheck;

class IncludedPlugins_Checker extends CheckPart
{	
	public function doCheck($php_files, $php_files_filtered, $css_files, $other_files, $themeInfo)
    {
		$this->errorLevel = ERRORLEVEL_SUCCESS;
		
		$filenames = array();

		foreach ( $other_files as $other_key => $otherfile ) {
			array_push( $filenames, strtolower( basename( $other_key ) ) );
		}

		$blacklist = array(
			'\.zip'	=> __( 'Zipped Plugin', 'theme-check' ),
		);

		foreach ( $blacklist as $file => $reason ) {
			if ( $filename = preg_grep( '/' . $file . '/', $filenames ) ) {
				$error = implode( array_unique( $filename ), ' ' );
				
				$this->messages[] = __all('<Plugins are not allowed in themes. The zip file found was <code>%s</code>.', $error );
				$this->errorLevel = $this->threatLevel;
			}
		}
    }
}

class IncludedPlugins extends Check
{	
    protected function createChecks()
    {
		$this->title = __all("Included plugins");
		$this->checks = array(
					new IncludedPlugins_Checker('INCLUDEDPLUGINS_1', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_CRITICAL, __all('Zip file found'), null, 'ut_includedplugins_1.zip'),
		);
    }
}