#!/usr/bin/php
<?php
/**
 * Creates scaled sprites from normal sprites.
 * 
 * @author 	Stefan Hahn
 * @copyright	2011-2012 Stefan Hahn
 * @license	Simplified BSD License License <http://projects.swallow-all-lies.com/licenses/simplified-bsd-license.txt>
 */
require_once('./lib.php');

$pokemans = glob('./sprites/*.png');
$mode = 0;
$scaleFunction = function(&$image, &$newImage) {
	return scaleNearestNeighbor($image, $newImage);
};

if (!is_dir('./scale')) mkdir('./scale');

if ($argc === 1) {
	echo 'Define the scaling algorithm by entering its number.'."\n";
	echo 'There are four different algorithms:'."\n";
	echo ' 1 - Nearest Neighbor (default)'."\n";
	echo ' 2 - Scale4x'."\n";
	echo ' 3 - 4xSaI'."\n";
	echo ' 4 - hq4x'."\n";
	echo '> ';
	
	$mode = intval(fread(STDIN, 1));
}
else {
	$mode = intval($argv[1]);
}

switch ($mode) {
	case 0:
	case 1:
		echo 'Will use Nearest Neighbor'."\n";
	break;
	case 2:
		$scaleFunction = function(&$image, &$newImage) {
			return scaleScale4x($image, $newImage);
		};
	break;
	case 3:
		$scaleFunction = function(&$image, &$newImage) {
			return scale4xSaI($image, $newImage);
		};
	break;
	case 4:
		$scaleFunction = function(&$image, &$newImage) {
			return scaleHq4x($image, $newImage);
		};
	break;
	default:
		echo 'Invalid scaling mode, will use Nearest Neighbor'."\n";
	break;
}

foreach ($pokemans as $pokeman) {
	$image = imagecreatefrompng($pokeman);
	$newImage = imagecreatetruecolor(imagesx($image) * 4, imagesy($image) * 4);
	
	echo 'Scaling pokemon '.str_replace('./', '', str_replace('.png', '', $pokeman))."\n";
	
	imagealphablending($newImage, false);
	imagesavealpha($newImage, true);
	imagefill($newImage, 0, 0, imagecolorallocatealpha($newImage, 0xff, 0xff, 0xff, 0x7f));
	
	$scaleFunction($image, $newImage);
	
	imagepng($newImage, str_replace('./sprites/', './scale/', $pokeman), 9);
	imagedestroy($image);
	imagedestroy($newImage);
}

function scaleNearestNeighbor(&$image, &$newImage) {
	$imageInfo = getImageInfo($image);
	$colors = array();
	
	for ($y = 0; $y < $imageInfo['height']; $y++) {
		for ($x = 0; $x < $imageInfo['width']; $x++) {
			$color = imagecolorsforindex($image, imagecolorat($image, $x, $y));
			
			if ($color['alpha'] < 0x7F) {
				$hex = ownDecHex($color['red']).ownDecHex($color['green']).ownDecHex($color['blue']);
				
				if (!isset($colors[$hex])) {
					$colors[$hex] = imagecolorallocatealpha($newImage, $color['red'], $color['green'], $color['blue'], 0x00);
				}
				
				imagefilledrectangle($newImage, intval($x * 4), intval($y * 4), intval($x * 4 + 4), intval($y * 4 + 4), $colors[$hex]);
			}
		}
	}
}

function scaleScale4x(&$image, &$newImage) {
	
}

function scale4xSaI(&$image, &$newImage) {
	
}

function scaleHq4x(&$image, &$newImage) {
	
}
