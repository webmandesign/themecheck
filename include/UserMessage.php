<?php
namespace ThemeCheck;

class UserMessage {
	private static $instance;
	public $messages;
	private function __construct() 
	{
			$messages = array();
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
	
	public function enqueueMessage($message, $errorlevel)
	{
		if (!isset($this->messages[$message])) $this->messages[$message] = $errorlevel;
	}
	
	public function getMessagesHtml()
	{
		$html = '';
		if (empty($this->messages)) return;
		foreach ($this->messages as $message => $errorlevel)
		{
			$alert_class = 'alert-success';
			if ($errorlevel === ERRORLEVEL_WARNING) $alert_class = 'alert-warning';
			else if ($errorlevel === ERRORLEVEL_ERROR) $alert_class = 'alert-danger';
			else if ($errorlevel === ERRORLEVEL_INFO) $alert_class = 'alert-info';
			
			$html .= '<div class="alert '.$alert_class.'">'.$message.'</div>';
		}
		return $html;
	}
}