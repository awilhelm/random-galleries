<?php

## Copyright 2010 Alexis Wilhelm
## This program is Free Software under the Elvis Presley Licence:
## You can do anything, but lay off of my blue suede shoes.

## Une classe pour mettre en forme automatiquement le code HTML.

class HTMLPrinter {

	public function __construct() {
		header('content-type: text/html; charset=UTF-8');
		ob_start();
	}

	public function __destruct() {
		$page = ob_get_contents();
		ob_end_clean();
		echo preg_replace(array('/>\s+</', '/^\s+/', '/\s+$/'), array('><', '', ''), $page);
	}
}

?>
