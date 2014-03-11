<?php
namespace ThemeCheck;

class MandatoryFiles_Checker extends CheckPart
{	
		public function doCheck($php_files, $css_files, $other_files)
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
						new MandatoryFiles_Checker(TT_COMMON, ERRORLEVEL_CRITICAL, __all('Presence of file index.php'), 'index.php', 'ut_mandatoryfiles_index.zip'),
						new MandatoryFiles_Checker(TT_WORDPRESS, ERRORLEVEL_WARNING, __all('Presence of file style.css'), 'style.css', 'ut_mandatoryfiles_style.zip'),
						new MandatoryFiles_Checker(TT_WORDPRESS, ERRORLEVEL_WARNING, __all('Presence of file readme.txt'), 'readme.txt', 'ut_mandatoryfiles_readme.zip'),
						new MandatoryFiles_Checker(TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('Presence of file templateDetails.xml'), 'templateDetails.xml', 'ut_mandatoryfiles_templatedetails.zip'),
						new MandatoryFiles_Checker(TT_JOOMLA, ERRORLEVEL_CRITICAL, __all('Presence of file template_thumbnail.png'), 'template_thumbnail.png', 'ut_mandatoryfiles_template_thumbnail.zip')
			);
    }
}