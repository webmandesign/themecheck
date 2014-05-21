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
				$result .= preg_replace('#[^\R]#', '-', $token[1]); // replace non-lineending characters by "-"
			else
				$result .= $token[1];
		}

		return $result;
	}

	/**
	*	Encode an HTML string in BB code
	*/
  static function encodeBB($string)
	{
		$search = array(
		
			'#<img[^\>]*?src=("|\')([^\1]*?)\1[^\>]*?/?>#is',
			'#<a[^\>]*?href=("|\')([^\1]*?)\1[^\>]*?>(.*?)</a>#is',
			'#<iframe[^\>]*?src=("|\')(https?:)?//www\.youtube(-nocookie)?\.com/embed/([A-Za-z0-9]{11})(\?rel=0)?\1[^\>]*?></iframe>#is',
			
			'#<br\s*/?>#is',
			'#<hr\s*/?>#is',
			'#<(b|strong)>#is',
			'#<(i|em)>#is',
			'#<u>#is',

			'#<ul>#is',
			'#<li>#is',
			'#<code>#is',
			'#<blockquote>#is',
			'#<p>#is',
			'#<div>#is',
 			'#<span>#is',

			'#<\/p>#is',
			'#<\/div>#is',
			'#<\/b>#is',
			'#<\/strong>#is',
			'#<\/u>#is',
			'#<\/i>#is',
			'#<\/em>#is',
			'#<\/span>#is',
			'#<\/ul>#is',
			'#<\/li>#is',
			'#<\/code>#is',
			'#<\/pre>#is'
		);
		$replace = array(
			'[img]$1$2$1[/img]',
			'[url=$1$2$1]$3[/url]',
			'[youtube]$4[/youtube]',

			'[br]',
			'[hr]',
			'[b]',
			'[i]',
			'[u]',
			
			'[ul]',
			'[li]',
			'[code]',
			'[quote]',
			'[p]',
			'[div]',
 			'[span]',

			'[/p]',
			'[/div]',
			'[/b]',
			'[/b]',
			'[/u]',
			'[/i]',
			'[/i]',
			'[/span]',
			'[/ul]',
			'[/li]',
			'[/code]',
			'[/code]'
		);
		
		return preg_replace($search, $replace, $string);
	}

	/**
	*	Decode a BB string to HTML
	*/
	static function decodeBB($string)
	{
		$search = array(
			'#\[quote=(.*?)\](.*?)\[\/quote\]#is',
			'#\[quote\](.*?)\[\/quote\]#is',
			'#\[img\](.*?)\[\/img\]#is',
			'#\[img class="(.*?)"\](.*?)\[\/img\]#is',
			'#\[img style="(.*?)"\](.*?)\[\/img\]#is',
			'#\[img class="(.*?)" style="(.*?)"\](.*?)\[\/img\]#is',
			'#\[youtube\]([A-Za-z0-9]{11})\[\/youtube\]#is',
			'#\[br\]#is',
			'#\[hr\]#is',
			'#\[b\]#is',
			'#\[i\]#is',
			'#\[u\]#is',
			'#\[ul\]#is',
			'#\[ul style="(.*?)"\]#is',
			'#\[ul class="(.*?)"\]#is',
			'#\[ul class="(.*?)" style="(.*?)"\]#is',
			'#\[li\]#is',
			'#\[code\]#is',
			'#\[url\="(.*?)" class="(.*?)"\]#is',
			'#\[url\=(.*?)\]#is',
			'#\[p\]#is',
			'#\[p style="(.*?)"\]#is',
			'#\[p class="(.*?)"\]#is',
			'#\[p class="(.*?)" style="(.*?)"\]#is',
			'#\[div\]#is',
			'#\[div style="(.*?)"\]#is',
			'#\[div class="(.*?)"\]#is',
			'#\[div class="(.*?)" style="(.*?)"\]#is',
			'#\[float=left\]#is',
 			'#\[span\]#is',
			'#\[span style="(.*?)"\]#is',
			'#\[span class="(.*?)"\]#is',
			'#\[span class="(.*?)" style="(.*?)"\]#is',
			'#\[code\](.*?)\[\/code\]#is',
			'#\[code\=(.*?)\](.*?)\[\/code\]#is',
			'#\[\/p\]#is',
			'#\[\/div\]#is',
			'#\[\/float\]#is',
			'#\[\/b\]#is',
			'#\[\/u\]#is',
			'#\[\/i\]#is',
			'#\[\/span\]#is',
			'#\[\/ul\]#is',
			'#\[\/li\]#is',
			'#\[\/url\]#is',
			'#\[\/code\]#is',
			'#\[marginleft\](.*?)\[\/marginleft\]#is',
			'#\[float=left\](.*?)\[\/float]#',
			'#\[class=(.*?)\](.*?)\[\/class\]#is',
			'#\[font size\=(.*?)\](.*?)\[\/font\]#is',
			'#\[font color\=(.*?)\](.*?)\[\/font\]#is',
		);
		
		$replace = array(
			'<div class="blockquote">$2<div class="from">$1</div></div>',
			'<div class="blockquote">$1</div>',
			'<img src=$1/>',
			'<img src="$2" class="$1" />',
			'<img src="$2" style="$1" />',
			'<img src="$3" class="$1" style="$2" />',
			'<iframe allowfullscreen="" frameborder="0" height="315" class="youtubeVideo" src="https://www.youtube-nocookie.com/embed/$1?rel=0" width="560"></iframe>',
			'<br/>',
			'<hr/>',
			'<b>',
			'<i>',
			'<u>',
			'<ul>',
			'<ul style="$1">',
			'<ul class="$1">',
			'<ul class="$1" style="$2">',
			'<li>',
			'<code>',
			'<a href="$1" class="$2">',
			'<a href=$1>',
			'<p>',
			'<p style="$1">',
			'<p class="$1">',
			'<p class="$1" style="$2">',
			'<div>',
			'<div style="$1">',
			'<div class="$1">',
			'<div class="$1" style="$2">',
			'<div style="float:left">',
			'<span>',
			'<span style="$1">',
			'<span class="$1">',
			'<span class="$1" style="$2">',

			'<pre class="code">$1</pre>',
			'<pre class="code brush:$1;">$2</pre>',

			'</p>',
			'</div>',
			'</div>',
			'</b>',
			'</u>',
			'</i>',
			'</span>',
			'</ul>',
			'</li>',
			'</a>',
			'</code>',
			'<div style="margin-left: 40px;">$1</div>',
			'<div style="float: left; padding-right: 6px;">$1</div>',
			'<div class="$1">$2</div>',
			'<span style="font-size:$1;">$2</span>',
			'<span style="color:$1;">$2</span>'
		);
		return preg_replace($search, $replace, $string);
	}
}