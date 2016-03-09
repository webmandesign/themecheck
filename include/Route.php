<?php
namespace ThemeCheck;
include_once('ThemeInfo.php');

class Route {
	private static $instance;
	
	private function __construct() 
	{
		
	}

	public static function getInstance() 
	{
			if (!isset(self::$instance)) {
					$c = __CLASS__;
					self::$instance = new $c;
			}
			return self::$instance;
	}

	public function __clone(){trigger_error('Cloning not authorized.', E_USER_ERROR);}
	
	// This function is only called by I18N class. Static to avoid circular references.
	public static function getLangFromUrl()
	{
		global $ExistingLangs; // defined in Bootstrap.php
		
		$port = '';
		if ($_SERVER['SERVER_PORT'] != '80') $port = ':'.$_SERVER['SERVER_PORT'];
		$url = $_SERVER["SERVER_NAME"].$port.$_SERVER["REQUEST_URI"];

		// remove domain
		$p = strpos($url, TC_DOMAIN);
		if ($p !== FALSE) $url = substr($url, $p + strlen(TC_DOMAIN));	
		$url = strtolower($url);
		
		// remove params
		$tmp = explode("?", $url);
		$url = trim($tmp[0], '/ ');
		
		if (empty($url)) return TC_DEFAULT_LANG;
		if (strlen($url) < 2) return TC_DEFAULT_LANG;
		
		$pos = strpos($url, "/");
		if ($pos !== FALSE) $a = substr($url, 0, $pos);
		else $a = $url;
		
		foreach($ExistingLangs as $l)
		{
			if ($a === $l) return $l;
		}
		return TC_DEFAULT_LANG;
	}

	private static function endsWith($haystack, $needle)
	{
		$length = strlen($needle);
		if ($length == 0) {
			return true;
		}

		return (substr($haystack, -$length) === $needle);
	}

	// remove .html ou .php extension
	private static function removeStandardExtensionsFromUrl($value)
	{
		if (self::endsWith($value, ".html")) return substr($value, 0, strlen($value) - 5);
		if (self::endsWith($value, ".php")) return substr($value, 0, strlen($value) - 4);
		return $value;
	}
	
	// Rewritten URL -> Parameters
	public function match($url = null) 
	{ 
		global $ExistingLangs;
		$i18n = I18N::getInstance();
	
		$url = trim($url, '/ ');
		$port = '';
		if ($url == null) {
			if ($_SERVER['SERVER_PORT'] != '80') $port = ':'.$_SERVER['SERVER_PORT'];
			$url = $_SERVER["SERVER_NAME"].$port.$_SERVER["REQUEST_URI"];
		}
		
		// get url without domain
		$p = strpos($url, TC_DOMAIN);
		if ($p !== FALSE) $url = substr($url, $p + strlen(TC_DOMAIN));	
		$url = trim($url, '/ ');
		
		$route = array();
		$route["lang"] = TC_DEFAULT_LANG; // default language
		
		$path = strtolower(trim(urldecode($url), '/ '));
		// extract GET params
		$url_parts = parse_url($url);
		if (isset($url_parts["query"]))
		{
			$queryParts = explode('&', $url_parts["query"]); 
			$queries=array();
			foreach($queryParts as $q) 
			{ 
				list($key, $value) = explode("=", $q); 
				$route[$key] = urldecode($value);
			} 
		}
		
		$aa = explode("?", $path);
		$path = $aa[0];
		$params = null;
		if (count($aa)>1) $params = $aa[1];
		
		$parts = explode("/", $path);  
		foreach($ExistingLangs as $l)
		{
			if ($parts[0] == $l) {
				$route["lang"] = $l;
				array_shift($parts);
				break;
			}
		}

		if (count($parts) === 0)
		{
			$route["phpfile"] = "home"; 
		} 
		if (count($parts) === 1)
		{
			$p0 = $parts[0]; 
			
			if (empty($p0) || $p0== "index.php" || $p0 == "index.html" || $p0 == "index") $route["phpfile"] = "home";
			else if ($p0 == $i18n->url($route["lang"], "score")) 
			{
				$route["phpfile"] = "results";
			} else if ($p0 == $i18n->url($route["lang"], "unittests")) 
			{
				$route["phpfile"] = "unittests";
			} else if ($p0 == $i18n->url($route["lang"], "contact")) 
			{
				$route["phpfile"] = "contact";
			} else if ($p0 == "massimport") 
			{
				$route["phpfile"] = "massimport";
			}
			else if ($p0 == "download")
			{
				$route["phpfile"] = "download";
			}
				
			if (empty($route["phpfile"])) $route["phpfile"] = "error404";
		}
		else if (count($parts) > 1)
		{	 
			$p0 = $parts[0];                              

			if ($p0 == $i18n->url($route["lang"], "score")) 
			{
				if (isset($route["ut"]))
				{
					$route["phpfile"] = "results";
				} else {
					$uriNameSeo = null;
					$themetypesprefix = array ('wordpress_theme','wordpress-theme','joomla_template','joomla-template');

					foreach ($themetypesprefix as $prefix)
					{
						$prefixi18n = trim($i18n->url($route["lang"], $prefix));
						
						if (preg_match('/^'.$prefixi18n.'[_-]([0-9a-zA-Z\-\_\(\)]+)\.html$/', $parts[1], $matches))
						{
							$uriNameSeo = $matches[1];
							
							break;
						} 
					}
					
					if (empty($uriNameSeo)) $route["phpfile"] = "error404";
					else {
						$history = new History();
						
						$hash = $history->getHashFromUriNameSeoHigherVersion($uriNameSeo);
						if (empty($hash)) {
							$hash = $history->getHashFromUriNameSeo($uriNameSeo);
							
							if (!empty($hash)) { // url rewriting migration (to be erased in 2017) : redirect if highest version theme is reached from a versioned url
								$isHighestVersion = $history->isHighestVersion($hash);
								if ($isHighestVersion == 1)
								{
									$newUrl = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>$route["lang"], "phpfile"=>"results", "hash"=>$hash));
									ob_clean();
									header("Status: 301 Moved Permanently", false, 301);
									header("Location: ".$newUrl);
									exit();
								}
							}
							
							if (empty($hash)) {
								$hash = $history->getHashFromNamesanitized($uriNameSeo);
								
								if (!empty($hash)) {
									$history->generateUriNameSeoInDb($hash);
									$newUrl = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>$route["lang"], "phpfile"=>"results", "hash"=>$hash));
									ob_clean();
									header("Status: 301 Moved Permanently", false, 301);
									header("Location: ".$newUrl);
									exit();
								}
							}
						}
						if (empty($hash)) $route["phpfile"] = "error404";
						else {
							$route["hash"] = $hash;
							if ($p0 == $i18n->url($route["lang"], "unittests"))	$route["phpfile"] = "unittests";
							else $route["phpfile"] = "results";
						}
					}	
				}				
			} 

			if (empty($route["phpfile"])) $route["phpfile"] = "error404";
		}
                
		return $route;
	}

	// Parameters -> Rewritten URL
	public function assemble($route = array())
	{
		$i18n = I18N::getInstance();
	
		$lang = "";
		if (isset($route["lang"]))
		{
			if ($route["lang"] == TC_DEFAULT_LANG) $lang = ""; // if language is default TC_DEFAULT_LANG : no language part in url
			else $lang = $route["lang"];
		}
		
		$url = "";
		if (!empty($lang)) $url = $lang.'/';
		if (!isset($route["phpfile"])) return "Error : phpfile not defined";
		if ($route["phpfile"] == "home")
		{
			$url = trim($url, '/ ');                                                                                   
		} 
		else if ($route["phpfile"] == "results")
		{
			$data = array();
			if (isset($route["hash"])) 
			{
				$url = trim($url.$i18n->url($route["lang"], 'score'), '/ ');
				
				$history = new History();
				$themeInfo = $history->loadThemeFromHash($route["hash"]);
				if (empty($themeInfo)) return null;
				if ($themeInfo->themetype == TT_WORDPRESS) 	$data["themetype"] = 'wordpress-theme';
				if ($themeInfo->themetype == TT_JOOMLA) 	$data["themetype"] = 'joomla-template';
				if ($themeInfo->themetype == TT_WORDPRESS_CHILD) 	$data["themetype"] = 'wordpress-theme';

				if ($themeInfo->isHigherVersion)
					$url .= '/'.trim($i18n->url($route["lang"], $data["themetype"])).'-'.$themeInfo->uriNameSeoHigherVersion.'.html';
				else 
					$url .= '/'.trim($i18n->url($route["lang"], $data["themetype"])).'-'.$themeInfo->uriNameSeo.'.html';
			}	else if (isset($route["uriNameSeo"]) && isset($route["themetype"])) 
			{
				$url = trim($url.$i18n->url($route["lang"], 'score'), '/ ');

				if ($route["themetype"] == TT_WORDPRESS) 	$data["themetype"] = 'wordpress-theme';
				if ($route["themetype"] == TT_JOOMLA) 		$data["themetype"] = 'joomla-template';
				if ($route["themetype"] == TT_WORDPRESS_CHILD) 	$data["themetype"] = 'wordpress-theme';
				$url .= '/'.trim($i18n->url($route["lang"], $data["themetype"])).'-'.$route["uriNameSeo"].'.html';
			} 
			else if (isset($route["namesanitized"]) && isset($route["themetype"])) // legacy
			{
				$url = trim($url.$i18n->url($route["lang"], 'score'), '/ ');

				if ($route["themetype"] == TT_WORDPRESS) 	$data["themetype"] = 'wordpress_theme';
				if ($route["themetype"] == TT_JOOMLA) 		$data["themetype"] = 'joomla_template';
				if ($route["themetype"] == TT_WORDPRESS_CHILD) 	$data["themetype"] = 'wordpress_theme';
				$url .= '/'.trim($i18n->url($route["lang"], $data["themetype"])).'_'.$route["namesanitized"].'.html';
			}
			else if (isset($route["ut"]))
			{
				$url = trim($url.$i18n->url($route["lang"], 'score'), '/ ').'?ut='.urlencode($route["ut"]);
			}
			else {
				$url = trim($url.$i18n->url($route["lang"], 'score'), '/ ');
			}
		} 
        else if ($route["phpfile"] == "unittests")
		{
			$url = trim($url.$i18n->url($route["lang"], 'unittests'), '/ ');
			
		} 
        else if ($route["phpfile"] == "massimport")
		{
			$url = "massimport";
		}	
        else if ($route["phpfile"] == "contact")
		{
			$url = trim($url.$i18n->url($route["lang"], 'contact'), '/ ');
		} 
        else if ($route["phpfile"] == "error404.php")
		{
			$url = trim($url.$i18n->url($route["lang"], $route["phpfile"]), '/ ');
		}
             
		return strtolower($url);
	}
	
	public function updateSitemap($themetype = 0)
	{
		$d = date("Y-m-d");
		$string = '<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
   <sitemap>
      <loc>'.TC_HTTPDOMAIN.'/sitemap_static.xml</loc>
      <lastmod>'.$d.'</lastmod>
   </sitemap>
   <sitemap>
      <loc>'.TC_HTTPDOMAIN.'/sitemap_wordpress.xml</loc>
      <lastmod>'.$d.'</lastmod>
   </sitemap>
   <sitemap>
      <loc>'.TC_HTTPDOMAIN.'/sitemap_wordpress_child.xml</loc>
      <lastmod>'.$d.'</lastmod>
   </sitemap>
   <sitemap>
      <loc>'.TC_HTTPDOMAIN.'/sitemap_joomla.xml</loc>
      <lastmod>'.$d.'</lastmod>
   </sitemap>
</sitemapindex>';
		
		$fp = fopen(TC_ROOTDIR.'/sitemap.xml', 'w');
		fwrite($fp, $string);
		fclose($fp);
		
		$string = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
	<loc>'.TC_HTTPDOMAIN.'</loc>
	<lastmod>'.$d.'</lastmod>
	<changefreq>hourly</changefreq>
	<priority>1</priority>
  </url>
  <url>
	<loc>'.TC_HTTPDOMAIN.'/fr/</loc>
	<lastmod>'.$d.'</lastmod>
	<changefreq>hourly</changefreq>
	<priority>1</priority>
  </url>
</urlset>';

		$fp = fopen(TC_ROOTDIR.'/sitemap_static.xml', 'w');
		fwrite($fp, $string);
		fclose($fp);
		
		if ($themetype > 0) {
			$history = new History();
			
			if ($themetype == 2 ) 
			{
				$filename = 'sitemap_joomla.xml';
				$rows = $history->getAllFromType(2);
			} else if ($themetype == 4 ) {
				$filename = 'sitemap_wordpress_child.xml';
				$rows = $history->getAllFromType(4);
			} else {
				$rows = $history->getAllFromType(1);
				$filename = 'sitemap_wordpress.xml';
			}
			
			$fp = fopen(TC_ROOTDIR.'/'.$filename, 'w');
			$string = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
			fwrite($fp, $string);
			foreach($rows as $row)
			{
				$l = 'en';
				$d = substr($row["validationDate"],0,10);
				global $ExistingLangs;
				foreach($ExistingLangs as $l)
				{
					if ($row['isHigherVersion'] == 1)
						$url = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>$l, "phpfile"=>"results", "uriNameSeo"=>$row["uriNameSeoHigherVersion"], "themetype"=>$row["themetype"]));
					else
						$url = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>$l, "phpfile"=>"results", "uriNameSeo"=>$row["uriNameSeo"], "themetype"=>$row["themetype"]));
					$string = '<url>
	<loc>'.$url.'</loc>
	<lastmod>'.$d.'</lastmod>
	<changefreq>monthly</changefreq>
	<priority>0.5</priority>
  </url>';
					fwrite($fp, $string);
				}
			}
			fwrite($fp, '</urlset>');
			fclose($fp);
		}
	}
}