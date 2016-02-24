#!/usr/bin/php
<?php
/*
	Description: Part of ircblog to be able to submit images through mail..
	date: 13/09/2011
*/


$stdin = file_get_contents('php://stdin');

$pmail = new ircblog_parseMail();
if ($pmail->setMailBody($stdin,"stdin")) {
	$pmail->parse();
}

exit(0);

class ircblog_parseMail {


	public $config = array(
			'web_base_dir'  =>      '/home/sites/ircblog.net/web/',
			'log_file'      =>      '/tmp/ircblog-submit.log',
			'thumb_dir'     =>      'images/thumb/',
			'full_dir'		=>		'images/full/',
			'domain'        =>      'ircblog.net',

			'db_host'       =>      'localhost',
			'db_user'       =>      '',
			'db_pass'       =>      '',
			'db_db'         =>      '',
	);
	private $valid_domains = array('ircblog.net');
	
	public $mysql_link;
	public $mail_body = false;
	public $headers = array(
		'to'		=>	false,
		'from'		=>	false,
		'subject'	=>	false,
		'date'		=>	false);
	private $mail_body_text = '';
	
	private $channel_info = array(
		'channel_name'	=>	'',
		'channel_id'	=>	'');
		
	private $image_filename = false;
	private $image_data = false;
	public $images = array();
	
	public function __construct() {
		require_once 'Mail/mimeDecode.php';
		
		if ($this->mysql_link = mysql_connect($this->config['db_host'],$this->config['db_user'],$this->config['db_pass'])) {
				if (!mysql_select_db($this->config['db_db'])) {
						debug_log("MySQL select database failed:[".mysql_errno($mysql_link).": ".mysql_error($mysql_link)."]",true);
				}
		} else {
				debug_log("MySQL Connection failed:[".mysql_errno($mysql_link).": ".mysql_error($mysql_link)."]",true);
		}
	}

	function debug_log($string,$die = false) { 
		$console = false;
		$file = $this->config['log_file'];
		$str = time()."[MAIL] ". $string."\n";
		file_put_contents($file,$str,FILE_APPEND);
		if ($die) {
			die("ERROR: $string\n");
		}
		if ($console) {
			print $str;
		}
	}

	public function setMailBody($file,$type) {
		if ($type == "file") {
			if (file_exists($file)) {
					$this->mail_body = file_get_contents($file);
					return true;
			} else { 
				return false;
			}
		} elseif ($type == "stdin") {
			if (!empty($file)) {
				$this->mail_body = $file;
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}

	}
	private function getChannelFromMail() {
		
		if (!array_key_exists("to",$this->headers)) { return false; }
		if (FALSE !== ($vto = filter_var($this->headers['to'],FILTER_VALIDATE_EMAIL))) {
		
			list($user,$domain) = explode("@", $this->headers['to']);
			if (!in_array($domain,$this->valid_domains)) { return false; }

			// lookup if the user is linked to a channel and ifso get channel details;
			$query = sprintf("SELECT mail_link.channel_id,mail_link.address,channel.name FROM `mail_link` LEFT JOIN channel on channel.id = mail_link.channel_id WHERE address = '%s' LIMIT 1",mysql_real_escape_string($user));
			$this->debug_log("getChannelFromMail; $query");
			#print $query;
			if (FALSE !== ($res = mysql_query($query,$this->mysql_link))) {
				if (mysql_num_rows($res) == 1) {
					$tmp = mysql_fetch_assoc($res);
					$this->channel_info['channel_id'] = $tmp['channel_id'];
					$this->channel_info['channel_name'] = $tmp['name'];
					return true;
				} 
			}
		}
		return false;
	}
	public function parse() {
	
		if ($this->parseMail()) {
			// yay now we got headers.. lets see if we can place this mail..
			if (!$this->getChannelFromMail($this->channel_info)) {
				$this->debug_log("Couldnt find channel information.. so nooo idea where to added this image :(",true);
				exit(0);
			}
			//print_r($this->channel_info);
			
			// Check for duplicates in MySQL.
			foreach ($this->images as $imgid => $imgdata) {
				$this->debug_log("Duplicate check; img_hash:[".$imgdata['image_hash']."]");
				$q_check = sprintf("SELECT id,url,url_hash FROM images WHERE url_hash = '%s' LIMIT 1",mysql_real_escape_string($imgdata['image_hash']));
				if ($q_res = mysql_query($q_check,$this->mysql_link)) {
					if (($rnum = mysql_num_rows($q_res)) >= 1) {
						$this->debug_log("Duplicate check; img_hash:[".$imgdata['image_hash']."] already exists!");
						unset($this->images[$imgid]);
					}
				} else {
					$this->debug_log("MySQL query failed:[".mysql_errno($mysql_link).": ".mysql_error($mysql_link)."] query:[".$q_check."]",true);
				}
			}
			
			// yay guess we got results lets resize and insert em
			$img_ts = time();
			$savePath_thumb = sprintf("%s%s",$this->config['web_base_dir'].$this->config['thumb_dir'],date('Y/m',$img_ts));
			$savePath_full = sprintf("%s%s",$this->config['web_base_dir'].$this->config['full_dir'],date('Y/m',$img_ts));
			
			foreach ($this->images as $ikey => $idata) {
				// save it somewhere
				$tmpfile = tempnam("/tmp/","ircblog");
				$this->debug_log('DEBUG: tmpfile: '.$tmpfile);
				$image_extention =  pathinfo($idata['filename'],PATHINFO_EXTENSION);
				$imagename_tmp = md5(time().$idata['filename']);
				$imagename_thumb = $imagename_tmp."_thumb.".$image_extention;
				$imagename_full = $imagename_tmp."_full.".$image_extention;
				if (FALSE === file_put_contents($tmpfile,$idata['body'])) { 
					$this->debug_log('DEBUG: skipping image.. erreur');
					continue; 
				}
				
				// resize it..
				try {
				
					$tbn = new Imagick($tmpfile);
					$size_w = $tbn->getImageWidth();
					$size_h = $tbn->getImageHeight();
					$tbn->thumbnailImage(400,400,true);
					
					if (!file_exists($savePath_thumb)) { mkdir($savePath_thumb,0755,true); }
					$tbn->writeImage($savePath_thumb."/".$imagename_thumb);
					chmod($savePath_thumb."/".$imagename_thumb, 0664);
					
					// Save full image also for a change
					if (!file_exists($savePath_full)) { mkdir($savePath_full,0755,true); }
					if (FALSE === copy($tmpfile,$savePath_full."/".$imagename_full)) {
						$this->debug_log('copy; failed to copy file '.$imagename_full);
					}
					chmod($savePath_full."/".$imagename_full, 0664);

				} catch(ImagickException $e) {
					$this->debug_log('Imagick Error; '. $e->getMessage());
					$imagename_thumb = '';
				}
				
				// Remove tmp file.
				unlink($tmpfile);
				
				// Insert Image..
				//$this->debug_log("INSERT $imagename_thumb");
				
				// Insert into MySQL
				$image_context = sprintf("Subject: %s\nBody: %s\n",$this->headers['subject'],$this->mail_body_text);
				$image_poster = "-";
				
				$q_ins = sprintf("INSERT INTO images SET url='-', url_hash='%s', timestamp='%d', channel='%s', poster='%s', context='%s', full_name = '%s', thumb_name='%s', image_name='%s', chan_id = '%d', post_type = 'mail'",
					mysql_real_escape_string($idata['image_hash']),
					mysql_real_escape_string($img_ts),
					mysql_real_escape_string($this->channel_info['channel_name']),
					mysql_real_escape_string($image_poster),
					mysql_real_escape_string($image_context),
					mysql_real_escape_string($imagename_full),
					mysql_real_escape_string($imagename_thumb),
					mysql_real_escape_string($idata['filename']),
					$this->channel_info['channel_id']
				);
				#print $q_ins;
				
				if ($res = mysql_query($q_ins,$this->mysql_link)) {
					$this->debug_log("MySQL Image stored.. channelID[".$this->channel_info['channel_id']."]");
				} else {
					$this->debug_log("MySQL insert failed:[".mysql_errno($mysql_link).": ".mysql_error($mysql_link)."] query:[".$q_ins."]");
				}
				

			}
			
			
		}


	
	}
	private function parseMail() {
		if ($this->mail_body === FALSE) { return false; }
		
		$params['include_bodies'] = true;
		$params['decode_bodies']  = true;
		$params['decode_headers'] = true;
	
		$decoder = new Mail_mimeDecode($this->mail_body);
		$structure = $decoder->decode($params);
		
		if (is_object($structure)) {
			foreach ($structure as $ltype => $ldata) {

				if ($ltype == "headers") {
					if (count($ldata) > 0) {
						$this->headers = array_merge($this->headers,$ldata);
					}
				} elseif ($ltype == "parts") {
					foreach ($ldata as $pkey => $pdata) {
						if ($pdata->ctype_primary == "text" && $pdata->ctype_secondary == "plain" && !empty($pdata->body)) {
							$this->mail_body_text = substr($pdata->body,0,1000);
						} elseif ($pdata->ctype_primary == "image") {
							if (!empty($pdata->d_parameters['filename'])) {
								$this->images[$pdata->d_parameters['filename']]['filename'] = $pdata->d_parameters['filename'];
								$this->images[$pdata->d_parameters['filename']]['body'] = $pdata->body;
								$this->images[$pdata->d_parameters['filename']]['image_hash'] = md5($pdata->body);
							}
							
						
						}
					}
				
				}
			
			}
			// Did we get anything valid in the end?
			if (count($this->headers) > 0 && count($this->images) > 0) {
				return true;
			} else { return false; }
			
		} else {
			return false;
		}
	
	}
	

}



?>
