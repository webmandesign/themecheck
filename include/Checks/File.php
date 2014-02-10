<?php
namespace ThemeCheck;

class File_Checker extends CheckPart
{		
		public function doCheck($php_files, $css_files, $other_files)
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
            $this->messages[] = sprintf(__('<strong>%1$s</strong> was found.'), $error) ;
            $this->errorLevel = ERRORLEVEL_ERROR;
        }
    }
}

class File extends Check
{	
    protected function createChecks()
    {
			$this->title = __("Unwanted files");
			$this->checks = array(
						new File_Checker(TT_COMMON, ERRORLEVEL_ERROR, __('Windows thumbnail store'), 'thumbs.db', 'ut_file_thumbs.zip'),
						new File_Checker(TT_COMMON, ERRORLEVEL_ERROR, __('Windows system file'), 'desktop.ini', 'ut_file_desktop.zip'),
						new File_Checker(TT_COMMON, ERRORLEVEL_ERROR, __('NetBeans project file'), 'project.xml', 'ut_file_project_xml.zip'),
						new File_Checker(TT_COMMON, ERRORLEVEL_ERROR, __('NetBeans project properties file'), 'project.properties', 'ut_file_desktop.zip'),
						new File_Checker(TT_COMMON, ERRORLEVEL_ERROR, __('Komodo project file'), '\.kpf', 'ut_file_kpf.zip'),
						new File_Checker(TT_COMMON, ERRORLEVEL_ERROR, __('hidden file(s) or folder(s)'), '^\.+[a-zA-Z0-9]', 'ut_file_hidden_files.zip'),
						new File_Checker(TT_COMMON, ERRORLEVEL_ERROR, __('PHP server settings file'), 'php.ini', 'ut_file_php_ini.zip'),
						new File_Checker(TT_COMMON, ERRORLEVEL_ERROR, __('Dreamweaver project file'), 'dwsync.xml', 'ut_file_dwsync_xml.zip'),
						new File_Checker(TT_COMMON, ERRORLEVEL_ERROR, __('PHP error log'), 'error_log', 'ut_file_error_log.zip'),
						new File_Checker(TT_COMMON, ERRORLEVEL_ERROR, __('server settings file'), 'web.config', 'ut_file_web_config.zip'),
						new File_Checker(TT_COMMON, ERRORLEVEL_ERROR, __('SQL dump file'), '\.sql', 'ut_file_sql.zip')
			);
    }
}