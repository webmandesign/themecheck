<?php
namespace ThemeCheck;

class Controller_contact
{
	public $meta = array();
	public $samepage_i18n = array();
	
	public function __construct()
	{
	}
	
	public function prepare()
	{
		$this->meta["title"] = __("Contact us");
		$this->meta["description"] = __("Contact us");
		global $ExistingLangs;
		foreach ($ExistingLangs as $l)
		{
                    $this->samepage_i18n[$l] = TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>$l, "phpfile"=>"contact"));
		}
	}
	
	public function render()
	{ 
            ?>
            <script type="text/javascript"> var page="contact" </script>
<?php
		if(isset($_POST['send'])){  
			$errors = array();
			if(isset($_SESSION['token_'.$_POST['token']]))
			{
				unset($_SESSION['token_'.$_POST['token']]);
				
				if(empty($_POST['name'])) $errors['name'] = __("Required field");
				if(empty($_POST['email'])) $errors['email'] = __("Required field");
				elseif(false === filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = __("invalid email address");
				if(empty($_POST['message'])) $errors['message'] = __("Required field");
				
				// Send email
				if(count($errors)==0)
				{
					require_once TC_INCDIR.'/Swift-4.3.0/lib/swift_required.php';
    
					$message = \Swift_Message::newInstance();
					$message->setSubject('CONTACT THEMECHECK');
					$message->setFrom(array('mailer@themecheck.org' => 'Themecheck.org'));
					
					$text = "Contact from : ".htmlspecialchars($_POST['name'])." : ".htmlspecialchars($_POST['email'])."<br>";
					if(!empty($_POST['website'])) $text .= "Website: ".htmlspecialchars($_POST['website'])."<br>";
					
					$text .= "<br>Message:<br>";
					$text .= nl2br(htmlspecialchars($_POST['message']));
					
					if (preg_match("/[bcdfghjklmnpqrstvwxzBCDFGHJKLMNPQRSTVWXZ]{5,}/", $_POST['message'])) die; // anti spam 
					$message->setBody($text, 'text/html');
					
					$to = array();
					$to[TC_CONTACT_MAIL] = TC_CONTACT_NAME;
					
					$message->setTo($to);
					
					if(TC_ENVIRONMENT == 'dev')
					{
						// for unconfigured php.ini smtp use (xampp/wamp etc...):
						$transport = \Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl') // or newInstance('smtp.example.org', 465, 'ssl') 
							->setUsername('username')
							->setPassword('password');
					}
					else
					{
						$transport = \Swift_SmtpTransport::newInstance();
					}
					
					$mailer = \Swift_Mailer::newInstance($transport);
					$test = $mailer->send($message);
					
                                        $errors['mail_sent'] = __('Message sent. We&#39;ll contact you soon.');
					//echo '<div class="container"><div class="alert alert-success">'. __("Message sent. We&#39;ll contact you soon.") .'</div><a href="'.TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"index.php")).'">'.__("Back to home page").'</a></div>';
				}
                                else
                                {
                                    $errors['mail_error'] = __('Please complete the required field.');
                                }
			}
			else
			{
				$errors['token'] = __("Invalid form");
			}
		}
		
		if(!isset($_POST['send']) || count($errors)>0)
		{
			$token = uniqid(true);
			$_SESSION['token_'.$token] = time();
		
			?>
                        
                        <section id="content">
                            <div class="container_contact">
                                <div class="bg_contact">
                                    <div class="content_contact">
                                        <h1><?php echo __("Contact us"); ?></h1>

                                        <span class="line_contact">
                                                <img src="<?php echo TC_HTTPDOMAIN; ?>/img/images/line_content-home.png" alt="">
                                        </span>

                                        <form class="form_contact" method="post" action="">
                                            <div class="renseignement_utilisateur">
                                                    <div class="block_label">
                                                            <label class="infos_user <?php if(isset($errors['name'])):?>has-error<?php endif;?>"><?php echo __("Name"); ?></label>
                                                            <label class="required"><?php echo __("Required"); ?></label>
                                                    </div>
                                                    <input type="text" class="text_input <?php if(isset($errors['name'])):?>has-error<?php endif;?>" value="" id="name" name="name">
                                            </div>
                                            <div class="renseignement_utilisateur">
                                                    <div class="block_label">
                                                            <label class="infos_user <?php if(isset($errors['email'])):?>has-error<?php endif;?>" for="email"><?php echo __('Email'); ?></label>
                                                            <label class="required"><?php echo __('Required'); ?></label>
                                                    </div>
                                                    <input type="text" class="text_input <?php if(isset($errors['email'])):?>has-error<?php endif;?>" value="" id="email" name="email">
                                                   
                                            </div>
                                            <div class="renseignement_utilisateur">
                                                    <div class="block_label">
                                                            <label class="infos_user"><?php echo __("Website");?></label>
                                                    </div>
                                                    <input type="text" class="text_input" value="" id="website" name="website">
                                                  
                                            </div>
                                            <div class="renseignement_utilisateur">
                                                    <div class="block_label userMessage">
                                                            <label class="infos_user <?php if(isset($errors['message'])):?>has-error<?php endif;?>"><?php echo __("Message");?></label>
                                                            <label class="required"><?php echo __("Required");?></label>
                                                    </div>
                                                    <textarea class="text_input messageUser <?php if(isset($errors['message'])):?>has-error<?php endif;?>" rows="10" id="message" name="message"></textarea>
                                                    
                                            </div>
                                             <div class="container_message">
                                                <span class="fake_input <?php if(isset($errors['mail_sent'])):?>mail_sent<?php endif;?>"><?php echo $errors['mail_sent']; ?></span>
                                                <span class="fake_input <?php if(isset($errors['mail_error'])):?>mail_error<?php endif;?>"><?php echo $errors['mail_error']; ?></span>
                                            </div>
                                            <label for="send" class="btn_action submitForm" value="<?php echo __("SUBMIT");?>"><span class="sprite arrow_white"></span><?php echo __("SUBMIT");?></label>
                                            <input type="submit" class="fake_input" name="send" id="send">
                                            <input type="hidden" name="token" value="<?php echo $token; ?>">
                                           
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </section>			
			<?php
		}
	}
	
}