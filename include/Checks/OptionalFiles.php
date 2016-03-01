<?php
namespace ThemeCheck;

class OptionalFiles_Checker extends CheckPart
{	
		public function doCheck($php_files, $php_files_filtered, $css_files, $other_files, $themeInfo)
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
						$this->messages[] = __all('This theme does not contain optional file <strong>%1$s</strong>.', $mandatoryfile);
            $this->errorLevel = $this->threatLevel;
        }
    }
}

class OptionalFiles extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("Optional files");
			$this->checks = array(
						new OptionalFiles_Checker('OPTIONALFILES_RTLCSS', TT_WORDPRESS, ERRORLEVEL_INFO, __all('Presence of rtl stylesheet rtl.css'), 'rtl.php', 'ut_todo.zip'),
						new OptionalFiles_Checker('OPTIONALFILES_COMMENTSPHP', TT_WORDPRESS, ERRORLEVEL_INFO, __all('Presence of comments template file comments.php'), 'comments.php', 'ut_todo.zip'),
						new OptionalFiles_Checker('OPTIONALFILES_FRONTPAGEPHP', TT_WORDPRESS, ERRORLEVEL_INFO, __all('Presence of front page template file front-page.php'), 'front-page.php', 'ut_todo.zip'),
						new OptionalFiles_Checker('OPTIONALFILES_HOMEPHP', TT_WORDPRESS, ERRORLEVEL_INFO, __all('Presence of home template file home.php'), 'home.php', 'ut_todo.zip'),
						new OptionalFiles_Checker('OPTIONALFILES_SINGLEPHP', TT_WORDPRESS, ERRORLEVEL_INFO, __all('Presence of single post template file single.php'), 'comments.php', 'ut_todo.zip'),
						new OptionalFiles_Checker('OPTIONALFILES_PAGEPHP', TT_WORDPRESS, ERRORLEVEL_INFO, __all('Presence of page template file page.php'), 'page.php', 'ut_todo.zip'),
						new OptionalFiles_Checker('OPTIONALFILES_CATEGORYPHP', TT_WORDPRESS, ERRORLEVEL_INFO, __all('Presence of category template file category.php'), 'category.php', 'ut_todo.zip'),
						new OptionalFiles_Checker('OPTIONALFILES_TAGPHP', TT_WORDPRESS, ERRORLEVEL_INFO, __all('Presence of tag template file tag.php'), 'tag.php', 'ut_todo.zip'),
						new OptionalFiles_Checker('OPTIONALFILES_TAXONOMYPHP', TT_WORDPRESS, ERRORLEVEL_INFO, __all('Presence of term template file taxonomy.php'), 'taxonomy.php', 'ut_todo.zip'),
						new OptionalFiles_Checker('OPTIONALFILES_AUTHORPHP', TT_WORDPRESS, ERRORLEVEL_INFO, __all('Presence of author template file author.php'), 'author.php', 'ut_todo.zip'),
						new OptionalFiles_Checker('OPTIONALFILES_DATEPHP', TT_WORDPRESS, ERRORLEVEL_INFO, __all('Presence of date/time template file date.php'), 'date.php', 'ut_todo.zip'),
						new OptionalFiles_Checker('OPTIONALFILES_ARCHIVEPHP', TT_WORDPRESS, ERRORLEVEL_INFO, __all('Presence of archive template file archive.php'), 'archive.php', 'ut_todo.zip'),
						new OptionalFiles_Checker('OPTIONALFILES_SEARCHPHP', TT_WORDPRESS, ERRORLEVEL_INFO, __all('Presence of search results template file search.php'), 'search.php', 'ut_todo.zip'),
						new OptionalFiles_Checker('OPTIONALFILES_ATTACHMENTPHP', TT_WORDPRESS, ERRORLEVEL_INFO, __all('Presence of attachment template file attachment.php'), 'attachment.php', 'ut_todo.zip'),
						new OptionalFiles_Checker('OPTIONALFILES_IMAGEPHP', TT_WORDPRESS, ERRORLEVEL_INFO, __all('Presence of image template file image.php'), 'image.php', 'ut_todo.zip'),
						new OptionalFiles_Checker('OPTIONALFILES_404PHP', TT_WORDPRESS, ERRORLEVEL_INFO, __all('Presence of 404 Not Found template file 404.php'), '404.php', 'ut_todo.zip')
			);
    }
}