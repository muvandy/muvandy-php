<?php

	require_once "muvandy/muvandy.php";

	if ( isset($_SERVER["REMOTE_ADDR"]) ) {
		$ip = $_SERVER["REMOTE_ADDR"] . ' ';
	} else if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ){
		$ip = $_SERVER["HTTP_X_FORWARDED_FOR"] . ' ';
	} else if ( isset($_SERVER["HTTP_CLIENT_IP"]) ){
		$ip = $_SERVER["HTTP_CLIENT_IP"] . ' ';
	}
	
	$slugs = array("bullet-1", "bullet-2", "bullet-3", "bullet-4", "call-to-action", "button");
	
	// Initialize visitor class
	$v = Muvandy::visitor_init('mysite-home', $ip, $_SERVER['HTTP_REFERER'], $_REQUEST, $slugs);

	if ($v) {
		foreach ($slugs as $slug){
			echo $v->variable_version($slug)."<br/>";
		}
	
	}
?>

