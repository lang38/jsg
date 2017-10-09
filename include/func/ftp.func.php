<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename ftp.func.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013 1247966050 3722 $
 */


if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

/**
 * 作者：狐狸<foxis@qq.com>
 * 功能描述： FTP相关
 * @version $Id: ftp.func.php 5114 2013-11-27 07:06:10Z wuliyong $
 */



function __randgetftp(){
	$ftps = jconf::get('ftp');
	if(empty($ftps) || !is_array($ftps)){
		return -1;
	}
	$ftp = array();
	foreach($ftps as $key => $val){
		if($val['on']){
			$ftp[$key] = $val['priority'];
		}
	}
	if(!empty($ftp)){
		$arr = array();
		foreach($ftp as $k => $v){
			for($i=0;$i<$v;$i++){
				$arr[] = $k;
			}
		}
		$num = count($arr)-1;
		shuffle($arr);
		$fkey = $arr[mt_rand(0,$num)];
		return $fkey;
	}else{
		return -1;
	}
}

function __getftpkey($ftpurl=''){
	$ftps = jconf::get('ftp');
	if(empty($ftps) || !is_array($ftps)){
		return -1;
	}
	foreach($ftps as $key => $val){
		if($val['attachurl'] == $ftpurl){
			$ftpkey = $key;
			break;
		}
	}
	if(isset($ftpkey)){
		return $ftpkey;
	}else{
		return -1;
	}
}

function __getftptype($ftpurl=''){
	$ftps = jconf::get('ftp');
	if(empty($ftps) || !is_array($ftps)){
		return '';
	}
	foreach($ftps as $key => $val){
		if($val['attachurl'] == $ftpurl){
			if(!isset($val['type'])){
				$val['type'] = 'FTP';
			}
			$ftptype = $val['type'];
			break;
		}
	}
	if(isset($ftptype)){
		return $ftptype;
	}else{
		return '';
	}
}

function __ftpcmd($cmd, $arg1 = '', $arg2 = '',$ftpkey=null) {
	$ftps = jconf::get('ftp');
	if(isset($ftpkey)){
		$ftpon = $ftps[$ftpkey]['on'];
	}else{
		return 0;
	}
	if(!isset($ftps[$ftpkey]['type'])){
		$ftps[$ftpkey]['type'] = 'FTP';
	}
	if($ftps[$ftpkey]['type']=='FTP'){		if(!$ftpon) {
			return $cmd == 'error' ? -101 : 0;
		} else {
			$ftp = jclass('jishigou/ftp');
			$ftp->init($ftpkey);
		}
		if(!$ftp->enabled) {
			if('error' != $cmd)
			{
				return 0;
			}
		} elseif($ftp->enabled && !$ftp->connectid) {
			$ftp->connect();
		}
		switch ($cmd) {
			case 'get' : return $ftp->ftp_get($arg1, $arg2 , FTP_BINARY); break;
			case 'upload' : return $ftp->upload(ROOT_PATH . $arg1, $arg2 ? $arg2 : $arg1); break;
			case 'delete' : return $ftp->ftp_delete($arg1); break;
			case 'mkdir'  : return $ftp->ftp_mkdir($arg1); break;
			case 'close'  : return $ftp->ftp_close(); break;
			case 'error'  : return $ftp->error(); break;
			case 'object' : return $ftp; break;
			default       : return false;
		}
	}elseif($ftps[$ftpkey]['type']=='Aliyun'){		define('ALI_LOG', FALSE);
		define('ALI_DISPLAY_LOG', FALSE);
		define('ALI_LANG', 'zh');
		define('OSS_ACCESS_ID', $ftps[$ftpkey]['username']);
		define('OSS_ACCESS_KEY', $ftps[$ftpkey]['password']);
		define('OSS_BUCKET', $ftps[$ftpkey]['attachdir']);
		define('OSS_HOST_NAME',$ftps[$ftpkey]['host']);
		define('OSS_HOST_PORT',$ftps[$ftpkey]['port']);
		define('OSS_SIGN_TIMEOUT',$ftps[$ftpkey]['timeout']);
		define('OSS_ENABLED',$ftps[$ftpkey]['on']);
		if(!$ftpon) {
			return $cmd == 'error' ? -101 : 0;
		} else {
			$oss = jclass('jishigou/oss');
		}
		if(!$oss->enabled) {
			if('error' != $cmd)
			{
				return 0;
			}
		}
		$arg1 = str_replace('./','',$arg1);
		$arg2 = str_replace('./','',$arg2);
		switch ($cmd) {
			case 'get' : return $oss->ftp_get($arg1, $arg2); break;
			case 'upload' : return $oss->upload(ROOT_PATH . $arg1, $arg2 ? $arg2 : $arg1); break;
			case 'delete' : return $oss->ftp_delete($arg1); break;
			case 'error'  : return $oss->error(); break;
			case 'object' : return $oss; break;
			default       : return false;
		}
	}elseif($ftps[$ftpkey]['type']=='Upyun'){	}elseif($ftps[$ftpkey]['type']=='99Pan'){	}
}

?>