<?php

	require_once "lib/muvandy.php";

	$muvandy = new MuvandyVisitor("mysite-home", 'your-api-key', true);
	$muvandy->convert("49.99");
?>
<html>
	<body>
		<h1>Thanks for subscribing!</h1>
	</body>
</html>