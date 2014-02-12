<?php
namespace ThemeCheck;

class LineEndings_Checker extends CheckPart
{		
	public function doCheck($php_files, $css_files, $other_files)
	{		
		$this->errorLevel = ERRORLEVEL_SUCCESS;
		$files = array_merge($php_files, $css_files, $other_files);

		foreach ( $files as $key => $file_content ) {
			$e = pathinfo($key);
		//	$file_content = file_get_contents( $key );
			if ( isset( $e['extension'] ) && in_array( $e['extension'], array( 'php', 'css', 'txt', 'js' ) ) ) {
					if (preg_match("/\r\n/", $file_content) && preg_match("/[^\r]\n/", $file_content)) {
						$filename = tc_filename( $key );
						$this->messages[] = __all('Found a mix of &#92;r&#92;n and &#92;n line endings in file <strong>%1$s</strong>.', $filename);
						$this->errorLevel = $this->threatLevel;
					}
			}
		}	
	}
}

class LineEndings extends Check
{	
    protected function createChecks()
    {
		//	$test = "windows style line ending\r\nunix style line ending\n";
		//	file_put_contents('C:\xampp\htdocs\PIQPAQ\themecheck\include\unittests\index.php',$test);
		
			$this->title = __all("Line endings consistency");
			$this->checks = array(
						new LineEndings_Checker(TT_COMMON, ERRORLEVEL_WARNING, __all('Both DOS and UNIX style line endings'), null, 'ut_lineendings.zip')
			);
    }
}