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

$sprites = glob('./sprites/*.png');
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

foreach ($sprites as $sprite) {
	$image = imagecreatefrompng($sprite);
	$newImage = imagecreatetruecolor(imagesx($image) * 4, imagesy($image) * 4);
	
	echo 'Scaling sprite '.basename($sprite, '.png')."\n";
	
	imagealphablending($newImage, false);
	imagesavealpha($newImage, true);
	imagefill($newImage, 0, 0, imagecolorallocatealpha($newImage, 0xFF, 0xFF, 0xFF, 0x7F));
	
	$scaleFunction($image, $newImage);
	
	imagepng($newImage, './scale/'.basename($sprite), 9);
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
	$tmpImage = imagecreatetruecolor(imagesx($image) * 2, imagesy($image) * 2);
	
	imagealphablending($tmpImage, false);
	imagesavealpha($tmpImage, true);
	imagefill($tmpImage, 0, 0, imagecolorallocatealpha($tmpImage, 0xFF, 0xFF, 0xFF, 0x7F));
	
	scaleScale2x($image, $tmpImage);
	scaleScale2x($tmpImage, $newImage);
	imagedestroy($tmpImage);
}

function scaleScale2x(&$image, &$newImage) {
	$imageInfo = getImageInfo($image);
	$imageColorGrid = getColorGrid($image, PX_ARGB);
	$colors = array();
	
	for ($y = 0; $y < $imageInfo['height']; $y++) {
		for ($x = 0; $x < $imageInfo['width']; $x++) {
			$scaledPixel = array($imageColorGrid[$y][$x], $imageColorGrid[$y][$x], $imageColorGrid[$y][$x], $imageColorGrid[$y][$x]);
			$a = $imageColorGrid[(($y === 0) ? $y : ($y - 1))][$x];
			$b = $imageColorGrid[$y][(($x === ($imageInfo['width'] - 1)) ? $x : ($x + 1))];
			$c = $imageColorGrid[$y][(($x === 0) ? $x : ($x - 1))];
			$d = $imageColorGrid[(($y === ($imageInfo['height'] - 1)) ? $y : ($y + 1))][$x];
			
			if (($c === $a) && ($c !== $d) && ($a !== $b)) {
				$scaledPixel[0] = $a;
			}
			if (!isset($colors[$scaledPixel[0]])) {
				$colors[$scaledPixel[0]] = imagecolorallocatealpha($newImage, ($scaledPixel[0] & 0x00FF0000) >> 16, ($scaledPixel[0] & 0x0000FF00) >> 8, $scaledPixel[0] & 0x000000FF, ($scaledPixel[0] & 0xFF000000) >> 24);
			}
			imagesetpixel($newImage, $x * 2, $y * 2, $colors[$scaledPixel[0]]);
			
			if (($a === $b) && ($a !== $c) && ($d !== $b)) {
				$scaledPixel[1] = $b;
			}
			if (!isset($colors[$scaledPixel[1]])) {
				$colors[$scaledPixel[1]] = imagecolorallocatealpha($newImage, ($scaledPixel[1] & 0x00FF0000) >> 16, ($scaledPixel[1] & 0x0000FF00) >> 8, $scaledPixel[1] & 0x000000FF, ($scaledPixel[1] & 0xFF000000) >> 24);
			}
			imagesetpixel($newImage, $x * 2 + 1, $y * 2, $colors[$scaledPixel[1]]);
			
			if (($d === $c) && ($d !== $b) && ($c !== $a)) {
				$scaledPixel[2] = $c;
			}
			if (!isset($colors[$scaledPixel[2]])) {
				$colors[$scaledPixel[2]] = imagecolorallocatealpha($newImage, ($scaledPixel[2] & 0x00FF0000) >> 16, ($scaledPixel[2] & 0x0000FF00) >> 8, $scaledPixel[2] & 0x000000FF, ($scaledPixel[2] & 0xFF000000) >> 24);
			}
			imagesetpixel($newImage, $x * 2, $y * 2 + 1, $colors[$scaledPixel[2]]);
			
			if (($b === $d) && ($b !== $a) && ($d !== $c)) {
				$scaledPixel[3] = $d;
			}
			if (!isset($colors[$scaledPixel[3]])) {
				$colors[$scaledPixel[3]] = imagecolorallocatealpha($newImage, ($scaledPixel[3] & 0x00FF0000) >> 16, ($scaledPixel[3] & 0x0000FF00) >> 8, $scaledPixel[3] & 0x000000FF, ($scaledPixel[3] & 0xFF000000) >> 24);
			}
			imagesetpixel($newImage, $x * 2 + 1, $y * 2 + 1, $colors[$scaledPixel[3]]);
		}
	}
}

function scale4xSaI(&$image, &$newImage) {
	
}

function scaleHq4x(&$image, &$newImage) {
	
}
