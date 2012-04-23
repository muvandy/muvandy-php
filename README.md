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

A 'visitor_key' is required. By default, we recommend using the visitor's IP address but if you have other information on them their account id or email address make good unique visitor identifiers.
	
Get the value by providing variable key and a fallback text. Fallback text will be displayed if in case muvandy returns an error for the variable.

	<?=
		$muvandy->get_variation('heading-1', '<h1>Welcome</h1>');
	?>

## Conversions

Just include the code below on your post submission or thank you page.
	
	<?php
		require_once "lib/muvandy.php";	
		
		Muvandy::convert("1.0", "experiment-id", 'your-api-key', 'visitor-key');
		
	?>