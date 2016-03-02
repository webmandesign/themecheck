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
                            hash_sha1, name, namesanitized, namedemo, themetype, parentId, cmsVersion, score, 
                            criticalCount, warningsCount, zipfilename, zipmimetype, zipfilesize, userIp, 
                            author, description, descriptionBB, themeUri, version, authorUri, authorMail, 
                            tags, layout, license, licenseUri, filesIncluded, copyright, isThemeForest, 
                            isTemplateMonster, isCreativeMarket, isPiqpaq, isNsfw, creationDate, 
                            modificationDate, validationDate,isOpenSource) VALUES (:hash, UNHEX(:hash_md5), 
                            UNHEX(:hash_sha1), :name,:namesanitized,:namedemo,:themetype,:parentId, :cmsVersion,
                            :score,:criticalCount,:warningsCount,:zipfilename,:zipmimetype,:zipfilesize,
                            INET_ATON(:userIp),:author,:description,:descriptionBB,:themeUri,:version,:authorUri,
                            :authorMail,:tags,:layout,:license,:licenseUri,:filesIncluded,:copyright,:isThemeForest,
                            :isTemplateMonster, :isCreativeMarket, :isPiqpaq, :isNsfw,  FROM_UNIXTIME(:creationDate),
                            FROM_UNIXTIME(:modificationDate), FROM_UNIXTIME(:validationDate),:isOpenSource)');
			
			$this->query_theme_update_score = $this->db->prepare('UPDATE theme SET themeUri=:themeUri, '
                                . 'namedemo=:namedemo, score=:score, criticalCount=:criticalCount, warningsCount=:warningsCount,'
                                . 'layout=:layout, cmsVersion=:cmsVersion, isThemeForest=:isThemeForest, '
                                . 'isTemplateMonster=:isTemplateMonster, isCreativeMarket=:isCreativeMarket, isPiqpaq=:isPiqpaq,'
                                . ' isNsfw=:isNsfw, validationDate=FROM_UNIXTIME(:validationDate), description=:description, '
                                . 'descriptionBB=:descriptionBB,isOpenSource=:isOpenSource WHERE id = :id');
		
			$this->query_theme_select_hash = $this->db->prepare('SELECT *,INET_NTOA(userIp) as userIp, HEX(hash_md5) as hash_md5, HEX(hash_sha1) as hash_sha1, UNIX_TIMESTAMP(creationDate) as creationDate, UNIX_TIMESTAMP(modificationDate) as modificationDate, UNIX_TIMESTAMP(validationDate) as validationDate from theme where hash = :hash');
			$this->query_theme_select_id = $this->db->prepare('SELECT *,INET_NTOA(userIp) as userIp, HEX(hash_md5) as hash_md5, HEX(hash_sha1) as hash_sha1, UNIX_TIMESTAMP(creationDate) as creationDate, UNIX_TIMESTAMP(modificationDate) as modificationDate, UNIX_TIMESTAMP(validationDate) as validationDate from theme where id = :id');
			$this->query_theme_select_namesanitized = $this->db->prepare('SELECT hash from theme where namesanitized = :namesanitized');
			
			$this->query_theme_select_recent = $this->db->prepare('SELECT *,INET_NTOA(userIp) as userIp,HEX(hash_md5) as hash_md5, HEX(hash_sha1) as hash_sha1, UNIX_TIMESTAMP(creationDate) as creationDate, UNIX_TIMESTAMP(modificationDate) as modificationDate, UNIX_TIMESTAMP(validationDate) as validationDate from theme ORDER BY id DESC limit 0,100');
			$this->query_theme_select_sorted = $this->db->prepare('SELECT *,INET_NTOA(userIp) as userIp,HEX(hash_md5) as hash_md5, HEX(hash_sha1) as hash_sha1, UNIX_TIMESTAMP(creationDate) as creationDate, UNIX_TIMESTAMP(modificationDate) as modificationDate, UNIX_TIMESTAMP(validationDate) as validationDate from theme WHERE themetype IN (:type) ORDER BY :sort DESC limit 0,100');
			$this->query_theme_select_olderthan = $this->db->prepare('SELECT *,INET_NTOA(userIp) as userIp,HEX(hash_md5) as hash_md5, HEX(hash_sha1) as hash_sha1, UNIX_TIMESTAMP(creationDate) as creationDate, UNIX_TIMESTAMP(modificationDate) as modificationDate, UNIX_TIMESTAMP(validationDate) as validationDate from theme WHERE id < :olderthan ORDER BY id DESC limit 0,100');
			$this->query_theme_select_olderthan_sorted = $this->db->prepare('SELECT *,INET_NTOA(userIp) as userIp,HEX(hash_md5) as hash_md5, HEX(hash_sha1) as hash_sha1, UNIX_TIMESTAMP(creationDate) as creationDate, UNIX_TIMESTAMP(modificationDate) as modificationDate, UNIX_TIMESTAMP(validationDate) as validationDate from theme WHERE :olderthan AND themetype IN (:type) ORDER BY :sort DESC limit 0,100');
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
	
	/** 
	*		Sanitize string and returns alphanumeric and underscores only
	**/
	static public function sanitizedString($str)
	{
		// convert accents to un-accented letters
		$unwanted_array = array('Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
		$str = strtr( $str, $unwanted_array );

		// convert non alphanumeric chars to "_"	
		$result = preg_replace("/[^-a-zA-Z0-9]+/", "_", $str);
		$result = strtolower($result);
		return $result;
	}
	
	public function getUniqueSanitizedName($name)
	{
		$sanitized = self::sanitizedString($name);
		$sanitized_orig = $sanitized;
		$i = '';
		do {
			$sanitized = $sanitized_orig.$i;
			$q = $this->db->query('SELECT count(*) from theme where namesanitized = '.$this->db->quote($sanitized));
			$row = $q->fetch();
			if (empty($i)) $i = 1; else $i++;
		} while (intval($row[0]) > 0);
		return $sanitized;
	}
	
	public function saveTheme($themeInfo, $update = false)
	{
	  // If theme hash already exists return immediately
		$q = $this->db->query('SELECT id from theme where hash = '.$this->db->quote($themeInfo->hash));
		$row = $q->fetch();
		
		// force wordpress version to last known version
		if (intval($themeInfo->themetype) == TT_WORDPRESS || intval($themeInfo->themetype) == TT_WORDPRESS_CHILD) $themeInfo->cmsVersion = LAST_WP_VERSION;
		
		if ($row !== false)
		{
			if ($update)
			{
				$id = intval($row[0]);
				$this->query_theme_update_score->bindValue(':themeUri', 		 $themeInfo->themeUri, \PDO::PARAM_STR);
				$this->query_theme_update_score->bindValue(':namedemo', 		 $themeInfo->namedemo, \PDO::PARAM_STR);
				$this->query_theme_update_score->bindValue(':score', 				 $themeInfo->score, \PDO::PARAM_STR);
				$this->query_theme_update_score->bindValue(':criticalCount', $themeInfo->criticalCount, \PDO::PARAM_STR);
				$this->query_theme_update_score->bindValue(':cmsVersion', 	 $themeInfo->cmsVersion, \PDO::PARAM_STR);
				$this->query_theme_update_score->bindValue(':warningsCount', $themeInfo->warningsCount, \PDO::PARAM_STR);
				$this->query_theme_update_score->bindValue(':description', 	 $themeInfo->description, \PDO::PARAM_STR);
				$this->query_theme_update_score->bindValue(':descriptionBB', $themeInfo->descriptionBB, \PDO::PARAM_STR);
				$this->query_theme_update_score->bindValue(':layout', 			 $themeInfo->layout, \PDO::PARAM_INT);
				$this->query_theme_update_score->bindValue(':isThemeForest', $themeInfo->isThemeForest, \PDO::PARAM_BOOL);
				$this->query_theme_update_score->bindValue(':isTemplateMonster', $themeInfo->isTemplateMonster, \PDO::PARAM_BOOL);
				$this->query_theme_update_score->bindValue(':isCreativeMarket', $themeInfo->isCreativeMarket, \PDO::PARAM_BOOL);
				$this->query_theme_update_score->bindValue(':isPiqpaq', $themeInfo->isPiqpaq, \PDO::PARAM_BOOL);
				$this->query_theme_update_score->bindValue(':isNsfw', $themeInfo->isNsfw, \PDO::PARAM_BOOL);
				$this->query_theme_update_score->bindValue(':validationDate', $themeInfo->validationDate, \PDO::PARAM_INT);
				$this->query_theme_update_score->bindValue(':isOpenSource', $themeInfo->isOpenSource, \PDO::PARAM_BOOL);
				
				$this->query_theme_update_score->bindValue(':id', $id, \PDO::PARAM_INT);
				$r = $this->query_theme_update_score->execute();
				if ($r===FALSE && TC_ENVIRONMENT !== 'prod')
				{
					$e = $this->query_theme_update_score->errorInfo();
					trigger_error(sprintf(__("DB error : %s"), $e[2]), E_USER_ERROR);
				}
			} else {
				$userMessage = UserMessage::getInstance();
				$userMessage->enqueueMessage(__('This theme has already been submitted.'), ERRORLEVEL_INFO);
			}
			return;
		}

		// If theme name already exists
		$q = $this->db->query('SELECT id,score,hash,name,namesanitized,INET_NTOA(userIp) as userIp from theme where name = '.$this->db->quote($themeInfo->name));
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
				if (intval($existing_score) < intval($themeInfo->score) || $existing_ip == $themeInfo->userIp) // ip match : consider the theme is a new version of the existing one. ip is the only information we can rely on to be sure the user is the same. Any other data such as author name is easily hackable and mean people could use this to overwrite existing themes.
				{
						$userMessage->enqueueMessage(__('It seems this archive is a new version of the theme "'.htmlspecialchars($themeInfo->name).'" you submitted previously. Validation results were updated.'), ERRORLEVEL_INFO);
						
						$this->query_theme_update_all = $this->db->prepare('UPDATE theme SET hash=:hash,
																																		 hash_md5=:hash_md5,
																																		 hash_sha1=:hash_sha1,
																																		 name=:name,
																																		 namesanitized=:namesanitized,
																																		 namedemo=:namedemo,
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
						$themeInfo->namesanitized = $row['namesanitized']; // can't call getUniqueSanitizedName because it would generate a new unique name, we need the same as before because namesanitized is used to build url and we do'nt want to change url
						$this->query_theme_update_all->bindValue(':id', $id, \PDO::PARAM_INT);
						$this->query_theme_update_all->bindValue(':hash', $themeInfo->hash, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':hash_md5', $themeInfo->hash_md5, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':hash_sha1', $themeInfo->hash_sha1, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':name', $themeInfo->name, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':namesanitized', $themeInfo->namesanitized, \PDO::PARAM_STR);
						$this->query_theme_update_all->bindValue(':namedemo', $themeInfo->namedemo, \PDO::PARAM_STR);
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
						return ;
				} else { // ip doesn't match : this is another theme with a duplicate name. Flag it as not serializable and display a message to user. (It would have been possible to generate a new name, but it would make it almost impossible to follow new versions afterwards)
					$themeInfo->serializable = false;
					$userMessage->enqueueMessage(__('The name "'.htmlspecialchars($themeInfo->name).'" of this theme is already used by a theme that was previously submitted. The results of this validation could not be saved.'), ERRORLEVEL_WARNING);
					return;
				}
			}
		}
		
		// generate sanitized name
		$themeInfo->namesanitized = $this->getUniqueSanitizedName($themeInfo->name);
 
		// save to DB
		$this->query_theme_insert->bindValue(':hash', $themeInfo->hash, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':hash_md5', $themeInfo->hash_md5, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':hash_sha1', $themeInfo->hash_sha1, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':name', $themeInfo->name, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':namesanitized', $themeInfo->namesanitized, \PDO::PARAM_STR);
		$this->query_theme_insert->bindValue(':namedemo', $themeInfo->namedemo, \PDO::PARAM_STR);
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
		$themeInfo->namedemo = $obj->namedemo;
		$themeInfo->author = $obj->author;
		$themeInfo->description = $obj->description;
		$themeInfo->descriptionBB = $obj->descriptionBB;
		$themeInfo->themeUri = $obj->themeUri;
		$themeInfo->version = $obj->version;
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
		$themeInfo->namesanitized = $obj->namesanitized;
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
		$this->query_theme_select_namesanitized = $this->db->prepare('SELECT hash from theme where namesanitized = :namesanitized');
		
		$this->query_theme_select_namesanitized->bindValue(':namesanitized', $namesanitized, \PDO::PARAM_STR);
	  $r = $this->query_theme_select_namesanitized->execute();
		if ($r===FALSE && TC_ENVIRONMENT !== 'prod')
		{
			$e = $this->query_theme_select_namesanitized->errorInfo();
			trigger_error(sprintf(__("DB error : %s"), $e[2]), E_USER_ERROR);
		}
		$row = $this->query_theme_select_namesanitized->fetch(); // return first row
		return $row[0];
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
			$this->query_theme_select_olderthan->bindValue(':olderthan', $olderthan, \PDO::PARAM_STR);
			$this->query_theme_select_olderthan->execute();
			$ret = array();
			while ($row = $this->query_theme_select_olderthan->fetch(\PDO::FETCH_ASSOC)) {
				$ret[]=$row; 
			}
		}
		return $ret;
	}
	
	public function getSorted($sort, $type, $olderthan=null)
	{
	
		$themeType = array("wordpress"=>1, "joomla"=>2);
		//$orderBy = array("creationDate", "score");
		$orderBy = array("id", "score");
		
		if(null==$olderthan)
		{
			$queryString = $this->query_theme_select_sorted->queryString;
		}
		else
		{
			$queryString = $this->query_theme_select_olderthan_sorted->queryString;
			
			// get last if info
			/*$query = $this->db->prepare('SELECT score,creationDate from theme WHERE id = :id');
			$query->bindValue(':id', $olderthan, \PDO::PARAM_INT);
			$query->execute();
			$lastItem = $query->fetch(\PDO::FETCH_ASSOC);*/
			
			#gdsgsdg
			
			// sort by score
			if($sort=="score")
			{
				$query = $this->db->prepare('SELECT score from theme WHERE id = :id');
				$query->bindValue(':id', $olderthan, \PDO::PARAM_INT);
				$query->execute();
				$lastItem = $query->fetch(\PDO::FETCH_ASSOC);
				$queryString = str_replace(':olderthan', 'score <= "'.$lastItem['score'].'" and id < '.intval($olderthan).'', $queryString);
			}
			// sort by creation date
			else
			{
				//$queryString = str_replace(':olderthan', 'creationDate < "'.$lastItem['creationDate'].'" and id < '.intval($olderthan).'', $queryString);
				$queryString = str_replace(':olderthan', 'id < '.intval($olderthan).'', $queryString);
			}
		}
	
		// type
		$r = array();
		foreach ($type as $v)
		{
			if(array_key_exists($v, $themeType)){
				$r[]= $this->db->quote($themeType[$v]);
			}
		}
		$type = implode(",", $r);

		$queryString = str_replace(':type', $type, $queryString);
		
		// order
		if(in_array($sort, $orderBy)){
			$order = $sort;
		} else {
			$order = $orderBy[0];
		}
		$queryString = str_replace(':sort', $order, $queryString);
		
		$query = $this->db->prepare($queryString);
		$query->execute();
		
		$ret = array();
		while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
			$ret[]=$row; 
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
                        . 'namesanitized from theme WHERE name=:name');
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
            $query = $this->db->query("SELECT namedemo,zipfilename,license,themetype"
                    . "FROM theme WHERE ISNULL(isOpenSource)");
            $query->execute();
            $datas = array();
            while($row = $query->fetch())
            {
                $datas[] = $row; 
            }
           
            return $datas;
        }
        
        public function updateIsOpenSource($value,$namedemo)
        {
            $query = $this->db->prepare("UPDATE theme SET isOpenSource=:isOpenSource WHERE namedemo=:namedemo");
            $query->bindValue(':isOpenSource',$value,\PDO::PARAM_BOOL);
            $query->bindValue(':namedemo',$namedemo,\PDO::PARAM_STR);
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
}
