<?php

$p = new HTMLPrinter;

# Récupère la liste des galeries.
$pool = parse_ini_file($config['pool-path'], true);

# Récupère les descriptions des galeries.
$description = parse_ini_file($config['descriptions-path'], true);

# Construit une liste de galeries aléatoires.
$list = array();
foreach(array_rand($pool, min($config['collection']['count'], count($pool))) as $dir) {
	$keys = array_keys($pool[$dir]);
	$hash = $keys[array_rand($keys)];
	$list[] = array(
		'gallery' => $dir,
		'thumbnail' => get_thumbnail($hash, 'collection'),
		'name' => @$description[$dir]['name']
	);
}

# Mélange la liste des galeries.
shuffle($list);

?>



<!doctype html public "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>Mes galeries aléatoires</title>
		<link href="<?=$config['install-path']?>/style.css" rel="stylesheet" type="text/css">
	</head>
	<body>
		<h1>Mes galeries aléatoires</h1>
		<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit.</p>
		<div id="collection">
			<? foreach($list as $item) { ?>
				<a href="?gallery=<?=$item['gallery']?>" title="<?=@$item['name']?>">
					<img src="<?=$item['thumbnail']?>">
				</a>
			<? } ?>
		</div>
	</body>
</html>

