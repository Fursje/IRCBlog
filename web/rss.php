<?php

header("content-type: application/xml");

/* Simpel rss feed. */

require_once(dirname(__FILE__)."/includes/config.inc.php");

$lastBuildDate = date('r',time());
$head = <<< EOH
<?xml version="1.0"?>
<rss version="0.91">
<channel>
	<title>IRC Blog</title>
	<link>http://www.ircblog.net/</link>
	<description>IRC Blog</description>
	<language>en-en</language>
	<managingEditor>webmaster@ircblog.net (Editor)</managingEditor>
	<webMaster>webmaster@ircblog.net (Webmaster)</webMaster>
	<generator>IRC Blog RSS Generator v1.0</generator>
	<lastBuildDate>$lastBuildDate</lastBuildDate>
EOH;

print $head;

$g_sql = "SELECT images.*, channel.public FROM images LEFT JOIN channel on images.chan_id = channel.id LEFT JOIN network on channel.network_id = network.id WHERE thumb_name != '' AND channel.public = '1' ORDER BY `timestamp` DESC LIMIT 10";
if ($res = mysql_query($g_sql,$mysql_link)) {
	while ($row = mysql_fetch_assoc($res)) {
		print "<item>\n";
		print sprintf("<title>%s %s by (%s)</title>\n",$row['channel'],$row['image_name'],$row['poster']);
		$img_url = "http://".$config['domain']."/".$config['thumb_dir'].date('Y/m',$row['timestamp'])."/".$row['thumb_name'];
//		print sprintf("<description><![CDATA[%s &lt;br /&gt; %s]]></description>\n",htmlspecialchars("<img src=\"".$img_url."\" border=\"0\" alt=\"\">"),str_replace(array("\n","<","","",""),array("&lt;br&gt;","","","",""),$row['context']) );
//		print sprintf("<description>%s &lt;br /&gt; %s</description>\n",htmlspecialchars("<img src=\"".$img_url."\" border=\"0\" alt=\"\">"),str_replace(array("\n","<","","",""),array("&lt;br&gt;","","","",""),$row['context']) );
		$saveContext = str_replace(array("&","<",">","]","","",""),array("&#x26;","&#x3C;","&#62;","&#93;","","",""),$row['context']);
		print sprintf("<description><![CDATA[%s <br /> %s]]></description>\n","<img src=\"".$img_url."\" border=\"0\" alt=\"\">",str_replace(array("\n","","",""),array("<br/>","","",""),$saveContext)  );
		print sprintf("<link>%s</link>\n","http://".$config['domain']."/view/".$row['id'].".html");
		print sprintf("<pubDate>%s</pubDate>\n",date('r',$row['timestamp']));
		print "</item>\n";
	}
}

print "</channel>\n</rss>\n";

?>