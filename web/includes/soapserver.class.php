<?php

class SoapServer {
	public $ircblog;
	public $user;
	public $mysql_link;

	public function __construct() {
		include(dirname(__FILE__)."/config.inc.php");
		$this->ircblog = $ircblog;
		$this->user = $user;
		$this->mysql_link = $mysql_link;
		
	}
	private function CheckAuth($auth) {
		if (array_key_exists('user',$auth) && user::validateUserName($auth['user'])) { $user = $auth['user']; } else { throw new SoapFault("1001","Invalid username"); }
		if (array_key_exists('pass',$auth) && user::validatePassWord($auth['pass'])) { $pass = $auth['pass']; } else { throw new SoapFault("1002","Invalid password"); }
		$data = $this->user->CheckAPIUser($user,$pass);
		if ($data === false) {
			throw new SoapFault("1000","Invalid credentials.");
		} else {
			return $data;
		}
	
	}
	public function getConnectOptions($auth) {
		$data = $this->CheckAuth($auth);
		return $data;
	}
	private static function hasAccess($networkID,$chanID,$accessList) {
	
	
	}
	/**
	 * Let a bot submit a batch of data to be queued.
	 * @param struct $auth credentials
	 * @param int $networkID
	 * @param string $chanName
	 * @param struct $data filled with data $data[id] = array('url'=>'','context'=>'','time'=>'','nick'=>'')
	 * @return bool 
	 */
	public function SubmitMedia($auth,$networkID,$chanName,$data) {
	
	
	}
	
	
	
	public function sendMedia($auth,$data) {

	}

	public function sendMediaBatch($auth,$data) {
	
	}
	
	public function getLast($user,$channel = false) {
	
	}
	
	public function getRandom($channel = false) {
	
	}
	
	public function getStatistics($who = array("user","#channel")) {
	
	}
	
	
	public function testcall() {
		//return $this->mysql_link;
		$this->ircblog->getImage($imgdata,11370);
		return $imgdata;
		return array("i haz cookiez!");
	}

}


?>