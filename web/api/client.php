<?php

class ircblogClientAPI {
	
	public $authName	= "";
	public $authPass	= "";

	public $client;
	
	public function __construct($auth = array('user'=>'','pass'=>'')) {
		$this->client = new SoapClient(
			null, 
			array('location' => "http://ircblog.net/api/server.php",
			'uri'      => "http://ircblog.net/api")
		);
		$this->authName = $auth['user'];
		$this->authPass = md5($auth['pass']);
	}
	
	public function getConnectOptions() {
		try {
			return $this->client->getConnectOptions($this->sAuth());
		} catch(SoapFault $e) {
			print "Fatal Error ".$e->faultcode.": ".$e->getMessage()."\n";
			exit(0);
		}
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
	public function testcall(){
		return $this->client->testcall();
	}
	public function DirectCall($method,$data = array()){
		return $this->client->$method($data);
	
	}
	private function sAuth() {
		return array(
			'user'	=>	$this->authName,
			'pass'	=>	$this->authPass);
	}
}

$auth = array('user'=>'test','pass'=>'cookies');
$api = new ircblogClientAPI($auth);
print_r($api->getConnectOptions()); 
#print_r($api->DirectCall('testcall')); 
//print_r($api->getConnectOptions()); 
//print_r($client->testcall());

?>
