<?php

# Charge automatiquement les classes à la demande.
function __autoload($class) {
	global $config;
	require_once "${config['install-path']}/$class.php";
}

# Choisit le script à exécuter pour satisfaire la requête de l'utilisateur.
function branch() {
	switch(true) {
		case isset($_REQUEST['do']): return $_REQUEST['do'];
		case isset($_REQUEST['image']): return 'view-image';
		case isset($_REQUEST['gallery']): return 'view-gallery';
		default: return 'view-collection';
	}
}

# Construit le chemin d'accès à une miniature d'une image pour un contexte donné.
function get_thumbnail($hash, $context) {
	global $config;
	$path = $config['thumbnails-path'];
	$width = $config[$context]['width'];
	$height = $config[$context]['height'];
	return "$path/${width}x${height}/$hash.png";
}

# Fonction principale (juste pour tricher sur l'appel des destructeurs).
function main() {
	global $config;

	# Mesure le temps mis pour générer la page.
	$sw = new Stopwatch;

	# Enregistre des statistiques à propos de la page courante.
	$stats = new Counter;

	# Fait appel au script adapté pour satisfaire la requête de l'utilisateur.
	require_once $config['install-path'] . '/' . branch() . '.php';
}

# Lit le fichier de configuration.
$config = parse_ini_file('config.ini', true);

# Démarre le script.
main();

?>
