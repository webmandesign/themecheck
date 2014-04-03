<?php
namespace ThemeCheck;

class Helpers
{
	/***
	*	Converts byte size in php.ini format to plain integer format
	**/
	public static function returnBytes($val) 
	{
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    switch($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
	}
	
	/*
	* replace non php code, string contents and comments with '-'. This function keeps the structure (lines, spaces, etc.) of the string to allow comparison with the orginal.
	TODO : detect <? ?>, <% %> and <script language="php"> </script> as PHP parts
	*/
	public static function filterPhp($raw)
	{
		$r = '';
		$len = strlen($raw);
		// 0 : not in an escaped part, 1 : 1 line comment, 2 : multiple lines comment, 3 : double quotes string, 4 : simple quotes string
		$current_mode = 0;
		$php = false;
		$last_c = '';
		for($i = 0; $i < $len; $i++)
		{
			$c = $raw[$i];
										
			if (!$php && strtolower(substr($raw, $i, 5)) == '<?php') $php = true;
			if ($php && $last_c == '>' && $raw[$i-2] == "?") $php = false;
			
			// mode start
			if ($php)
			{
				if ($current_mode == 0)
				{			
					if ($c == '/' && $last_c == '/') $current_mode = 1;
					else if ($c == '*' && $last_c == '/') $current_mode = 2;
					else if ($c == '"') $current_mode = 3;
					else if ($c == "'") $current_mode = 4;
					$r .= $c;
				} else {
					// mode end
					if ($current_mode == 1 && $c == "\n")
					{
						$current_mode = 0;
					}
					if ($current_mode == 2 && ($c == '/' && $last_c == '*'))
					{
						$r[strlen($r) - 1] = $last_c;
						$current_mode = 0;
					}
					if ($current_mode == 3 && ($c == '"' && $last_c != "\\"))
					{
						$current_mode = 0;
					}
					if ($current_mode == 4 && ($c == "'" && $last_c != "\\"))
					{
						$current_mode = 0;
					}		
					
					if ($current_mode == 0 || $c == "\n" || $c == "\t" || $c == "\r" || $c == "\f" || $c == " ")
						$r .= $c;
					else 
						$r .= '-';
				}
			} else {
				if ($c == "\n" || $c == "\t" || $c == "\r" || $c == "\f" || $c == " ")
					$r .= $c;
				else 
					$r .= '-';
			}
			$last_c = $c;
		}
			
		return $r;
	}
	
	public static function versionCmp($v1, $v2, $themetype)
	{
	
	}
}