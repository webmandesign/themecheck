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
		
		public function doCheck($php_files, $php_files_filtered, $css_files, $other_files)
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
                if ( $image[0] > 880 || $image[1] > 660 ) {
                    $this->messages[] = __all('Screenshot size is too large. Detected: <strong>%1$sx%2$spx</strong>. Recommended size is 880x660px.', $image[0], $image[1]);
                    $this->errorLevel = $this->threatLevel;
                }
                if ( $image[1] / $image[0] != 0.75 ) {
										$width = $image[0];
										$height = $image[1];
										$this->reduc($width, $height);
                    $this->messages[] = __all('Wrong screenshot dimensions. Detected: <strong>%1$sx%2$spx (%3$s:%4$s)</strong>. Ratio of width to height should be 4:3.', $image[0], $image[1], $width, $height);
                    $this->errorLevel = $this->threatLevel;
                }
                if ( $image[0] < 600 || $image[1] < 450 ) {
                    $this->messages[] = __all('Screenshot size is <strong>%1$sx%2$spx</strong>. Although any 4:3 image size is acceptable, size should be at least 600x450 to account for HiDPI displays.',$image[0], $image[1]);
                    $this->errorLevel = ERRORLEVEL_WARNING;
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