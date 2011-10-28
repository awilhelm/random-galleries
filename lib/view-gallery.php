<?php

$p = new HTMLPrinter;

# Récupère la liste des images de la galerie sélectionnée.
$pool = parse_ini_file($config['pool-path'], true);
$pool = $pool[$_REQUEST['gallery']];

# Récupère la description de la galerie sélectionnée.
$description = parse_ini_file($config['descriptions-path'], true);
$description = @$description[$_REQUEST['gallery']];

# Construit une liste d'images aléatoires.
$list = array();
foreach(array_rand($pool, min($config['gallery']['count'], count($pool))) as $hash)
	$list[] = array(
		'image' => "${config['images-path']}/${pool[$hash]}",
		'thumbnail' => get_thumbnail($hash, 'gallery')
	);

# Mélange la liste des images.
shuffle($list);

?>



<!doctype html public "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title><?=@$description['name']?></title>
		<link href="<?=$config['install-path']?>/style.css" rel="stylesheet" type="text/css">
		<script src="<?=$config['install-path']?>/script.js" type="application/javascript"></script>
	</head>
	<body>
		<div id="gallery">
			<? foreach($list as $item) { ?>
				<a href="?image=<?=$item['image']?>" onclick="return show(this)">
					<img src="<?=$item['thumbnail']?>">
				</a>
			<? } ?>
		</div>
		<h1><?=@$description['name']?></h1>
		<p><?=@$description['description']?></p>
		<img id="image">
	</body>
</html>

