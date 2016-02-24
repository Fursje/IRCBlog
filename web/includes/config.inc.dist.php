<?php

/* Default config */

$config = array(
	'web_base_dir'	=>	'/home/sites/ircblog.net/web/',
	'log_file'	=>	'log/default.log',
	'thumb_dir'	=>	'images/thumb/',
	'full_dir'	=>	'images/full/',
	'domain'	=>	'ircblog.net',
	
	'db_host'	=>	'localhost',
	'db_user'	=>	'',
	'db_pass'	=>	'',
	'db_db'		=>	'ircblog',
);

$validPageIncludes = array('about','login');
define("PAGE_INCLUDED",	true);

/* Start MySQL Connection */

if ($mysql_link = mysql_connect($config['db_host'],$config['db_user'],$config['db_pass'])) {
	if (!mysql_select_db($config['db_db'])) {
		debug_log("MySQL select database failed:[".mysql_errno($mysql_link).": ".mysql_error($mysql_link)."]",true);
	}
} else {
	debug_log("MySQL Connection failed:[".mysql_errno($mysql_link).": ".mysql_error($mysql_link)."]",true);
}

require_once(dirname(__FILE__)."/ircblog.class.php");
require_once(dirname(__FILE__)."/user.class.php");

$ircblog = new ircblog($mysql_link);
$user = new user($mysql_link);

session_start();

/* Public functions */
function debug_log($string,$die = false) {
	global $config;
	$console = false;
	$file = $config['web_base_dir'].$config['log_file'];
	$str = time()." ". $string."\n";
	file_put_contents($file,$str,FILE_APPEND);
	if ($die) {
		die("ERROR: $string\n");
	}
	if ($console) {
		print $str;
	}
}

?>
