#!/usr/bin/php
<?php
/**
 * Creates outline sprites from normal sprites.
 * 
 * @author 	Stefan Hahn
 * @copyright	2011-2012 Stefan Hahn
 * @license	Simplified BSD License License <http://projects.swallow-all-lies.com/licenses/simplified-bsd-license.txt>
 */
if (strtolower(php_sapi_name()) != 'cli') die('Script has to be invoked from cli');

$pokemans = glob('./sprites/*.png');

if (!is_dir('./outline')) mkdir('./outline');

if ($argc === 1) {
	echo 'Enter hexadecimal color for outline pokemons and press enter!'."\n";
	echo '> ';

	$newColor = hex2RGB(fread(STDIN, 7));
}
else {
	$newColor = hex2RGB($argv[1]);
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
	$newImage = imagecreatetruecolor(80, 80);
	
	$newColorIndex = imagecolorallocatealpha($newImage, $newColor['red'], $newColor['green'], $newColor['blue'], 0x00);
	
	echo 'Turning pokemon '.str_replace('./', '', str_replace('.png', '', $pokeman)).' to outline'."\n";
	imagealphablending($newImage, false);
	imagesavealpha($newImage, true);
	imagefill($newImage, 0, 0, imagecolorallocatealpha($newImage, 0xff, 0xff, 0xff, 0x7f));
	
	for ($y = 0; $y < 80; $y++) {
		for ($x = 0; $x < 80; $x++) {
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

/**
 * Convert a hexa decimal color code to its RGB equivalent
 *
 * @param string $hexStr (hexadecimal color value)
 * @param boolean $returnAsString (if set true, returns the value separated by the separator character. Otherwise returns associative array)
 * @param string $seperator (to separate RGB values. Applicable only if second parameter is true.)
 * @return array or string (depending on second parameter. Returns False if invalid hex color value)
 */                                                                                                
function hex2RGB($hexStr, $returnAsString = false, $seperator = ',') {
	$hexStr = preg_replace('/[^0-9A-Fa-f]/', '', $hexStr);
	$rgbArray = array();
	
	if (strlen($hexStr) == 6) {
		$colorVal = hexdec($hexStr);
		$rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
		$rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
		$rgbArray['blue'] = 0xFF & $colorVal;
	}
	elseif (strlen($hexStr) == 3) {
		$rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
		$rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
		$rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
	}
	else {
		return false;
	}
	
	return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray;
}
