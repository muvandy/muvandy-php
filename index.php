<?php

	require_once "lib/muvandy.php";

	$muvandy = new Muvandy("experiment-id", 'your-api-key', 'visitor-key');
?>
<html>
	<body>
		<h1><?= $muvandy->get_variation("Headline", "<h1>Welcome</h1>"); ?></h1>
		<ul>
			<?= $muvandy->get_variation("Header Bullet 1"); ?>
			<?= $muvandy->get_variation("Header Bullet 2", "<li>Listing 2</li>"); ?>				
			<?= $muvandy->get_variation("Header Bullet 3", "<li>Listing 3</li>"); ?>								
			<?= $muvandy->get_variation("Header Bullet 4", "<li>Listing 4</li>"); ?>
		</ul>

			<p>
				<?= $muvandy->get_variation("call-to-action"); ?>								
				<input type="text" />
				<input type="button" value="<?= $muvandy->get_variation("button"); ?>" />
			</p>
	</body>
</html>

