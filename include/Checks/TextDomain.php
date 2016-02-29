<?php
namespace ThemeCheck;
class TextDomain_Checker extends CheckPart
{
	// taken form WordPress
	function reset_mbstring_encoding() {
		$this->mbstring_binary_safe_encoding( true );
	}

	// taken form WordPress
	function mbstring_binary_safe_encoding( $reset = false ) {
		static $encodings = array();
		static $overloaded = null;
	 
		if ( is_null( $overloaded ) )
			$overloaded = function_exists( 'mb_internal_encoding' ) && ( ini_get( 'mbstring.func_overload' ) & 2 );
	 
		if ( false === $overloaded )
			return;
	 
		if ( ! $reset ) {
			$encoding = $this->mb_internal_encoding();
			array_push( $encodings, $encoding );
			$this->mb_internal_encoding( 'ISO-8859-1' );
		}
	 
		if ( $reset && $encodings ) {
			$encoding = array_pop( $encodings );
			$this->mb_internal_encoding( $encoding );
		}
	}
	
	// taken form WordPress
	function utf8_uri_encode( $utf8_string, $length = 0 ) {
		$unicode = '';
		$values = array();
		$num_octets = 1;
		$unicode_length = 0;

		$this->mbstring_binary_safe_encoding();
		$string_length = strlen( $utf8_string );
		$this->reset_mbstring_encoding();

		for ($i = 0; $i < $string_length; $i++ ) {

			$value = ord( $utf8_string[ $i ] );

			if ( $value < 128 ) {
				if ( $length && ( $unicode_length >= $length ) )
					break;
				$unicode .= chr($value);
				$unicode_length++;
			} else {
				if ( count( $values ) == 0 ) {
					if ( $value < 224 ) {
						$num_octets = 2;
					} elseif ( $value < 240 ) {
						$num_octets = 3;
					} else {
						$num_octets = 4;
					}
				}

				$values[] = $value;

				if ( $length && ( $unicode_length + ($num_octets * 3) ) > $length )
					break;
				if ( count( $values ) == $num_octets ) {
					for ( $j = 0; $j < $num_octets; $j++ ) {
						$unicode .= '%' . dechex( $values[ $j ] );
					}

					$unicode_length += $num_octets * 3;

					$values = array();
					$num_octets = 1;
				}
			}
		}

		return $unicode;
	}

	// taken from wordpress
	function seems_utf8( $str ) {
		$this->mbstring_binary_safe_encoding();
		$length = strlen($str);
		$this->reset_mbstring_encoding();
		for ($i=0; $i < $length; $i++) {
			$c = ord($str[$i]);
			if ($c < 0x80) $n = 0; // 0bbbbbbb
			elseif (($c & 0xE0) == 0xC0) $n=1; // 110bbbbb
			elseif (($c & 0xF0) == 0xE0) $n=2; // 1110bbbb
			elseif (($c & 0xF8) == 0xF0) $n=3; // 11110bbb
			elseif (($c & 0xFC) == 0xF8) $n=4; // 111110bb
			elseif (($c & 0xFE) == 0xFC) $n=5; // 1111110b
			else return false; // Does not match any model
			for ($j=0; $j<$n; $j++) { // n bytes matching 10bbbbbb follow ?
				if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
					return false;
			}
		}
		return true;
	}

	// taken from wordpress
	function sanitize_title_with_dashes( $title ) {
		$title = strip_tags($title);
		// Preserve escaped octets.
		$title = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $title);
		// Remove percent signs that are not part of an octet.
		$title = str_replace('%', '', $title);
		// Restore octets.
		$title = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $title);

		if ($this->seems_utf8($title)) {
			if (function_exists('mb_strtolower')) {
				$title = mb_strtolower($title, 'UTF-8');
			}
			$title = $this->utf8_uri_encode($title, 200);
		}

		$title = strtolower($title);
		$title = preg_replace('/&.+?;/', '', $title); // kill entities
		$title = str_replace('.', '-', $title);

		//if ( 'save' == $context ) {
			// Convert nbsp, ndash and mdash to hyphens
			$title = str_replace( array( '%c2%a0', '%e2%80%93', '%e2%80%94' ), '-', $title );

			// Strip these characters entirely
			$title = str_replace( array(
				// iexcl and iquest
				'%c2%a1', '%c2%bf',
				// angle quotes
				'%c2%ab', '%c2%bb', '%e2%80%b9', '%e2%80%ba',
				// curly quotes
				'%e2%80%98', '%e2%80%99', '%e2%80%9c', '%e2%80%9d',
				'%e2%80%9a', '%e2%80%9b', '%e2%80%9e', '%e2%80%9f',
				// copy, reg, deg, hellip and trade
				'%c2%a9', '%c2%ae', '%c2%b0', '%e2%80%a6', '%e2%84%a2',
				// acute accents
				'%c2%b4', '%cb%8a', '%cc%81', '%cd%81',
				// grave accent, macron, caron
				'%cc%80', '%cc%84', '%cc%8c',
			), '', $title );

			// Convert times to x
			$title = str_replace( '%c3%97', 'x', $title );
		//}

		$title = preg_replace('/[^%a-z0-9 _-]/', '', $title);
		$title = preg_replace('/\s+/', '-', $title);
		$title = preg_replace('|-+|', '-', $title);
		$title = trim($title, '-');

		return $title;
	}
    public function doCheck($php_files, $php_files_filtered, $css_files, $other_files)
    {		
        $this->errorLevel = ERRORLEVEL_SUCCESS;
	
		if ($this->id == 'TEXTDOMAIN_THEMEPATH')
		{
			// core names their themes differently
			$exceptions = array( 'twentyten',  'twentyeleven',  'twentytwelve',  'twentythirteen',  'twentyfourteen',  'twentyfifteen',  'twentysixteen',  'twentyseventeen',  'twentyeighteen',  'twentynineteen',  'twentytwenty'  );
			
			// ignore core themes and uploads on w.org for this one check
			$themeName = $this->code[0]; // Notice : in themecheck plugin, variable $themename holds the theme directory
			$themeDir = $this->code[1]; 

			if ( !in_array($themeDir, $exceptions) ) { // check for exeptions but not for !defined( 'WPORGPATH' ) as the plugin does because it doesn't make sense when not in a WordPress installation.
				$correct_domain = $this->sanitize_title_with_dashes($themeName);

				if ( $themeDir != $correct_domain ) {
					$this->messages[] = __all('Wrong installation directory for the theme name. The directory name must match the slug of the theme. This theme\'s correct slug and text-domain is <strong>%1$s</strong>.', $correct_domain );
					$this->errorLevel = $this->threatLevel;
				}
			}
		}
		
		if ($this->id == 'TEXTDOMAIN_MISSING')
		{
			// rules come from WordPress core tool makepot.php, modified by me to have domain info
			$rules = array(
				'__' => array('string', 'domain'),
				'_e' => array('string', 'domain'),
				'_c' => array('string', 'domain'),
				'_n' => array('singular', 'plural', 'domain'),
				'_n_noop' => array('singular', 'plural', 'domain'),
				'_nc' => array('singular', 'plural', 'domain'),
				'__ngettext' => array('singular', 'plural', 'domain'),
				'__ngettext_noop' => array('singular', 'plural', 'domain'),
				'_x' => array('string', 'context', 'domain'),
				'_ex' => array('string', 'context', 'domain'),
				'_nx' => array('singular', 'plural', 'context', 'domain'),
				'_nx_noop' => array('singular', 'plural', 'context', 'domain'),
				'_n_js' => array('singular', 'plural', 'domain'),
				'_nx_js' => array('singular', 'plural', 'context', 'domain'),
				'esc_attr__' => array('string', 'domain'),
				'esc_html__' => array('string', 'domain'),
				'esc_attr_e' => array('string', 'domain'),
				'esc_html_e' => array('string', 'domain'),
				'esc_attr_x' => array('string', 'context', 'domain'),
				'esc_html_x' => array('string', 'context', 'domain'),
				'comments_number_link' => array('string', 'singular', 'plural', 'domain'),
			);
			
			// make sure the tokenizer is available
			if ( !function_exists( 'token_get_all' ) ) {
				return;
			}

			$funcs = array_keys($rules);
			
			$domains = array();
			
			foreach ( $php_files as $php_key => $phpfile ) {
				$error='';
				
				// tokenize the file
				$tokens = token_get_all($phpfile);
				
				$in_func = false;
				$args_started = false;
				$parens_balance = 0;
				$found_domain = false;

				foreach($tokens as $token) {
					$string_success = false;
					
					if (is_array($token)) {
						list($id, $text) = $token;
						if (T_STRING == $id && in_array($text, $funcs)) {
							$in_func = true;
							$func = $text;
							$parens_balance = 0;
							$args_started = false;
							$found_domain = false;
						} elseif (T_CONSTANT_ENCAPSED_STRING == $id) {
							if ($in_func && $args_started) {
								if (! isset( $rules[$func][$args_count] ) ) {
									// avoid a warning when too many arguments are in a function, cause a fail case
									$new_args = $args;
									$new_args[] = $text;
									$filename = tc_filename( $php_key );
									$this->messages[] = __all('Found a translation function that has an incorrect number of arguments. Function %1$s, with the arguments %2$s in file %3$s.', 
																'<strong>' . $func . '</strong>',
																'<strong>' . implode(', ',$new_args) . '</strong>',
																'<strong>' . $filename . '</strong>');
																
									$this->errorLevel = $this->threatLevel;
								} else if ($rules[$func][$args_count] == 'domain') {
									// strip quotes from the domain, avoids 'domain' and "domain" not being recognized as the same
									$text = str_replace(array('"', "'"), '', $text);
									$domains[] = $text;
									$found_domain=true;
								}
								if ($parens_balance == 1) {
									$args_count++;
									$args[] = $text;
								}
							}
						}
						$token = $text;
					} elseif ('(' == $token){
						if ($parens_balance == 0) {
							$args=array();
							$args_started = true;
							$args_count = 0;
						}
						++$parens_balance;
					} elseif (')' == $token) {
						--$parens_balance;
						if ($in_func && 0 == $parens_balance) {
							if (!$found_domain) {
								$filename = tc_filename( $php_key );
								$this->messages[] = __all('Found a translation function that is missing a text-domain. Function %1$s, with the arguments %2$s in file %3$s.', 
																'<strong>' . $func . '</strong>',
																'<strong>' . implode(', ',$args) . '</strong>',
																'<strong>' . $filename . '</strong>');
																
								$this->errorLevel = $this->threatLevel;
							}
							$in_func = false;
							$func='';
							$args_started = false;
							$found_domain = false;
						}
					}
				}
			}
			
			$domains = array_unique($domains);
			$domainlist = implode( ', ', $domains );
			$domainscount = count($domains);
			
			if ( $domainscount > 1 ) {
				$this->messages[] = __all('More than one text-domain is being used in this theme. This means the theme will not be compatible with WordPress.org language packs. The domains found are %1$s.', 
																'<strong>' . $domainlist . '</strong>');
				$this->errorLevel = $this->threatLevel;
			}  
			// themecheck plugin info "Only one text-domain is being used in this theme" not relevant in themecheck.org
			/*else {
				$this->error[] = '<span class="tc-lead tc-info">' . __( 'INFO', 'theme-check' ) . '</span>: ' 
				. __( "Only one text-domain is being used in this theme. Make sure it matches the theme's slug correctly so that the theme will be compatible with WordPress.org language packs.", 'theme-check' )
				. '<br>'
				. sprintf( __( 'The domain found is %s', 'theme-check'), '<strong>' . $domainlist . '</strong>' );
			}*/
		}
    }
}

class TextDomain extends Check
{	
    protected function createChecks()
    {
		$this->title = __all("Text domain");
		$this->checks = array(
					new TextDomain_Checker('TEXTDOMAIN_THEMEPATH', TT_WORDPRESS, ERRORLEVEL_WARNING, __all('Incorrect use of translation functions.'), null, 'ut_textdomain_themepath.zip'),
					new TextDomain_Checker('TEXTDOMAIN_MISSING', TT_WORDPRESS | TT_WORDPRESS_CHILD, ERRORLEVEL_WARNING, __all('Incorrect use of translation functions.'), null, 'ut_textdomain_missing.zip')
		);
    }
	
	public function doCheck($php_files, $php_files_filtered, $css_files, $other_files)
	{
		$start_time_checker = microtime(true);
		foreach ($this->checks as &$check)
		{
			if ($this->currentThemetype & $check->themetype)
			{
				$start_time = microtime(true);
				$check->code = array($this->currentThemeName, $this->currentThemeDir);
				$check->doCheck($php_files, $php_files_filtered, $css_files, $other_files);
				$check->duration = microtime(true) - $start_time; // check duration is calculated outside of the check to simplify check's code
			}
		}	
		$this->duration = microtime(true) - $start_time_checker;
	}
}