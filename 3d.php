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

$pokemans = glob('./sprites/*.png');
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

foreach ($pokemans as $pokeman) {
	$image = imagecreatefrompng($pokeman);
	$imageInfo = getImageInfo($image);
	
	$newImage = imagecreatetruecolor($imageInfo['width'] + $factor, $imageInfo['height']);
	$colors = array();
	
	echo 'Turning pokemon '.str_replace('./', '', str_replace('.png', '', $pokeman)).' to 3d'."\n";
	imagealphablending($newImage, false);
	imagesavealpha($newImage, true);
	imagefill($newImage, 0, 0, imagecolorallocatealpha($newImage, 0xff, 0xff, 0xff, 0x7f));
	
	for ($y = 0; $y < $imageInfo['height']; $y++) {
		for ($x = $imageInfo['width'] - 1; $x > -1; $x--) {
			$color = imagecolorsforindex($image, imagecolorat($image, $x, $y));
			
			if ($color['alpha'] < 127) {
				$hex = ownDecHex($color['red']).ownDecHex($color['green']).ownDecHex($color['blue']);
				
				if (!isset($colors[$hex])) {
					$colors[$hex] = imagecolorallocatealpha($newImage, $color['red'], $color['green'], $color['blue'], 0x00);
				}
				
				imageline($newImage, $x + $factor, $y, $x, $y, $colors[$hex]);
			}
		}
	}
	
	imagepng($newImage, str_replace('./sprites/', './3d/', $pokeman), 9);
	imagedestroy($image);
	imagedestroy($newImage);
}
