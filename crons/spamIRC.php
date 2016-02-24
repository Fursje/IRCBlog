#!/usr/bin/php
<?php

/*
	Description: Part of IRCBlog.. lame hack to spam IRC for 1 channel only ;(
*/

$ib = new ircblog_spamIRC();
$ib->checknewItems();

class ircblog_spamIRC {


	public $config = array(
			'db_host'       =>      'localhost',
			'db_user'       =>      '',
			'db_pass'       =>      '',
			'db_db'         =>      '',
	);
	
	public $mysql_link;
	public $irc;
	public $lastItems = array();
	public $lastItemFile = "/home/users/user/ircblog/spamirc.dat";
	public $maxItems = 5;
	public $channel_id = 2;
	public $url = "http://ircblog.net/n/EFnet/gaap/view/%d.html";
	private $clickme;
	
	public function __construct() {
		require_once(dirname(__FILE__)."/include/irc.class.php");
		$this->irc = new irc();
		
		if ($this->mysql_link = mysql_connect($this->config['db_host'],$this->config['db_user'],$this->config['db_pass'])) {
				if (!mysql_select_db($this->config['db_db'])) {
						$this->debug_log("MySQL select database failed:[".mysql_errno($mysql_link).": ".mysql_error($mysql_link)."]",true);
				}
		} else {
				$this->debug_log("MySQL Connection failed:[".mysql_errno($mysql_link).": ".mysql_error($mysql_link)."]",true);
		}
	}
	
	public function checknewItems() {
		$this->readlastItems();
		$query = sprintf("SELECT images.id,images.channel, images.chan_id, images.poster, images.image_name, images.post_type, images.context FROM `images` WHERE images.chan_id = '%d' AND images.post_type = 'mail' ORDER BY `images`.`id`  DESC LIMIT %d",$this->channel_id,$this->maxItems);
		if (FALSe !== ($res = mysql_query($query,$this->mysql_link))) {
			while ($row = mysql_fetch_assoc($res)) {
				if (!in_array($row['id'],$this->lastItems)) {
					$this->lastItems[] = $row['id'];
					// not spammed yet display.
					#print_r($row);
					$subject = "";
					if (preg_match("/Subject:\s(.*)\n/",$row['context'],$m)) {
						#print_r($m);
						$subject = $m[1];
					}
					$url = sprintf($this->url,$row['id']);
					$this->getShortUrl($url);
					$tmp_msg = "[IRCBlog] Recieved a new image from [%s] titled [%s] - %s";
					$msg = sprintf($tmp_msg,$row['poster'],$subject,$url);
					#print $msg."\n";
					$this->irc->SendNow($msg,"#gaap");
				}
			}
		}
		
		$this->writelastItems();
	
	}
	private function getShortUrl(&$url) {
		if (!is_object($this->clickme)) {
			$this->clickme = new SoapClient(
				null,
				array(
					'location'	=>	"http://cl1ck.me/api.php",
					'uri'		=>	"http://cl1ck.me")
			);
		}
		$key = "xxxxxxxxxxxxxx";

		try {
			$cret = $this->clickme->add($key,$url);
			$url = $cret['url_hash'];
		} catch(SoapFault $e) {
				$this->debug_log("Fatal Error ".$e->faultcode.": ".$e->getMessage());
		}
	
	}
	private function readlastItems() {
		if (file_exists($this->lastItemFile)) {
			if ($tmp = file_get_contents($this->lastItemFile)) {
				if (FALSE !== ($tmp1 = unserialize($tmp))) {
					$this->lastItems = $tmp1;
					return true;
				}
			} else { return false; }
		} else {
			return false;
		}
	}
	
	private function writelastItems() {
		arsort($this->lastItems);
		#print_r($this->lastItems);
		$tmp_items = array();
		$c = 0;
		foreach ($this->lastItems as $item) {
			if ($c <= ($this->maxItems+5)) {
				$tmp_items[] = $item;
				$c++;
			} else {
				break;
			}
		}
		#print_r($tmp_items);
		file_put_contents($this->lastItemFile,serialize($tmp_items));
	}
	public function debug_log($a,$b) {}
}
?>
