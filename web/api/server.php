<?php

include(dirname(__FILE__)."/../includes/soapserver.class.php");

/* Start Server */
$server = new SoapServer(null, array('uri' => "http://ircblog.net/api"));
$server->setClass('SoapServer');
$server->handle();

/*
class SoapServer {
	public $ircblog;
	public $user;
	public $mysql_link;

	public function __construct() {
		include(dirname(__FILE__)."/../includes/config.inc.php");
		$this->ircblog = $ircblog;
		$this->user = $user;
		$this->mysql_link = $mysql_link;
		
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
	
	private function verifyAuth($authName,$authKey) {
		return true;
	}
	
	public function testcall() {
		//return $this->mysql_link;
		$this->ircblog->getImage($imgdata,11370);
		return $imgdata;
		return array("i haz cookiez!");
	}
	private function CheckAuth($user,$pass) {
		$data = $this->user->CheckAPIUser($user,$pass);
		if ($data === false) {
			throw new SoapFault("1000","Invalid credentials.");
		} else {
			return $data;
		}
	
	}
	public function getConnectOptions($auth) {
		$data = $this->CheckAuth($auth['user'],$auth['pass']);
		return $data;
		//print $data;die;
	}
}
*/

?>