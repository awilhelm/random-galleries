<?php

$p = new TextPrinter;

# Récupère la liste des fichiers présents en théorie dans les répertoires des miniatures.
$pool = array_keys(parse_ini_file($config['pool-path'], false));
foreach($pool as &$hash) $hash = "$hash.png";

# Dans chaque sous-répertoire du répertoire des miniatures...
$path = $config['thumbnails-path'];
foreach(scandir($path) as $dir) {
	if($dir[0] == '.' or !is_dir("$path/$dir")) continue;

	# Supprime les miniatures qui ne correspondent pas à une image existante.
	foreach(array_diff(scandir("$path/$dir"), $pool) as $file) {
		if($file[0] == '.') continue;
		unlink("$path/$dir/$file");
	}
}

?>
