<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename image.func.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013 1821725164 2401 $
 */


if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

/**
 * 作者：狐狸<foxis@qq.com>
 * 功能描述： 图片相关
 * @version $Id: image.func.php 5268 2013-12-16 08:28:12Z wuliyong $
 */


function __is_image($filename,$allow_types=array('gif'=>1,'jpg'=>1,'png'=>1,'bmp'=>1,'jpeg'=>1)) {
	clearstatcache();
	if(!is_file($filename)) {
		return false;
	}

	$imagetypes = array('1'=>'gif','2'=>'jpg','3'=>'png','4'=>'swf','5'=>'psd','6'=>'bmp','7'=>'tiff','8'=>'tiff','9'=>'jpc','10'=>'jp2','11'=>'jpx','12'=>'jb2','13'=>'swc','14'=>'iff','15'=>'wbmp','16'=>'xbm','17'=>'jpeg');
	if(!$allow_types) {
		$allow_types = array('gif'=>1,'jpg'=>1,'png'=>1,'bmp'=>1,'jpeg'=>1);
	}
	$typeid = 0;
	$imagetype = '';
	if(function_exists('exif_imagetype')) {
		$typeid = exif_imagetype($filename);
	} elseif (function_exists('getimagesize')) {
		$_tmps = getimagesize($filename);
		if($_tmps) {
			$typeid = (int) $_tmps[2];
		}
	} else {
		$str2 = jio()->ReadFile($filename, 2);
		if($str2) {
			$strInfo = unpack("C2chars", $str2);
			$fileTypes = array(7790=>'exe',7784=>'midi',8297=>'rar',255216=>'jpg',7173=>'gif',6677=>'bmp',13780=>'png',);
			$imagetype = $fileTypes[intval($strInfo['chars1'] . $strInfo['chars2'])];
		}
	}
	$file_ext = strtolower(trim(substr(strrchr($filename, '.'), 1)));
	if($typeid > 0) {
		$imagetype = $imagetypes[$typeid];
	}

	if($allow_types && $file_ext && $imagetype && isset($allow_types[$file_ext]) && isset($allow_types[$imagetype])) {
		return true;
	}

	return false;
}

function __grayJpeg($imgname)
{
	$im = @imagecreatefromjpeg($imgname);

	if(!$im)
	{
		$im  = imagecreatetruecolor(150, 30);
		$bgc = imagecolorallocate($im, 255, 255, 255);
		$tc  = imagecolorallocate($im, 0, 0, 0);

		imagefilledrectangle($im, 0, 0, 150, 30, $bgc);
		imagestring($im, 10, 5, 5, 'Error loading ' . $imgname, $tc);
	}
	else{
		$img_width = ImageSX($im);
		$img_height = ImageSY($im);
		for ($y = 0; $y <$img_height; $y++) {
			for ($x = 0; $x <$img_width; $x++) {
				$gray = (ImageColorAt($im, $x, $y) >> 8) & 0xFF;
				imagesetpixel ($im, $x, $y, ImageColorAllocate ($im, $gray,$gray,$gray));
			}
		}
	}
	return $im;
}

?>