<?php
if (strtolower(php_sapi_name()) != 'cli') die('Script has to be invoked from cli');

define('PX_ARGB', 1);
define('PX_RGBA', 2);
define('PX_ABGR', 4);
define('PX_BGRA', 8);
define('PX_ARRAY', 16);

function ownDecHex($number) {
	if ($number < 0x10) return '0'.dechex($number);
	else return dechex($number);
}

function hex2color($hexStr, $returnAsString = false, $seperator = ',') {
	$hexStr = preg_replace('/[^0-9A-Fa-f]/', '', $hexStr);
	$rgbArray = array(
		'alpha' => 0x00
	);
	
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

function colorToHex($color, $order = PX_ARGB) {
	switch ($order) {
		case PX_BGRA:
			return ($color['alpha'] + ($color['red'] << 8) + ($color['green'] << 16) + ($color['blue'] << 24));
		case PX_ABGR:
			return ($color['red'] + ($color['green'] << 8) + ($color['blue'] << 16) + ($color['alpha'] << 24));
		case PX_RGBA:
			return ($color['alpha'] + ($color['blue'] << 8) + ($color['green'] << 16) + ($color['red'] << 24));
		case PX_ARGB:
		default:
			return ($color['blue'] + ($color['green'] << 8) + ($color['red'] << 16) + ($color['alpha'] << 24));
	}
}

function getImageInfo(&$image) {
	return array(
		'width' => imagesx($image),
		'height' => imagesy($image)
	);
}

function getColorGrid(&$image, $pixelType = PX_ARRAY) {
	$imageInfo = getImageInfo($image);
	$colorGrid = array();
	
	for ($y = 0; $y < $imageInfo['height']; $y++) {
		$colorGrid[$y] = array();
		
		for ($x = 0; $x < $imageInfo['width']; $x++) {
			$colorGrid[$y][$x] = (($pixelType === PX_ARRAY) ? imagecolorsforindex($image, imagecolorat($image, $x, $y)) : colorToHex(imagecolorsforindex($image, imagecolorat($image, $x, $y)), $pixelType));
		}
	}
	
	return $colorGrid;
}
