<?php
/**
 *
 * 微博SITE相关的数据库操作
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: site.logic.php 3684 2013-05-27 02:58:05Z wuliyong $
 */
if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class SiteLogic {
	
	var $db = null;

	function SiteLogic() {
		$this->db = jtable('site');
	}

	
	function get($p) {
		return $this->db->get($p);
	}

	
	function delete($p) {
		return $this->db->delete($p);
	}

	
	function add($host, $name='', $description='') {
		$host = $this->_host($host);
		if(!$host) {
			return jerror('站点地址host不能为空', -1);
		}

		$p = array(
			'host' => $host,
			'name' => $name,
			'description' => $description,
			'dateline' => TIMESTAMP,
		);
		$id = $this->db->insert($p, 1);
		if($id < 1) {
			return jerror('添加新的站点地址host失败', -2);
		}

		return jreturn($id);
	}

	
	function modify($data, $p = array()) {
		foreach($data as $k=>$v) {
			if(in_array($k, array('id', 'dateline', 'url_count', 'status'))) {
				$v = (int) $v;
			} else {
				$v = trim(strip_tags($v));
			}
			$data[$k] = $v;
		}
		return $this->db->update($data, $p);
	}

	
	function get_info($id, $is = 'id') {
		$id = trim($id);
		if(!$id && strlen($id) < 1) {
			return array();
		}

		if(!in_array($is, array('id', 'host'))) {
			return array();
		}

		if('id' == $is) {
			$id = max(0, (int) $id);
			if($id < 1) {
				return array();
			}
		}

		$p = array(
			$is => $id,
		);
		return $this->db->info($p);
	}

	
	function get_info_by_id($id) {
		return $this->get_info($id, 'id');
	}
	
	
	function info($host, $name=null, $description=null) {
		return $this->get_info_by_host($host, 1, $name, $description);
	}
	
	
	function get_info_by_host($host, $add_or_update=1, $name=null, $description=null) {
		$host = $this->_host($host);
		if(!$host) {
			return array();
		}

		$site_info = $this->get_info($host, 'host');
		if($add_or_update) {
			$id = $re_get = 0;
			if(!$site_info) {
				$rets = $this->add($host, $name, $description);
				if(is_array($rets) && $rets['error']) {
					return array();
				} else {
					$id = $re_get = $rets['result'];
				}
			} else {
				$data = array();
				if(isset($name) && $name != $site_info['name']) {
					$data['name'] = $name;
				}
				if(isset($description) && $description != $site_info['description']) {
					$data['description'] = $description;
				}
				if($data) {
					$id = $site_info['id'];
					$re_get = $this->modify($data, array('id'=>$id));
				}
			}
			if($re_get && $id > 0) {
				$site_info = $this->get_info_by_id($id);
			}
		}

		return $site_info;
	}

	
	function set_url_count($id, $url_count) {
		return $this->db->update_count($id, 'url_count', $url_count);
	}

	
	function set_status($id, $status = 0) {
		if(empty($id)) return 0;

		$status = (int) $status;

		return $this->modify(array('status' => $status), $id);
	}

	
	function _host($host) {
		if(false !== strpos($host, ':/'.'/')) {
			$urls = parse_url($host);
			$host = $urls['host'];
		}
		$host = strtolower(trim(strip_tags($host)));
		return $host;
	}
	
}

?>