<?php
/*
	page: login
*/

class user {

	public $mysql_link;

	public function __construct($mysql_link) {
		$this->mysql_link = $mysql_link;
	}
	
	
	public function getUser($name) {
		if (FALSE !== ($res = mysql_query("SELECT * FROM users WHERE user = '".mysql_real_escape_string($name)."' LIMIT 1",$this->mysql_link))) {
			if (mysql_num_rows($res) > 0) {
				return mysql_fetch_assoc($res);
			} 
		}
		return false;
	}
	
	public function login($name,$pass) {
		if (FALSE !== ($res = mysql_query("SELECT * FROM users WHERE user = '".mysql_real_escape_string($name)."' AND password = '".mysql_real_escape_string(md5($pass))."' LIMIT 1",$this->mysql_link))) {
			if (mysql_num_rows($res) == 1) {
				$user = mysql_fetch_assoc($res);
				$_SESSION['user'] = $user['user'];
				$_SESSION['level'] = $user['level'];
				return true;
			}
		}
		return false;
	}
	
	public function isLoggedin() {
		if (array_key_exists('user',$_SESSION) && array_key_exists('level',$_SESSION)) {
			return $this->getUser($_SESSION['user']);
		} else {
			return false;
		}
	}
	public function logout() {
		unset($_SESSION['user'],$_SESSION['level']);
	}
	public function getLevel() {
		if ($udata = $this->isLoggedin()) {
			return $udata['level'];
		} else {
			return 0;
		}
	}
	public function getName() {
		if ($udata = $this->isLoggedin()) {
			return $udata['user'];
		} else {
			return false;
		}	
	}
	public static function validateUserName($user) {
		if (preg_match("/^[0-9a-z\-\.]{5,32}$/",$user)) { return true; } else { return false; } 
	}
	public static function validatePassWord($pass) {
		if (preg_match("/^[0-9a-z\-\.]{5,32}$/",$pass)) { return true; } else { return false; } 
	}	
	/* API Authentication Functions */
	public function CheckAPIUser($user,$pass) {

		if (FALSE !== ($res = mysql_query("SELECT id,user,pass FROM api WHERE user = '".mysql_real_escape_string($user)."' AND pass = '".mysql_real_escape_string($pass)."' LIMIT 1",$this->mysql_link))) {

			if (mysql_num_rows($res) == 1) {
				$user = mysql_fetch_assoc($res);
				return $this->getAPIAccess($user['id']);
			}
		}

		return false;
	}
	private function getAPIAccess($uid) {
		$aNetworks = array();
		$query = sprintf("SELECT bot.id as bot_id FROM `apiLink` LEFT JOIN bot on apiLink.bot_id = bot.id WHERE apiLink.api_id = '%d';",$uid);
		if (FALSE !== ($res = mysql_query($query,$this->mysql_link))) {
			if (mysql_num_rows($res) >= 1) {
				while ($row = mysql_fetch_assoc($res)) {
					$qChan = sprintf("SELECT
channel.network_id as network_id,
network.name,network.server,
channel.id as chan_id,
channel.name as chan_name,
channel.public,
channel.bot_id,
bot.nick,bot.authNick,bot.authPass,bot.AuthEnabled,bot.authType
FROM network
LEFT JOIN channel on network.id = channel.network_id
LEFT JOIN bot on channel.bot_id = bot.id
WHERE  channel.bot_id = '%d'",
					$row['bot_id']);
					if (FALSE !== ($resb = mysql_query($qChan,$this->mysql_link))) {
						if (mysql_num_rows($res) >= 1) {
							while ($rowb = mysql_fetch_assoc($resb)) {
								$aNetworks[$rowb['name']]['details'] = array(
									'server'	=>	$rowb['server'],
									'name'		=>	$rowb['name'],
									'id'		=>	$rowb['network_id']);
								$aNetworks[$rowb['name']]['bot'][$rowb['nick']]['channel'][$rowb['chan_name']] = array(
									'public'	=>	$rowb['public']);
								$aNetworks[$rowb['name']]['bot'][$rowb['nick']]['auth'] = array(
									'authNick'	=>	$rowb['authNick'],
									'authPass'	=>	$rowb['authPass'],
									'authEnabled'	=>	$rowb['AuthEnabled'],
									'authType'	=>	$rowb['authType']);
								$aNetworks[$rowb['name']]['bot'][$rowb['nick']]['details'] = array(
									'nick'	=>	$rowb['nick'],
									'id'	=>	$row['bot_id']);
							}
						}
					}
				}
			}
		
		}
		return $aNetworks;
	}
}


?>
