<?php
/**
 *
 * 记事狗内存缓存相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: apc.class.php 3678 2013-05-24 09:48:20Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class memory_apc {

	function memory_apc() {
		;
	}
	
	function init($config = array()) {
		return true;
	}
	
	function get($key) {
		return apc_fetch($key);
	}
	
	function get_multi($keys) {
		return apc_fetch($keys);
	}
	
	function set($key, $val, $ttl = 0) {
		return apc_store($key, $val, $ttl);
	}
	
	function rm($key) {
		return apc_delete($key);
	}
	
	function clear() {
		return apc_clear_cache('user');
	}
	
	function inc($key, $step = 1) {
		return ((false !== apc_inc($key, $step)) ? apc_fetch($key) : false);
	}
	
	function dec($key, $step = 1) {
		return ((false !== apc_dec($key, $step)) ? apc_fetch($key) : false);
	}
	
}

?>