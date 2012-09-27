#!/usr/bin/php
<?php
/**
 * Creates outline sprites from normal sprites.
 * 
 * @author 	Stefan Hahn
 * @copyright	2011-2012 Stefan Hahn
 * @license	Simplified BSD License License <http://projects.swallow-all-lies.com/licenses/simplified-bsd-license.txt>
 */
require_once('./lib.php');

$pokemans = glob('./sprites/*.png');

if (!is_dir('./outline')) mkdir('./outline');

if ($argc === 1) {
	echo 'Enter hexadecimal color for outline pokemons and press enter!'."\n";
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

foreach ($pokemans as $pokeman) {
	$image = imagecreatefrompng($pokeman);
	$imageInfo = getImageInfo($image);
	
	$newImage = imagecreatetruecolor($imageInfo['width'], $imageInfo['height']);
	$newColorIndex = imagecolorallocatealpha($newImage, $newColor['red'], $newColor['green'], $newColor['blue'], 0x00);
	
	echo 'Turning pokemon '.str_replace('./', '', str_replace('.png', '', $pokeman)).' to outline'."\n";
	imagealphablending($newImage, false);
	imagesavealpha($newImage, true);
	imagefill($newImage, 0, 0, imagecolorallocatealpha($newImage, 0xff, 0xff, 0xff, 0x7f));
	
	for ($y = 0; $y < $imageInfo['height']; $y++) {
		for ($x = 0; $x < $imageInfo['width']; $x++) {
			$color = imagecolorsforindex($image, imagecolorat($image, $x, $y));
			
			if ($color['alpha'] < 0x7f) {
				$colorTop = @imagecolorsforindex($image, imagecolorat($image, $x, ((($y-1) > -1) ? ($y-1) : $y)));
				$colorBottom = @imagecolorsforindex($image, imagecolorat($image, $x, ((($y+1) < 80) ? ($y+1) : $y)));
				$colorLeft = @imagecolorsforindex($image, imagecolorat($image, ((($x-1) > -1) ? ($x-1) : $x), $y));
				$colorRight = @imagecolorsforindex($image, imagecolorat($image, ((($x+1) < 80) ? ($x+1) : $x), $y));
				
				if (($colorTop['alpha'] === 0x7f) || ($colorBottom['alpha'] === 0x7f) || ($colorLeft['alpha'] === 0x7f) || ($colorRight['alpha'] === 0x7f) || (($colorTop['alpha'] < 0x7f) && (($y-1) < 0)) || (($colorLeft['alpha'] < 0x7f) && (($x-1) < 0)) || (($colorBottom['alpha'] < 0x7f) && (($y+1) > 79)) || (($colorRight['alpha'] < 0x7f) && (($x+1) > 79))) {
					imagesetpixel($newImage, $x, $y, $newColorIndex);
				}
			}
		}
	}
	
	imagepng($newImage, str_replace('./sprites/', './outline/', $pokeman), 9);
	imagedestroy($image);
	imagedestroy($newImage);
}
