<?php
namespace ThemeCheck;

class UserMessage {
	private static $instance;
	public $messages = array();
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
	
	public function enqueueMessage($message, $errorlevel)
	{
		if (!isset($this->messages[$message])) $this->messages[$message] = $errorlevel;
	}
	
	public static function enqueue($message, $errorlevel)
	{
		$_this = UserMessage::getInstance();
		if (!isset($_this->messages[$message])) $_this->messages[$message] = $errorlevel;
	}
	
	public static function getCount($errorlevel)
	{
		$_this = UserMessage::getInstance();
		$count = 0;
		foreach ($_this->messages as $message => $level)
		{
			if ($level==$errorlevel) $count++;
		}
		return $count;
	}
	
	public static function getMessagesHtml()
	{
		$_this = UserMessage::getInstance(); // init instance
		$html = '';
		if (empty($_this->messages)) return;
		foreach ($_this->messages as $message => $errorlevel)
		{
			$alert_class = 'alert-success';
			if ($errorlevel === ERRORLEVEL_WARNING) $alert_class = 'alert-warning';
			else if ($errorlevel === ERRORLEVEL_ERROR) $alert_class = 'alert-danger';
			else if ($errorlevel === ERRORLEVEL_FATAL) $alert_class = 'alert-danger';
			else if ($errorlevel === ERRORLEVEL_INFO) $alert_class = 'alert-info';
			
			$html .= '<div class="alert '.$alert_class.'">'.$message.'</div>';
		}
		return $html;
	}
	
	public static function getMessages($errorlevel)
	{
		$_this = UserMessage::getInstance();
		$ret = array();
		foreach ($_this->messages as $message => $level)
		{
			if ($level==$errorlevel) $ret[] = $message;
		}
		return $ret;
	}
}