<?php

## Copyright 2010 Alexis Wilhelm
## This program is Free Software under the Elvis Presley Licence:
## You can do anything, but lay off of my blue suede shoes.

## Une classe pour écrire dans des fichiers.

class File {

	private $fp;

	public function __construct($file, $method = 'w') {
		$this->fp = fopen($file, $method);
		flock($this->fp, LOCK_EX);
	}

	public function __destruct() {
		fflush($this->fp);
		flock($this->fp, LOCK_UN);
		fclose($this->fp);
	}

	public function write($text) {
		fwrite($this->fp, $text);
	}
}

?>
