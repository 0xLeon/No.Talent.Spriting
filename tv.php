#!/usr/bin/php
<?php
/**
 * Creates tv sprites from normal sprites.
 * 
 * @author 	Stefan Hahn
 * @copyright	2011-2012 Stefan Hahn
 * @license	Simplified BSD License License <http://projects.swallow-all-lies.com/licenses/simplified-bsd-license.txt>
 */
require_once('./lib.php');

$sprites = glob('./sprites/*.png');

if (!is_dir('./tv')) mkdir('./tv');

foreach ($sprites as $sprite) {
	$image = imagecreatefrompng($sprite);
	$imageInfo = getImageInfo($image);
	
	$newImage = imagecreatetruecolor($imageInfo['width'], $imageInfo['height']);
	$colors = array();
	
	echo 'Turning sprite '.basename($sprite, '.png').' to tv'."\n";
	imagealphablending($newImage, false);
	imagesavealpha($newImage, true);
	imagefill($newImage, 0, 0, imagecolorallocatealpha($newImage, 0xFF, 0xFF, 0xFF, 0x7F));
	
	for ($y = 0; $y < $imageInfo['height']; $y += 2) {
		for ($x = 0; $x < $imageInfo['width']; $x++) {
			$color = imagecolorsforindex($image, imagecolorat($image, $x, $y));
			
			if ($color['alpha'] < 0x7F) {
				$hex = ownDecHex($color['red']).ownDecHex($color['green']).ownDecHex($color['blue']);
				
				if (!isset($colors[$hex])) {
					$colors[$hex] = imagecolorallocatealpha($newImage, $color['red'], $color['green'], $color['blue'], 0x00);
				}
				
				imagesetpixel($newImage, $x, $y, $colors[$hex]);
			}
		}
	}
	
	imagepng($newImage, './tv/'.basename($sprite), 9);
	imagedestroy($image);
	imagedestroy($newImage);
}
