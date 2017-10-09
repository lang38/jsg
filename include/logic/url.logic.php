<?php
/**
 *
 * 微博URL相关的数据库操作
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: url.logic.php 4709 2013-10-16 06:14:18Z conglin $
 */
if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class UrlLogic {

	var $db = null;

	function UrlLogic() {
		$this->db = jtable('url');
	}

	
	function get($p) {
		return $this->db->get($p);
	}


	function get_my_pm($touid) {
		$touid = (int) $touid;

		$p = array('uid'=>MEMBER_ID, 'touid'=>$touid);

		return $this->get($p);
	}

	
	function delete($p) {
		return $this->db->delete($p);
	}

	
	function add($url, $title='', $description='') {
		

		$url = $this->_url($url);
		if(!$url) {
			return jerror('URL链接地址不能为空', -1);
		}

		$p = array(
			'url' => $url,
			'url_hash' => $this->_hash($url),
			'title' => $title,
			'description' => $description,
			'dateline' => TIMESTAMP,
		);
		$id = $this->db->insert($p, 1);
		if($id < 1) {
			$this->clear_invalid();
			return jerror('添加新的URL链接地址失败', -2);
		}

		$ret = $this->set_key($id);
		if(!$ret) {
			$this->clear_invalid();
			return jerror('新的URL链接地址key值设置失败', -3);
		}
		$site_info = jlogic('site')->info($url);
		
		$this->set_site_id($id, $site_info['id']);
		
		$status = $site_info['status'];
		$status = (int) ($status ? $status : jconf::get('url', 'status_default'));
		$this->set_status($id, $status);

		return jreturn($id);
	}

	
	function modify($data, $p = array()) {
		foreach($data as $k=>$v) {
			if(in_array($k, array('id', 'dateline', 'open_times', 'site_id', 'status'))) {
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

		if(!in_array($is, array('id', 'key', 'url', 'url_hash'))) {
			return array();
		}

		if('id' == $is) {
			$id = max(0, (int) $id);
			if($id < 1) {
				return array();
			}
		} elseif ('url' == $is) {
			$id = $this->_hash($id);
			$is = 'url_hash';
		}

		$p = array(
			$is => $id,
		);
		return $this->db->info($p);
	}

	
	function get_info_by_id($id) {
		return $this->get_info($id, 'id');
	}

	
	function get_info_by_key($key) {
		return $this->get_info($key, 'key');
	}

	
	function get_info_by_url_hash($url_hash) {
		return $this->get_info($url_hash, 'url_hash');
	}

	
	function info($url, $title=null, $description=null) {
		return $this->get_info_by_url($url, 1, $title, $description);
	}

	
	function get_info_by_url($url, $add_or_update=1, $title=null, $description=null) {
		$url = $this->_url($url);
		if(!$url) {
			return array();
		}

		$url_info = $this->get_info($url, 'url');
		if($add_or_update) {
			$id = $re_get = 0;
			if(!$url_info) {
				$rets = $this->add($url, $title, $description);
				if(is_array($rets) && $rets['error']) {
					return array();
				} else {
					$re_get = $id = $rets['result'];
				}
			} else {
				$data = array();
				if(isset($title) && $title != $url_info['title']) {
					$data['title'] = $title;
				}
				if(isset($description) && $description != $url_info['description']) {
					$data['description'] = $description;
				}
				if($data) {
					$id = $url_info['id'];
					$re_get = $this->modify($data, array('id'=>$id));
				}
			}
			if($re_get && $id > 0) {
				$url_info = $this->get_info_by_id($id);
			}
		}

		return $url_info;
	}

	
	function set_open_times($id, $open_times) {
		return $this->db->update_count($id, 'open_times', $open_times);
	}

	
	function set_key($id, $key='') {
		$id = (is_numeric($id) ? $id : 0);
		if($id < 1) return 0;

		$key = ($key ? $key : $this->_key($id));
		return $this->modify(array('key'=>$key), array('id'=>$id));
	}

	
	function set_site_id($id, $site_id=0) {
		$id = is_numeric($id) ? $id : 0;
		if($id < 1) return 0;

		$site_id = (is_numeric($site_id) ? $site_id : 0);

		return $this->modify(array('site_id'=>$site_id), array('id'=>$id));
	}

	
	function set_status($id, $status = 0) {
		if(empty($id)) return 0;

		$status = (int) $status;

		return $this->modify(array('status' => $status), $id);
	}

	
	function clear_invalid() {
		return $this->delete(array('key'=>''));
	}

	
	function get_url($url, $strip_fragment = 0) {
		return $this->_url($url, $strip_fragment);
	}
	function _url($url, $strip_fragment = 1) {
		$url = trim(strip_tags($url));
		if($strip_fragment) {
			$strpos1 = strpos($url, '#');
			if(false !== $strpos1) {
				$url = substr($url, 0, $strpos1);
			}
		}
		if(false === strpos($url, ':/'.'/')) {
			if(0 === strpos(strtolower($url), 'www.')) {
				$url = 'http:/'.'/' . $url;
			} else {
				return '';
			}
		}
		if (false == preg_match('~^(?:https?\:\/\/|www\.)(?:[A-Za-z0-9\_\-]+\.)+[A-Za-z0-9]{1,4}(?:\:\d{1,6})?(?:\/[\w\d\/=\?%\-\&_\~\`\:\+\#\.]*(?:[^\;\@\[\]\<\>\'\"\n\r\t\s\x7f-\xff])*)?$~i',
                $url)) {
            return '';
        }
		return $url;
	}

	
	function _key($id,$op="ENCODE") {
		$index = 'z6OmlGsC9xqLPpN7iw8UDAb4HIBXfgEjJnrKZSeuV2Rt3yFcMWhakQT1oY5v0d';
		$base = 62;

		$out = "";
		if('ENCODE' == $op) {
		   for ( $t = floor( log10( $id ) / log10( $base ) ); $t >= 0; $t-- ) {
		       $a = floor( $id / pow( $base, $t ) );
		       $out = $out . substr( $index, $a, 1 );
		       $id = $id - ( $a * pow( $base, $t ) );
		   }
		} elseif ('DECODE' == $op) {
			;
		}

	   return $out;
	}

	
	function _hash($url) {
		return md5($url);
	}

}

?>