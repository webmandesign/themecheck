<?php
namespace ThemeCheck;

class Directories_Checker extends CheckPart
{	
		public function doCheck($php_files, $php_files_filtered, $css_files, $other_files, $themeInfo)
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
						$this->messages[] = __all('<strong>%1$s</strong> was found.', $this->code);
            $this->errorLevel = $this->threatLevel;
        }
    }
}

class Directories extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("Unwanted directories");
			$this->checks = array(
						new Directories_Checker('DIRECTORIES_GIT', TT_COMMON, ERRORLEVEL_WARNING, __all('GIT revision control directory'), '.git', 'ut_directories_git.zip'),
						new Directories_Checker('DIRECTORIES_SVN', TT_COMMON, ERRORLEVEL_WARNING, __all('SVN revision control directory'), '.svn', 'ut_directories_svn.zip'),
						new Directories_Checker('DIRECTORIES_HG', TT_COMMON, ERRORLEVEL_WARNING, __all('Mercurial revision control directory'), '.hg', 'ut_directories_hg.zip'),
						new Directories_Checker('DIRECTORIES_MACOSX', TT_COMMON, ERRORLEVEL_WARNING, __all('OSX system directory'), '__macosx', 'ut_directories___MACOSX.zip')
			);
    }
}