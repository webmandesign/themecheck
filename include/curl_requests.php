<?php
namespace ThemeCheck;

/**Création de curl
 * 
 * @param  $testUrl string
 * @return type curl
 */
function requestsCurl($testUrl)
{
    $ch = curl_init(); 
    curl_setopt($ch,CURLOPT_URL,$testUrl);   // ajout url
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // verification du certificat
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);   // suivi des redirections (sinon code 301)
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // retour des infos de la requête
    
    return $ch;
}


/*
* Verifie si le theme existe sur le site wordpress.org
* @param string
* @return booleen
*/
function isOnWordpressOrg($nomTheme)
{
   
    $themeExist = true;
    $testUrl = 'https://wordpress.org/themes/'.$nomTheme;
 
    $ch = requestsCurl($testUrl);
    curl_exec($ch);
    // récupération du code de résultat de requête
    $test = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
    
    if($test != 200)
    {
        $themeExist = false;
    }
  
    return $themeExist;
}        

/**Vérifie si le thème existe sur Joomla24.com
 * 
 * @param $nomTheme type string
 * @param $zipfilename type string
 * @return boolean
 */
function isOnJoomla24($name,$zipfilename)
{
    $themeExist = false;
    $testUrl = 'http://www.joomla24.com/rd_sitemap.html';
    
    $explodeZip = explode('.',$zipfilename);
    $nomZip = $explodeZip[0];
    $name = str_replace(' ','_',$name);
    $nomZip = str_replace(' ','_',$nomZip);
    
    
    //Ouverture fichier de la liste des thèmes Joomla 
    if(!$fichier = @fopen(TC_INCDIR."/fichierJoomla.php","r+"))
    {
	echo "Echec à l'ouverture du fichier";
    }
    else
    {
        $today = date("Y-m-d");
        $mAj = 'Fichier mis à jour le '.$today.' ';
        
        //Recupération de la date inscrit dans le fichier
        $mAjFichier = substr(fgets($fichier),23,10);
        $nextMaJ = date('Y-m-d',strtotime($mAjFichier." + 7 days"));
     
        if((strlen(fgets($fichier))!= 0)&&($today<$nextMaJ))
        { 
          //Verification si theme existe
          while(!feof($fichier))
	  {
	    $contenu = fgets($fichier);
		
	    if((preg_match("/\b$name\b/i",$contenu))||
                    (preg_match("/\b$nomZip\b/i",$contenu)))
	    {
                $themeExist = true;
            }
     	  }
        }
        else
        {
           
           set_time_limit(60);  // A tester sur serveur
           //Récupère le contenu de la page des thèmes Joomla
           $ch = requestsCurl($testUrl);
           $pageContent = curl_exec($ch);
          
           fseek($fichier,0); // positionnement au début du fichier
           fputs($fichier,strtolower($pageContent)); // ecriture de la nouvelle
           // valeur dans le fichier texte
           fseek($fichier,0); // positionnement au début du fichier
           fputs($fichier,$mAj); // Insertion date de mise à jour
          
         
           isOnJoomla24($name); //Relance la fonction après écriture du fichier
        }
    }
    fclose($fichier);
  
    return $themeExist;
}   
?>

