<?php

require_once(dirname(__FILE__).'/include/config.incl.php');


if (count($config['files']) == 0) { die('Config fail..\n'); }

foreach ($config['files'] as $cname => $cdata) {
	if (array_key_exists($cname,$file_locations)) {
		if (!is_numeric($file_locations[$cname])) { $file_locations[$cname] = 1; }
	}
	else { $file_locations[$cname] = 1; }

	if ($ft->add($cname,$cdata['file'],$file_locations[$cname])) {
		$img_found = 0;
		$img_cnt = 0;
		$found_images[$cname] = array();
		$img_hashlist = array();
		$prevline = false;
		while(($ft->fetch($cname,$row))) {
			/*if ($img_cnt >= 100) { 
				//debug stop
				file_put_contents("/tmp/yay.log",print_r($found_images,TRUE));
				die();
			}
			*/
			// DEBUG IRSSI CODE
			#if (parseLogLine(trim($row),$cdata['logtype'],$cname,$mline)) {
			#	print "CHANNEL: [$cname]\n";
			#	print_r($mline);
			#}
			
			#if (preg_match("/^([0-9]{10})\s:(.*)!(.*)@(.*)\sPRIVMSG\s([\S.*]{1,})\s:(.*)$/",trim($row),$mline)) {
			if (parseLogLine(trim($row),$cdata['logtype'],$cname,$mline)) {
				if (!preg_match("/^(".implode('|',$config['files'][$cname]['channels']).")$/i",$mline['channel'])) { continue; }
				if (preg_match("/^(".implode('|',$config['files'][$cname]['ignoreUsers']).")$/i",$mline['nick'])) { continue; }

				if ($img_found > 0) {
					$context_edit = $img_cnt-$img_found;
					for ($tmpc=1;$tmpc <=$img_found;$tmpc++) {
						#$found_images[$cname][$context_edit]['context'] .= "<".$mline['2']."> ".str_replace('ACTION','',$mline['6'])."\n";
						$found_images[$cname][$context_edit]['context'] .= $mline['t_line']."\n";
						$context_edit++;
					}
					$img_found = 0;
				}
				
				#if (preg_match_all("/(http?\:\/\/[\S.*]{1,}\/([\S.*]{1,}\.(jpg|jpeg|gif|bmp|png|tif|tiff)))+/i",$mline['6'],$murl)) {
				if (preg_match_all("/(https?\:\/\/[\S.*]{1,}\/([\S.*]{1,}\.(jpg|jpeg|gif|bmp|png|tif|tiff)))+/i",$mline['text'],$murl) && !preg_match("/(\bNBLOG\b|\bNB\b)/",$mline['text']) ) {
					#print_r($murl);
					$duplicated_cnt = 0;
					foreach ($murl[0] as $img_key => $img_url) {
						if (!in_array(md5($img_url),$img_hashlist)) {
							$found_images[$cname][$img_cnt]['img'] = $img_url;
							$found_images[$cname][$img_cnt]['img_host'] = parse_url($img_url,PHP_URL_HOST);
							$found_images[$cname][$img_cnt]['url_hash'] = md5($img_url);
							if ($prevline == false) {
								$found_images[$cname][$img_cnt]['context'] = $mline['t_line']."\n";
							} else {
								#$found_images[$cname][$img_cnt]['context'] = "<".$prevline['2']."> ".str_replace('ACTION','',$prevline['6'])."\n"."<".$mline['2']."> ".str_replace('ACTION','',$mline['6'])."\n";
								$found_images[$cname][$img_cnt]['context'] = $prevline['t_line']."\n".$mline['t_line']."\n";
							}
							$found_images[$cname][$img_cnt]['img_name'] = $murl[2][$img_key];
							$found_images[$cname][$img_cnt]['type'] = $murl[3][$img_key];
							$found_images[$cname][$img_cnt]['timestamp'] = $mline['timestamp'];
							$found_images[$cname][$img_cnt]['channel'] = $mline['channel'];
							$found_images[$cname][$img_cnt]['poster'] = $mline['nick'];
							$found_images[$cname][$img_cnt]['thumbnail'] = $found_images[$cname][$img_cnt]['url_hash']."_thumb.".$found_images[$cname][$img_cnt]['type'];
							$found_images[$cname][$img_cnt]['full_image'] = $found_images[$cname][$img_cnt]['url_hash']."_full.".$found_images[$cname][$img_cnt]['type'];
							$img_hashlist[] = $found_images[$cname][$img_cnt]['url_hash'];
							$img_cnt++;
						} else {
							$duplicated_cnt++;
						}
					}
					$img_found = count($murl[0])-$duplicated_cnt;
				}
				$prevline = $mline;
			}
		}
		// Get Loc
		$file_locations[$cname] = $ft->handle[$cname]['loc'];
		
		debug_log("LogScan[$cname]: Found images:[".count($found_images[$cname])."]");
		//fix imgurl.com images.
		//todo
		
		// Check for duplicates in MySQL.
		foreach ($found_images[$cname] as $imgid => $imgdata) {
			debug_log("Duplicate & ignored host check; img_hash:[".$imgdata['url_hash']."] img_host:[".$imgdata['img_host']."]");
			$q_check = sprintf("SELECT id,url,url_hash FROM images WHERE url_hash = '%s' LIMIT 1",mysql_real_escape_string($imgdata['url_hash']));
			if ($q_res = mysql_query($q_check,$mysql_link)) {
				if (($rnum = mysql_num_rows($q_res)) >= 1) {
					debug_log("Duplicate check; img_hash:[".$imgdata['url_hash']."] already exists!");
					unset($found_images[$cname][$imgid]);
				}
			} else {
				debug_log("MySQL query failed:[".mysql_errno($mysql_link).": ".mysql_error($mysql_link)."] query:[".$q_check."]",true);
			}
			if (in_array($imgdata['img_host'],$config['ignored_hosts'])) {
				debug_log("Invalid hosts check; img_hash:[".$imgdata['url_hash']."] img_host:[".$imgdata['img_host']."]");
				unset($found_images[$cname][$imgid]);
			}
		}
		// If no duplicated anymore start getting the images.
		$valid_images = array(
			'content_types'	=>	array(
				'image/jpeg',
				'image/png',
				'image/gif',
			),
			'http_codes'	=>	array(
				'200'
			),	
		);
		foreach ($found_images[$cname] as $imgid => $imgdata) {

			if (getFile($imgdata['img'],$data)) {
				$logline = sprintf("name:[%s] file:[%s] content_type:[%s] http_code:[%s] size:[%s] time:[%s] url:[%s]",$imgdata['img_name'],$data['tmpfile'],$data['content_type'],$data['http_code'],$data['download_content_length'],$data['total_time'],$data['url']);
				debug_log("getFile: $logline");
				
				// IF only valid images left, so only http_code 200 and content_type = image/*
		

				if (in_array($data['http_code'],$valid_images['http_codes']) && in_array($data['content_type'],$valid_images['content_types'])) {
					$savePath = sprintf("%s%s",$config['web_base_dir'].$config['thumb_dir'],date('Y/m',$imgdata['timestamp']));
					$symlink_path = sprintf("../../../../%s%s/%s",$config['thumb_dir'],date('Y/m',$imgdata['timestamp']),$imgdata['thumbnail']);
					$savePath_full = sprintf("%s%s",$config['web_base_dir'].$config['full_dir'],date('Y/m',$imgdata['timestamp']));
					if ($data['content_type'] == "image/gif") {
						if (filesize($data['tmpfile']) < 15728640) {
							if (FALSE === copy($data['tmpfile'],$savePath."/".$imgdata['thumbnail'])) {
								debug_log('copy; failed to copy file '.$data['tmpfile'].'');
							} else {
								// create for now symlink to the full version so no duplicates exist.
								if (!symlink($symlink_path,$savePath_full."/".$imgdata['full_image'])) {
									debug_log("symlink; failed to create symlink.");
								}
							}
						} else { 
							debug_log('imageSize('.$imgdata['img_name'].'); the '.$data['content_type'].' image is bigger then 15mb');
						}
					} else {
						try {
						
							$tbn = new Imagick($data['tmpfile']);
							$size_w = $tbn->getImageWidth();
							$size_h = $tbn->getImageHeight();
							$tbn->thumbnailImage(400,400,true);
							
							if (!file_exists($savePath)) {
								mkdir($savePath,0755,true);
							}
							$tbn->writeImage($savePath."/".$imgdata['thumbnail']);

							// Save full image also for a change
							if (!file_exists($savePath_full)) { mkdir($savePath_full,0755,true); }
							if (FALSE === copy($data['tmpfile'],$savePath_full."/".$imgdata['full_image'])) {
								$this->debug_log('copy; failed to copy file '.$imgdata['full_image']);
							}
							chmod($savePath_full."/".$imgdata['full_image'], 0664);
							
						} catch(ImagickException $e) {
							debug_log('Imagick Error; '. $e->getMessage());
							$imgdata['thumbnail'] = '';
						}
					}
				
				} else {
					debug_log("ValidateFile; image not available anymore.");
					$imgdata['thumbnail'] = '';
				}
				// Remove tmp file.
				unlink($data['tmpfile']);
				
				// Fetch ChannelID (must be cached at some point...
				$channelID = getChannelID($cdata['networkID'],$imgdata['channel']);
				
				// Insert into MySQL
				$q_ins = sprintf("INSERT INTO images SET url='%s', url_hash='%s', timestamp='%d', channel='%s', poster='%s', context='%s', thumb_name='%s', full_name='%s', image_name='%s', chan_id = '%d', img_host='%s'",
					mysql_real_escape_string($imgdata['img']),
					mysql_real_escape_string($imgdata['url_hash']),
					mysql_real_escape_string($imgdata['timestamp']),
					mysql_real_escape_string($imgdata['channel']),
					mysql_real_escape_string($imgdata['poster']),
					mysql_real_escape_string($imgdata['context']),
					mysql_real_escape_string($imgdata['thumbnail']),
					mysql_real_escape_string($imgdata['full_image']),
					mysql_real_escape_string($imgdata['img_name']),
					$channelID,
					mysql_real_escape_string($imgdata['img_host'])
				);
				if ($res = mysql_query($q_ins,$mysql_link)) {
					debug_log("MySQL Image stored.. url_hash:[".$imgdata['url_hash']."] networkID[".$cdata['networkID']."] channelID[$channelID]");
				} else {
					debug_log("MySQL insert failed:[".mysql_errno($mysql_link).": ".mysql_error($mysql_link)."] query:[".$q_ins."]");
				}
				
			} else {
				$logline = sprintf("getFile: Error:[%s]",$data);
			}
		
		}
		

		
		// Make thumbails rfom the fetches images
		// Move tumbnail to web folder && insert in mysql
		
		// Cleanup.
		// Save location.
		
		SaveLoc($config['base_dir'].$config['loc_file'],$file_locations);
		
	}
	
}


function SaveLoc($locfile,$file_locations) {
	if (is_array($file_locations)) {
		if (!file_put_contents($locfile,serialize($file_locations))) {
			debug_log('SaveLoc: Write erreur.',true);
		}
	
	}

}

function ReadLoc($locfile,&$file_locations) {
	if (is_readable($locfile)) {
		if ($tmp = file_get_contents($locfile)) {
			if (!$file_locations = unserialize($tmp)) { 
				$file_locations = array();
				debug_log('ReadLoc unable to read stored data, starting from the beginning of the file :(');
			}
		} else {
			debug_log('ReadLoc failed..');
		}
	} else {
		$file_locations = array();
	}
}

function debug_log($string,$die = false) {
	global $config;
	$console = false;
	$file = $config['base_dir'].$config['log_file'];
	$str = time()." ". $string."\n";
	file_put_contents($file,$str,FILE_APPEND);
	if ($die) {
		die("ERROR: $string\n");
	}
	if ($console) {
		print $str;
	}
}
function getChannelID($networkID,$chanName) {
	global $mysql_link;
	$sql = sprintf("SELECT id FROM channel WHERE network_id='%d' AND name='%s' LIMIT 1",$networkID,mysql_real_escape_string($chanName));
	if (FALSE !== ($res = mysql_query($sql,$mysql_link))) {
		if (mysql_num_rows($res) == 1) {
			$tmp = mysql_fetch_assoc($res);
			return $tmp['id'];
		} else {
			return 0;
		}
	}
	return 0;
}
function getFile($url,&$data) {
	$ch = curl_init();
	$tmpfile = tempnam('/tmp',"ircblog");
	$reqinfo = false;
	if (!$fh = fopen($tmpfile,'w')) { $data = "Error with creating tmpfile."; return false; }
	
    curl_setopt($ch, CURLOPT_FILE, $fh);
    curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_SSLVERSION,3);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,10);
	curl_setopt($ch, CURLOPT_TIMEOUT,30);

    curl_exec($ch);
	fclose($fh);
	
	if(!curl_errno($ch)) {
		$reqinfo = curl_getinfo($ch);
		$data = $reqinfo;
		$data['tmpfile'] = $tmpfile;
		curl_close($ch);
		return true;
	} else {
		$data = curl_error($ch);
		return false;
	}
}

function parseLogLine($line,$type,$channel,&$data = array()) {
	$data = array(
		'timestamp'	=>	'',
		'nick'		=>	'',
		'ident'		=>	'',
		'host'		=>	'',
		'channel'	=>	'',
		'text'		=>	''
	);

	$parse_regexs = array(
		'irssi'		=>	'/^(?<time>.*CET|.*CEST)\s(?<t_line>\<?(?<nick>.*)\>\s(?<text>.*)|\s\*\s(?<nick2>[\S.*]{1,})\s(?<text2>.*))$/',
		'debug_output'	=>	'/^(?<time>[0-9]{10})\s:(?<nick>.*)!(?<ident>.*)@(?<host>.*)\sPRIVMSG\s(?<channel>[\S.*]{1,})\s:(?<text>.*)$/'
	);
	if (preg_match($parse_regexs[$type],$line,$matches)) {
		if ($type == "debug_output") {
			$data['timestamp'] = $matches['time']; 
			$data['nick'] = $matches['nick'];
			$data['ident'] = $matches['ident'];
			$data['host'] = $matches['host'];
			$data['channel'] = $matches['channel'];
			if (preg_match("/^ACTION.*/",$matches['text'])) {
				$tmp_find = array('ACTION','');
				$tmp_repl = array('','');
				$data['text'] = str_replace($tmp_find,$tmp_repl,$matches['text']);
				$data['type'] = "action";
				$data['t_line'] = " * ".$data['nick']."".$data['text'];
			} else {
				$data['t_line'] = "<".$data['nick']."> ".$matches['text'];
				$data['text'] = $matches['text'];
				$data['type'] = "normal";
			}

		} elseif ($type == "irssi") {
			$tmp_date = date_parse($matches['time']);
			$data['timestamp'] = mktime($tmp_date['hour'],$tmp_date['minute'],$tmp_date['second'],$tmp_date['month'],$tmp_date['day'],$tmp_date['year']);
			$data['t_line'] = $matches['t_line'];
			$data['channel'] = "#".$channel;
			$tmp_nfind = array('+','@','~','%',' ');
			$tmp_nreplace = array('','','','','');
			if (!empty($matches['nick'])) {
					$data['nick'] = str_replace($tmp_nfind,$tmp_nreplace,$matches['nick']);
					$data['text'] = $matches['text'];
					$data['type'] = "normal";
			} else {
					$data['nick'] = str_replace($tmp_nfind,$tmp_nreplace,$matches['nick2']);
					$data['text'] = $matches['text2'];
					$data['type'] = "action";

			}

		} else {
			return false;
		}	

		return true;
	}
	
}
?>
