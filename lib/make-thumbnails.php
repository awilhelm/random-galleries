<?php

$p = new TextPrinter;

# Récupère la liste complète des images disponibles.
$pool = parse_ini_file($config['pool-path'], false);
$path = $config['images-path'];

# Pour chaque contexte nécessitant des miniatures...
# (représentés par des sections dans config.ini)
foreach($config as $context => $prefs) {
	if(!is_array($prefs)) continue;

	# Crée un répertoire pour accueillir les miniatures.
	@mkdir(dirname(get_thumbnail('dummy', $context)), 0777, true);

	# Construit le miniature-o-tron.
	$thumbnailer = new Thumbnailer($prefs['width'], $prefs['height'], $config['thumbnails-quality']);

	# Pour chaque image dans la liste...
	foreach($pool as $hash => $file) {

		# Construit le chemin d'accès à la miniature.
		$thumbnail = get_thumbnail($hash, $context);

		# Crée une miniature pour ce fichier s'il n'en existe pas déjà une.
		if(!file_exists($thumbnail) or filemtime($thumbnail) < filemtime("$path/$file")) {
			$image = $thumbnailer->process("$path/$file");
			imagepng($image, $thumbnail, 9);
		}
	}
}

?>
