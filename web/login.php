<?

/* Disable direct access to the page */
if (!defined('PAGE_INCLUDED')) {
	header('Location: /');
}

$f_user = Util::POST("f_user");
$f_pass = util::post("f_pass");
$loggedin = false;

if ($f_user && $f_pass ) {
	if ($user->login($f_user,$f_pass)) {
		$loggedin = true;
	}
}
print "<br><br><br>";

if ($loggedin == false) {
?>
<div id="login_frame" align="center">
	<FORM action="/page/login" method="post">
	<p class="login_f_header"> .: Login :.</p>
	<p class="login_f_body">Username:  <INPUT size="15" type="text" name="f_user" value="<?=$f_user?>"> </p>
	<p class="login_f_body">Password:  <INPUT size="15" type="password" name="f_pass" value=""> </p>
	<p class="login_f_footer"><INPUT type="submit" value="login"></p>
	</FORM>
</div>
<?

} else {
	Util::messagebox("You have been logged in.");
}

?>