<?php

$p = new TextPrinter;

$path = $config['images-path'];
$queue = array('');
$pool = array();

# Pour chaque répertoire...
while(count($queue)) {
	$dir = array_shift($queue);

	# Pour chaque fichier dans ce répertoire...
	foreach(scandir("$path/$dir") as $file) {
		if($file[0] == '.') continue;
		if(strlen($dir)) $file = "$dir/$file";
		switch(true) {

			# Si c'est un sous-répertoire, on l'explore récursivement.
			case is_dir("$path/$file"):
				array_push($queue, $file);
				break;

			# Si c'est une image, on l'ajoute à la liste.
			case @getimagesize("$path/$file"):
				$pool[$dir][$file] = fileinode("$path/$file");
				break;
		}
	}
}

# Crée un fichier pour contenir la liste des images.
@mkdir(dirname($config['pool-path']), 0777, true);
$fd = new File($config['pool-path']);

# Écrit la liste des images dans le format INI.
foreach($pool as $dir => $files) {
	$fd->write("\n[$dir]\n");
	foreach($files as $file => $hash) $fd->write("$hash = '$file'\n");
}

?>
