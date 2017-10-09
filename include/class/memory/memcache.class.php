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
 * @version $Id: memcache.class.php 3678 2013-05-24 09:48:20Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class memory_memcache {

	var $enable;
	var $obj;

	function memory_memcache() {
		;
	}

	function init($config = array()) {
		if($config['enable'] && is_array($config['connect']) && count($config['connect'])) {
			$this->obj = new Memcache();
			$enable = false;
			foreach($config['connect'] as $key => $row) {
				$host = ($row['host'] ? $row['host'] : ($row['server'] ? $row['server'] : '127.0.0.1'));
				$port = ($row['port'] ? $row['port'] : '11211');
				$pconnect = (isset($row['pconnect']) ? $row['pconnect'] : true) && true;
				$weight = max(1, (int) $row['weight']);
				$ret = $this->obj->addServer($host, $port, $pconnect, $weight);
				if($ret) {
					$enable = true;
				}
			}
			$this->enable = $enable;
		}
		return $this->enable;
	}

	function get($key) {
		return $this->obj->get($key);
	}

	function get_multi($keys) {
		return $this->obj->get($keys);
	}

	function set($key, $val, $ttl = 0) {
		return $this->obj->set($key, $val, MEMCACHE_COMPRESSED, $ttl);
	}

	function rm($key) {
		return $this->obj->delete($key);
	}

	function clear() {
		return $this->obj->flush();
	}

	function inc($key, $step = 1) {
		return $this->obj->increment($key, $step);
	}

	function dec($key, $step = 1) {
		return $this->obj->decrement($key, $step);
	}

	function stats() {
		return $this->obj->getStats();
	}
}

?>