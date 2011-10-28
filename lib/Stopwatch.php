<?php

## Copyright 2010 Alexis Wilhelm
## This program is Free Software under the Elvis Presley Licence:
## You can do anything, but lay off of my blue suede shoes.

## Une classe pour mesurer le temps d'exécution d'une portion de code.

class Stopwatch {

	private $begin;

	public function __construct() {
		$this->begin = microtime(true);
	}

	public function __destruct() {
		$date = date('H:i:s');
		$duration = microtime(true) - $this->begin;
		$unit = $duration < 2 ? 'seconde' : 'secondes';
		echo "\n<!-- exécuté à $date en $duration $unit -->\n";
	}
}

?>
