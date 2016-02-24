<?php

// Include Header
require_once(dirname(__FILE__)."/includes/header.incl.php");

if (array_key_exists('page',$_REQUEST)) { $page = $_REQUEST['page']; } else { $page = "main"; }
if (array_key_exists('id',$_REQUEST)) { $id = $_REQUEST['id']; } else { $id = 0; }
$pageID = Util::GET('pageID');




if (Util::splitScreen($page)) { 
	print '<div id="content-2col">';
	print '<div id="media">';
} else {
	print '<div id="content-1col">';
}

if ($page == "main") {
	$fetchOptions = array('hide'=>true,'hidefailed'=>true,'hideSecret'=>true);
	$pageLimit = 10;
	if (is_numeric($pageID)) { $pageStart = $pageID*$pageLimit; } else { $pageStart = 0; }

	if ($ircblog->getImages($data,$fetchOptions,$pageStart,$pageLimit)) {
		foreach($data['result'] as $imgid => $imgdata) {
			print $ircblog->getImageHTML($imgdata,array('public'=>$imgdata['public'],'detailed'=>false));
			//print "<br />";
		}

		$pager = $ircblog->pager(10,$data['total_results'],"main/");
		print $pager;
	} else {
		print "no results";
	}
} elseif ($page == "detail") {
	if ($id > 0) {
	
		if ($ircblog->getImage($imgdata,$id)) {
		
			print $ircblog->getImageHTML($imgdata['result'],array('public'=>$imgdata['result']['public'],'detailed'=>true));
		} else {
			print "id not found.";
		}
	
	}
} elseif ($page == "search") {
	$fetchOptions = array('hide'=>true,'hidefailed'=>true);
	$pageLimit = 10;
	if (is_numeric($pageID)) { $pageStart = $pageID*$pageLimit; } else { $pageStart = 0; }
	$searchq = Util::POST("search");
	if ($searchq === false) {
		if (array_key_exists('searchq',$_SESSION)) { $searchq = $_SESSION['searchq']; }
	} else {
		if (ircblog::validateSearchInput($searchq)) { $_SESSION['searchq'] = substr($searchq,0,64); }
	}
	if (ircblog::validateSearchInput($searchq)) {
		if ($searchq !== false && !empty($searchq)) {
			if (preg_match("/[*]+/",$searchq)) {
				$fetchOptions['poster_wild'] = str_replace("*","%",$searchq);
			} else {
				$fetchOptions['poster'] = $searchq;
			}
			if ($ircblog->getImages($data,$fetchOptions,$pageStart,$pageLimit)) {
				if ($pageStart == 0) {
					print '<p class="searchresult">'.sprintf("Found <B>%d</B> matches for <B>%s</B><br>",$data['total_results'],$searchq)."</p>";
				}
				foreach($data['result'] as $imgid => $imgdata) {
					print $ircblog->getImageHTML($imgdata,array('public'=>$imgdata['public'],'detailed'=>false));
					print "<br />";
				}
				//print "sql:[".$data['query']."]";
				$pager = $ircblog->pager(10,$data['total_results'],"search/");
				print $pager;
			} else {
				Util::messagebox("no results");
			}
		} else {
			print "uhmm.. dunno what to search for doc..";
		}
	} else {
		Util::messagebox("Invalid search query..");
	}
} elseif ($page == "logout") {
	print "<br><br><br>";
	if ($user->isLoggedin()) {
		$user->logout();
		Util::messagebox("You have been logged out.");
	} else {
		Util::messagebox("You are not loggedin.");
	}
} elseif (in_array($page,$validPageIncludes)) {
	include(dirname(__FILE__)."/".$page.".php");
} else {
	//print_r($_REQUEST);
	Util::messagebox("Coming soon, through a favorite browser near you! ;)");
}
	if (Util::splitScreen($page)) {	
?>		
			</div>
		</div>		
		
		<div id="panel">
				<div id="channelonlysearch">
				Search a post on IRC Blog:
				</div>
			<div id="search-bar">
					<form action="/page/search" method="POST">
						<input type="text" placeholder="Username..." name="search"> <input type="Submit" value=">>" name="Submit"> <!--placeholder/value-->
					</form>
			</div>

			<div id="top-users">
<!-- TOP for users including the CSS fix! (still need to be enable in the layout.css file!)
				<P class="top-title">TOP Users</P>
					<P class="top-name"><a href="/link-to-user-here" title="username">_username_</a></P>
						<table>
							<tr><td>PICdump</td><td>_count_</td></tr>
							<tr><td>GIFdump</td><td>_count_</td></tr>
							<tr><td>VIDdump</td><td>_count_</td></tr>
						</table>
										
					<P class="top-name"><a href="/link-to-user-here" title="username">_username_</a></P>
						<table>
							<tr><td>PICdump</td><td>_count_</td></tr>
							<tr><td>GIFdump</td><td>_count_</td></tr>
							<tr><td>VIDdump</td><td>_count_</td></tr>
						</table>
					
					<P class="top-name"><a href="/link-to-user-here" title="username">_username_</a></P>
						<table>
							<tr><td>PICdump</td><td>_count_</td></tr>
							<tr><td>GIFdump</td><td>_count_</td></tr>
							<tr><td>VIDdump</td><td>_count_</td></tr>
						</table>
NEW FUNCTION FOR UPDATE SOON! --> 
			</div>
			<!-- TOP for channels including the CSS fix! (still need to be enable in the layout.css file!)
			<div id="top-channels">
				<P class="top-title">TOP Channels</P>
					<P class="top-name"><a href="/link-to-channel-here" title="channel">_channel_</a></P>
						<table>
							<tr><td>PICdump</td><td>_count_</td></tr>
							<tr><td>GIFdump</td><td>_count_</td></tr>
							<tr><td>VIDdump</td><td>_count_</td></tr>
						</table>
									
					<P class="top-name"><a href="/link-to-channel-here" title="channel">_channel_</a></P>
						<table>
							<tr><td>PICdump</td><td>_count_</td></tr>
							<tr><td>GIFdump</td><td>_count_</td></tr>
							<tr><td>VIDdump</td><td>_count_</td></tr>
						</table>
					
					<P class="top-name"><a href="/link-to-channel-here" title="channel">_channel_</a></P>
						<table>
							<tr><td>PICdump</td><td>_count_</td></tr>
							<tr><td>GIFdump</td><td>_count_</td></tr>
							<tr><td>VIDdump</td><td>_count_</td></tr>
						</table>				
			</div>
			-->
			<div id="validate">
				<a href="http://validator.w3.org/check?uri=referer"><img src="/images/valid-html.png" onmouseover="this.src='/images/valid-html-hover.png'" onmouseout="this.src='/images/valid-html.png'" alt="Valid HTML 4.01 Transitional" height="30" width="88"></a>
				<a href="http://jigsaw.w3.org/css-validator/check/referer"><img src="/images/valid-css.png" onmouseover="this.src='/images/valid-css-hover.png'" onmouseout="this.src='/images/valid-css.png'" alt="Valid CSS!" height="30" width="88"></a>
   			</div>
		</div>	
<?php
	} else {
		print "</div>";
	}
	

// Include Footer
require_once(dirname(__FILE__)."/includes/footer.incl.php");

?>
