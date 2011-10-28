<?php

$p = new HTMLPrinter;

?>



<!doctype html public "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title></title>
		<link href="<?=$config['install-path']?>/style.css" rel="stylesheet" type="text/css">
	</head>
	<body>
		<img id="image" class="selected" src="<?=$_REQUEST['image']?>">
	</body>
</html>

