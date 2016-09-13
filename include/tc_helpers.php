<?php
namespace ThemeCheck;

// helper functions imported from wordpress "theme checks" plugin 

function tc_filename( $file ) {
	$filename = ( preg_match( '/themes\/[a-z0-9-]*\/(.*)/', $file, $out ) ) ? $out[1] : basename( $file );
	return $filename;
}

// some functions theme checks use
function tc_grep( $error, $file ) {
	if ( ! file_exists( $file ) ) {
		return '';
	}
	$lines = file( $file, FILE_IGNORE_NEW_LINES ); // Read the theme file into an array
	$line_index = 0;
	$bad_lines = '';
	foreach( $lines as $this_line )	{
		if ( stristr ( $this_line, $error ) ) {
			$error = str_replace( '"', "'", $error );
			$this_line = str_replace( '"', "'", $this_line );
			$error = ltrim( $error );
			$pre = ( FALSE !== ( $pos = strpos( $this_line, $error ) ) ? substr( $this_line, 0, $pos ) : FALSE );
			$pre = ltrim( htmlspecialchars( $pre ) );
			$bad_lines .= __("<pre>Line ") . ( $line_index+1 ) . ": " . $pre . htmlspecialchars( substr( stristr( $this_line, $error ), 0, 75 ) ) . "</pre>";
		}
		$line_index++;
	}
	return str_replace( $error, '<span>' . $error . '</span>', $bad_lines );
}

function tc_preg( $preg, $file )
{
    $lines = file( $file, FILE_IGNORE_NEW_LINES ); // Read the theme file into an array
    $line_index = 0;
    $bad_lines = '';
	$error = '';
    foreach( $lines as $this_line ) {
        if ( preg_match( $preg, $this_line, $matches ) ) {
            $error = $matches[0];
            $this_line = str_replace( '"', "'", $this_line );
            $error = ltrim( $error );
            $pre = '';
			if ( !empty( $error ) ) {
				$pre = ( FALSE !== ( $pos = strpos( $this_line, $error ) ) ? substr( $this_line, 0, $pos ) : FALSE );
			}
            $pre = ltrim( htmlspecialchars( $pre ) );
            $bad_lines .= __("<pre>Line ") . ( $line_index+1 ) . ": " . $pre . htmlspecialchars( substr( stristr( $this_line, $error ), 0, 75 ) ) . "</pre>";
        }
        $line_index++;

    }
    return str_replace( $error, '<span>' . $error . '</span>', $bad_lines );
}

function tc_preg_lines( $preg, $file )
{
    $lines = file( $file, FILE_IGNORE_NEW_LINES ); // Read the theme file into an array
    $bad_lines = array();
    foreach( $lines as $this_line ) {
        if ( preg_match( $preg, $this_line, $matches ) ) {
            $this_line = str_replace( '"', "'", $this_line );
						$bad_lines[] = trim($this_line);
        }
    }
    return $bad_lines;
}

function listdir( $dir ) {
	$files = array();
	$dir_iterator = new \RecursiveDirectoryIterator( $dir );
	$iterator = new \RecursiveIteratorIterator($dir_iterator, \RecursiveIteratorIterator::SELF_FIRST);
	
	foreach ($iterator as $file) {
    	array_push( $files, $file->getPathname() );
	}
	return $files;
}

function esc_html( $html, $char_set = 'UTF-8' )
{
    if ( empty( $html ) ) {
        return '';
    }

    $html = (string) $html;
    $html = htmlspecialchars( $html, ENT_QUOTES, $char_set );

    return $html;
}