#!/usr/bin/php
<?php
/**
 * Creates monocolor sprites from normal sprites.
 * 
 * @author 	Stefan Hahn
 * @copyright	2011-2012 Stefan Hahn
 * @license	Simplified BSD License License <http://projects.swallow-all-lies.com/licenses/simplified-bsd-license.txt>
 */
require_once('./lib.php');

$sprites = glob('./sprites/*.png');

if (!is_dir('./monocolor')) mkdir('./monocolor');

if ($argc === 1) {
	echo 'Enter hexadecimal color for monocolor pokemons and press enter!'."\n";
	echo '> ';

	$newColor = hex2color(fread(STDIN, 7));
}
else {
	$newColor = hex2color($argv[1]);
}

if (!$newColor) {
	echo 'Invalid color given, use #000000.'."\n";
	
	$newColor = array(
		'red' => 0x00,
		'green' => 0x00,
		'blue' => 0x00
	);
}

foreach ($sprites as $sprite) {
	$image = imagecreatefrompng($sprite);
	$imageInfo = getImageInfo($image);
	
	$newImage = imagecreatetruecolor($imageInfo['width'], $imageInfo['height']);
	$newColorIndex = imagecolorallocatealpha($newImage, $newColor['red'], $newColor['green'], $newColor['blue'], 0x00);
	
	echo 'Turning sprite '.basename($sprite, '.png').' to monocolor'."\n";
	imagealphablending($newImage, false);
	imagesavealpha($newImage, true);
	imagefill($newImage, 0, 0, imagecolorallocatealpha($newImage, 0xFF, 0xFF, 0xFF, 0x7F));
	
	for ($y = 0; $y < $imageInfo['height']; $y++) {
		for ($x = 0; $x < $imageInfo['width']; $x++) {
			$color = imagecolorsforindex($image, imagecolorat($image, $x, $y));
			
			if ($color['alpha'] < 0x7F) {
				imagesetpixel($newImage, $x, $y, $newColorIndex);
			}
		}
	}
	
	imagepng($newImage, './monocolor/'.basename($sprite), 9);
	imagedestroy($image);
	imagedestroy($newImage);
}
