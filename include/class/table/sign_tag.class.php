<?php
/**
 *
 * 数据表 sign_tag 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: sign_tag.class.php 3678 2013-05-24 09:48:20Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_sign_tag extends table {
	
	
	var $table = 'sign_tag';
	
	function table_sign_tag() {
		$this->init($this->table);
	}
	
	function row() {
		return $this->get(array('result_count'=>1, 'return_list_first'=>1));
	}
	
	function add($tag = '', $credits = '') {
		$data = array(
			'tag' => ($tag ? trim((is_array($tag) ? implode("\r\n", $tag) : $tag)) : ''),
			'credits' => $credits
		);
		if(($row = $this->row())) {
			$ret = $this->update($data, $row['id']);
		} else {
			$ret = $this->insert($data, true);
		}
		$this->cache_rm('list');
		return $ret;
	}
	
	function get_sign_tag($get = 'tags') {
		if(false === ($rets = cache_file('get', ($cache_id = $this->cache_id('list'))))) {
			$rets = $this->row();
			if($rets) {
				if($rets['tag']) {
					$arr = explode("\r\n", $rets['tag']);
					$tags = array();
					foreach($arr as $tag) {
						$tag = trim($tag);
						if($tag) {
							$tags[$tag] = $tag;
						}
					}
					$rets['tags'] = $tags;
				}
			} else {
				$rets = array();
			}
			cache_file('set', $cache_id, $rets);
		}
		if($get && isset($rets[$get])) {
			return $rets[$get];
		}
		return $rets;
	}
	
	function is_sign_tag($tags) {		
		if(empty($tags)) {
			return false;
		}
		$rets = $this->get_sign_tag();
		if(empty($rets)) {
			return false;
		}
		settype($tags, 'array');
		foreach($tags as $tag) {
			if(isset($rets[$tag])) {
				return true;
			}
		}
		return false;
	}
		
}

?>