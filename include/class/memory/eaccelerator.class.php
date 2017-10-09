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
 * @version $Id: eaccelerator.class.php 3678 2013-05-24 09:48:20Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class memory_eaccelerator {

	function memory_eaccelerator() {
		;
	}

	function init($config = array()) {
		return true;
	}

	function get($key) {
		return eaccelerator_get($key);
	}

	function set($key, $val, $ttl = 0) {
		return eaccelerator_put($key, $val, $ttl);
	}

	function rm($key) {
		return eaccelerator_rm($key);
	}

}

?>