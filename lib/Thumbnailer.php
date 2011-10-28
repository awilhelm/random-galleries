<?php

## Copyright 2010 Alexis Wilhelm
## This program is Free Software under the Elvis Presley Licence:
## You can do anything, but lay off of my blue suede shoes.

## Une classe pour construire des miniatures en recadrant sur les
## parties les plus intéressantes des images.

class Thumbnailer {

	public function __construct($Width, $Height, $Quality = 1) {
		$this->OutputWidth = $Width;
		$this->OutputHeight = $Height;
		$this->OutputRatio = $Width / $Height;
		$this->Quality = $Quality;
		$this->MapWidth = floor($Width * $this->Quality);
		$this->MapHeight = floor($Height * $this->Quality);
	}

	public function process($InputFile) {

		# Mesure l'image.
		list($InputWidth, $InputHeight, $InputType,,) = getimagesize($InputFile);
		$InputRatio = $InputWidth / $InputHeight;

		# Calcule la taille de la miniature.
		$OutputWidth = min($this->OutputWidth, $InputWidth);
		$OutputHeight = min($this->OutputHeight, $InputHeight);

		# Calcule la taille de la carte des énergies.
		$MapWidth = $InitialMapWidth = min($this->MapWidth, floor($InputWidth * $this->Quality));
		$MapHeight = $InitialMapHeight = min($this->MapHeight, floor($InputHeight * $this->Quality));

		# Initialise le jeu.
		$HorizontalPlay = 0;
		$VerticalPlay = 0;

		# Adapte toutes ces dimensions en fonction du rapport d'aspect.
		if($InputRatio < $this->OutputRatio) {

			# Cas où l'image est plus haute que la miniature.
			$OutputHeight = floor($OutputWidth / $InputRatio);
			$MapHeight = floor($MapWidth / $InputRatio);
			$VerticalPlay = $MapHeight - $InitialMapHeight;
		} else {

			# Cas où l'image est plus large que la miniature.
			$OutputWidth = floor($OutputHeight * $InputRatio);
			$MapWidth = floor($MapHeight * $InputRatio);
			$HorizontalPlay = $MapWidth - $InitialMapWidth;
		}

		# Charge l'image depuis le fichier.
		switch($InputType) {
			case IMAGETYPE_GIF: $InputImage = imagecreatefromgif($InputFile); break;
			case IMAGETYPE_JPEG: $InputImage = imagecreatefromjpeg($InputFile); break;
			case IMAGETYPE_PNG: $InputImage = imagecreatefrompng($InputFile); break;
			case IMAGETYPE_WBMP: $InputImage = imagecreatefromwbmp($InputFile); break;
			case IMAGETYPE_XBM: $InputImage = imagecreatefromxbm($InputFile); break;
			default: trigger_error('invalid image type');
		}

		# Mesure la quantité d'information portée par chaque pixel.
		$EnergyMap = $this->computeEnergyMap($InputImage,
			$InputWidth, $InputHeight, $MapWidth, $MapHeight);
		$EnergyMap = $this->computeIntegral($EnergyMap, $MapWidth, $MapHeight);

		# Trouve la zone de l'image qui contient le plus d'information.
		list($HorizontalOffset, $VerticalOffset) = $this->locateContent($EnergyMap,
			$MapWidth, $MapHeight, $HorizontalPlay, $VerticalPlay);

		# Crée une nouvelle image.
		$OutputImage = imagecreatetruecolor($this->OutputWidth, $this->OutputHeight);

		# Désactive le mode "blending" (en conflit avec le canal alpha).
		imagealphablending($OutputImage, FALSE);

		# Active le canal alpha.
		imagesavealpha($OutputImage, TRUE);

		# Remplit le fond avec du vide.
		imagefill($OutputImage, 0, 0, imagecolorallocatealpha($OutputImage, 0, 0, 0, 0x7F));

		# Calcule les offsets.
		$HorizontalOffset = $OutputWidth > $this->OutputWidth
			? $HorizontalOffset / $this->Quality
			: ($OutputWidth - $this->OutputWidth) / 2;
		$VerticalOffset = $OutputHeight > $this->OutputHeight
			? $VerticalOffset / $this->Quality
			: ($OutputHeight - $this->OutputHeight) / 2;

		# Recadre et redimmensionne l'image.
		imagecopyresampled($OutputImage, $InputImage,
			-$HorizontalOffset, -$VerticalOffset, 0, 0,
			$OutputWidth, $OutputHeight,
			$InputWidth, $InputHeight);

		return $OutputImage;
	}

	private $Quality,
		$OutputWidth, $OutputHeight, $OutputRatio,
		$MapWidth, $MapHeight;

	private function computeEnergyMap($InputImage,
		$InputWidth, $InputHeight, $OutputWidth, $OutputHeight) {

		# Copie l'image en la rétrécissant.
		$OutputImage = imagecreatetruecolor($OutputWidth, $OutputHeight);
		imagecopyresampled($OutputImage, $InputImage, 0, 0, 0, 0,
			$OutputWidth, $OutputHeight, $InputWidth, $InputHeight);

		# Applique des filtres pour mesurer la quantité d'informations.
		imagefilter($OutputImage, IMG_FILTER_GRAYSCALE);
		imagefilter($OutputImage, IMG_FILTER_GAUSSIAN_BLUR);
		imagefilter($OutputImage, IMG_FILTER_EDGEDETECT);

		return $OutputImage;
	}

	# Calcule la somme des valeurs des pixels au nord-ouest de chaque pixel.
	private function computeIntegral($InputImage, $InputWidth, $InputHeight) {

		# On obtient une image plus grande, parce qu'il y a des zéros
		# sur la première ligne et la première colonne.
		$OutputImage = imagecreatetruecolor($InputWidth + 1, $InputHeight + 1);

		# La première ligne vaut toujours zéro.
		for($x = 0; $x < $InputWidth + 1; ++$x) imagesetpixel($OutputImage, $x, 0, 0);

		# Pour chaque ligne après la première...
		for($y = 0; $y < $InputHeight; ++$y) {
			$Sum = 0;

			# La première colonne vaut toujours zéro.
			imagesetpixel($OutputImage, 0, $y, 0);

			# Pour chaque colonne après la première...
			for($x = 0; $x < $InputWidth; ++$x) {

				# Met à jour la somme des valeurs des pixels de la ligne courante.
				$Sum += abs((imagecolorat($InputImage, $x, $y) & 0xFF) - 0x7F);

				# Calcule l'intégrale de l'image au point courant.
				imagesetpixel($OutputImage, $x + 1, $y + 1,
					$Sum + imagecolorat($OutputImage, $x + 1, $y));
			}
		}

		return $OutputImage;
	}

	private function locateContent($EnergyMap,
		$MapWidth, $MapHeight, $HorizontalPlay, $VerticalPlay) {

		$BestEnergy = 0;
		$BestHorizontalOffset = 0;
		$BestVerticalOffset = 0;

		# Pour toutes les positions possibles...
		for($x = 0; $x <= $HorizontalPlay; ++$x)
		for($y = 0; $y <= $VerticalPlay; ++$y) {

			# Calcule l'énergie contenue dans l'image.
			$xx = $x + min($this->MapWidth, $MapWidth);
			$yy = $y + min($this->MapHeight, $MapHeight);
			$Energy = imagecolorat($EnergyMap, $xx, $yy)
				- imagecolorat($EnergyMap, $xx, $y)
				- imagecolorat($EnergyMap, $x, $yy)
				+ imagecolorat($EnergyMap, $x, $y);

			# Met à jour l'image d'énergie maximale.
			if($Energy > $BestEnergy) {
				$BestEnergy = $Energy;
				$BestHorizontalOffset = $x;
				$BestVerticalOffset = $y;
			}
		}
		return array($BestHorizontalOffset, $BestVerticalOffset);
	}
}

?>
