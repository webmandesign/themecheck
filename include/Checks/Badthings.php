<?php
namespace ThemeCheck;
class Badthings_Checker extends CheckPart
{
    public function doCheck($php_files, $css_files, $other_files)
    {		
        $this->errorLevel = ERRORLEVEL_SUCCESS;
        $grep = '';
				
				if ($this->threatLevel == ERRORLEVEL_CRITICAL) $files = $php_files;
				else $files = array_merge($php_files, $other_files);
				
        foreach ( $files as $php_key => $phpfile ) {
            if ( preg_match( $this->code, $phpfile, $matches ) ) {
                $filename = tc_filename( $php_key );
                $error = ltrim( trim( $matches[0], '(' ) );
								$grep = tc_preg( $this->code, $php_key ); 
								$this->messages[] = __all('Found <strong>%1$s</strong> in file <strong>%2$s</strong>. %3$s', $error, $filename, $grep);
                $this->errorLevel = $this->threatLevel;
            }
        }
    }
}

class Badthings extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("Security breaches");
			$this->checks = array(
						new Badthings_Checker(TT_COMMON, ERRORLEVEL_CRITICAL, __all('Use of eval()')													, '/(?<![_|a-z0-9|\.])eval\s?\(/i', 'ut_badthings_eval.zip'),
						new Badthings_Checker(TT_COMMON, ERRORLEVEL_CRITICAL, __all('Use of PHP sytem calls')							  , '/[^a-z0-9](?<!_)(popen|proc_open|[^_]exec|shell_exec|system|passthru)\(/', 'ut_badthings_systemcalls.zip'),
						new Badthings_Checker(TT_COMMON, ERRORLEVEL_CRITICAL, __all('Use of backticks in PHP code')					, '/`/'														, 'ut_badthings_backticks.zip'),
						new Badthings_Checker(TT_COMMON, ERRORLEVEL_CRITICAL, __all('Modification of PHP server settings')		, '/\s?ini_set\(/'								, 'ut_badthings_serversettings.zip'),
						new Badthings_Checker(TT_COMMON, ERRORLEVEL_CRITICAL, __all('Use of base64_decode()')								, '/base64_decode/'								, 'ut_badthings_base64_decode.zip'),
						new Badthings_Checker(TT_WORDPRESS, ERRORLEVEL_CRITICAL, __all('Use of base64_encode()')								, '/base64_encode/'								, 'ut_badthings_base64_encode.zip'),
						new Badthings_Checker(TT_JOOMLA, ERRORLEVEL_WARNING, __all('Use of base64_encode()')								, '/base64_encode/'								, 'ut_badthings_base64_encode.zip'), // On joomla, usee of base64_encode just displays a warning because it may be used in template overrides
						new Badthings_Checker(TT_COMMON, ERRORLEVEL_CRITICAL, __all('Use of uudecode()')											, '/uudecode/ims'									, 'ut_badthings_uudecode.zip'),
						new Badthings_Checker(TT_COMMON, ERRORLEVEL_CRITICAL, __all('Use of str_rot13()')										, '/str_rot13/ims'								, 'ut_badthings_str_rot13.zip'),
						new Badthings_Checker(TT_COMMON, ERRORLEVEL_WARNING, __all('Presence of Google search code')			, '/cx=[0-9]{21}:/'								, 'ut_badthings_googlesearch.zip'),
						new Badthings_Checker(TT_COMMON, ERRORLEVEL_WARNING, __all('Presence of Google advertising code')	, '/pub-[0-9]{16}/i'							, 'ut_badthings_googleadvertising.zip')
			);
    }
}