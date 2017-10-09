<?php
/**
 *
 * 阿里云OSS相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: oss.class.php 3730 2013-05-28 07:44:57Z wuliyong $
 */

if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

if(!defined('FTP_ERR_SERVER_DISABLED'))
{
	define('FTP_ERR_SERVER_DISABLED', -100);
	define('FTP_ERR_CONFIG_OFF', -101);
	define('FTP_ERR_CONNECT_TO_SERVER', -102);
	define('FTP_ERR_USER_NO_LOGGIN', -103);
	define('FTP_ERR_CHDIR', -104);
	define('FTP_ERR_MKDIR', -105);
	define('FTP_ERR_SOURCE_READ', -106);
	define('FTP_ERR_TARGET_WRITE', -107);
}

require ROOT_PATH.'./include/ext/oss/sdk.class.php';

class jishigou_oss
{

	var $enabled = false;
	var $_error;

	function &instance() {
		static $object;
		if(empty($object)) {
			$object = new jishigou_oss();
		}
		return $object;
	}

	function jishigou_oss() {
		$this->set_error(0);
		$this->enabled = OSS_ENABLED;
		$this->bucket = OSS_BUCKET;
		$ttimeout = OSS_SIGN_TIMEOUT;
		if ($stimeout>3599) {
			$this->stimeout=3600-(time()%3600)+$ttimeout;
		}else{
			$this->stimeout=1200-(time()%1200)+$ttimeout;
		}
		$this->hostname = OSS_HOST_NAME;
		$this->port = OSS_HOST_PORT;
		$this->connectID=1;
		if($this->enabled!=1) {
			$this->set_error(FTP_ERR_CONFIG_OFF);
		}
	}

	
	function upload($source, $target){
		$obj = new ALIOSS();
		$obj->set_host_name($this->hostname,$this->port);
		$obj->set_debug_mode(FALSE);
		$bucket = $this->bucket;
		$response = $obj->upload_file_by_file($bucket,$target,$source);
		$rt = jishigou_oss::status($response);
		return $rt == '2' ? 1 : 0;
	}

	
	function set_error($code = 0) {
		$this->_error = $code;
	}

	
	function error() {
		return $this->_error;
	}

	
	function clear($str) {
		return str_replace(array( "\n", "\r", '..'), '', $str);
	}

	
	function ftp_delete($path){
		$obj = new ALIOSS();
		$obj->set_host_name($this->hostname,$this->port);
		$obj->set_debug_mode(FALSE);
		$bucket = $this->bucket;
		$path = jishigou_oss::clear($path);
		$path = str_replace($bucket.'/','',$path);
		$response = $obj->delete_object($bucket,$path);
		$rt = jishigou_oss::status($response);
		return $rt == '2' ? 1 : 0;
	}

	
	function sign_url($file,$host=''){
		$obj = new ALIOSS();
		if ($host<>''){
			$obj->set_vhost($host);
		}else{
			$obj->set_host_name("oss.aliyuncs.com");
		}
		$obj->set_enable_domain_style();
		$obj->set_debug_mode(FALSE);
		$bucket = $this->bucket;
		$file = jishigou_oss::clear($file);
		$timeout = $this->stimeout;
		$response = $obj->get_sign_url($bucket,$file,$timeout);
		return $response;
	}

	
	function ftp_get($file,$path){
		$obj = new ALIOSS();
		$obj->set_host_name($this->hostname,$this->port);
		$obj->set_debug_mode(FALSE);
		$bucket = $this->bucket;
		$file = jishigou_oss::clear($file);
		$path = jishigou_oss::clear($path);
		$options = array(
			ALIOSS::OSS_FILE_DOWNLOAD => $path,
		);
		$response = $obj->get_object($bucket,$file,$options);
		$rt = jishigou_oss::status($response);
		return $rt == '2' ? 1 : 0;
	}

	
	function status($response){
		$rt='0';
		$rstatus=$response->status;
		if ($rstatus > ''){
			$rt=substr($rstatus,0,1);
		}
		return $rt;
	}
}