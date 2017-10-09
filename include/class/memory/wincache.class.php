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
 * @version $Id: wincache.class.php 3678 2013-05-24 09:48:20Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class memory_wincache {

	function memory_wincache() {
		;
	}

	function init($config = array()) {
		return true;
	}

	function get($key) {
		return wincache_ucache_get($key);
	}

	function get_multi($keys) {
		return wincache_ucache_get($keys);
	}

	function set($key, $val, $ttl = 0) {
		return wincache_ucache_set($key, $val, $ttl);
	}

	function rm($key) {
		return wincache_ucache_delete($key);
	}

	function clear() {
		return wincache_ucache_clear();
	}

	function inc($key, $step = 1) {
		return wincache_ucache_inc($key, $step);
	}

	function dec($key, $step = 1) {
		return wincache_ucache_dec($key, $step);
	}

}

?>