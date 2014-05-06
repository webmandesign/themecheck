<?php
namespace ThemeCheck;

class File_Checker extends CheckPart
{		
		public function doCheck($php_files, $php_files_filtered, $css_files, $other_files)
    {        
        $this->errorLevel = ERRORLEVEL_SUCCESS;
        $filenames = array();
        
        foreach ( $php_files as $key => $file ) {
            array_push( $filenames, strtolower( basename( $key ) ) );
        }
        foreach ( $css_files as $key => $file ) {
            array_push( $filenames, strtolower( basename( $key ) ) );
        }
        foreach ( $other_files as $key => $file ) {
            array_push( $filenames, strtolower( basename( $key ) ) );
        }
          
        if ( $filename = preg_grep( '/' . $this->code . '/', $filenames ) )
        {
            $error = implode( array_unique( $filename ), ' ' );
						
            $this->messages[] = __all('<strong>%1$s</strong> was found.', $error) ;
            $this->errorLevel = $this->threatLevel;

						if ($error == '.gitignore' || $error == '.ds_store' || $error == '.tm_properties') $this->errorLevel = ERRORLEVEL_WARNING;
        }
    }
}

class File extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("Unwanted files");
			$this->checks = array(
						new File_Checker('FILE_THUMBSDB', TT_COMMON, ERRORLEVEL_WARNING, __all('Windows thumbnail store'), 'thumbs.db', 'ut_file_thumbs.zip'),
						new File_Checker('FILE_DESKTOPINI', TT_COMMON, ERRORLEVEL_WARNING, __all('Windows system file'), 'desktop.ini', 'ut_file_desktop.zip'),
						new File_Checker('FILE_PROJECTXML', TT_COMMON, ERRORLEVEL_WARNING, __all('NetBeans project file'), 'project.xml', 'ut_file_project_xml.zip'),
						new File_Checker('FILE_PROJECTPROPERTIES', TT_COMMON, ERRORLEVEL_WARNING, __all('NetBeans project properties file'), 'project.properties', 'ut_file_desktop.zip'),
						new File_Checker('FILE_KPF', TT_COMMON, ERRORLEVEL_WARNING, __all('Komodo project file'), '\.kpf', 'ut_file_kpf.zip'),
						new File_Checker('FILE_HIDDEN', TT_COMMON, ERRORLEVEL_CRITICAL, __all('hidden file(s) or folder(s)'), '^\.+[a-zA-Z0-9]', 'ut_file_hidden_files.zip'),
						new File_Checker('FILE_PHPINI', TT_COMMON, ERRORLEVEL_CRITICAL, __all('PHP server settings file'), 'php.ini', 'ut_file_php_ini.zip'),
						new File_Checker('FILE_DWSYNCXML', TT_COMMON, ERRORLEVEL_WARNING, __all('Dreamweaver project file'), 'dwsync.xml', 'ut_file_dwsync_xml.zip'),
						new File_Checker('FILE_ERRORLOG', TT_COMMON, ERRORLEVEL_CRITICAL, __all('PHP error log'), 'error_log', 'ut_file_error_log.zip'),
						new File_Checker('FILE_WEBCONFIG', TT_COMMON, ERRORLEVEL_CRITICAL, __all('server settings file'), 'web.config', 'ut_file_web_config.zip'),
						new File_Checker('FILE_SQL', TT_COMMON, ERRORLEVEL_CRITICAL, __all('SQL dump file'), '\.sql', 'ut_file_sql.zip')
			);
    }
}