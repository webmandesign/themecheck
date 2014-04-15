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
		$tokens = token_get_all($raw);

		$result = '';
		foreach ($tokens as $token) {
			if (!isset($token[1]))
				$result .= $token;
			elseif (
				$token[0] == T_COMMENT
				|| $token[0] == T_INLINE_HTML
				|| $token[0] == T_CONSTANT_ENCAPSED_STRING
				|| $token[0] == T_START_HEREDOC
				|| $token[0] == T_END_HEREDOC
				|| $token[0] == T_ENCAPSED_AND_WHITESPACE
				|| $token[0] == T_DOC_COMMENT
			)
				$result .= preg_replace('#.#', '-', $token[1]); // permet de récupérer la newline d'origine
			else 
				$result .= $token[1];
		}
			
		return $result;
	}
}