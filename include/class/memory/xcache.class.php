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
 * @version $Id: xcache.class.php 3678 2013-05-24 09:48:20Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class memory_xcache {

	function memory_xcache() {
		;
	}

	function init($config = array()) {
		return true;
	}

	function get($key) {
		return xcache_get($key);
	}

	function set($key, $val, $ttl = 0) {
		return xcache_set($key, $val, $ttl);
	}

	function rm($key) {
		return xcache_unset($key);
	}

	function clear() {
		return xcache_clear_cache(XC_TYPE_VAR, 0);
	}

	function inc($key, $step = 1) {
		return xcache_inc($key, $step);
	}

	function dec($key, $step = 1) {
		return xcache_dec($key, $step);
	}

}

?>