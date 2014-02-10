<?php
namespace ThemeCheck;

class JManifest_Checker extends CheckPart
{		
		public function doCheck( $php_files, $css_files, $other_files)
    {
        $this->errorLevel = ERRORLEVEL_SUCCESS;
        
        foreach ($other_files as $other_key => $otherfile)
        {
            $pathinfos = pathinfo($other_key);
            
            if (isset($pathinfos['extension']) && $pathinfos['extension'] == "xml" && isset($pathinfos['filename']) && $pathinfos['filename'] == 'templateDetails')
            {
                $xml = simplexml_load_file($other_key);
                if ($xml !== FALSE)
                {
                    if (!is_null($xml))
                    {
                        if ($xml->getName() == 'extension')
                        {
                            if ( !preg_match( '/<' . $this->code . '>/i' , $otherfile, $matches ) )
                            {
                                $filename = tc_filename( $other_key );
                                $this->messages[] = sprintf(__('Missing tag "%1$s" in %2$s.'), $this->code, $filename);
                                $this->errorLevel = $this->threatLevel;
                            }
                        }
                    }
                } else 
								{
									$filename = tc_filename( $other_key );
									$this->messages[] = sprintf(__('Cannot read xml content <strong>%1$s</strong>.'), $filename);
                  $this->errorLevel = ERRORLEVEL_ERROR;
								}
            }
        }
    }
}

class JManifest extends Check
{	
    protected function createChecks()
    {
			$this->title = __("Manifest and Metadata");
			$this->checks = array(
						new JManifest_Checker(TT_COMMON, ERRORLEVEL_WARNING, __('Presence of name'), 'name', 'ut_jmanifestjoomla_manifest_name.zip'),
						new JManifest_Checker(TT_COMMON, ERRORLEVEL_WARNING, __('Presence of creationDate'), 'creationDate', 'ut_jmanifestjoomla_manifest_creationDate.zip'),
						new JManifest_Checker(TT_COMMON, ERRORLEVEL_WARNING, __('Presence of author'), 'author', 'ut_jmanifestjoomla_manifest_author.zip'),
						new JManifest_Checker(TT_COMMON, ERRORLEVEL_WARNING, __('Presence of copyright'), 'copyright', 'ut_jmanifestjoomla_manifest_copyright.zip'),
						new JManifest_Checker(TT_COMMON, ERRORLEVEL_WARNING, __('Presence of authorUrl'), 'authorUrl', 'ut_jmanifestjoomla_manifest_authorUrl.zip'),
						new JManifest_Checker(TT_COMMON, ERRORLEVEL_WARNING, __('Presence of version'), 'version', 'ut_jmanifestjoomla_manifest_version.zip'),
						new JManifest_Checker(TT_COMMON, ERRORLEVEL_WARNING, __('Presence of description'), 'description', 'ut_jmanifestjoomla_manifest_description.zip'),
			);
    }
}