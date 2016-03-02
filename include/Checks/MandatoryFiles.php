<?php
namespace ThemeCheck;

class MandatoryFiles_Checker extends CheckPart
{	
		public function doCheck($php_files, $php_files_filtered, $css_files, $other_files)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
        $mandatoryfile = $this->code;
        $missing = true;
				
				$files = array_merge($php_files, $css_files, $other_files);
        foreach (array_keys($files) as $filepath)
        {
            if (basename($filepath) == $mandatoryfile) {$missing = false; break;}
        }
        if ($missing)
        {
            $this->messages[] = __all('Could not find file <strong>%1$s</strong> in the theme.', $mandatoryfile);
            $this->errorLevel = $this->threatLevel;
        }
    }
}

class MandatoryFiles extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("Mandatory files");
			$this->checks = array(
						new MandatoryFiles_Checker('MANDATORYFILES_INDEXPHP', TT_JOOMLA | TT_WORDPRESS, ERRORLEVEL_CRITICAL, __all('Presence of file index.php'), 'index.php', 'ut_mandatoryfiles_index.zip'),
						new MandatoryFiles_Checker('MANDATORYFILES_STYLECSS', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Presence of file style.css'), 'style.css', 'ut_mandatoryfiles_style.zip'),
						new MandatoryFiles_Checker('MANDATORYFILES_COMMENTSPHP', TT_WORDPRESS, ERRORLEVEL_INFO, __all('Presence of file comments.php'), 'comments.php', 'ut_mandatoryfiles_todo.zip'),
						new MandatoryFiles_Checker('MANDATORYFILES_TEMPLATEDETAILSXML', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('Presence of file templateDetails.xml'), 'templateDetails.xml', 'ut_mandatoryfiles_templatedetails.zip'),
						new MandatoryFiles_Checker('MANDATORYFILES_TEMPLATE_THUMBNAILPNG', TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('Presence of file template_thumbnail.png'), 'template_thumbnail.png', 'ut_mandatoryfiles_template_thumbnail.zip')
			);
    }
}