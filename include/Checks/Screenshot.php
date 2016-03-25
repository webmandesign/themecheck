<?php

namespace ThemeCheck;

class Screenshot_Checker extends CheckPart
{
	private function pgcd($i, $j)
	{
		if($j == 0) return $i;

		return $this->pgcd($j, $i % $j);
	}

	private function reduc(&$a, &$b)
	{
		$p = $this->pgcd($a,$b);
		if ($p==0) return;
		$a = $a / $p;
		$b = $b / $p;
		
		// show 16:x instead of 8:x
		if ($a == 8)
		{
			$a = 16;
			$b *= 2;
		}
	}
	
	public function doCheck($php_files, $php_files_filtered, $css_files, $other_files, $themeInfo)
    {
        $found = false;
        $this->errorLevel = ERRORLEVEL_WARNING;

        foreach ( $other_files as $other_key => $otherfile )
        {
            if ( basename( $other_key ) === 'screenshot.png' || basename( $other_key ) === 'screenshot.jpg' || preg_match( '/.*themes\/[^\/]*\/screenshot\.(png|jpg)/', $other_key ))
            {
                $found = true;
                $this->errorLevel = ERRORLEVEL_SUCCESS;
                // we have or screenshot!
                $image = getimagesize( $other_key );
                if ( $image[0] > 1200 || $image[1] > 900 ) {
                    $this->messages[] = __all('Screenshot is wrong size! Detected: <strong>%1$sx%2$spx</strong>. Maximum allowed size is 1200x900px.', $image[0], $image[1]);
                    $this->errorLevel = $this->threatLevel;
                }
                if ( $image[1] / $image[0] != 0.75 ) {
										$width = $image[0];
										$height = $image[1];
										$this->reduc($width, $height);
                    $this->messages[] = __all('Screenshot dimensions are wrong! Detected: <strong>%1$sx%2$spx (%3$s:%4$s)</strong>. Ratio of width to height should be 4:3.', $image[0], $image[1], $width, $height);
                    $this->errorLevel = $this->threatLevel;
                }
                if ( $image[0] < 1200 || $image[1] < 900 ) {
                    $this->messages[] = __all('Screenshot size is <strong>%1$sx%2$spx</strong>. Screenshot size should be 1200x900, to account for HiDPI displays. Any 4:3 image size is acceptable, but 1200x900 is preferred.',$image[0], $image[1]);
                    $this->errorLevel = ERRORLEVEL_WARNING;
                }
				
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$mimetype = strtolower(finfo_file($finfo, $other_key));
				finfo_close($finfo);
				if ( pathinfo($other_key, PATHINFO_EXTENSION) == 'png' && $mimetype != "image/png" )  {
					$this->messages[] = __all('Bad screenshot file extension ! File <strong>%1$s</strong> is not an actual PNG file. Detected type was : <strong>&quot;%2$s&quot;</strong>.', htmlspecialchars(basename( $other_key )), $mimetype);
                    $this->errorLevel = $this->threatLevel;
				}
				if ( pathinfo($other_key, PATHINFO_EXTENSION) != 'jpg' && $mimetype != "image/jpeg" )  {
					$this->messages[] = __all('Bad screenshot file extension ! File <strong>%1$s</strong> is not an actual JPG file. Detected type was : <strong>&quot;%2$s&quot;</strong>.', htmlspecialchars(basename( $other_key )), $mimetype);
                    $this->errorLevel = $this->threatLevel;
				}
                break;
            }
        }
        
        if(!$found)
        {
            $this->messages[] = __all("No screenshot detected. Theme archive must contain a <strong>screenshot.png</strong> file with a recommanded resolution of 600x450.");
			$this->errorLevel = $this->threatLevel;
        }
    }
}

class Screenshot extends Check
{	
    protected function createChecks()
    {
			$this->title = __all("Screenshot");
			$this->checks = array(
						new Screenshot_Checker('SCREENSHOT', TT_WORDPRESS, ERRORLEVEL_WARNING, __all('Screenshot file'), null, 'ut_screenshot_wordpress.zip'),
			);
    }
}