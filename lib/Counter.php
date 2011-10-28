<?php

## Copyright 2010 Alexis Wilhelm
## This program is Free Software under the Elvis Presley Licence:
## You can do anything, but lay off of my blue suede shoes.

## Une classe pour mesurer l'activité du site.

class Counter {

	public function __destruct() {
		global $config;
		$fd = new File($config['stats-path'] . date('.ym'), 'a');
		@$fd->write(sprintf("%d\t%s\t%s\t%s\n",
			$_SERVER['REQUEST_TIME'],
			$_SERVER['REMOTE_ADDR'],
			$_SERVER['REQUEST_URI'],
			$_SERVER['HTTP_REFERER']));
	}
}

?>
