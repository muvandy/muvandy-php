<?php

	require_once "lib/muvandy.php";

	$muvandy = new MuvandyVisitor("mysite-home", 'your-api-key');
?>
<html>
	<body>
		<h1><?= $muvandy->version("coming-headline"); ?></h1>
		<ul>
			<?= $muvandy->version("bullet-1"); ?>
			<?= $muvandy->version("bullet-2"); ?>				
			<?= $muvandy->version("bullet-3"); ?>								
			<?= $muvandy->version("bullet-4"); ?>								
		</ul>

			<p>
				<?= $muvandy->version("call-to-action"); ?>								
				<input type="text" />
				<input type="button" value="<?= $muvandy->version("button"); ?>" />
			</p>
	</body>
</html>

