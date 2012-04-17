# muvandy-php

PHP Client library for working with [Muvandy](http://muvandy.com) API

## Installation

Download or clone a copy 

	git clone https://github.com/muvandy/muvandy-php.git

Place the muvandy-php/lib/muvandy.php file in a location where  PHP can locate it on a 'require'.

	require_once "lib/muvandy.php";

## Getting Variations

Initialize Muvandy class.

	<?php
		require_once "lib/muvandy.php";	
		$muvandy = new Muvandy('experiment-id', 'your-api-key', 'visitor-key');
	?>
	
Get the value by providing variable key and an optional default value. Default value will be displayed if value is not available.

	<?=
		$muvandy->get_variation('heading-1', '<h1>Welcome</h1>');
	?>

## Conversions

Just include the code below on your post submission or thank you page.
	
	<?php
		require_once "lib/muvandy.php";	
		
		Muvandy::convert("1.0", "experiment-id", 'your-api-key', 'visitor-key');
		
	?>