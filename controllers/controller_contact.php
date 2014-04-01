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
					
					echo '<div class="container"><div class="alert alert-success">'. __("Message sent. We&#39;ll contact you soon.") .'</div><a href="'.TC_HTTPDOMAIN.'/'.Route::getInstance()->assemble(array("lang"=>I18N::getCurLang(), "phpfile"=>"index.php")).'">'.__("Back to home page").'</a></div>';
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
			<div class="container">
			<h1><?php echo __("Contact us");?></h1>
			<br/><br/>
					<div id="contact-form">
						<form method="post" action="">
							<div class="row">
								<div class="form-group <?php if(isset($errors['name'])):?>has-error<?php endif;?> col-md-4 col-sm-6">
									<label class="label-text control-label" for="name"><?php echo __("Name (required)");?></label>
									<input type="text" class="name form-control" value="" id="name" name="name">
									<?php if(isset($errors['name'])):?><span class='control-label'><?php echo $errors['name'];?></span><?php endif;?>
								</div>
							</div>
							<div class="row">
								<div class="form-group <?php if(isset($errors['email'])):?>has-error<?php endif;?> col-md-4 col-sm-6">
									<label class="label-email control-label" for="email"><?php echo __("Email (required)");?></label>
									<input type="text" class="email font-primary form-control" value="" id="email" name="email">
									<?php if(isset($errors['email'])):?><span class='control-label'><?php echo $errors['email'];?></span><?php endif;?>
								</div>
							</div>
							<div class="row">
								<div class="form-group <?php if(isset($errors['website'])):?>has-error<?php endif;?> col-md-4 col-sm-6">
									<label class="label-text control-label" for="website"><?php echo __("Website");?></label>
									<input type="text" class="url font-primary form-control" value="" id="website" name="website">
									<?php if(isset($errors['website'])):?><span class='control-label'><?php echo $errors['website'];?></span><?php endif;?>
								</div>
							</div>
							<div class="row">
								<div class="form-group <?php if(isset($errors['message'])):?>has-error<?php endif;?> col-md-9">
									<label class="label-textarea control-label" for="message"><?php echo __("Message (required)");?></label>
									<textarea rows="10" id="message" name="message" class="font-primary form-control"></textarea>
									<?php if(isset($errors['message'])):?><span class='control-label'><?php echo $errors['message'];?></span><?php endif;?>
								</div>
							</div>
							<p class="contact-submit form-group <?php if(isset($errors['token'])):?>has-error<?php endif;?>">
								<input type="submit" class="btn btn-primary" name="send" value="<?php echo __("Submit");?>">
								<input type="hidden" name="token" value="<?php echo $token;?>">
								<?php if(isset($errors['token'])):?><span class='control-label'><?php echo $errors['token'];?></span><?php endif;?>
							</p>
						</form>
					</div>

			</div>
			
			<?php
		}
	}
	
}