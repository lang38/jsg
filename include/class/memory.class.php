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
 * @version $Id: memory.class.php 3740 2013-05-28 09:38:05Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class memory {

	var $config;
	var $extension = array();
	var $memory;
	var $prefix;
	var $user_prefix;
	var $type;
	var $enable = false;
	var $debug = array();

	function memory() {
		$this->extension['redis'] = extension_loaded('redis');
		$this->extension['memcache'] = extension_loaded('memcache');
		$this->extension['apc'] = (function_exists('apc_cache_info') && apc_cache_info());
		$this->extension['xcache'] = function_exists('xcache_get');
		$this->extension['eaccelerator'] = function_exists('eaccelerator_get');
		$this->extension['wincache'] = (function_exists('wincache_ucache_meminfo') && wincache_ucache_meminfo());

		$this->init();
	}

	function init($config = array()) {
		$this->config = ($config ? $config : jconf::get('memory'));
		$this->prefix = (empty($config['prefix']) ? substr(md5((getenv('HTTP_HOST') ? getenv('HTTP_HOST') : $_SERVER['HTTP_HOST'])), -6) . '_' : $config['prefix']);

		foreach($this->extension as $type=>$enable) {
			$_conf = $this->config[$type];
			if($enable && $_conf['enable'] && !is_object($this->memory)) {
				$this->memory = jclass("memory/{$type}");
				$_enable = $this->memory->init($_conf);
				if(!$_enable) {
					$this->memory = null;
				} else {
					$this->type = $type;
					$this->enable = true;
					break;
				}
			}
		}
	}

	function get($key, $prefix = '') {
		static $get_multi = null;
		$ret = false;
		if($this->enable) {
			if(!isset($get_multi)) {
				$get_multi = method_exists($this->memory, 'get_multi');
			}
			$this->user_prefix = $prefix;
			if(is_array($key)) {
				if($get_multi) {
					$ret = $this->memory->get_multi($this->_key($key));
					if(false !== $ret && !empty($ret)) {
						$_ret = array();
						foreach((array) $ret as $_key=>$_val) {
							$_ret[$this->_trim_key($_key)] = $_val;
						}
						$ret = $_ret;
					}
				} else {
					$ret = array();
					$_ret = false;
					foreach ($key as $id) {
						if(false !== ($_ret = $this->memory->get($this->_key($id))) && isset($_ret)) {
							$ret[$id] = $_ret;
						}
					}
				}
				if(empty($ret)) {
					$ret = false;
				}
			} else {
				$ret = $this->memory->get($this->_key($key));
				if(!isset($ret)) {
					$ret = false;
				}
			}
		}
		return $ret;
	}

	function set($key, $val, $ttl = 0, $prefix = '') {
		$ret = false;
		if(false === $val) {
			$val = '';
		}
		if($this->enable) {
			$this->user_prefix = $prefix;
			$ret = $this->memory->set($this->_key($key), $val, $ttl);
		}
		return $ret;
	}

	function rm($key, $prefix = '') {
		$ret = false;
		if($this->enable) {
			$this->user_prefix = $prefix;
			$key = $this->_key($key);
			foreach((array) $key as $id) {
				$ret = $this->memory->rm($id);
			}
		}
		return $ret;
	}
	function del($key, $prefix = '') {
		return $this->rm($key, $prefix);
	}

	function clear() {
		$ret = false;
		if($this->enable && method_exists($this->memory, 'clear')) {
			$ret = $this->memory->clear();
		}
		return $ret;
	}

	function inc($key, $step = 1) {
		static $has_inc = null;
		$ret = false;
		if($this->enable) {
			if(!isset($has_inc)) {
				$has_inc = method_exists($this->memory, 'inc');
			}
			if($has_inc) {
				$ret = $this->memory->inc($this->_key($key), $step);
			} else {
				if(false !== ($data = $this->memory->get($key))) {
					$ret = (false !== $this->memory->set($key, $data + ($step)) ? $this->memory->get($key) : false);
				}
			}
		}
		return $ret;
	}

	function dec($key, $step = 1) {
		static $has_dec = null;
		$ret = false;
		if($this->enable) {
			if(!isset($has_dec)) {
				$has_dec = method_exists($this->memory, 'dec');
			}
			if($has_dec) {
				$ret = $this->memory->dec($this->_key($key), $step);
			} else {
				if(false !== ($data = $this->memory->get($key))) {
					$ret = (false !== $this->memory->set($key, $data - ($step)) ? $this->memory->get($key) : false);
				}
			}
		}
		return $ret;
	}

	function _key($key) {
		$prefix = $this->prefix . $this->user_prefix;
		if(is_array($key)) {
			foreach($key as &$val) {
				$val = $prefix . $val;
			}
		} else {
			$key = $prefix . $key;
		}
		return $key;
	}

	function _trim_key($key) {
		return substr($key, strlen($this->prefix . $this->user_prefix));
	}

	function get_extension() {
		return $this->extension;
	}

	function get_config() {
		return $this->config;
	}

}

?>