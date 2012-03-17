<?php

	require_once "lib/muvandy.php";

	$muvandy = new Muvandy("experiment-id", 'your-api-key');
?>
<html>
	<body>
		<h1><?= $muvandy->visitor_value("Headline", "<h1>Welcome</h1>"); ?></h1>
		<ul>
			<?= $muvandy->visitor_value("Header Bullet 1"); ?>
			<?= $muvandy->visitor_value("Header Bullet 2", "<li>Listing 2</li>"); ?>				
			<?= $muvandy->visitor_value("Header Bullet 3", "<li>Listing 3</li>"); ?>								
			<?= $muvandy->visitor_value("Header Bullet 4", "<li>Listing 4</li>"); ?>
		</ul>

			<p>
				<?= $muvandy->visitor_value("call-to-action"); ?>								
				<input type="text" />
				<input type="button" value="<?= $muvandy->visitor_value("button"); ?>" />
			</p>
	</body>
</html>

