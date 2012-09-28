<?php
if (strtolower(php_sapi_name()) != 'cli') die('Script has to be invoked from cli');

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

function getImageInfo(&$image) {
	return array(
		'width' => imagesx($image),
		'height' => imagesy($image)
	);
}
