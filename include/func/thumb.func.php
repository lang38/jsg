<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename thumb.func.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013 1956194470 3425 $
 */


if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

/**
 * 作者：狐狸<foxis@qq.com>
 * 功能描述：缩略图
 * @version $Id: thumb.func.php 5114 2013-11-27 07:06:10Z wuliyong $
 */
function __makethumb($srcfile,$dstfile,$thumbwidth,$thumbheight,$maxthumbwidth=0,$maxthumbheight=0,$src_x=0,$src_y=0,$src_w=0,$src_h=0, $thumb_cut_type=0, $thumb_quality = 100) {
		if (!is_file($srcfile)) {
		return '';
	}

		$tow = (int) $thumbwidth;
	$toh = (int) $thumbheight;
	if($tow < 30) {
		$tow = 30;
	}
	if($toh < 30) {
		$toh = 30;
	}

	$make_max = 0;
	$maxtow = (int) $maxthumbwidth;
	$maxtoh = (int) $maxthumbheight;
	if($maxtow >= 300 && $maxtoh >= 300)
	{
		$make_max = 1;
	}

		$im = '';
	if(false != ($data = getimagesize($srcfile))) {
		if($data[2] == 1) {
			$make_max = 0;			if(function_exists("imagecreatefromgif")) {
				$im = imagecreatefromgif($srcfile);
			}
		} elseif($data[2] == 2) {
			if(function_exists("imagecreatefromjpeg")) {
				$im = imagecreatefromjpeg($srcfile);
			}
		} elseif($data[2] == 3) {
			if(function_exists("imagecreatefrompng")) {
				$im = imagecreatefrompng($srcfile);
			}
		}
	}
	if(!$im) return '';

	$srcw = ($src_w ? $src_w : imagesx($im));
	$srch = ($src_h ? $src_h : imagesy($im));

	$towh = $tow/$toh;
	$srcwh = $srcw/$srch;
	if($towh <= $srcwh) {
		$ftow = $tow;
		$ftoh = round($ftow*($srch/$srcw),2);
	} else {
		$ftoh = $toh;
		$ftow = round($ftoh*($srcw/$srch),2);
	}


	if($make_max) {
		$maxtowh = $maxtow/$maxtoh;
		if($maxtowh <= $srcwh) {
			$fmaxtow = $maxtow;
			$fmaxtoh = round($fmaxtow*($srch/$srcw),2);
		} else {
			$fmaxtoh = $maxtoh;
			$fmaxtow = round($fmaxtoh*($srcw/$srch),2);
		}

		if($srcw <= $maxtow && $srch <= $maxtoh) {
			$make_max = 0;		}
	}


	$maxni = '';
	$thumb_quality = (int) $thumb_quality;
	if($thumb_quality < 1 || $thumb_quality > 100) {
		$thumb_quality = 100;
	}
	if($srcw >= $tow || $srch >= $toh) {
		if(function_exists("imagecreatetruecolor") && function_exists("imagecopyresampled") && ($ni = imagecreatetruecolor($ftow, $ftoh))) {
			imagecopyresampled($ni, $im, 0, 0, $src_x, $src_y, $ftow, $ftoh, $srcw, $srch);
						if($make_max && ($maxni = imagecreatetruecolor($fmaxtow, $fmaxtoh))) {
				imagecopyresampled($maxni, $im, 0, 0, $src_x, $src_y, $fmaxtow, $fmaxtoh, $srcw, $srch);
			}
		} elseif(function_exists("imagecreate") && function_exists("imagecopyresized") && ($ni = imagecreate($ftow, $ftoh))) {
			imagecopyresized($ni, $im, 0, 0, $src_x, $src_y, $ftow, $ftoh, $srcw, $srch);
						if($make_max && ($maxni = imagecreate($fmaxtow, $fmaxtoh))) {
				imagecopyresized($maxni, $im, 0, 0, $src_x, $src_y, $fmaxtow, $fmaxtoh, $srcw, $srch);
			}
		} else {
			return '';
		}
		if(function_exists('imagejpeg')) {
			imagejpeg($ni, $dstfile, $thumb_quality);
						if($make_max && $maxni) {
				imagejpeg($maxni, $srcfile, $thumb_quality);
			}
		} elseif(function_exists('imagepng')) {
			imagepng($ni, $dstfile);
						if($make_max && $maxni) {
				imagepng($maxni, $srcfile);
			}
		}
		imagedestroy($ni);
		if($make_max && $maxni) {
			imagedestroy($maxni);
		}
	}
	imagedestroy($im);

	if(!is_file($dstfile)) {
		return '';
	} else {
		return $dstfile;
	}
}

?>