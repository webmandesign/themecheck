<?php
namespace ThemeCheck;

require_once TC_INCDIR.'/ThemeInfo.php';
require_once TC_INCDIR.'/ListDirectoryFiles.php';
require_once TC_INCDIR.'/Check.php';
require_once TC_INCDIR.'/tc_helpers.php';

class Download
{
    private static $db;
    
    
    public function __construct() 
    {
       self::$db =  new \PDO('mysql:host='.DB_HOST.';dbname='.DB_DATABASE, DB_USER, DB_PWD);
    }
    
    /**
     * Count nb downloads by user
     * 
     * @param $user_ip  string
     * @param $date_now  int
     * @param $date_anterieur  int
     * @return int
     */
    public function CountDownloadByUser($user_ip,$date_now,$date_anterieur)
    {
        $result = self::$db->prepare("
                SELECT COUNT(user_ip)
                FROM download 
                WHERE date_download BETWEEN (FROM_UNIXTIME(:date_anterieur)) AND (FROM_UNIXTIME(:date_now))
                AND user_ip=INET_ATON(:user_ip)");
        $result->bindValue(':user_ip',$user_ip,\PDO::PARAM_STR);
        $result->bindValue(':date_now',$date_now,\PDO::PARAM_INT);
        $result->bindValue(':date_anterieur',$date_anterieur,\PDO::PARAM_INT);
        $result->execute();
        
        return $result->fetch();
    }
  
     /**
     * 
     * @param $id_user string
     * @param $date_download int
     * 
     */
    public function InsertNewDownload($user_ip,$date_download)
    {
        $result = self::$db->prepare("
                INSERT INTO download (date_download,user_ip)
                VALUES(FROM_UNIXTIME(:date_download),INET_ATON(:user_ip))");
        $result->bindValue(':user_ip',$user_ip,\PDO::PARAM_STR);
        $result->bindValue(':date_download',$date_download,\PDO::PARAM_INT);
        $result->execute();
    }
	
    /**
     * Gets date and hour of 1st download od the day
     * 
     * @param $user_ip string 
     * @param $date_now int
     * @param $date_before int
     * @return type []
     */
    public function GetDateMinDownload($user_ip,$date_now,$date_before)
    {
        $result = self::$db->prepare("
                 SELECT MIN(date_download) 
                 FROM download 
                 WHERE date_download BETWEEN (FROM_UNIXTIME(:date_before)) AND (FROM_UNIXTIME(:date_now)) 
                 AND user_ip=(INET_ATON(:user_ip))");
        $result->bindValue(':user_ip',$user_ip,\PDO::PARAM_STR);
        $result->bindValue(':date_now',$date_now,\PDO::PARAM_INT);
        $result->bindValue(':date_before',$date_before,\PDO::PARAM_INT);
        $result->execute();
        
        return $result->fetch();
    }
    
}
