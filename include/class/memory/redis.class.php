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
 * @version $Id: redis.class.php 3678 2013-05-24 09:48:20Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class memory_redis {

	var $enable;
	var $obj;

	function memory_redis() {
		;
	}

	function init($config = array()) {
		if($config['server']) {
			try {
				$this->obj = new Redis();
				if($config['pconnect']) {
					$connect = @$this->obj->pconnect($config['server'], $config['port']);
				} else {
					$connect = @$this->obj->connect($config['server'], $config['port']);
				}
			} catch (RedisException $e) {
				;
			}
			$this->enable = ($connect ? true : false);
			if($this->enable) {
				@$this->obj->setOption(Redis::OPT_SERIALIZER, $config['serializer']);
			}
		}
		return $this->enable;
	}

	function get($key) {
		if(is_array($key)) {
			return $this->get_multi($key);
		}
		return $this->obj->get($key);
	}

	function get_multi($keys) {
		$result = $this->obj->getMultiple($keys);
		$newresult = array();
		$index = 0;
		foreach($keys as $key) {
			if($result[$index] !== false) {
				$newresult[$key] = $result[$index];
			}
			$index++;
		}
		unset($result);
		return $newresult;
	}

	function select($db=0) {
		return $this->obj->select($db);
	}

	function set($key, $val, $ttl = 0) {
		if($ttl) {
			return $this->obj->setex($key, $ttl, $val);
		} else {
			return $this->obj->set($key, $val);
		}
	}

	function set_multi($arr, $ttl = 0) {
		if(!is_array($arr)) {
			return falses;
		}
		foreach($arr as $key=>$val) {
			$this->set($key, $val, $ttl);
		}
		return true;
	}

	function rm($key) {
		return $this->obj->delete($key);
	}

	function clear() {
		return $this->obj->flushAll();
	}

	function inc($key, $step = 1) {
		return $this->obj->incr($key, $step);
	}

	function dec($key, $step = 1) {
		return $this->obj->decr($key, $step);
	}

	function keys($key) {
		return $this->obj->keys($key);
	}

	function expire($key, $second){
		return $this->obj->expire($key, $second);
	}

	function sort($key, $opt) {
		return $this->obj->sort($key, $opt);
	}

	function exists($key) {
		return $this->obj->exists($key);
	}

}

?>