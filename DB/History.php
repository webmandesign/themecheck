<?php
namespace ThemeCheck;
require_once TC_INCDIR.'/ThemeInfo.php';
require_once TC_INCDIR.'/ListDirectoryFiles.php';
require_once TC_INCDIR.'/Check.php';
require_once TC_INCDIR.'/tc_helpers.php';
require_once TC_INCDIR.'/wpvuln.php';

/** 
*		Database connection info
*		(All DB stuff is grouped in this file in order to make it easy to run the site without DB)
**/
//define("DB_HOST", "");
//define("DB_DATABASE", "");
//define("DB_USER", "");
//define("DB_PWD", "");
require_once ("db_credentials.php"); // not in git :-)

/** 
*		Store history of validations in DB.
* 	This class only manages metadata; Test results are stord on disk and don't require a database.
**/
class History
{
	private $db;
	private $query_theme_insert;
	private $query_theme_update_score;
	private $query_theme_select_hash;
	private $query_theme_select_namesanitized;
	private $query_theme_select_recent;
	private $query_theme_select_olderthan;
	private $query_theme_select_zipfilename;
	
       public function __construct()
	{
		try {
			$this->db = new \PDO('mysql:host='.DB_HOST.';dbname='.DB_DATABASE, DB_USER, DB_PWD);
                        
			$this->query_theme_insert = $this->db->prepare('INSERT INTO theme (hash, hash_md5, 
                            hash_sha1, name, namesanitized, uriNameSeo, uriNameSeoHigherVersion, themedir, themetype, parentId, cmsVersion, score, 
                            criticalCount, warningsCount, zipfilename, zipmimetype, zipfilesize, userIp, 
                            author, description, descriptionBB, themeUri, version, authorUri, authorMail, 
                            tags, layout, license, licenseUri, filesIncluded, copyright, isThemeForest, 
                            isTemplateMonster, isCreativeMarket, isPiqpaq, isNsfw, creationDate, 
                            modificationDate, validationDate,isOpenSource) VALUES (:hash, UNHEX(:hash_md5), 
                            UNHEX(:hash_sha1), :name, :namesanitized, :uriNameSeo, :uriNameSeoHigherVersion, :themedir, :themetype, :parentId, :cmsVersion,
                            :score,:criticalCount,:warningsCount,:zipfilename,:zipmimetype,:zipfilesize,
                            INET_ATON(:userIp),:author,:description,:descriptionBB,:themeUri,:version,:authorUri,
                            :authorMail,:tags,:layout,:license,:licenseUri,:filesIncluded,:copyright,:isThemeForest,
                            :isTemplateMonster, :isCreativeMarket, :isPiqpaq, :isNsfw,  FROM_UNIXTIME(:creationDate),
                            FROM_UNIXTIME(:modificationDate), FROM_UNIXTIME(:validationDate),:isOpenSource)');
			
			$this->query_theme_update_score = $this->db->prepare('UPDATE theme SET themeUri=:themeUri, uriNameSeo=:uriNameSeo, uriNameSeoHigherVersion=:uriNameSeoHigherVersion,'
                                . 'themedir=:themedir, authorUri=:authorUri, licenseUri=:licenseUri, score=:score, criticalCount=:criticalCount, warningsCount=:warningsCount,'
                                . 'layout=:layout, cmsVersion=:cmsVersion, isThemeForest=:isThemeForest, '
                                . 'isTemplateMonster=:isTemplateMonster, isCreativeMarket=:isCreativeMarket, isPiqpaq=:isPiqpaq,'
                                . ' isNsfw=:isNsfw, validationDate=FROM_UNIXTIME(:validationDate), modificationDate=FROM_UNIXTIME(:modificationDate), description=:description, '
                                . 'descriptionBB=:descriptionBB,isOpenSource=:isOpenSource WHERE id = :id');
		
			$this->query_theme_select_hash = $this->db->prepare('SELECT *,INET_NTOA(userIp) as userIp, HEX(hash_md5) as hash_md5, HEX(hash_sha1) as hash_sha1, UNIX_TIMESTAMP(creationDate) as creationDate, UNIX_TIMESTAMP(modificationDate) as modificationDate, UNIX_TIMESTAMP(validationDate) as validationDate from theme where hash = :hash');
			$this->query_theme_select_id = $this->db->prepare('SELECT *,INET_NTOA(userIp) as userIp, HEX(hash_md5) as hash_md5, HEX(hash_sha1) as hash_sha1, UNIX_TIMESTAMP(creationDate) as creationDate, UNIX_TIMESTAMP(modificationDate) as modificationDate, UNIX_TIMESTAMP(validationDate) as validationDate from theme where id = :id');
			
			//$this->query_theme_select_recent = $this->db->prepare('SELECT *,INET_NTOA(userIp) as userIp,HEX(hash_md5) as hash_md5, HEX(hash_sha1) as hash_sha1, UNIX_TIMESTAMP(creationDate) as creationDate, UNIX_TIMESTAMP(modificationDate) as modificationDate, UNIX_TIMESTAMP(validationDate) as validationDate from theme ORDER BY id DESC limit 0,100');
			$this->query_theme_select_recent = $this->db->prepare('SELECT *,INET_NTOA(userIp) as userIp,HEX(hash_md5) as hash_md5, HEX(hash_sha1) as hash_sha1, UNIX_TIMESTAMP(creationDate) as creationDate, UNIX_TIMESTAMP(modificationDate) as modificationDate, UNIX_TIMESTAMP(validationDate) as validationDate from theme ORDER BY modificationDate DESC limit 0,100');
			$this->query_theme_select_sorted = $this->db->prepare('SELECT *,INET_NTOA(userIp) as userIp,HEX(hash_md5) as hash_md5, HEX(hash_sha1) as hash_sha1, UNIX_TIMESTAMP(creationDate) as creationDate, UNIX_TIMESTAMP(modificationDate) as modificationDate, UNIX_TIMESTAMP(validationDate) as validationDate from theme WHERE themetype IN (:type) ORDER BY :sort DESC limit 0,100');
			$this->query_theme_select_olderthan = $this->db->prepare('SELECT *,INET_NTOA(userIp) as userIp,HEX(hash_md5) as hash_md5, HEX(hash_sha1) as hash_sha1, UNIX_TIMESTAMP(creationDate) as creationDate, UNIX_TIMESTAMP(modificationDate) as modificationDate, UNIX_TIMESTAMP(validationDate) as validationDate from theme WHERE id < :olderthan ORDER BY id DESC limit 0,100');
			$this->query_theme_select_olderthan_sorted = $this->db->prepare('SELECT *,INET_NTOA(userIp) as userIp,HEX(hash_md5) as hash_md5, HEX(hash_sha1) as hash_sha1, UNIX_TIMESTAMP(creationDate) as creationDate, UNIX_TIMESTAMP(modificationDate) as modificationDate, UNIX_TIMESTAMP(validationDate) as validationDate from theme WHERE :olderthan AND themetype IN (:type) ORDER BY :sort DESC');
			$this->query_theme_select_zipfilename = $this->db->prepare('SELECT id from theme WHERE zipfilename=:zipfilename');

			$this->query_vulndb_select_vuln = $this->db->prepare('SELECT * FROM wpvulndb_vulnerabilities WHERE id=:id');
			$this->query_vulndb_insert_vuln = $this->db->prepare('INSERT INTO wpvulndb_vulnerabilities (id, title, created_at, updated_at, published_date, vuln_type, fixed_in, refs) VALUES (:id, :title, :created_at, :updated_at, :published_date, :vuln_type, :fixed_in, :references)');
			$this->query_vulndb_update_vuln = $this->db->prepare('UPDATE wpvulndb_vulnerabilities SET title=:title, created_at=:created_at, updated_at=:updated_at, published_date=:published_date, published_date=:published_date, vuln_type=:vuln_type, fixed_in=:fixed_in, refs=:references WHERE id=:id');
		
			$this->query_theme_vulndb_select = $this->db->prepare('SELECT * FROM theme_wpvulnd WHERE theme_hash=:theme_hash');
			$this->query_theme_vulndb_insert = $this->db->prepare('INSERT INTO theme_wpvulnd (theme_hash, vuln_id) VALUES (:theme_hash, :vuln_id)');
		
		} catch (PDOException $e) {
			trigger_error(sprintf(__("DB Connexion error : %s"), $e->getMessage()), E_USER_ERROR);
		}
	}
	
	function __destruct() {
      unset ($this->db);
	}
		
	public function getUniqueUriNameSeo($name, $version)
	{
		$sanitizedN = ThemeInfo::sanitizedString($name);
		$sanitizedV = ThemeInfo::sanitizedString($version);
		$sanitizedV = str_replace("-", "_", $sanitizedV);
		$sanitizedV = str_replace("v", "", $sanitizedV);
		$sanitized_orig = $sanitizedN.'-v'.$sanitizedV;
		$i = 0;
		$j = '';
		do {
			$sanitized = $sanitized_orig.$j;
			$q = $this->db->query('SELECT count(*) from theme where uriNameSeo = '.$this->db->quote($sanitized));
			$row = $q->fetch();
			if ($i == 0) $i = 1; else $i++;
			$j = '('.$i.')';
		} while (intval($row[0]) > 0);
		return $sanitized;
	}
	
	public function getUniqueUriNameSeoHigherVersion($name, $themedir)
	{
		$sanitized_orig = ThemeInfo::sanitizedString($name);
		$i = 0;
		$j = '';
		do {
			$sanitized = $sanitized_orig.$j;
			$q = $this->db->query('SELECT themedir from theme where uriNameSeoHigherVersion = '.$this->db->quote($sanitized));
			$rows = $q->fetchAll();
			if (empty($rows)) return $sanitized;
			if (count($rows)==0) return $sanitized;
			foreach ($rows as $row)
			{
				if ($row["themedir"] == $themedir) return $sanitized;
			}
			if ($i == 0) $i = 1; else $i++;
			$j = '('.$i.')';
		} while (true);
	}
	
	public function saveTheme($themeInfo, $update = false)
	{
		$q = $this->db->query('SELECT id from theme where hash = '.$this->db->quote($themeInfo->hash));
		$row = $q->fetch();
		
		// force wordpress version to last known version
		if (intval($themeInfo->themetype) == TT_WORDPRESS || intval($themeInfo->themetype) == TT_WORDPRESS_CHILD) $themeInfo->cmsVersion = LAST_WP_VERSION;
		
		if ($row !== false)
		{
			if ($update)
			{
				$id = intval($row[0]);
				$this->query_theme_update_score->bindValue(':licenseUri', 		$themeInfo->licenseUri, \PDO::PARAM_STR);
				$this->query_theme_update_score->bindValue(':uriNameSeo',   	$themeInfo->uriNameSeo, \PDO::PARAM_STR);
				$this->query_theme_update_score->bindValue(':uriNameSeoHigherVersion',   	$themeInfo->uriNameSeoHigherVersion, \PDO::PARAM_STR);
				$this->query_theme_update_score->bindValue(':themeUri', 		$themeInfo->themeUri, \PDO::PARAM_STR);
				$this->query_theme_update_score->bindValue(':authorUri', 		$themeInfo->authorUri, \PDO::PARAM_STR);
				$this->query_theme_update_score->bindValue(':themedir', 		$themeInfo->themedir, \PDO::PARAM_STR);
				$this->query_theme_update_score->bindValue(':score', 			$themeInfo->score, \PDO::PARAM_STR);
				$this->query_theme_update_score->bindValue(':criticalCount', 	$themeInfo->criticalCount, \PDO::PARAM_STR);
				$this->query_theme_update_score->bindValue(':cmsVersion', 	 	$themeInfo->cmsVersion, \PDO::PARAM_STR);
				$this->query_theme_update_score->bindValue(':warningsCount', 	$themeInfo->warningsCount, \PDO::PARAM_STR);
				$this->query_theme_update_score->bindValue(':description', 	 	$themeInfo->description, \PDO::PARAM_STR);
				$this->query_theme_update_score->bindValue(':descriptionBB', 	$themeInfo->descriptionBB, \PDO::PARAM_STR);
				$this->query_theme_update_score->bindValue(':layout', 			$themeInfo->layout, \PDO::PARAM_INT);
				$this->query_theme_update_score->bindValue(':isThemeForest', 	$themeInfo->isThemeForest, \PDO::PARAM_BOOL);
				$this->query_theme_update_score->bindValue(':isTemplateMonster',$themeInfo->isTemplateMonster, \PDO::PARAM_BOOL);
				$this->query_theme_update_score->bindValue(':isCreativeMarket', $themeInfo->isCreativeMarket, \PDO::PARAM_BOOL);
				$this->query_theme_update_score->bindValue(':isPiqpaq', 		$themeInfo->isPiqpaq, \PDO::PARAM_BOOL);
				$this->query_theme_update_score->bindValue(':isNsfw', 			$themeInfo->isNsfw, \PDO::PARAM_BOOL);
				$this->query_theme_update_score->bindValue(':modificationDate', $themeInfo->modificationDate, \PDO::PARAM_INT);
				$this->query_theme_update_score->bindValue(':validationDate', 	$themeInfo->validationDate, \PDO::PARAM_INT);
				$this->query_theme_update_score->bindValue(':isOpenSource', 	$themeInfo->isOpenSource, \PDO::PARAM_BOOL);
				
				$this->query_theme_update_score->bindValue(':id', $id, \PDO::PARAM_INT);
				$r = $this->query_theme_update_score->execute();
				if ($r===FALSE && TC_ENVIRONMENT !== 'prod')
				{
					$e = $this->query_theme_update_score->errorInfo();
					trigger_error(sprintf(__("DB error : %s"), $e[2]), E_USER_ERROR);
				}
			} 
			
			$userMessage = UserMessage::getInstance();
			$userMessage->enqueueMessage(__('This theme has already been submitted.'), ERRORLEVEL_INFO);
			
			return;
		}

		// If themedir already exists
		/*$q = $this->db->query('SELECT id,score,hash,name,namesanitized,uriNameSeo, INET_NTOA(userIp) as userIp from theme where themedir = '.$this->db->quote($themeInfo->themedir));
		$row = $q->fetch();
		if (!empty($row))
		{
			$existing_hash = $row['hash'];
			$existing_ip = $row['userIp'];
			$existing_score = $row['score'];
			$themeInfo->serializable = true;
			$userMessage = UserMessage::getInstance();
			if ($existing_hash == $themeInfo->hash){
				$userMessage->enqueueMessage(__('This theme has already been submitted.'), ERRORLEVEL_INFO);
			} else {
				//if (intval($existing_score) < intval($themeInfo->score) || $existing_ip == $themeInfo->userIp) // ip match : consider the theme is a new version of the existing one. ip is the only information we can rely on to be sure the user is the same. Any other data such as author name is easily hackable and mean people could use this to overwrite existing themes.
				{
						//$userMessage->enqueueMessage(__('It seems this archive is a new version of the theme "'.htmlspecialchars($themeInfo->name).'" you submitted previously. Validation results were updated.'), ERRORLEVEL_INFO);
						
						$this->query_theme_update_all = $this->db->prepare('UPDATE theme SET hash=:hash,
																							 hash_md5=:hash_md5,
																							 hash_sha1=:hash_sha1,
																							 name=:name,
																							 namesanitized=:namesanitized,
																							 uriNameSeo=:uriNameSeo,
																							 themedir=:themedir,
																							 themetype=:themetype,
																							 parentId=:parentId,
																							 cmsVersion=:cmsVersion,
																							 score=:score,
																							 criticalCount=:criticalCount,
																							 warningsCount=:warningsCount,
																							 zipfilename=:zipfilename,
																							 zipmimetype=:zipmimetype,
																							 zipfilesize=:zipfilesize,
																							 userIp=:userIp,
																							 author=:author,
																							 description=:description,
																							 descriptionBB=:descriptionBB,
																							 themeUri=:themeUri,
																							 version=:version,
																							 authorUri=:authorUri,
																							 authorMail=:authorMail,
																							 tags=:tags,
																							 layout=:layout,
																							 license=:license,
																							 licenseUri=:licenseUri,
																							 filesIncluded=:filesIncluded,
																							 copyright=:copyright,
																							 isThemeForest=:isThemeForest,
																							 isTemplateMonster=:isTemplateMonster,
																							 isCreativeMarket=:isCreativeMarket,
																							 isPiqpaq=:isPiqpaq,
																							 isNsfw=:isNsfw,
																							 creationDate=:creationDate,
																							 modificationDate=:modificationDate,
																							 validationDate=:validationDate,
																							 isOpenSource=:isOpenSource WHERE id=:id');
																																					 
						$id = intval($row['id']);
						// update values in DB
						$themeInfo->namesanitized = $row['namesanitized']; // can't call getUniqueUriNameSeo because it would generate a new unique name, we need the same as before because namesanitized is used to build url and we do'nt want to change url
						$themeInfo->uriNameSeo = $row['uriNameSeo'];
						$this->query_theme_update_all->bindValue(':id', $id, \PDO::PARAM_INT);
						$this->query_theme_update_all->bindValue(':hash', $themeInfo->hash, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':hash_md5', $themeInfo->hash_md5, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':hash_sha1', $themeInfo->hash_sha1, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':name', $themeInfo->name, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':namesanitized', $themeInfo->namesanitized, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':uriNameSeo', $themeInfo->uriNameSeo, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':themedir', $themeInfo->themedir, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':themetype', $themeInfo->themetype, \PDO::PARAM_INT);
						$this->query_theme_update_all->bindValue(':parentId', $themeInfo->parentId, \PDO::PARAM_INT);
						$this->query_theme_update_all->bindValue(':cmsVersion', $themeInfo->cmsVersion, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':score', $themeInfo->score, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':criticalCount', $themeInfo->criticalCount, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':warningsCount', $themeInfo->warningsCount, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':zipfilename', $themeInfo->zipfilename, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':zipmimetype', $themeInfo->zipmimetype, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':zipfilesize', $themeInfo->zipfilesize, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':userIp', $themeInfo->userIp, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':author', $themeInfo->author, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':description', $themeInfo->description, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':descriptionBB', $themeInfo->descriptionBB, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':themeUri', $themeInfo->themeUri, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':version', $themeInfo->version, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':authorUri', $themeInfo->authorUri, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':authorMail', $themeInfo->authorMail, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':tags', $themeInfo->tags, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':layout', $themeInfo->layout, \PDO::PARAM_INT);
						$this->query_theme_update_all->bindValue(':license', $themeInfo->license, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':licenseUri', $themeInfo->licenseUri, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':filesIncluded', $themeInfo->filesIncluded, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':copyright', $themeInfo->copyright, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':isThemeForest', $themeInfo->isThemeForest, \PDO::PARAM_BOOL);
						$this->query_theme_update_all->bindValue(':isTemplateMonster', $themeInfo->isTemplateMonster, \PDO::PARAM_BOOL);
						$this->query_theme_update_all->bindValue(':isCreativeMarket', $themeInfo->isCreativeMarket, \PDO::PARAM_BOOL);
						$this->query_theme_update_all->bindValue(':isPiqpaq', $themeInfo->isPiqpaq, \PDO::PARAM_BOOL);
						$this->query_theme_update_all->bindValue(':isNsfw', $themeInfo->isNsfw, \PDO::PARAM_BOOL);
						$this->query_theme_update_all->bindValue(':creationDate', $themeInfo->creationDate, \PDO::PARAM_INT);
						$this->query_theme_update_all->bindValue(':modificationDate', $themeInfo->creationDate, \PDO::PARAM_INT);
						$this->query_theme_update_all->bindValue(':validationDate', $themeInfo->creationDate, \PDO::PARAM_INT);
                        $this->query_theme_update_all->bindValue(':isOpenSource', $themeInfo->isOpenSource, \PDO::PARAM_BOOL);
						$r = $this->query_theme_update_all->execute();
						if ($r===FALSE && TC_ENVIRONMENT !== 'prod')
						{
							$e = $this->query_theme_update_all->errorInfo();
							trigger_error(sprintf(__("DB error : %s"), $e[2]), E_USER_ERROR);
						}
						
						// recalculate highest version among all themes with the same $themedir
						$this->findHigherVersion($themedir);
				
						return ;
				} else { // ip doesn't match : this is another theme with a duplicate name. Flag it as not serializable and display a message to user. (It would have been possible to generate a new name, but it would make it almost impossible to follow new versions afterwards)
					$themeInfo->serializable = false;
					$userMessage->enqueueMessage(__('The name "'.htmlspecialchars($themeInfo->name).'" of this theme is already used by a theme that was previously submitted. The results of this validation could not be saved.'), ERRORLEVEL_WARNING);
					return;
				}
			}
		}
		*/
		
		// If themedir already exists
		$q = $this->db->query('SELECT id,score,hash,name,namesanitized,uriNameSeo, INET_NTOA(userIp) as userIp from theme where themedir = '.$this->db->quote($themeInfo->themedir).' AND version = '.$this->db->quote($themeInfo->version));
		$row = $q->fetch();
		if (!empty($row))
		{
			$themeInfo->serializable = false;
			$userMessage = UserMessage::getInstance();
			$userMessage->enqueueMessage(__('Directory "'.htmlspecialchars($themeInfo->themedir).'" and version "'.htmlspecialchars($themeInfo->version).'" of this theme match a theme that was previously submitted. The results of this validation could not be saved.'), ERRORLEVEL_WARNING);
			$userMessage->enqueueMessage(__('If you are an author in the process of debugging your theme, keep this version number to resubmit your modified archives until you are satisfied. Then increase the version and submit your archive one last time. Note : on themecheck.org, only the highest version of each theme is marked as indexable by search engines.'), ERRORLEVEL_WARNING);
			return;
		}
		
		// generate sanitized name
		$themeInfo->namesanitized = "";// not used anymore
		$themeInfo->uriNameSeo = $this->getUniqueUriNameSeo($themeInfo->name, $themeInfo->version);
		$themeInfo->uriNameSeoHigherVersion = $this->getUniqueUriNameSeoHigherVersion($themeInfo->name, $themeInfo->themedir);
		
		// save to DB
		$this->query_theme_insert->bindValue(':hash', $themeInfo->hash, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':hash_md5', $themeInfo->hash_md5, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':hash_sha1', $themeInfo->hash_sha1, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':name', $themeInfo->name, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':namesanitized', $themeInfo->namesanitized, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':uriNameSeo', $themeInfo->uriNameSeo, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':uriNameSeoHigherVersion', $themeInfo->uriNameSeoHigherVersion, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':themedir', $themeInfo->themedir, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':themetype', $themeInfo->themetype, \PDO::PARAM_INT);
		$this->query_theme_insert->bindValue(':parentId', $themeInfo->parentId, \PDO::PARAM_INT);
		$this->query_theme_insert->bindValue(':cmsVersion', $themeInfo->cmsVersion, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':score', $themeInfo->score, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':criticalCount', $themeInfo->criticalCount, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':warningsCount', $themeInfo->warningsCount, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':zipfilename', $themeInfo->zipfilename, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':zipmimetype', $themeInfo->zipmimetype, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':zipfilesize', $themeInfo->zipfilesize, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':userIp', $themeInfo->userIp, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':author', $themeInfo->author, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':description', $themeInfo->description, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':descriptionBB', $themeInfo->descriptionBB, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':themeUri', $themeInfo->themeUri, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':version', $themeInfo->version, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':authorUri', $themeInfo->authorUri, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':authorMail', $themeInfo->authorMail, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':tags', $themeInfo->tags, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':layout', $themeInfo->layout, \PDO::PARAM_INT);
		$this->query_theme_insert->bindValue(':license', $themeInfo->license, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':licenseUri', $themeInfo->licenseUri, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':filesIncluded', $themeInfo->filesIncluded, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':copyright', $themeInfo->copyright, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':isThemeForest', $themeInfo->isThemeForest, \PDO::PARAM_BOOL);
		$this->query_theme_insert->bindValue(':isTemplateMonster', $themeInfo->isTemplateMonster, \PDO::PARAM_BOOL);
		$this->query_theme_insert->bindValue(':isCreativeMarket', $themeInfo->isCreativeMarket, \PDO::PARAM_BOOL);
		$this->query_theme_insert->bindValue(':isPiqpaq', $themeInfo->isPiqpaq, \PDO::PARAM_BOOL);
		$this->query_theme_insert->bindValue(':isNsfw', $themeInfo->isNsfw, \PDO::PARAM_BOOL);
		$this->query_theme_insert->bindValue(':creationDate', $themeInfo->creationDate, \PDO::PARAM_INT);
		$this->query_theme_insert->bindValue(':modificationDate', $themeInfo->creationDate, \PDO::PARAM_INT);
		$this->query_theme_insert->bindValue(':validationDate', $themeInfo->creationDate, \PDO::PARAM_INT);
		$this->query_theme_insert->bindValue(':isOpenSource', $themeInfo->isOpenSource, \PDO::PARAM_BOOL);

		$r = $this->query_theme_insert->execute();
		if ($r===FALSE && TC_ENVIRONMENT !== 'prod')
		{
			$e = $this->query_theme_insert->errorInfo();
			trigger_error(sprintf(__("DB error : %s"), $e[2]), E_USER_ERROR);
		}
		
		// recalculate highest version among all themes with the same $themedir
		$this->findHigherVersion($themeInfo->themedir);
	}
	
	public function loadThemeFromHash($hash)
	{
		$this->query_theme_select_hash->bindValue(':hash', $hash, \PDO::PARAM_STR);
		$r = $this->query_theme_select_hash->execute();
		if ($r===FALSE && TC_ENVIRONMENT !== 'prod')
		{
			$e = $this->query_theme_select_hash->errorInfo();
			trigger_error(sprintf(__("DB error : %s"), $e[2]), E_USER_ERROR);
		}
		$obj = $this->query_theme_select_hash->fetchObject(); // return first row
		if (empty($obj)) return null;
		$themeInfo = new ThemeInfo($obj->hash);
		
		$themeInfo->themetype = $obj->themetype;
		$themeInfo->hash_md5 = $obj->hash_md5;
		$themeInfo->hash_sha1 = $obj->hash_sha1;
		$themeInfo->cmsVersion = $obj->cmsVersion;
		$themeInfo->themetype = $obj->themetype;
		$themeInfo->parentId = $obj->parentId;
		$themeInfo->score = $obj->score;
		$themeInfo->criticalCount = $obj->criticalCount;
		$themeInfo->warningsCount = $obj->warningsCount;
		$themeInfo->zipfilename = $obj->zipfilename;
		$themeInfo->zipmimetype = $obj->zipmimetype;
		$themeInfo->zipfilesize = $obj->zipfilesize;
		$themeInfo->userIp = $obj->userIp;
		$themeInfo->name = $obj->name;
		$themeInfo->namesanitized = $obj->namesanitized;
		$themeInfo->uriNameSeo = $obj->uriNameSeo;
		$themeInfo->uriNameSeoHigherVersion = $obj->uriNameSeoHigherVersion;
		$themeInfo->themedir = $obj->themedir;
		$themeInfo->author = $obj->author;
		$themeInfo->description = $obj->description;
		$themeInfo->descriptionBB = $obj->descriptionBB;
		$themeInfo->themeUri = $obj->themeUri;
		$themeInfo->version = $obj->version;
		$themeInfo->isHigherVersion = $obj->isHigherVersion;
		$themeInfo->authorUri = $obj->authorUri;
		$themeInfo->authorMail = $obj->authorMail;
		$themeInfo->tags = $obj->tags;
		$themeInfo->layout = $obj->layout;
		$themeInfo->license = $obj->license;
		$themeInfo->licenseUri = $obj->licenseUri;
		$themeInfo->licenseText = $obj->licenseText;
		$themeInfo->copyright = $obj->copyright;
		$themeInfo->isThemeForest = $obj->isThemeForest;
		$themeInfo->isTemplateMonster = $obj->isTemplateMonster;
		$themeInfo->isCreativeMarket = $obj->isCreativeMarket;
		$themeInfo->isPiqpaq = $obj->isPiqpaq;
		$themeInfo->isNsfw = $obj->isNsfw;		
		$themeInfo->filesIncluded = $obj->filesIncluded;
		$themeInfo->creationDate = $obj->creationDate;
		$themeInfo->modificationDate = $obj->modificationDate;
		$themeInfo->validationDate = $obj->validationDate;
		$themeInfo->isOpenSource = $obj->isOpenSource;
   
		
		try {
			$path = TC_VAULTDIR.'/upload';		
			$fullname = $path.'/'.$hash.'.zip';
			$dst = TC_ROOTDIR.'/../themecheck_vault/_no_merchant_db/'.$themeInfo->zipfilename;
			if ($themeInfo->isThemeForest) $dst = TC_ROOTDIR.'/../themecheck_vault/_e_db/'.$themeInfo->zipfilename;
			if ($themeInfo->isTemplateMonster) $dst = TC_ROOTDIR.'/../themecheck_vault/_t_db/'.$themeInfo->zipfilename;
			if ($themeInfo->isCreativeMarket) $dst = TC_ROOTDIR.'/../themecheck_vault/_c_db/'.$themeInfo->zipfilename;
			if ($themeInfo->isPiqpaq) $dst = TC_ROOTDIR.'/../themecheck_vault/_p_db/'.$themeInfo->zipfilename;

			$path_parts = pathinfo($dst);
			if (file_exists($path_parts['dirname']))
			{
				if (!file_exists($dst))	copy($fullname, $dst);
				else {
					$hashsrc = hash_file('md5', $fullname);
					$hashdst = hash_file('md5', $dst);
					if ($hashsrc != $hashdst) copy($fullname, $dst);
				}
			}
		}
		catch (Exception $e) {}
		
		return $themeInfo;
	}
	
	public function getHashFromNamesanitized($namesanitized)
	{
		$query = $this->db->prepare('SELECT hash from theme where namesanitized = :namesanitized');
		
		$query->bindValue(':namesanitized', $namesanitized, \PDO::PARAM_STR);
		$r = $query->execute();
		if ($r===FALSE && TC_ENVIRONMENT !== 'prod')
		{
			$e = $query->errorInfo();
			trigger_error(sprintf(__("DB error : %s"), $e[2]), E_USER_ERROR);
		}
		$row = $query->fetch(); // return first row
		return $row[0];
	}
	
	public function getHashFromUriNameSeo($uriNameSeo)
	{
		$query = $this->db->prepare('SELECT hash from theme where uriNameSeo = :uriNameSeo');
		
		$query->bindValue(':uriNameSeo', $uriNameSeo, \PDO::PARAM_STR);
		$r = $query->execute();
		if ($r===FALSE && TC_ENVIRONMENT !== 'prod')
		{
			$e = $query->errorInfo();
			trigger_error(sprintf(__("DB error : %s"), $e[2]), E_USER_ERROR);
		}
		$row = $query->fetch(); // return first row
		return $row[0];
	}
	
	public function getHashFromUriNameSeoHigherVersion($uriNameSeoHigherVersion)
	{
		$query = $this->db->prepare('SELECT hash from theme where uriNameSeoHigherVersion = :uriNameSeoHigherVersion && isHigherVersion = 1');
		
		$query->bindValue(':uriNameSeoHigherVersion', $uriNameSeoHigherVersion, \PDO::PARAM_STR);
		$r = $query->execute();
		if ($r===FALSE && TC_ENVIRONMENT !== 'prod')
		{
			$e = $query->errorInfo();
			trigger_error(sprintf(__("DB error : %s"), $e[2]), E_USER_ERROR);
		}
		$row = $query->fetch(); // return first row
		return $row[0];
	}
	
	public function generateUriNameSeoInDb($hash)
	{
		$q = $this->db->query('SELECT id,name,version,uriNameSeo,uriNameSeoHigherVersion,themedir from theme where hash = '.$this->db->quote($hash));
		$row = $q->fetch();
		
		$uriNameSeo = $row["uriNameSeo"];
		$uriNameSeoHigherVersion = $row["uriNameSeoHigherVersion"];
		
		if (!empty($uriNameSeo) && !empty($uriNameSeoHigherVersion)) return;
		
		if (empty($uriNameSeo))
			$uriNameSeo = $this->getUniqueUriNameSeo($row["name"], $row["version"]);
		
		if (empty($uriNameSeoHigherVersion))
			$uriNameSeoHigherVersion = $this->getUniqueUriNameSeoHigherVersion($row["name"], $row["themedir"]);
		
		$query = $this->db->prepare("UPDATE theme SET uriNameSeo=:uriNameSeo, uriNameSeoHigherVersion=:uriNameSeoHigherVersion WHERE hash=:hash");
		$query->bindValue(':uriNameSeo',$uriNameSeo,\PDO::PARAM_STR);
		$query->bindValue(':uriNameSeoHigherVersion',$uriNameSeoHigherVersion,\PDO::PARAM_STR);
		$query->bindValue(':hash',$hash,\PDO::PARAM_STR);
		$query->execute();
	}
	
	public function getRecent($olderthan = null)
	{
		if (empty($olderthan))
		{
			$this->query_theme_select_recent->execute();
			$ret = array();
			while ($row = $this->query_theme_select_recent->fetch(\PDO::FETCH_ASSOC)) {
				$ret[]=$row; 
			}
		} else {
			// get theme info of id = $olderthan 
			$query = $this->db->prepare('SELECT UNIX_TIMESTAMP(modificationDate) as modificationDate from theme WHERE id = :id');
			$query->bindValue(':id', $olderthan, \PDO::PARAM_INT);
			$query->execute();
			$lastItem = $query->fetch(\PDO::FETCH_ASSOC);
			
			$queryString = $this->query_theme_select_olderthan->queryString;
			$queryString = str_replace('id < :olderthan', 'modificationDate < FROM_UNIXTIME(:olderthanModificationDate)', $queryString);
			$query = $this->db->prepare($queryString);
			$query->bindValue(':olderthanModificationDate', $lastItem['modificationDate'], \PDO::PARAM_INT);
			$query->execute();
			$ret = array();
			while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
				$ret[]=$row; 
			}
		}
		return $ret;
	}
	
	public function getSorted($sort, $type, $olderthan = null)
	{
		$themeType = array("wordpress"=>1, "joomla"=>2, "wordpress_child"=>4);
		
		if(null==$olderthan)
		{
			$queryString = $this->query_theme_select_sorted->queryString;
		}
		else
		{
			$olderthan_quot = $this->db->quote($olderthan, \PDO::PARAM_INT);
			$queryString = $this->query_theme_select_olderthan_sorted->queryString;
						
			if($sort=="score")
			{
				$query = $this->db->prepare('SELECT score from theme WHERE id = :id');
				$query->bindValue(':id', $olderthan, \PDO::PARAM_INT);
				$query->execute();
				$lastItem = $query->fetch(\PDO::FETCH_ASSOC);				
				$queryString = str_replace(':olderthan', 'score <= '.$lastItem['score'], $queryString);
			}
			else if($sort=="modificationDate")
			{
				// get theme info of id = $olderthan 
				$query = $this->db->prepare('SELECT UNIX_TIMESTAMP(modificationDate) as modificationDate from theme WHERE id = :id');
				$query->bindValue(':id', $olderthan, \PDO::PARAM_INT);
				$query->execute();
				$lastItem = $query->fetch(\PDO::FETCH_ASSOC);
			
				$queryString = str_replace(':olderthan', 'modificationDate < FROM_UNIXTIME('.$lastItem['modificationDate'].')', $queryString);
			} else { // $sort=="id" and others
				$queryString = str_replace(':olderthan', 'id < '.$olderthan_quot, $queryString);
			}
		}
	
		// type
		$r = array();
		foreach ($type as $v)
		{
			if(array_key_exists($v, $themeType)){
				$r[]= $this->db->quote($themeType[$v]);
				if ($v == "wordpress") // when asking wordpress, display child too
				{
					$r[]= $this->db->quote($themeType["wordpress_child"]);
				}
			}
		}
		$type = implode(",", $r);

		$queryString = str_replace(':type', $type, $queryString);
		
		// order by
		if ($sort == "modificationDate") $queryString = str_replace(':sort', 'modificationDate', $queryString);
		else if ($sort == "score") $queryString = str_replace(':sort', 'score DESC, modificationDate', $queryString);
		else $queryString = str_replace(':sort', 'id', $queryString);
		
		$query = $this->db->prepare($queryString);
		$query->execute();
		
		$ret = array();
		$trouve = false;
		$i = 0;
		while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
			if ($sort == "score" && $olderthan !== null) { // only keep themes with the same score than $olderthan and that come AFTER $olderthan
				if ($trouve) {
					$ret[] = $row;
					$i ++;
				}
				if ($row['id'] == $olderthan) $trouve = true;
			} else {
				$ret[]=$row; 
				$i ++;
			}
			if ($i>= 100) break;
		}
		return $ret;
	}
	 
	public function getAll()
	{
		$q = $this->db->prepare('SELECT id, hash, name, themetype from theme ORDER BY id DESC');
		$q->execute();
		
		$ret = array();
		while ($row = $q->fetch(\PDO::FETCH_ASSOC)) {
			$ret[] = $row; 
		}
		return $ret;
	}
	
	// only to be used in admin tasks
	public function getIdFromZipName($zipfilename)
	{
		$this->query_theme_select_zipfilename->bindValue(':zipfilename', $zipfilename, \PDO::PARAM_STR);
		$this->query_theme_select_zipfilename->execute();
		$r = $this->query_theme_select_zipfilename->fetch();
		return $r;
	}
	
	public function getIdFromHash($hash)
	{		
		$query = $this->db->prepare('SELECT id from theme WHERE hash=:hash');
		$query->bindValue(':hash', $hash, \PDO::PARAM_STR);
		$query->execute();
		$r = $query->fetch();
		if (isset($r['id'])) return $r['id'];
		return null;
	}
	
	public function getFewInfo($id)
	{
		$this->query_theme_select_id->bindValue(':id', $id, \PDO::PARAM_INT);
		$this->query_theme_select_id->execute();
		$r = $this->query_theme_select_id->fetch();
		if (!empty($r["parentId"])) {
			$rParent = $this->getFewInfo(intval($r["parentId"]));
			$r["parentName"] = $rParent["name"];
		}
		
		return $r;
	}
	
	public function getFewInfoPreviousOne($id)
	{
		$previous_id = $id;
		for ($i =0; $i < 10; $i++) // loop until we find 1
		{
			$previous_id--;
			if ($previous_id == 0) return false;
			$this->query_theme_select_id->bindValue(':id', $previous_id, \PDO::PARAM_INT);
			$this->query_theme_select_id->execute();
			$r = $this->query_theme_select_id->fetch();
			if (!empty($r["parentId"])) {
				$rParent = $this->getFewInfo(intval($r["parentId"]));
				$r["parentName"] = $rParent["name"];
			}
			
			if (!empty($r["id"])) break;
		}

		return $r;
	}
	public function getInfoFromId($id)
	{
		$this->query_theme_select_id->bindValue(':id', $id, \PDO::PARAM_INT);
		$this->query_theme_select_id->execute();
		$r = $this->query_theme_select_id->fetch(\PDO::FETCH_ASSOC);
		if (!empty($r["parentId"])) {
			$rParent = $this->getFewInfo(intval($r["parentId"]));
			$r["parentName"] = $rParent["name"];
		}
		$zip_dir = TC_VAULTDIR.'/upload/';
		$unzip_dir = TC_VAULTDIR.'/unzip/';
		$r["webimagepath"] = TC_HTTPDOMAIN.'/'.$r['hash'].'/thumbnail.png';
		$r["zippath"] = $zip_dir.$r['hash'].'.zip';
		$r["unzippath"] = $unzip_dir.$r["hash"];

		// search root path
		$files = listdir( $r["unzippath"] );
		$index_php = null;
		foreach( $files as $key => $filename ) 
		{
			$path_parts = pathinfo($filename);
			$basename = $path_parts['basename'];
			
			if ($basename == 'index.php')
			{
				if (empty($index_php) || strlen($filename) < strlen($index_php)) {
					$index_php = $filename;
					$r["unzippath"] = realpath($path_parts['dirname']);
				}
			}
		}
		
		// snapshots and thumbs
		$imagefiles = array();
		if ($handle = opendir($r["unzippath"]))
		{
			while (false !== ($file = readdir($handle)))
			{
				$path_parts = pathinfo($r["unzippath"].'/'.$file);
				if (isset($path_parts['extension']) && in_array(strtolower($path_parts['extension']), ['png','jpg','gif']))
				{
					$imginfo = getimagesize($r["unzippath"].'/'.$file);
					if ($imginfo !== false && $imginfo[0] >= 64 && $imginfo[1] >= 64) // check we have an actual image and it is large enough
					{
						$imagefiles[] = $r["unzippath"].'/'.$file;
					}
				}
			}
			closedir($handle);
		}			
			
		$r["images"] = $imagefiles;
			
		return $r;
	}
	
	public function getFewInfoFromName($name)
	{
		$query = $this->db->prepare('SELECT id, hash, themetype, '
                        . 'namesanitized, uriNameSeo, uriNameSeoHigherVersion, isHigherVersion from theme WHERE name=:name');
		$query->bindValue(':name', $name, \PDO::PARAM_STR);
		$query->execute();
		$r = $query->fetch();
		if (isset($r['id'])) return $r;
		return null;
	}
	
	public function getNextId($id)
	{
		$_id = intval($id);
		$query = $this->db->prepare('SELECT id from theme WHERE id > :id ORDER BY id LIMIT 1');
		$query->bindValue(':id', $_id, \PDO::PARAM_INT);
		$query->execute();
		$r = $query->fetch();
		if (isset($r['id'])) return intval($r['id']);
		return null;
	}
	
	public function getPrevId($id)
	{
		$_id = intval($id);
		$query = $this->db->prepare('SELECT id from theme WHERE id < :id ORDER BY id DESC LIMIT 1');
		$query->bindValue(':id', $_id, \PDO::PARAM_INT);
		$query->execute();
		$r = $query->fetch();
		if (isset($r['id'])) return intval($r['id']);
		return null;
	}
	
	public function getMaxId()
	{
		$query = $this->db->prepare('SELECT MAX(id) from theme');
		$query->execute();
		$r = $query->fetch();
		if (isset($r[0])) return intval($r[0]);
		return null;
	}
	
	public function getEligibleItems()
	{
		$query = $this->db->prepare('SELECT *,INET_NTOA(userIp) as userIp,HEX(hash_md5) as hash_md5, HEX(hash_sha1) as hash_sha1, UNIX_TIMESTAMP(creationDate) as creationDate, UNIX_TIMESTAMP(modificationDate) as modificationDate, UNIX_TIMESTAMP(validationDate) as validationDate from theme WHERE score >= 100 AND (themetype = 1 OR themetype = 2) AND isThemeForest = 0 AND isTemplateMonster = 0 AND isCreativeMarket = 0 ORDER BY id DESC');
		$query->execute();
		$ret = array();
		$zip_dir = TC_VAULTDIR.'/upload/';		
		$unzip_dir = TC_VAULTDIR.'/unzip/';
		while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
			$nodb_unzippath = "";
			
			$row["webimagepath"] = TC_HTTPDOMAIN.'/'.$row['hash'].'/thumbnail.png';
			$row["zippath"] = $zip_dir.$row['hash'].'.zip';
			$row["unzippath"] = $unzip_dir.$row["hash"];
			
			$ret[] = $row; 
		}
		
		return $ret;
	}
     
	public function getNameIsOpenSource()
	{
		$query = $this->db->query("SELECT themedir,zipfilename,license,themetype"
				. "FROM theme WHERE ISNULL(isOpenSource)");
		$query->execute();
		$datas = array();
		while($row = $query->fetch())
		{
			$datas[] = $row; 
		}
	   
		return $datas;
	}
	
	public function updateIsOpenSource($value,$themedir)
	{
		$query = $this->db->prepare("UPDATE theme SET isOpenSource=:isOpenSource WHERE themedir=:themedir");
		$query->bindValue(':isOpenSource',$value,\PDO::PARAM_BOOL);
		$query->bindValue(':themedir',$themedir,\PDO::PARAM_STR);
		$query->execute();
	}
	
	public function getNumberOfTheme()
	{
		$query = $this->db->query("SELECT COUNT('id') FROM theme");
		$query->execute();
		$data = $query->fetch();
		
		return $data;
	}
	
	public function upsertWpVuln($theme_hash, $wpVuln)
	{
		if ($wpVuln == null) return;

		$this->query_theme_vulndb_select->bindValue(':theme_hash', $theme_hash, \PDO::PARAM_STR);
		$r= $this->query_theme_vulndb_select->execute();
		$row = $this->query_theme_vulndb_select->fetch();
		
		if (!isset($row[0]))
		{
			$this->query_theme_vulndb_insert->bindValue(':theme_hash', $theme_hash, \PDO::PARAM_STR);
			$this->query_theme_vulndb_insert->bindValue(':vuln_id', $wpVuln->id, \PDO::PARAM_INT);

			$r = $this->query_theme_vulndb_insert->execute();
			if ($r===FALSE && TC_ENVIRONMENT !== 'prod')
			{
				$e = $this->query_theme_vulndb_insert->errorInfo();
				trigger_error(sprintf(__("DB error : %s"), $e[2]), E_USER_ERROR);
			}
		}

		$references = implode(',', $wpVuln->references);
		
		$this->query_vulndb_select_vuln->bindValue(':id', $wpVuln->id, \PDO::PARAM_INT);
		$r= $this->query_vulndb_select_vuln->execute();
		$row = $this->query_vulndb_select_vuln->fetch();
		
		if (isset($row[0]))
		{	
			$this->query_vulndb_update_vuln->bindValue(':id', $wpVuln->id, \PDO::PARAM_INT);
			$this->query_vulndb_update_vuln->bindValue(':title', $wpVuln->title, \PDO::PARAM_STR);
			$this->query_vulndb_update_vuln->bindValue(':created_at', $wpVuln->created_at, \PDO::PARAM_STR);
			$this->query_vulndb_update_vuln->bindValue(':updated_at', $wpVuln->updated_at, \PDO::PARAM_STR);
			$this->query_vulndb_update_vuln->bindValue(':published_date', $wpVuln->published_date, \PDO::PARAM_STR);
			$this->query_vulndb_update_vuln->bindValue(':vuln_type', $wpVuln->vuln_type, \PDO::PARAM_STR);
			$this->query_vulndb_update_vuln->bindValue(':fixed_in', $wpVuln->fixed_in, \PDO::PARAM_STR);
			$this->query_vulndb_update_vuln->bindValue(':references', $references, \PDO::PARAM_STR);

			$r = $this->query_vulndb_update_vuln->execute();
		} else {
			$this->query_vulndb_insert_vuln->bindValue(':id', $wpVuln->id, \PDO::PARAM_INT);
			$this->query_vulndb_insert_vuln->bindValue(':title', $wpVuln->title, \PDO::PARAM_STR);
			$this->query_vulndb_insert_vuln->bindValue(':created_at', $wpVuln->created_at, \PDO::PARAM_STR);
			$this->query_vulndb_insert_vuln->bindValue(':updated_at', $wpVuln->updated_at, \PDO::PARAM_STR);
			$this->query_vulndb_insert_vuln->bindValue(':published_date', $wpVuln->published_date, \PDO::PARAM_STR);
			$this->query_vulndb_insert_vuln->bindValue(':vuln_type', $wpVuln->vuln_type, \PDO::PARAM_STR);
			$this->query_vulndb_insert_vuln->bindValue(':fixed_in', $wpVuln->fixed_in, \PDO::PARAM_STR);
			$this->query_vulndb_insert_vuln->bindValue(':references', $references, \PDO::PARAM_STR);

			$r = $this->query_vulndb_insert_vuln->execute();
			if ($r===FALSE && TC_ENVIRONMENT !== 'prod')
			{
				$e = $this->query_vulndb_insert_vuln->errorInfo();
				trigger_error(sprintf(__("DB error : %s"), $e[2]), E_USER_ERROR);
			}
		}
	}
	
	public function booom()
	{
		$query = $this->db->query('SELECT themedir from theme GROUP BY themedir');
		$query->execute();
		$rows = $query->fetchAll();
		foreach ($rows as $row)
		{
			$this->findHigherVersion($row["themedir"]);
		}
		
		$query2 = $this->db->query('SELECT hash from theme');
		$query2->execute();
		$rows2 = $query2->fetchAll();
		foreach ($rows2 as $row)
		{
			$this->generateUriNameSeoInDb($row["hash"]);
		}
	}
	
	public function findHigherVersion($themedir)
	{
		$query2 = $this->db->prepare('SELECT id, version from theme WHERE themedir = :themedir');
		$query3 = $this->db->prepare("UPDATE theme SET isHigherVersion=0 WHERE id!=:id AND themedir=:themedir");
		$query4 = $this->db->prepare("UPDATE theme SET isHigherVersion=1 WHERE id=:id");

		$query2->bindValue(':themedir', $themedir, \PDO::PARAM_STR);
		$query2->execute();
		
		$higherVersion = '';
		$higherVersionId = 0;
		$rows2 = $query2->fetchAll();

		foreach ($rows2 as $row2)
		{
			// We use strictly > 0 to force authors to increase version number when they modify their themes. 
			// This way, new archives with the same version are not considered the higher version and search engine don't 
			// index them (noindex in controller_results) so users are not be fooled when they use an old file : we preserve the worst score for a version.
			if (version_compare($row2["version"], $higherVersion) > 0) 
			{
				$higherVersion = $row2["version"];
				$higherVersionId = $row2["id"];
			}
		}
		
		if (!empty($higherVersion))
		{
			$query3->bindValue(':id',$higherVersionId,\PDO::PARAM_INT);
			$query3->bindValue(':themedir',$themedir,\PDO::PARAM_STR);
			$query3->execute();
			
			$query4->bindValue(':id',$higherVersionId,\PDO::PARAM_INT);
			$query4->execute();
		}
		
		return $higherVersionId;
	}
	
	public function getHigherVersion($themedir)
	{
		$query2 = $this->db->prepare('SELECT id, version from theme WHERE themedir = :themedir');
		$query3 = $this->db->prepare("UPDATE theme SET isHigherVersion=0 WHERE id!=:id AND themedir=:themedir");
		$query4 = $this->db->prepare("UPDATE theme SET isHigherVersion=1 WHERE id=:id");

		$query2->bindValue(':themedir', $themedir, \PDO::PARAM_STR);
		$query2->execute();
		
		$higherVersion = '';
		$rows2 = $query2->fetchAll();

		foreach ($rows2 as $row2)
		{
			// We use strictly > 0 to force authors to increase version number when they modify their themes. 
			// This way, new archives with the same version are not considered the higher version and search engine don't 
			// index them (noindex in controller_results) so users are not be fooled when they use an old file : we preserve the worst score for a version.
			if (version_compare($row2["version"], $higherVersion) > 0) 
			{
				$higherVersion = $row2["version"];
			}
		}
		
		return $higherVersion;
	}
}