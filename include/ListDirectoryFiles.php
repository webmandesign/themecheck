<?php
namespace ThemeCheck;
/**
 * Liste les fichiers d'un répertoire donné
 */
class ListDirectoryFiles
{
    private $_files = array();
    private $_directory;
    
    /**
     * Liste le répertoire
     * @param string $pPath
     */
    public function __construct($pPath)
    {
        $this->_directory = $pPath;
        
        $ritit = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($pPath), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($ritit as $splFileInfo) {
           $path = $splFileInfo->isDir()
                 ? array($splFileInfo->getFilename() => array())
                 : array($splFileInfo->getFilename());

           for ($depth = $ritit->getDepth() - 1; $depth >= 0; $depth--) {
               $path = array($ritit->getSubIterator($depth)->current()->getFilename() => $path);
           }
           $this->_files = array_merge_recursive($this->_files, $path);
        }
    }
    
    /**
     * Renvoie un array de string contenant un listing de tout les fichiers:
     * 
     * Exemple:
     * array(
     *   [0] => "/path/to/file1"
     *   [1] => "/path/to/another/file2"
     *   [2] ...
     * }
     * 
     * @return array
     */
    public function getFileList()
    {
        return $this->createFileList($this->_files);
    }
    
    /**
     * Même chose que getFileList mais trié par extensions
     * 
     * Exemple:
     * array(
     *   ["png"] => array(
     *     [0] => "/path/to/file1.png"
     *     [1] => "/path/to/another/file2.png"
     *   ),
     *   ["php"] => array(
     *      [0] => "/path/to/file1.php"
     *      [1] => "/path/to/another/file2.php"
     *   ),
     *   [...],
     * )
     * 
     * @return array
     */
    public function getFileListByType()
    {
        $array = array();
        $fileList = $this->getFileList();
        
        foreach($fileList as $file){
            $filePart = explode(".", basename($file));
            $ext = ((count($filePart)==2 && !empty($filePart[0])) OR count($filePart)>2) ? $filePart[count($filePart)-1] : "noext";
            $array[$ext][] = $file;
        }
        return $array;
    }
    
    /**
     * Permet de construire la liste de fichier de la fonction getFileList
     */
    public function createFileList($pArray, $pPath = "")
    {
        $array = array();
        foreach($pArray as $key => $fileOrDir){
            if(is_array($fileOrDir)){
                if($key!="." && $key!=".."){
                    $array = array_merge($this->createFileList($fileOrDir, $pPath.$key."/"), $array);
                }
            }
            else {
                $array[] = realpath($this->_directory.'/'.$pPath."/".$fileOrDir);
            }
        }
        return $array;
    }
    
    /**
     * Renvoie un array recursif du listing des fichiers:
     * 
     * Exemple:
     * array(
     *   ["directory1"] => array(
     *     ["sub-directory1] => array(
     *        [...]
     *     )
     *     [0] => (string) "file1.tmp",
     *   ),
     *   ["directory2"] => array(
     *     [0] => (string) "file1.tmp"
     *   ),
     *   [...],
     *   [0] => (string) "file3.tmp",
     *   [1] => (string) "file4.tmp",
     *   [2] => [...]
     * )
     * 
     * @return array
     */
    
    public function getRecursiveFileList()
    {
        return $this->_files;
    }
    
    /**
     *  Supprime récursivement le dossier en cours.
     */
    public function removeDir()
    {
        return self::recursiveRemoveDir($this->_directory);
    }
    public static function recursiveRemoveDir($dir)
    {
        $dir = realpath($dir);
        $files = array_diff(scandir($dir), array('.','..'));
        
        foreach ($files as $file) {
            if(is_dir("$dir/$file")){
                self::recursiveRemoveDir("$dir/$file");
            }
            else {
                unlink("$dir/$file");
            }
        }
        return rmdir($dir);
    }
}
?>
