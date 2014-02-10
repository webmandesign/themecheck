<?php
namespace ThemeCheck;

class Directories_Checker extends CheckPart
{	
		public function doCheck($php_files, $css_files, $other_files)
    {		
        $this->errorLevel = ERRORLEVEL_SUCCESS;
        
        $error = false;
        
        foreach ( $php_files as $name => $phpfile ) {
            if ( strpos( $name, $this->code ) !== false ) $error = true;
        }

        foreach ( $css_files as $name => $file ) {
            if ( strpos( $name, $this->code ) !== false ) $error = true;
        }

        foreach ( $other_files as $name => $file ) {
            if ( strpos( $name, $this->code ) !== false ) $error = true;
        }

        if ($error)
        {
            $this->messages[] = $this->hint;
						$this->messages[] = sprintf(__('<strong>%1$s</strong> was found.'), $this->code);
            $this->errorLevel = $this->threatLevel;
        }
    }
}

class Directories extends Check
{	
    protected function createChecks()
    {
			$this->title = __("Unwanted directories");
			$this->checks = array(
						new Directories_Checker(TT_COMMON, ERRORLEVEL_ERROR, __('GIT revision control directory'), '.git', 'ut_directories_git.zip'),
						new Directories_Checker(TT_COMMON, ERRORLEVEL_ERROR, __('SVN revision control directory'), '.svn', 'ut_directories_svn.zip'),
						new Directories_Checker(TT_COMMON, ERRORLEVEL_ERROR, __('Mercurial revision control directory'), '.hg', 'ut_directories_hg.zip'),
						new Directories_Checker(TT_COMMON, ERRORLEVEL_ERROR, __('OSX system directory'), '__macosx', 'ut_directories___MACOSX.zip')
			);
    }
}