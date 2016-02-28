<?php
namespace ThemeCheck;
require_once TC_INCDIR."/FileValidator.php";
if (USE_DB) include_once (TC_ROOTDIR.'/DB/History.php');

class Controller_massimport
{
	public $meta = array();
	public $samepage_i18n = array();
	public $importpath;
	
	public function __construct()
	{
		$this->importpath = TC_ROOTDIR.'/../items';
	}
	
	public function prepare()
	{
		$this->meta["title"] = __("Mass import");
		$this->meta["description"] = __("Mass import");
	}
	
	public function render()
	{
		?>
         <section id="content">
            <div class="container_massimport">	
		<div style="text-align:center;">
			<button type="button" id="import-btn" class="btn">
				<?php echo "Import new zip files";?>
			</button>
			<button type="button" id="update-btn" class="btn">
				<?php echo "Update DB files";?>
			</button>
           		<button type="button" id="upOpenSource-btn" class="btn">
				<?php echo "Update DB files open source";?>
			</button>
		</div>
                <div id="nb_theme" style="text-align: center;margin-top: 20px;"></div>
                <div id="progress" style="text-align: center;"></div>
                <div id="chargement" style="text-align: center;visibility: hidden;">chargement en cours...</div>
                <div id="mAj_inter" style="text-align: center;visibility: hidden;">La mise à jour des fichiers open source 
                s'est arretée à <?php  if(isset($_SESSION['pourcentage'])) echo substr($_SESSION['pourcentage'],0,4); ?>%<br>
                pour continuer cliquez sur le bouton update open source</div>
                <div style="text-align: center;" >
                    <textarea id="ajax_opSource" style="width: 280px;height: 400px;visibility: hidden;"></textarea>
                </div>
                
            </div>
        </section>
               <script type='text/javascript'>var pourcentage =<?php 
               if(isset($_SESSION['pourcentage']))echo $_SESSION['pourcentage']; ?>;
                  
                   if((pourcentage!=0)&&(pourcentage!=100))
                   { 
                       if(document.getElementById('ajax_opSource').style.visibility == 'hidden')
                       { 
                           document.getElementById('mAj_inter').style.visibility = 'visible';
                       }
                   }
               </script>
		<?php
               
		$files = listdir( $this->importpath );
		$fileszip = array();
		
		// read themelist files
		$themelist = array();
		foreach ($files as $f)
		{
			if (strpos($f, "themelist.csv")!==false) {
				if (($handle = fopen($f, "r")) !== FALSE) {
						while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
								if (count($data) == 2)
								{
									$zipfilename = $data[0];
									$date = $data[1];
									$themelist[$zipfilename] = $date;
								}
						}
						fclose($handle);
				}
			}
		}
		
		$history = new History();
		$countNew = 0;
		$countTotal = 0;
		foreach ($files as $f)
		{
			$path_parts = pathinfo($f);
			if (isset($path_parts['extension']) && $path_parts['extension'] == "zip") 
			{
				$zipfilename = $path_parts['basename'];
				if (isset($themelist[$zipfilename]))
				{
					$timestamp = strtotime($themelist[$zipfilename]);
					if ($timestamp > 946681200) // filter buggy dates
					{
						$id = $history->getIdFromZipName($zipfilename);
						if ($id === false) // doesn't exist in DB
						{
							$fileszip[$f] = $timestamp;
							$countNew++;
						} 
						$countTotal++;
					}
				}
			}
		}
		
		asort($fileszip);
		
		echo "<br/>Not imported yet : $countNew / $countTotal<hr>";
		
		foreach ($fileszip as $file => $timestamp)
		{
			echo date("Y-m-d", $timestamp).' : '.$file.'<br>';
		}

	/*	$count = 0;
		foreach ($fileszip as $f)
		{
			if ($count > 5) break;
			if(USE_DB)
			{
				$hash_md5 = md5_file($f); 
				$hash_alpha = base_convert($hash_md5, 16, 36); // shorten hash to shorten urls (better looking, less bandwidth)
				while(strlen($hash_alpha) < 25) $hash_alpha = '0'.$hash_alpha;
				$history = new History();
				$themeInfo = $history->loadThemeFromHash($hash_alpha);
				if (!empty($themeInfo)) continue;
			}
			
			$path_parts = pathinfo($f);
			$path_item = $path_parts['dirname'];
			$filename = $path_parts['filename'].'.'.$path_parts['extension'];
		
			$themeInfo = FileValidator::prepareThemeInfo($path_item.'/'.$filename, $filename, 'application/zip', false);

			if (!empty($themeInfo))
			{
				$this->fileValidator = new FileValidator($themeInfo);
				$this->fileValidator->validate();	
				$this->fileValidator->serialize();
				
				$this->validationResults = $this->fileValidator->getValidationResults(I18N::getCurLang());
				$themeInfo = $this->fileValidator->themeInfo;
				echo '<p>'.htmlspecialchars($themeInfo->name).' : '.intval($themeInfo->score).'%</p>';
				$count++;
			}
		}*/
		?>
		<script>
		var zips = new Array();
		var zip_index = 0;
		var theme_id_sart = 145;
		var theme_id = theme_id_sart;
                var etatVerif = 'Chargement des thèmes :';
                var nbtheme;
                var iteration=0;
		<?php 
		$a = array_keys($fileszip);
		for ($i = 0; $i< count($a); $i++)
		{
			$unixStylePath = str_replace('\\','/',realpath($a[$i]));
			$index = $a[$i];
			$timestamp = $fileszip[$index];
			echo 'zips['.$i.'] = new Array("'.$unixStylePath.'","'.$timestamp.'");'."\n";
		}
		?>
		
		function importNext()
		{
			$.ajax({
				type: "POST",
				url: "<?php echo TC_HTTPDOMAIN.'/ajax.php?controller=massimport&action=importnext';?>",
				data : {path : zips[zip_index][0], timestamp : zips[zip_index][1]}
			}).done(function( obj ) {
				obj.index = zip_index++;
				console.log(obj);
				importNext();
			}).fail(function() {
				console.log("ajax error");
			})
		}
				
		$('#import-btn').click(importNext);
		
		function updateNext()
		{
			$.ajax({
				type: "POST",
				url: "<?php echo TC_HTTPDOMAIN.'/ajax.php?controller=massimport&action=updatenext';?>",
				data : {id : theme_id, timestamp : zips[zip_index][1]}
			}).done(function( obj ) {
				console.log(obj);
				if (obj.nextId == null) {
					console.log("update done");
					theme_id = theme_id_sart;
				} else {
					theme_id = obj.nextId;
					updateNext();
				}
			}).fail(function() {
				console.log("ajax error");
			})
		}
		$('#update-btn').click(updateNext);
                
                function updateOpenSource()
                {
                   if(iteration==0)
                   { 
                      document.getElementById('chargement').style.visibility = 'visible';
                   }
                   
                    $.ajax({
                        url : "<?php echo TC_HTTPDOMAIN.'/ajax.php?controller=massimport&action=updateOpenSource';?>",
                        type : "POST",
                        dataType : 'text',
                        success :function(message){
                          document.getElementById("chargement").style.visibility = "hidden";
                            if(message!='Mise à jour terminée')
                            {
                              etatVerif += message;
                              document.getElementById("ajax_opSource").innerHTML = etatVerif;
                              document.getElementById("ajax_opSource").style.visibility = "visible";
                              document.getElementById('mAj_inter').style.visibility = 'hidden';
                              updateOpenSource();
                              iteration++;
                            }
                            else
                            { 
                                etatVerif += "\r\n"+message;
                                document.getElementById("ajax_opSource").innerHTML = etatVerif;
                                document.getElementById("ajax_opSource").style.visibility = "visible";
                                document.getElementById('mAj_inter').style.visibility = 'hidden';
                                etatVerif='Chargement des thèmes :';
                                iteration=0;
                            }
                            progressVerif();
                         }
                       
                     });
                   
                    $.ajax({
                        url : "<?php echo TC_HTTPDOMAIN.'/ajax.php?controller=massimport&action=nombreTheme';?>",
                        type : "POST",
                        dataType : 'text',
                        success :function(message){                   
                          document.getElementById("nb_theme").innerHTML = message + " thème(s) concerné(s) par la mise à jour";
                          document.getElementById("nb_theme").style.visibility = "visible";}
                         });
                   }
                   $('#upOpenSource-btn').click(updateOpenSource);
                   
                   
                   function progressVerif()
                   {
                       $.ajax({
                        url : "<?php echo TC_HTTPDOMAIN.'/ajax.php?controller=massimport&action=progressVerif';?>",
                        type : "POST",
                        dataType : 'text',
                        success :function(response){ 
                         document.getElementById('progress').innerHTML = "Avancement vérification : "+response+"%";
                       }}); 
                   }
          	</script>
            <?php
	}
	
	public function ajax_importnext()
	{
		$time_start = microtime(true);
		$response["error"] = "none";
		$response["file"] = "none";
		
		if (file_exists($_POST["path"]))
		{
			$response["file"] = $_POST["path"];
			if (USE_DB)
			{
				$f = $_POST["path"];
				global $g_creationDate;
				$g_creationDate = intval($_POST["timestamp"]); // bad style, but so much easier

				$hash_md5 = md5_file($f); 
				$hash_alpha = base_convert($hash_md5, 16, 36); // shorten hash to shorten urls (better looking, less bandwidth)
				while(strlen($hash_alpha) < 25) $hash_alpha = '0'.$hash_alpha;
				$history = new History();
				$themeInfo = $history->loadThemeFromHash($hash_alpha);
				
				if (empty($themeInfo)) // don't do anything if already in DB
				{
					$path_parts = pathinfo($f);
					$path_item = $path_parts['dirname'];
					$filename = $path_parts['filename'].'.'.$path_parts['extension'];			
					$themeInfo = FileValidator::prepareThemeInfo($path_item.'/'.$filename, $filename, 'application/zip', false);

					if (!empty($themeInfo))
					{
						$this->fileValidator = new FileValidator($themeInfo);
						$this->fileValidator->validate();	
						if (UserMessage::getCount(ERRORLEVEL_FATAL) > 0)
						{
							$response["error"] = "fatal error:\n";
							foreach(UserMessage::getMessages(ERRORLEVEL_FATAL) as $m)
							{
								$response["error"] .= "\n".$m;
							}
						} else {
						
							if ($this->fileValidator->serialize())
							{
								if (UserMessage::getCount(ERRORLEVEL_FATAL) > 0)
								{
									// at least one error occured while serializing (no thumbnail...)
									$response["error"] = "fatal error, could not serialize validation results:\n";
									foreach(UserMessage::getMessages(ERRORLEVEL_FATAL) as $m)
									{
										$response["error"] .= "\n".$m;
									}
									foreach(UserMessage::getMessages(ERRORLEVEL_CRITICAL) as $m)
									{
										$response["error"] .= "\n".$m;
									}
								} else {
									$this->validationResults = $this->fileValidator->getValidationResults(I18N::getCurLang());
									$themeInfo = $this->fileValidator->themeInfo;
									$response["themeinfo"] = $themeInfo;
								}
							} else {
								// at least one error occured while serializing (no thumbnail...)
								if (UserMessage::getCount(ERRORLEVEL_CRITICAL) > 0)
								$response["error"] = "could not serialize validation results";
								foreach(UserMessage::getMessages(ERRORLEVEL_CRITICAL) as $m)
								{
									$response["error"] .= "\n".$m;
								}
							}
						}
					} else {
						if (UserMessage::getCount(ERRORLEVEL_FATAL) > 0)
						{
							// at least one error occured while serializing (no thumbnail...)
							$response["error"] = "could not execute validation:\n";
							foreach(UserMessage::getMessages(ERRORLEVEL_FATAL) as $m)
							{
								$response["error"] .= "\n".$m;
							}
							foreach(UserMessage::getMessages(ERRORLEVEL_CRITICAL) as $m)
							{
								$response["error"] .= "\n".$m;
							}
						} else {
							$response["error"] = "could not execute validation (unknown error).";
						}
					}
				}
			}			
		}
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		$response["duration"] = $time;
		//ob_clean();
		header('Content-Type: application/json');
		echo json_encode($response);
	}
	
	public function ajax_updatenext()
	{ 
		$time_start = microtime(true);
		$response["error"] = "none";
		$response["file"] = "none";
		$response["nextId"] = null;
		if (!empty($_POST["id"]))
		{
			$id = intval($_POST["id"]);
			if ($id < 1) $i = 1;
			
			$history = new History();
			$themeInfo = $history->getFewInfo($id);
			if (!empty($themeInfo))
			{	
				$unzippath = TC_VAULTDIR.'/unzip/'.$themeInfo["hash"]."/";
				if (file_exists($unzippath)){
					$nextId = $history->getNextId($themeInfo["id"]);
					$response["nextId"] = $nextId;
					
					$themeInfo = $history->loadThemeFromHash($themeInfo["hash"]);// need an objet and not an array
					$r = $themeInfo->initFromUnzippedArchive($unzippath, $themeInfo->zipfilename, $themeInfo->zipmimetype, $themeInfo->zipfilesize); // merchant...
					$this->fileValidator = new FileValidator($themeInfo);
					$this->fileValidator->validate();	
					if (UserMessage::getCount(ERRORLEVEL_FATAL) > 0)
					{
						$response["error"] = "fatal error:\n";
						foreach(UserMessage::getMessages(ERRORLEVEL_FATAL) as $m)
						{
							$response["error"] .= "\n".$m;
						}
					} else {
						if ($this->fileValidator->serialize(true))
						{
							if (UserMessage::getCount(ERRORLEVEL_FATAL) > 0)
							{
								// at least one error occured while serializing (no thumbnail...)
								$response["error"] = "fatal error, could not serialize validation results:\n";
								foreach(UserMessage::getMessages(ERRORLEVEL_FATAL) as $m)
								{
									$response["error"] .= "\n".$m;
								}
								foreach(UserMessage::getMessages(ERRORLEVEL_CRITICAL) as $m)
								{
									$response["error"] .= "\n".$m;
								}
							} else {
								$this->validationResults = $this->fileValidator->getValidationResults(I18N::getCurLang());
								$themeInfo = $this->fileValidator->themeInfo;
								$response["name"] = $themeInfo->name;
							}
						} else {
							// at least one error occured while serializing (no thumbnail...)
							if (UserMessage::getCount(ERRORLEVEL_CRITICAL) > 0)
							$response["error"] = "could not serialize validation results";
							foreach(UserMessage::getMessages(ERRORLEVEL_CRITICAL) as $m)
							{
								$response["error"] .= "\n".$m;
							}
						}
					}
				} else {
					$response["error"] .= "No zip file ".$unzippath;
				}
			} else {
				if (UserMessage::getCount(ERRORLEVEL_FATAL) > 0)
				{
					// at least one error occured while serializing (no thumbnail...)
					$response["error"] = "could not execute validation:\n";
					foreach(UserMessage::getMessages(ERRORLEVEL_FATAL) as $m)
					{
						$response["error"] .= "\n".$m;
					}
					foreach(UserMessage::getMessages(ERRORLEVEL_CRITICAL) as $m)
					{
						$response["error"] .= "\n".$m;
					}
				} else {
					$response["error"] = "could not execute validation (unknown error).";
				}
			}
		}
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		$response["duration"] = $time;
		//ob_clean();
		header('Content-Type: application/json');
		echo json_encode($response);
	}
        public function ajax_updateOpenSource()
        {    
           
            if(empty($_SESSION['themedir']))
            {
               //Récuparation des themedir dans la bdd
               $data = new History();
               $datas = $data->getNameIsOpenSource();
              
               $_SESSION['countReq'] = count($datas);
              
               //Création de la variable de session
               for($i=0;$i<count($datas);$i++)
               {
                   $_SESSION['name'][$i] = $datas[$i]['name'];
                   $_SESSION['themedir'][$i] = $datas[$i]['themedir'];
                   $_SESSION['zipfilename'][$i] = $datas[$i]['zipfilename'];
                   $_SESSION['license'][$i] = $datas[$i]['license'];
                   $_SESSION['themetype'][$i] = $datas[$i]['themetype'];
               }
            
               $_SESSION['iteration'] = 0;
              
            }
          
            if($_SESSION['iteration']<$_SESSION['countReq'])
            {

                if(isset($_SESSION['themedir']))
                {
                    include_once("include/curl_requests.php");
                    $name = $_SESSION['name'][$_SESSION['iteration']];
                    $themeDir = $_SESSION['themedir'][$_SESSION['iteration']];
                    $zipfilename = $_SESSION['zipfilename'][$_SESSION['iteration']];
                    $license =  $_SESSION['license'][$_SESSION['iteration']];
                    $themetype = $_SESSION['themetype'][$_SESSION['iteration']];
                    
                   
                    $themeOpenSource = false;
                    if($themetype != 2)
                    {
                       //Controle si thème existe sur site WordPress.org
                       $themeOpenSource = themeWordPressExist($themeDir);
                    }
                    else
                    {
                        //Vérification si le thème existe sur Joomla
                        $themeOpenSource = isOnJoomla24($name,$zipfilename);
                    }
    
                    if($themeOpenSource)
                    {
                       //Controle si thème existe sur site WordPress.org
                       $themeOpenSource = isOnWordpressOrg($themeDir);
                    }
                    else
                    { 
			$name = str_replace(' ','_',$name);

                        //Vérification si le thème existe sur Joomla
                        $themeOpenSource = isOnJoomla24($name,$zipfilename);
                  }                   
		    
		    if($themeOpenSource)
                    {
                       $update = new History();
                       $update->updateIsOpenSource($themeOpenSource,$themeDir);
                       $_SESSION['iteration']++;
                       echo 'Le thème '.$themeDir.' a été mis à jour';
                    }
                    else 
                    {
		       $_SESSION['iteration']++;
                       echo 'Le thème '.$themeDir.' est à jour';
                    }
                }
            }
            else
            {
                // Réinitialisation des variables de session
                echo'Mise à jour terminée';
                $_SESSION['iteration']=0;
                $_SESSION['themedir']='';
            }
      
        }
        
        public function ajax_nombreTheme()
        {   
           echo $_SESSION['countReq'];
        }
        
        public function ajax_progressVerif()
        { 
           $_SESSION['pourcentage'] = 100/$_SESSION['countReq'];
          
            if($_SESSION['iteration']>0)
            {
               if($_SESSION['iteration']===$_SESSION['countReq'])
               {
                 $_SESSION['pourcentage'] = 100;
               }
               else
               {
                 $_SESSION['pourcentage'] = $_SESSION['pourcentage']*$_SESSION['iteration'];
               }
            }
            else
            {
                $_SESSION['pourcentage'] = 100;
            }
            echo substr($_SESSION['pourcentage'],0,4);
          
        }
}





