#!/usr/bin/php
<?php
/**
 * Creates 3D sprites from normal sprites.
 * 
 * @author 	Stefan Hahn
 * @copyright	2011-2012 Stefan Hahn
 * @license	Simplified BSD License License <http://projects.swallow-all-lies.com/licenses/simplified-bsd-license.txt>
 */
require_once('./lib.php');

$sprites = glob('./sprites/*.png');
$factor = 7;

if (!is_dir('./3d')) mkdir('./3d');

if ($argc === 1) {
	echo 'Define 3D factor.'."\n";
	echo 'Default 3D factor is 7'."\n";
	echo 'Maximum 3D factor is 99'."\n";
	echo '> ';
	
	$factor = intval(fread(STDIN, 2));
}
else {
	$factor = intval($argv[1]);
}

if (($factor < 1) || ($factor > 99)) {
	echo 'Invalid 3D factor given, will use 7'."\n";
	$factor = 7;
}

foreach ($sprites as $sprite) {
	$image = imagecreatefrompng($sprite);
	$imageInfo = getImageInfo($image);
	
	$newImage = imagecreatetruecolor($imageInfo['width'] + $factor, $imageInfo['height']);
	$colors = array();
	
	echo 'Turning sprite '.basename($sprite, '.png').' to 3d'."\n";
	imagealphablending($newImage, false);
	imagesavealpha($newImage, true);
	imagefill($newImage, 0, 0, imagecolorallocatealpha($newImage, 0xFF, 0xFF, 0xFF, 0x7F));
	
	for ($y = 0; $y < $imageInfo['height']; $y++) {
		for ($x = $imageInfo['width'] - 1; $x > -1; $x--) {
			$color = imagecolorsforindex($image, imagecolorat($image, $x, $y));
			
			if ($color['alpha'] < 0x7F) {
				$hex = ownDecHex($color['red']).ownDecHex($color['green']).ownDecHex($color['blue']);
				
				if (!isset($colors[$hex])) {
					$colors[$hex] = imagecolorallocatealpha($newImage, $color['red'], $color['green'], $color['blue'], 0x00);
				}
				
				imageline($newImage, $x + $factor, $y, $x, $y, $colors[$hex]);
			}
		}
	}
	
	imagepng($newImage, './3d/'.basename($sprite), 9);
	imagedestroy($image);
	imagedestroy($newImage);
}
