<?php
	/* Used by default for all pages to get the same body */

	require_once(dirname(__FILE__)."/config.inc.php");
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<!-- NEW LAYOUT FOR IRCBLOG.NET BY BOUDY ::06-08-2011:: -->
<html>
<head>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
	<title>IRC Blog</title>
	<link rel="shortcut icon" href="http://ircblog.net/favicon.png" type="image/png">
	<link rel="icon" href="http://ircblog.net/favicon.png" type="image/png">
	<link type="text/css" rel="stylesheet" href="/layout.css">
	
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
	<script type="text/javascript" src="/fancybox/jquery.mousewheel-3.0.4.pack.js"></script>
	<script type="text/javascript" src="/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
	<link rel="stylesheet" type="text/css" href="/fancybox/jquery.fancybox-1.3.4.css" media="screen">
	
	<script type="text/javascript">

	  var _gaq = _gaq || [];
	  _gaq.push(['_setAccount', 'UA-16891239-1']);
	  _gaq.push(['_trackPageview']);

	  (function() {
	    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();

	$(document).ready(function() {
		$("a[rel=example_group]").fancybox({
			'transitionIn'		: 'none',
			'transitionOut'		: 'none',
			'titlePosition' 	: 'over',
			'titleFormat'		: function(title, currentArray, currentIndex, currentOpts) {
				return '<span id="fancybox-title-over">Image ' + (currentIndex + 1) + ' / ' + currentArray.length + (title.length ? ' &nbsp; ' + title : '') + '</span>';
			}
		});

	});
	</script>
</head>
<body>
	<div id="container">
		<div id="header">
			<ul>
				<li id="sitetitle"><a href="/" title="IRC Blog">IRC Blog</a></li>
				<li><a href="/rss/" title="RSS">RSS</a></li>
				<li>|</li>
				<li><a href="/page/about" title="About">About</a></li>
				<li>|</li>
				<li><a href="/page/channels" title="Channels">Channels</a></li>
			</ul>
		</div>
		



