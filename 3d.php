#!/usr/bin/php
<?php
/**
 * Creates 3D sprites from normal sprites.
 * 
 * @author 	Stefan Hahn
 * @copyright	2011-2012 Stefan Hahn
 * @license	Simplified BSD License License <http://projects.swallow-all-lies.com/licenses/simplified-bsd-license.txt>
 */
if (strtolower(php_sapi_name()) != 'cli') die('Script has to be invoked from cli');

$pokemans = glob('./sprites/*.png');

if (!is_dir('./3d')) mkdir('./3d');

foreach ($pokemans as $pokeman) {
	$image = imagecreatefrompng($pokeman);
	$newImage = imagecreatetruecolor(90, 80);
	$colors = array();
	
	echo 'Turning pokemon '.str_replace('./', '', str_replace('.png', '', $pokeman)).' to 3d'."\n";
	imagealphablending($newImage, false);
	imagesavealpha($newImage, true);
	imagefill($newImage, 0, 0, imagecolorallocatealpha($newImage, 0xff, 0xff, 0xff, 0x7f));
	
	for ($i = 0; $i < 7; $i++) {
		for ($y = 0; $y < 80; $y++) {
			for ($x = 0; $x < 80; $x++) {
				$color = imagecolorsforindex($image, imagecolorat($image, $x, $y));
				
				if ($color['alpha'] < 127) {
					$hex = ownDecHex($color['red']).ownDecHex($color['green']).ownDecHex($color['blue']);
					
					if (!isset($colors[$hex])) {
						$colors[$hex] = imagecolorallocatealpha($newImage, $color['red'], $color['green'], $color['blue'], 0x00);
					}
					
					@imagesetpixel($newImage, $x+$i, $y, $colors[$hex]);
				}
			}
		}
	}
	
	imagepng($newImage, str_replace('./sprites/', './3d/', $pokeman), 9);
	imagedestroy($image);
	imagedestroy($newImage);
}

function ownDecHex($number) {
	if ($number < 0x10) return '0'.dechex($number);
	else return dechex($number);
}
