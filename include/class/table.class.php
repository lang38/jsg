<?php
/**
 *
 * 通用数据表相关操作类基类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: table.class.php 5462 2014-01-18 01:12:59Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table {

	
	var $table = '';
	
	var $cache_time = 0;

	
	var $field = array();
	
	var $pri = '';

	function table($name='') {
		$this->init($name);
	}

	
	function init($name='') {
		if($name) {
			$this->set_table($name);
			$this->cache_time = $GLOBALS['_J']['config']['table'][$this->table]['cache_time'];
			if($GLOBALS['_J']['config']['acceleration_mode'] || ($GLOBALS['_J']['config']['memory_enable'] && $GLOBALS['_J']['config']['cache_db_to_memory'])) {
				$this->cache_time = max($this->cache_time, 10);
			}
		}
	}
	
	function set_table($name = '') {
		if(!$name) {
			exit('table name is empty');
		}
		$this->table = $name;

		$cache_id = 'table/' . $this->table;
		if(false === ($datas = cache_file('get', $cache_id))) {
			$all_field = $this->fetch_all_field();
			if(false == $all_field) {
				exit('table name is invalid, table ' . $this->table . ' is not exists');
			}
			$pri = array();
			$field = array();
			foreach($all_field as $row) {
				$f = $row['Field'];
				if('PRI' == $row['Key']) {
					$pri[] = $f;
				}
				$row['Null'] = ('NO' == $row['Null'] ? 0 : 1);
				foreach($row as $k=>$v) {
					if(empty($v)) {
						unset($row[$k]);
					}
				}
				unset($row['Field']);
				$field[$f] = $row;
			}
			$datas = array('pri' => ($pri ? $pri[0] : ''), 'field' => $field);

			cache_file('set', $cache_id, $datas);
		}
		$this->pri = $datas['pri'];
		$this->field = $datas['field'];

		return $datas;
	}
	
	function fetch_all_field($name = '') {
		return DB::fetch_all('SHOW FIELDS FROM ' . DB::table(($name ? $name : $this->table)));
	}
	
	function fetch_all_key($name = '') {
		return DB::fetch_all('SHOW KEYS FROM ' . DB::table(($name ? $name : $this->table)));
	}
	
	function fetch_table_status($name = '', $cache_time = 864000) {
		$name = ($name ? $name : $this->table);
		$cache_id = 'table_stauts/' . $name;
		if($cache_time < 1 || false === ($ret = cache_file('get', $cache_id))) {
			$ret = DB::fetch_first('SHOW TABLE STATUS FROM `' . $GLOBALS['_J']['config']['current_db_name'] . "` LIKE '" . DB::table($name) . "'");
			if($cache_time > 0) {
				cache_file('set', $cache_id, $ret, $cache_time);
			}
		}
		return $ret;
	}
	
	function is_table($name= '', $cache_time = 86400) {
		return $this->fetch_table_status($name, $cache_time);
	}

	
	function table_name() {
		return $this->get_table();
	}
	
	function get_table() {
		return $this->table;
	}
	
	function get_field() {
		return $this->field;
	}
	
	function is_field($field) {
		return (($field && isset($this->field[$field])) ? true : false);
	}
	
	function get_pri() {
		return $this->pri;
	}
	
	function is_pri($is_auto_increment = 1) {
		$ret = false;
		if($this->pri) {
			if($is_auto_increment) {
				$ret = ('auto_increment' == $this->field[$this->pri]['Extra']) ? true : false;
			} else {
				$ret = true;
			}
		}
		return $ret;
	}

	
	function optimize($name = '') {
		DB::query('OPTIMIZE TABLE ' . DB::table(($name ? $name : $this->table)), 'SLIENT');
	}
	
	function repair($name = '') {
		DB::query('REPAIR TABLE ' . DB::table(($name ? $name : $this->table)), 'SLIENT');
	}
	
	function truncate($name = '') {
		DB::query('TRUNCATE TABLE ' . DB::table(($name ? $name : $this->table)));
	}
	
	function drop($name = '') {
		$name = ($name ? $name : $this->table);
		DB::query('DROP TABLE IF EXISTS ' . DB::table($name));
		$this->_rm_table_cahce($name);
	}
	
	function rename($to_name) {
		DB::query('RENAME TABLE ' . DB::table($this->table) . ' TO ' . DB::table($to_name));
		$this->_rm_table_cache();
	}
	
	function alter($field_list = array()) {
		$ret = false;
		if($field_list) {
			$tfl = $this->get_field();
			if($tfl) {
				$sql_l = array();
				foreach ($field_list as $field=>$info) {
					if(!isset($tfl[$field])) {
						$sql_l[]="ADD " . preg_replace("/,\s*([a-z])/i",",ADD \\1", $info);
					}
				}
				if(count($sql_l) > 0) {
					$ret = DB::query('ALTER TABLE ' . DB::table($this->table) . implode(",\r\n\t", $sql_l));
					$this->_rm_table_cache();
				}
			}
		}
		return $ret;
	}
	
	function copy($to_name = '', $type = 0, $p = array(), $fields = array()) {
		$ret = false;
		if(empty($to_name)) {
			$to_name = $this->table . '_copy';
		}
		if($this->fetch_table_status($to_name)) {
			return $ret;
		}
		if($fields) {
			foreach($fields as $k=>$v) {
				if(!$this->is_field($v)) {
					unset($fields[$k]);
				}
			}
		}
		if(!$fields) {
			$fields = array_keys($this->field);
		}
		$status = $this->fetch_table_status($this->table, 0);
		$_keys = $this->fetch_all_key();
		$keys = array();
		foreach($_keys as $row) {
			if(in_array($row['Column_name'], $fields)) {
				$keys[$row['Key_name']]['Column_name'][] = $row['Column_name'];
			}
			$keys[$row['Key_name']]['Non_unique'] = $row['Non_unique'];
		}
		$keys_list = array();
		if($this->is_pri() && ($pri_field = $this->field[$this->pri])) {
			$keys_list[$this->pri] = " `{$this->pri}` {$pri_field['Type']} NOT NULL " . strtoupper($pri_field['Extra']);
		}
		foreach($keys as $k=>$row) {
			if($row['Column_name']) {
				$keys_list[$k] = ('PRIMARY' == $k ? " {$k} KEY" : (($row['Non_unique'] ? "" : " UNIQUE") . " KEY `{$k}`")) .
					"(`" . implode("`,`", $row['Column_name']) . "`)";
			}
		}
		if(!$keys_list) {
			return $ret;
		}
		$sql = 'CREATE TABLE ' . DB::table($to_name) .
			' (' . implode(", ", $keys_list) .
			') ' . (mysql_get_server_info() > '4.1' ?
				'ENGINE=' . $status['Engine'] . ' DEFAULT CHARSET=' . $GLOBALS['_J']['db_charset'] :
				'TYPE=' . $status['Engine']) .
			($status['Auto_increment'] > 1 ? ' AUTO_INCREMENT = ' . $status['Auto_increment'] : '') .
						($status['Comment'] ? " COMMENT = '{$status['Comment']}'" : '');
		$sql .= 'SELECT `' . implode("`,`", $fields) . '` FROM ' . DB::table($this->table) . ' ';
		$sql_where = '';
		if(0 == $type) {
			$sql .= ' WHERE 1 = 0 ';
		} elseif (1 == $type) {
			$sql .= ' ';
		} elseif ((2 == $type || 3 == $type) && $p) {
			settype($p, 'array');
			$p['return_sql_where'] = 1;
			$sql_where = $this->get($p);
			$sql .= $sql_where;
		}
		$ret = DB::query($sql) ? true : false;

		if($ret && 3 == $type && $sql_where) {
			$ret = $this->delete($p) ? true : false;
		}

		return $ret;
	}

	
	function insert($data, $return_insert_id = false, $replace = false, $silent = false) {
		$ret = false;
		if(is_array($data) && $data && ($datas = $this->_filter($data))) {
			$ret = DB::insert($this->table, $datas, $return_insert_id, $replace, $silent);
			if($this->cache_time > 0 && $return_insert_id && $ret) {
				$this->cache_rm($ret);
			}
		}
		return $ret;
	}

	
	function replace($data, $return_insert_id = false, $silent = false) {
		return $this->insert($data, $return_insert_id, 1, $silent);
	}

	
	function update($data, $p = '', $unbuffered = false, $low_priority = false) {
		$ret = false;
		if($data && is_array($data)) {
			$infog = 0;
			if(!$p && $this->pri && $data[$this->pri]) { 				$p = array($this->pri => $data[$this->pri]);
				$infog = 1;
			} elseif($p && is_numeric($p)) {
				if($this->pri) {
					$p = array($this->pri => $p);
					$infog = 1;
				} else {
					exit('param $p is invalid');
				}
			}
			if($p) {
				$datas = $this->_filter($data);
				if($infog && ($info = $this->info($p)) && $info != $data) {
					foreach($datas as $k=>$v) { 						if(isset($info[$k]) && $v == $info[$k]) {
							unset($datas[$k]);
						}
					}
				}
				if($datas) {
										$condition = $this->get($p, 'sql_where');
					if($condition) {
						$ret = DB::update($this->table, $datas, $condition, $unbuffered, $low_priority) ? true : false;
					}
				}
				if($this->cache_time > 0) {
					$this->cache_rm($p);
				}
			}
		}
		return $ret;
	}

	
	function delete($ids, $limit = 0, $unbuffered = true) {
		$ret = false;
		if(!empty($ids)) {
			$p = array();
			if(is_array($ids)) {
				$p = $ids;
			} else {
				if(is_numeric($ids)) {
					if($this->is_pri()) {
						$p[$this->pri] = $ids;
					}
				} else {
					if(false != trim($ids)) {
						$p = (string) $ids;
					}
				}
			}
			if($p && ($condition = $this->_filter($p, 1))) {
				$ret = DB::delete($this->table, $condition, $limit, $unbuffered) ? true : false;
			}
			if($this->cache_time > 0) {
				$this->cache_rm($ids);
			}
		}
		return $ret;
	}

	
	function get($p = array(), $return = '') {
		$ps = array();
		if($p) {
			if(!is_array($p)) {
				if($this->is_pri() && is_numeric($p)) {
					$p = array($this->pri=>$p, 'result_count'=>1, 'return_list_first'=>1); 				} elseif($p) {
					$ps = (string) $p;
					$p = array();
				}
			}
		} else {
			$p = array();
		}
		if(is_array($p) && !empty($p)) {
			$ps = $this->_filter($p, 1);
			if(!$ps && $p['sql_where']) {
				$ps = (string) $p['sql_where'];
			}
		}
		$sql_where = DB::where($ps);
		if($p['return_sql_where'] || 'sql_where' == $return) {
			return $sql_where;
		}

		$cache_file = false;
		$cache_time = max(0, (int) $p['cache_time']);
		if($cache_time) {
			$cache_file = (bool) ($p['cache_file'] ? $p['cache_file'] : $p['cache_to_file']);
			$cache_key = ($p['cache_key'] ? $p['cache_key'] : $this->cache_id('table-get-' . md5(serialize($p)), $cache_file));
		}

		$sql_table = ' ' . ($p['sql_table'] ? $p['sql_table'] : DB::table($this->table)) . ' ';

		$p['count'] = ($p['count'] ? $p['count'] : ($p['result_count'] ? $p['result_count'] : $p['total_record']));
		$count = max(0, (int) $p['count']);
		if($count < 1) {
			if(!$cache_time || false===($count = $this->cache_get(($cache_id = $cache_key . '-count'), $cache_file))) {
				$count = DB::result_first('SELECT COUNT(1) FROM ' . $sql_table . $sql_where);
				if($cache_time) {
					$this->cache_set($cache_id, $count, $cache_time, $cache_file);
				}
			}
		}
		if($p['return_count'] || 'count' == $return) {
			return $count;
		}

		$rets = array();
		if($count > 0) {
			$page = array();
			$sql_limit = '';
			if(($page_num = (int) ($p['page_num'] ? $p['page_num'] : ($p['perpage'] ? $p['perpage'] : ($p['per_page'] ? $p['per_page'] : $p['per_page_num'])))) > 0) {
				$page_func = (true === IN_JISHIGOU_WAP ? 'wap_page' : 'page');
								if($p['page_func'] && function_exists($p['page_func'])) {
					$page_func = $p['page_func'];
				}
				$po = array('return'=>'Array', 'extra'=>$p['page_extra'], 'var'=>$p['page_var'], 'page_url'=>$p['page_url'], 'page_link'=>$p['page_link']);
				if($p['page_options'] && is_array($p['page_options'])) {
					$po = array_merge($po, $p['page_options']);
				}
				$page = $page_func($count, $page_num, ($po['page_url'] ? $po['page_url'] : $po['page_link']), $po);
				if($p['return_page'] || 'page' == $return) {
					return $page;
				}
				$sql_limit = $page['limit'];
			} elseif($p['sql_limit']) {
				$sql_limit = ' ' . (false === strpos(strtolower($p['sql_limit']), 'limit ') ? ' LIMIT ' : '') . $p['sql_limit'];
			} elseif($p['count'] && false === strpos(strtolower($sql_where), ' limit ')) {
				$sql_limit = ' LIMIT ' . $count;
			}

			$sql_order = '';
			if($p['sql_order']) {
				$sql_order = ' ' . (false === strpos(strtolower($p['sql_order']), 'order by ') ? ' ORDER BY ' : '') . $p['sql_order'];
			}

			$sql_group = '';
			if($p['sql_group']) {
				$sql_group = ' ' . (false === strpos(strtolower($p['sql_group']), 'group by ') ? ' GROUP BY ' : '') . $p['sql_group'];
			}

			$sql_field = ($p['sql_field'] ? (is_array($p['sql_field']) ? implode(" , ", $p['sql_field']) : (string) $p['sql_field']) : '*');
			if($p['sql_field_prefix'] && false === strpos(strtolower($sql_field), ' as ')) {
				$_fields = (array) ('*' == $sql_field ? array_keys($this->field) : explode(',', $sql_field));
				foreach($_fields as $_k=>$_v) {
					$_vt = trim($_v, ' `,');
					if($_vt) {
						$_fields[$_k] = " `{$_vt}` AS `{$p['sql_field_prefix']}{$_vt}` ";
					}
				}
				$sql_field = implode(" , ", $_fields);
			}

			$sql = "select $sql_field FROM $sql_table $sql_where $sql_group $sql_order $sql_limit ";
			if($p['return_sql'] || 'sql' == $return) {
				return $sql;
			}

			if(!$cache_time || false===($list = $this->cache_get(($cache_id = $cache_key . ((1 == $p['count'] && ' LIMIT 1' == $sql_limit) ? '' : ('-list-' . $sql_limit))), $cache_file))) {
				$list = array();
				if($p['sql_include_subquery']) {
					$this->set_query_safes($sql);
				}
				$query = DB::query($sql);
				while(false != ($row = DB::fetch($query))) {
					if($p['result_list_row_make_func']) {
						$row = call_user_func($p['result_list_row_make_func'], $row);
					}
					if($p['result_list_row_unset_empty_value']) {
						foreach($row as $k=>$v) {
							if(empty($v)) {
								unset($row[$k]);
							}
						}
					}
					if($p['result_list_key_is_pri'] && $this->is_pri() && isset($row[$this->pri])) {
						$list[$row[$this->pri]] = $row;
					} else {
						$list[] = $row;
					}
				}
				if($p['result_list_order_by_self'] && !$sql_order && ($pss = $ps[$this->pri]) && is_array($pss) && $list) {
					$_list = array();
					foreach($pss as $v) {
						foreach($list as $k=>$row) {
							if(isset($row[$this->pri]) && $v == $row[$this->pri]) {
								$_list[$k] = $row;
							}
						}
					}
					if($_list) {
						$list = $_list;
					}
					unset($_list, $pss);
				}
				if($cache_time) {
					$this->cache_set($cache_id, $list, $cache_time, $cache_file);
				}
			}
			if($p['result_list_make_func'] && $list) {
				$list = call_user_func($p['result_list_make_func'], $list);
			}
			if($p['return_list_first'] || 'list_first' == $return) {
				return $list[0];
			}
			if($p['return_list'] || 'list' == $return) {
				return $list;
			}
			if($list) {
				$rets = array('count'=>$count, 'list'=>$list, 'page'=>$page);
			}
		}
		return $rets;
	}

	
	function get_ids($p, $field='', $more=0) {
		$rets = $ids = array();
		if(empty($field)) {
			$field = $this->pri;
		}
		if($this->is_field($field)) {
						$p['sql_field'] = " `{$field}` ";
			$rets = $this->get($p);
			if($rets && is_array($rets['list'])) {
				foreach($rets['list'] as $row) {
					$ids[$row[$field]] = $row[$field];
				}
				unset($rets['list']);
			}
		}
		if($more && $rets) {
			if(is_array($rets) && $ids) {
				$rets['ids'] = $ids;
				unset($ids);
			}
			return $rets;
		} else {
			unset($rets);
			return $ids;
		}
	}

	
	function info($id, $cache_time=0, $cache_to_file=0) {
		if(!$id) {
			return false;
		}
		$p = array();
		if(is_array($id)) {
			$p = $id;
		} else {
			if(!$this->pri) {
				exit('pri is empty');
			}
			$p[$this->pri] = $id;
		}
		$p['result_count'] = 1; 		$p['return_list_first'] = 1; 		$cache_time = (int) ($cache_time > 0 ? $cache_time : $this->cache_time);
		if($cache_time > 0 && ($cache_key = $this->cache_id($id, $cache_to_file))) {
			$p['cache_time'] = $cache_time;
			$p['cache_key'] = $cache_key;
			$p['cache_file'] = $cache_to_file;
		}
		return $this->get($p);
	}

	
	function val($id, $key, $cache_to_file=0) {
		$info = $this->info($id, 0, $cache_to_file);
		$val = false;
		if($info && isset($info[$key])) {
			$val = $info[$key];
		}
		return $val;
	}

	
	function count($p=array()) {
		$count = 0;
		if(is_array($p)) {
			$p['return_count'] = 1;
			$count = $this->get($p);
		} else {
			$rets = $this->get($p);
			$count = $rets['count'];
		}
		return (int) $count;
	}

	
	function update_count($id, $key='', $val=0, $is_unsigned=1, $p=array()) {
		$ret = false;
		if($id && $key && $this->field[$key] && $this->pri!=$key && ($info=$this->info($id)) && isset($info[$key])) {
			$val = (is_numeric($val) ? $val : 0);
			$val_old = (is_numeric($info[$key]) ? $info[$key] : 0);
			$signed = substr((string) $val, 0, 1);
			$val_new = (in_array($signed, array('-', '+')) ? $val_old + $val : $val);
			if($is_unsigned && $val_new < 0) {
				$val_new = 0;
			}
			if($val_old != $val_new || $p) {
				$infon = array($key => $val_new);
				if(is_array($p) && count($p)) {
					$infon = array_merge($infon, $p);
				}
				foreach($infon as $k=>$v) {					if(isset($info[$k]) && $v == $info[$k]) {
						unset($infon[$k]);
					}
				}
				if($infon) {
					$ret = $this->update($infon, (is_array($id) ? $id : ($this->pri ? array($this->pri => $id) : $info)));
				}
			}
			if($this->cache_time > 0) {
				$this->cache_rm($id);
			}
		}
		return $ret;
	}

	
	function cache_id($id, $file = 1) {
		$key = $this->_cache_key($id);
		if($key && $file) {
			$key = 'table_cache/' . $key;
		}
		return $key;
	}

	
	function cache_get($id, $file = 0) {
		if($file) {
			return cache_file('get', $id);
		} else {
			return cache_db('mget', $id);
		}
	}

	
	function cache_set($id, $data, $time, $file = 0) {
		if($file) {
			cache_file('set', $id, $data, $time);
		} else {
			if($data) {
				cache_db('mset', $id, $data, $time);
			}
		}
	}

	
	function cache_rm($id, $is_key = 0) {
		$key = $is_key ? $id : $this->_cache_key($id);
		if($key) {
			cache_db('mrm', $key);
			cache_file('rm', 'table_cache/' . $key);
		}
	}

	
	function _cache_key($id, $check=1) {
		if($check && (!$this->table || !$this->pri || !$id)) {
			return '';
		} else {
			$k = $this->table . '/';
			if(is_array($id)) {
				ksort($id); 				foreach($id as $_k=>$_v) {
					$_ks[] = "{$_k}-{$_v}";
				}
				$k .= implode('-', $_ks);
			} else {
				$k .= $this->pri . '-' . (string) $id;
			}
			return $k;
		}
	}

	
	function _rm_table_cache($name = '') {
		$name = ($name ? $name : $this->table);
		cache_file('rm', 'table/' . $name);
		cache_file('rm', 'table_status/' . $name);
	}

	
	function _filter(&$p, $try_ids=0) {
		$ps = $p;
		if(is_array($ps) && count($ps) && $this->field) {
			$k_is_num = true;
			$ps_o = $ps;
			foreach($ps as $k=>$v) {
								if(is_array($v) && isset($v['glue']) && !$this->field[$v['key']]) {
					echo 'table _filter is invalid';
					jlog('table__filter', $ps);
				}
				if($try_ids) {
					$k_is_num = ($k_is_num && is_numeric($k));
				}
				if(!$this->field[$k]) {
					$at_pos = strpos($k, '@');
					if(false !== $at_pos) {
						$kk = substr($k, $at_pos + 1);
						if($this->field[$kk]) { 							$glue = substr($k, 0, $at_pos);
														if($glue && in_array($glue, array('=', '-', '+', '|', '&', '^', '>', '<', '<>', '>=', '<=', 'like', 'in', 'notin',))) {
								$ps['glue_' . mt_rand(). '_' . $kk] = array(
									'glue' => $glue,
									'key' => $kk,
									'val' => $v,
								);
							}
						}
					}
					unset($ps[$k]);
				}
			}
			if($try_ids && !$ps && $k_is_num && $this->pri) {
				$ps = array($this->pri=>$ps_o);
								$p['result_count'] = count($ps_o);
				$p['result_list_order_by_self'] = 1;
				$p['return_list'] = 1;
			}
			unset($p['sql_include_subquery']); 			if($this->_sub_table_by_field && $this->_table && !$p['sql_table'] && !$p['return_sql'] && $p[$this->_sub_table_by_field]) {
				$p = $this->_set_sub_table($this->_sub_table_by_field, $p);
				if($p['sql_table']) {
					$ps = array();
				}
			}
		}
		return $ps;
	}

	
	function _set_sub_table($k, $p) {
		if($k && $p[$k]) {
			settype($p[$k], 'array');
			$maps = array();
			foreach ($p[$k] as $_k=>$v) {
				if($v) {
					$t = $this->_init_table($v);
					$maps[$t][$v] = $v;
				}
			}
						if($maps) {
				$maps_count = count($maps);
				if($maps_count > 1) {
					$_p = $p;
					$_p['return_sql'] = 1;
					if($_p['sql_order'] && $_p['sql_field'] && '*' != $_p['sql_field']) { 																		settype($_p['sql_field'], 'array');
						$_t1 = explode(',', $_p['sql_order']);
						foreach($_t1 as $_t2) {
							$_t2 = trim($_t2);
							if($_t2) {
								$_t3 = explode(' ', $_t2);
								foreach($_t3 as $_t4) {
									$_t4 = trim($_t4);
									if($_t4 && !in_array(strtolower($_t4), array('order', 'by', 'asc', 'desc'))) {
										$_p['sql_field'][] = $_t4;
									}
								}
							}
						}
					}
					$_rs = array();
					foreach($maps as $_k=>$_vs) {
						$_p[$k] = $_vs;
						$this->init($_k);
						$_rs[$_k] = $this->get($_p);
					}
					$p['sql_table'] = " \r\n\t ( ( " . implode(" ) UNION ALL \r\n\t ( ", $_rs) . " ) ) `{$this->_table}_union_all_table_alias` \r\n ";
					$p['sql_include_subquery'] = 1;
										if(!$p['cache_time']) {
						$p['cache_time'] = 180;
					}
				} else {
					$p[$k] = $maps[$t];
					$this->init($t);
				}
			}
		}
		return $p;
	}

	
	function set_query_safes($sql) {
		$sql_md5 = $sql_md5 = md5($sql);
		if(!isset($GLOBALS['_J']['query_safes'][$sql_md5])) {
			$GLOBALS['_J']['query_safes'][$sql_md5] = md5($sql_md5 . $GLOBALS['_J']['config']['auth_key']);
		}
	}

}

class model extends table {}