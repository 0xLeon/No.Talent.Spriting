#!/usr/bin/php
<?php
/**
 * Creates grayscale sprites from normal sprites.
 * 
 * @author 	Stefan Hahn
 * @copyright	2011-2012 Stefan Hahn
 * @license	Simplified BSD License License <http://projects.swallow-all-lies.com/licenses/simplified-bsd-license.txt>
 */
require_once('./lib.php');

$pokemans = glob('./sprites/*.png');
$mode = 4;
$grayscaleFunction = function($color) {
	return grayscaleLuminosityHDTV($color);
};

if (!is_dir('./grayscale')) mkdir('./grayscale');

if ($argc === 1) {
	echo 'Define the grayscaling algorithm by entering its number.'."\n";
	echo 'There are four different algorithms:'."\n";
	echo ' 1 - Lightness'."\n";
	echo ' 2 - Average'."\n";
	echo ' 3 - Luminosity NTSC'."\n";
	echo ' 4 - Luminosity HTDV (default)'."\n";
	echo '> ';
	
	$mode = intval(fread(STDIN, 1));
}
else {
	$mode = intval($argv[1]);
}

if (($mode > 0) && ($mode < 5)) {
	switch ($mode) {
		case 1:
			$grayscaleFunction = function($color) {
				return grayscaleLightness($color);
			};
		break;
		case 2:
			$grayscaleFunction = function($color) {
				return grayscaleAverage($color);
			};
		break;
		case 3:
			$grayscaleFunction = function($color) {
				return grayscaleLuminosityNTSC($color);
			};
		break;
	}
}
else {
	echo 'Invalid grayscaling mode, will use luminosity HDTV'."\n";
}



foreach ($pokemans as $pokeman) {
	$image = imagecreatefrompng($pokeman);
	$imageInfo = getImageInfo($image);
	
	$newImage = imagecreatetruecolor($imageInfo['width'], $imageInfo['height']);
	$colors = array();
	
	echo 'Turning pokemon '.str_replace('./', '', str_replace('.png', '', $pokeman)).' to grayscale'."\n";
	imagealphablending($newImage, false);
	imagesavealpha($newImage, true);
	imagefill($newImage, 0, 0, imagecolorallocatealpha($newImage, 0xff, 0xff, 0xff, 0x7f));
	
	for ($y = 0; $y < $imageInfo['height']; $y++) {
		for ($x = 0; $x < $imageInfo['width']; $x++) {
			$color = imagecolorsforindex($image, imagecolorat($image, $x, $y));
			
			if ($color['alpha'] < 127) {
				$gray = $grayscaleFunction($color);
				
				if (!isset($colors[$gray])) {
					$colors[$gray] = imagecolorallocatealpha($newImage, $gray, $gray, $gray, 0x00);
				}
				
				imagesetpixel($newImage, $x, $y, $colors[$gray]);
			}
		}
	}
	
	imagepng($newImage, str_replace('./sprites/', './grayscale/', $pokeman), 9);
	imagedestroy($image);
	imagedestroy($newImage);
}

function grayscaleLightness($color) {
	return intval(round((min($color) + max($color)) / 2));
}

function grayscaleAverage($color) {
	return intval(round(($color['red'] + $color['green'] + $color['blue']) / 3));
}

function grayscaleLuminosityNTSC($color) {
	return intval(round(0.299 * $color['red'] + 0.587 * $color['green'] + 0.114 * $color['blue']));
}

function grayscaleLuminosityHDTV($color) {
	return intval(round(0.2126 * $color['red'] + 0.7152 * $color['green'] + 0.0722 * $color['blue']));
}
