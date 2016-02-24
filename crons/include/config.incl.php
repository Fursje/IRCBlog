<?php


/*
	default config
*/

$config = array(
	'files'	=> array(
		'n1' => array(
			'file'	=>	'/home/users/furs/xxxx/logs/system/debug.log',
			'channels'	=>	array('#xxxx','#xxxx'),
			'networkID'	=>	'1',
			'ignoreUsers'	=> array('xxx','xxxx'),
			'logtype'	=>	'debug_output',
		),
		'n2' => array(
			'file'	=>	'/home/users/furs/xxxx/logs/system/debug.log',
			'channels'	=>	array('#xxxx'),
			'networkID'	=>	'2',
			'ignoreUsers'	=> array('xxxx'),
			'logtype'	=>	'debug_output',
		),
		'n3' => array(
			'file'	=>	'/home/users/furs/irclogs/xxxx.log',
			'channels'	=>	array('#xxxx'),
			'networkID'	=>	'3',
			'ignoreUsers'	=> array(),
			'logtype'	=>	'irssi',
		),
		'n4' => array(
			'file'	=>	'/home/users/furs/irclogs/xxxx.log',
			'channels'	=>	array('#xxxx'),
			'networkID'	=>	'1',
			'ignoreUsers'	=> 	array(),
			'logtype'	=>	'irssi',
		),

	),
	'loc_file'	=>	'dat/log.position',
	'base_dir'	=>	'/home/users/furs/ircblog/',
	'log_file'	=>	'log/default.log',

	'web_base_dir'	=>	'/home/sites/ircblog.net/web/',
	'thumb_dir'	=>	'images/thumb/',
	'full_dir'	=>	'images/full/',
	
	'db_host'	=>	'localhost',
	'db_user'	=>	'',
	'db_pass'	=>	'',
	'db_db'		=>	'',

	'ignored_hosts'	=>	array('ircblog.net'),
);

/* Load classes */

require_once(dirname(__FILE__).'/filetailer.class.php');

$ft = new filetailer();

ReadLoc($config['base_dir'].$config['loc_file'],$file_locations);

/* Start MySQL Connection */

if ($mysql_link = mysql_connect($config['db_host'],$config['db_user'],$config['db_pass'])) {
	if (!mysql_select_db($config['db_db'])) {
		debug_log("MySQL select database failed:[".mysql_errno($mysql_link).": ".mysql_error($mysql_link)."]",true);
	}
} else {
	debug_log("MySQL Connection failed:[".mysql_errno($mysql_link).": ".mysql_error($mysql_link)."]",true);
}


?>
