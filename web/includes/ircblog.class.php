<?php

class ircblog {

	public $mysql_link;

	public function __construct($mysql_link) {
		$this->mysql_link = $mysql_link;
	}

	public function getImages(&$data,$search = array(),$start = 0,$limit = 10,$order = "DESC") {
		
		$sql_search = '';
		$sql_join = '';
		$sql_select = '';
		if (count($search) > 0) {
			$sql_searchw = array();
			if (array_key_exists('networkID',$search) && array_key_exists('channelID',$search)) {
				#$sql_join = "LEFT JOIN channel on images.chan_id = channel.id LEFT JOIN network on channel.network_id = network.id";
				$sql_searchw[] = sprintf("channel.id = '%d'", mysql_real_escape_string($search['channelID']));
				$sql_searchw[] = sprintf("channel.network_id = '%d'", mysql_real_escape_string($search['networkID']));
				$sql_select = ",channel.name as channel_name, channel.id as channel_id, network.name as network_name, network.id as network_id";
			} 
			$sql_join = "LEFT JOIN channel on images.chan_id = channel.id LEFT JOIN network on channel.network_id = network.id";
			$sql_select .= ",channel.public as public";
			if (array_key_exists('hideSecret',$search) && $search['hideSecret'] == true) { $sql_searchw[] = "channel.secret = '0'"; }
			if (array_key_exists('poster',$search)) { $sql_searchw[] = sprintf("images.poster = '%s'", mysql_real_escape_string($search['poster'])); }
			if (array_key_exists('poster_wild',$search)) { $sql_searchw[] = sprintf("images.poster LIKE '%s'", mysql_real_escape_string($search['poster_wild'])); }
			if (array_key_exists('hidefailed',$search) && $search['hidefailed'] == true) { $sql_searchw[] = "images.thumb_name != ''"; }
			if (array_key_exists('hide',$search) && $search['hide'] == true) { $sql_searchw[] = "images.hide != '1'"; }
			#if (array_key_exists('hide_none_public_channel_mail_submit',$search) && $search['hide_none_public_channel_mail_submit'] == true) { $sql_searchw[] = "(images.post_type != 'mail' AND channel.public = '1')"; }
			if (count($sql_searchw) > 0) {
				$sql_search = "WHERE ".implode(" AND ",$sql_searchw);
			}
		}
		$g_sql = sprintf("SELECT SQL_CALC_FOUND_ROWS images.* %s FROM `images` %s %s ORDER by timestamp %s LIMIT %d,%d",
		$sql_select,
		$sql_join,
		$sql_search,$order,$start,$limit);
		#print $g_sql;
		
/*		
SELECT SQL_CALC_FOUND_ROWS images.*,
channel.name as channel_name,
channel.id as channel_id,
network.name as network_name,
network.id as network_id
FROM `images`
LEFT JOIN channel on images.chan_id = channel.id
LEFT JOIN network on channel.network_id = network.id
WHERE 
channel.id = '1' AND 
channel.network_id = '1' AND

images.thumb_name != '' AND 
images.hide != '1'

ORDER by timestamp %s LIMIT %d,%d
*/		
		
		$data = array(
			'query'		=>	$g_sql,
			'total_results'	=>	0,
		);
		if (FALSE !== ($res = mysql_query($g_sql,$this->mysql_link))) {
			if (mysql_num_rows($res) > 0) {
				while ($row = mysql_fetch_assoc($res)) {
					$data['result'][$row['id']] = $row;
				}
				$data['total_results'] = $this->getFoundRows();
			}
		}
		if (array_key_exists('result',$data)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function getImage(&$data,$imgid) {
		if (!is_numeric($imgid)) { return false; }
		$g_sql = sprintf("SELECT images.* , channel.public as public FROM `images` LEFT JOIN channel on images.chan_id = channel.id LEFT JOIN network on channel.network_id = network.id WHERE images.id='%d' AND images.hide != '1' LIMIT 1",mysql_real_escape_string($imgid));
		$data = array('query' => $g_sql);
		if (FALSE !== ($res = mysql_query($g_sql,$this->mysql_link))) {
			if (mysql_num_rows($res) > 0) {
				while ($row = mysql_fetch_assoc($res)) {
					$data['result'] = $row;
				}
			}
		}
		if (array_key_exists('result',$data)) {
			return true;
		} else {
			return false;
		}		
	}
	
	public function getImageHTML($imageInfo,$opts = array('detailed'=>false)) {
		global $config;
		
		if (array_key_exists('detailed',$opts) && is_bool($opts['detailed'])) { $detailed = $opts['detailed']; } else { $detailed = false; }
		if (array_key_exists('public',$opts) && is_numeric($opts['public'])) { $public = ($opts['public'] == 1 ? true:false); } else { $public = true; }
		
		if (array_key_exists('base_url',$imageInfo)) { $base_url = $imageInfo['base_url']; } else { $base_url = "/view/"; }

		$data = "<div class=\"item\">\n";
		if (!$public) {
			$imageInfo['channel'] = "unknown";
			$imageInfo['poster'] = "Anonymous";
			$imageInfo['context'] = "";
		}

		$data .= sprintf("<div class=\"item-header\"><ul><li><a href=\"%s%s.html\" >%d</a> - by %s on %s</li><li class=\"time\">%s</li></ul></div>\n",
				$base_url,
				$imageInfo['id'],
				$imageInfo['id'],
				htmlentities($imageInfo['poster']),
				htmlentities($imageInfo['channel']),
				date('H:i d/m/Y ',$imageInfo['timestamp']));
		$data .= "<div class=\"item-content\">\n";			
		if ($detailed == false) {
			/*
			$data.= sprintf("\t<a href=\"%s%s.html\" title=\"%s\"><img src=\"%s\" border=\"0\" alt=\"%s\"></a>\n",
				$base_url,
				$imageInfo['id'],
				$imageInfo['image_name'],
				"/".$config['thumb_dir'].date("Y/m",$imageInfo['timestamp'])."/".$imageInfo['thumb_name'],
				$imageInfo['image_name']);
			*/
			//<a rel="example_group" href="./example/9_b.jpg" title="Lorem ipsum dolor sit amet"><img alt="" src="./example/9_s.jpg" /></a>
			if (empty($imageInfo['full_name'])) {
				if ($public) { 
					$imageInfo['full_image'] = $imageInfo['url'];
				} else {
					$imageInfo['full_image'] = "/images/img_not_available.png";
				}
			} else {
				$imageInfo['full_image'] = "/".$config['full_dir'].date("Y/m",$imageInfo['timestamp'])."/".$imageInfo['full_name'];
			}
			$data.= sprintf("\t<a rel=\"example_group\" href=\"%s\" title=\"%s\"><img src=\"%s\" border=\"0\" alt=\"%s\"></a>\n",
				$imageInfo['full_image'],
				$imageInfo['image_name'],
				"/".$config['thumb_dir'].date("Y/m",$imageInfo['timestamp'])."/".$imageInfo['thumb_name'],
				$imageInfo['image_name']);
				
		} else {
			$data.= sprintf("\t<img src=\"%s\" border=\"0\" alt=\"%s\">\n",
			"/".$config['thumb_dir'].date("Y/m",$imageInfo['timestamp'])."/".$imageInfo['thumb_name'],$imageInfo['image_name']);	
			
		}
		$data.= "</div>\n";
		$data .= sprintf("\t<div class=\"item-comment\">%s</div>",str_replace("\n","<br>",htmlentities($imageInfo['context'])) );

		if ($detailed) {
			if ($public){
				$data.= sprintf(
					"\t<div id=\"item-detail\"><table>
						<tr><td>Original name:</td><td>%s</td><tr>
						<tr><td>Original:</td><td><a href=\"%s\" target=\"_blanco\">*click*</a></td><tr>
					</table></div>",
				htmlentities($imageInfo['image_name']),
				htmlentities($imageInfo['url'])
				);
			} else {
				$data.= sprintf(
					"\t<div id=\"item-detail\"><table>
						<tr><td>Original name:</td><td>%s</td><tr>
					</table></div>",
				htmlentities($imageInfo['image_name'])
				);
			}
		}
		$data.= "</div>\n";
		
		return $data;
	}

	public static function pager($per_page, $total, $baselink, $reverse=false, $postfix="") {

		$max_page = ceil($total / $per_page);	// do not replace ceil by floor
		$cur_page = Util::GET("pageID") !== false ? Util::GET("pageID") : ($reverse ? $max_page - 1 : 0);	// if reverse default cur_page is max_page (that's the only difference for the pager)

		$string = "\n\n".'<div id="page-index">';
		$string .= "\n\t".'<a href="/' . $baselink . 'page/' . max($cur_page-1, 0) . $postfix . '" class="' . ($cur_page == 0 ? 'selected' : '') . '">&laquo; Prev</a>';
		
		$edgearound = 1;
		$midaround = 2;
		$strings = array();
		$toggle = true;
		for($i = 0; $i < $max_page; $i++) {
			if(!($i < $edgearound || $i > $max_page - 1 - $edgearound ||			// if start or end
				($i > $cur_page - $midaround && $i < $cur_page + $midaround))) {	// or around current page
				if($toggle) {
					$toggle = false;
					$strings[$i] = '&hellip;';								// just print some dots
				}
				continue;													// and continue
			}
			$toggle = true;
			$start = $i * $per_page + 1;
			$end = $start + $per_page - 1;
			
			$strings[$i] = "\n\t".'<a href="/' . $baselink . 'page/' . $i . $postfix . '" class="' . ($i == $cur_page ? 'selected' : '') . '">' . $start . ' - ' . min($end, $total) . '</a>';
		}
		$string .= " | " . implode(" | " , $strings) . " | ";
		$string .= "\n\t".'<a href="/' . $baselink . 'page/' . min(($cur_page+1), $max_page - 1) . $postfix . '" class="' . ($cur_page == $max_page - 1 ? 'selected' : '') . '">Next &raquo;</a>';
		$string .= "\n".'</div>';
		return $string;
	
	}
	
	
	public function getFoundRows() {
		$sql = "SELECT FOUND_ROWS() as count";
		if (FALSE !== ($res = mysql_query($sql,$this->mysql_link))) {
			$tmp = mysql_fetch_assoc($res);
			return $tmp['count'];
		}
	}
	public static function validateSearchInput($data) {
		if (strlen($data) < 4) { return false; }
		if (preg_match("/([\s]+)/",$data)) { return false; }
		
		return true;
	}
	
	// Functions mainly for channel/network 
	
	public function checkNetwork($name,&$info) {
		if (FALSE !== ($res = mysql_query("SELECT * FROM network WHERE name = '".mysql_real_escape_string($name)."' LIMIT 1",$this->mysql_link))) {
			if (mysql_num_rows($res) == 1) {
				$info = mysql_fetch_assoc($res);
				return true;
			} else { return false; }
		}
		return false;
	}
	public function checkChannel($name,&$info) {
		if (FALSE !== ($res = mysql_query("SELECT * FROM channel WHERE name = '#".mysql_real_escape_string($name)."' LIMIT 1",$this->mysql_link))) {
			if (mysql_num_rows($res) == 1) {
				$info = mysql_fetch_assoc($res);
				return true;
			} else { return false; }
		}
		return false;
	}
	
	public function checkChannelPassword($cNetwork,$cChannel) {
		$loggedin = false;
		if (!empty($cChannel['password'])) {
			$chanID = $cNetwork['id']."_".$cChannel['name'];
			if (array_key_exists('channelPW',$_SESSION) && array_key_exists($chanID,$_SESSION['channelPW']) && $_SESSION['channelPW'][$chanID] == $cChannel['password']) {
				// Valid Password... yay..
				$loggedin = true;
			} else {
				// seems no password is set but channel requires once.. so lets give the option to set one or check if it was send.
				if (array_key_exists('channel_pass',$_POST)) {
					if (substr($_POST['channel_pass'],0,32) == $cChannel['password']) {
						$_SESSION['channelPW'][$chanID] = $cChannel['password'];
						$loggedin = true; 
					}
				}
				if ($loggedin == false) {
					$pageURL = $_SERVER['REDIRECT_URL'];
					print '<br><br><br>';
					print '<div id="login_frame" align="center">';
					print '	<FORM action="'.$pageURL.'" method="post">';
					print '	<p class="login_f_header"> .: Channel is password protected :.</p>';
					print '	<p class="login_f_body">Password:  <INPUT size="15" type="password" name="channel_pass" value=""> </p>';
					print '	<p class="login_f_footer"><INPUT type="submit" value="login"></p>';
					print '	</FORM>';
					print '</div>';
					
					$loggedin = false;
				}
				
			}
			return $loggedin;
		} else {
			// No password set.. so lets continue;
			return true;
		}
	}

}

class Util {

	public static function GET($key) {
		return isset($_GET[$key]) ? $_GET[$key] : false;
	}

	public static function POST($key) {

		return isset($_POST[$key]) ? $_POST[$key] : false;
	}
	public static function messagebox($text) {
		#print '<div id="messagebox_frame" >';
		print '<p class="messagebox_text">'.$text.'</p>';
		#print '</div>';	
	}
	public static function splitScreen($page) {
		$splitScreen = array(
			'main',
			'search',
			'detail'
		);
		if (in_array($page,$splitScreen)) {
			return true;
		} else {
			return false;
		}
	}
}

?>
