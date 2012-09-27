#!/usr/bin/php
<?php
/**
 * Creates negative sprites from normal sprites.
 * 
 * @author 	Stefan Hahn
 * @copyright	2011-2012 Stefan Hahn
 * @license	Simplified BSD License License <http://projects.swallow-all-lies.com/licenses/simplified-bsd-license.txt>
 */
require_once('./lib.php');

$pokemans = glob('./sprites/*.png');

if (!is_dir('./negative')) mkdir('./negative');

foreach ($pokemans as $pokeman) {
	$image = imagecreatefrompng($pokeman);
	$newImage = imagecreatetruecolor(80, 80);
	$colors = array();
	
	echo 'Turning pokemon '.str_replace('./', '', str_replace('.png', '', $pokeman)).' to negative'."\n";
	imagealphablending($newImage, false);
	imagesavealpha($newImage, true);
	imagefill($newImage, 0, 0, imagecolorallocatealpha($newImage, 0xff, 0xff, 0xff, 0x7f));
	
	for ($y = 0; $y < 80; $y++) {
		for ($x = 0; $x < 80; $x++) {
			$color = imagecolorsforindex($image, imagecolorat($image, $x, $y));
			
			if ($color['alpha'] < 127) {
				$hex = ownDecHex($color['red']).ownDecHex($color['green']).ownDecHex($color['blue']);
				
				if (!isset($colors[$hex])) {
					$colors[$hex] = imagecolorallocatealpha($newImage, 0xff - $color['red'], 0xff - $color['green'], 0xff - $color['blue'], 0x00);
				}
				
				imagesetpixel($newImage, $x, $y, $colors[$hex]);
			}
		}
	}
	
	imagepng($newImage, str_replace('./sprites/', './negative/', $pokeman), 9);
	imagedestroy($image);
	imagedestroy($newImage);
}
