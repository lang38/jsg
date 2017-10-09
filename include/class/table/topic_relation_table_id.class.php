<?php
/**
 *
 * 数据表 topic_relation_table_id 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: topic_relation_table_id.class.php 3678 2013-05-24 09:48:20Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_topic_relation_table_id extends table {

	
	var $table = 'topic_relation_table_id';

	function table_topic_relation_table_id() {
		$this->init($this->table);
	}

	function row($tid, $is_dateline = 0) {
		$ret = false;
		$tid = jfilter($tid, 'int');
		if($tid > 0) {
												$maps = $this->get_maps();
			if($maps && $maps['count'] > 0) {
				if($maps['count'] <= 1) {
										$ret = $maps['list'][$maps['table_id']];
				} else {
					$key = ($is_dateline ? 'dateline' : 'tid');
					foreach($maps['list'] as $k=>$row) {
						if($row[$key] >= $tid) {
														$ret = $row;
							break;
						}
					}
				}
			}
			
		}
		return $ret;
	}

	function add($tid) {
		$ret = 0;
		$tid = jfilter($tid, 'int');
		if($tid > 0 && !($this->row($tid))) {
			$p = array(
				'tid' => $tid,
				'dateline' => TIMESTAMP
			);
			$ret = $this->replace($p, true);
			if($ret) {
				$this->cache_rm('get_maps');
			}
		}
		return $ret;
	}

	function table_id($tid, $by_dateline = 0) {
		$ret = false;
		$row = $this->row($tid, $by_dateline);
		if($row) {
			$ret = $row['id'];
		}
		return $ret;
	}

	function next($id = 0, $is = 'id', $new = 1) {
		$ret = false;
		if($this->get_maps()) {
			$is = (in_array($is, array('id', 'tid', 'dateline')) ? $is : 'id');
			$id = jfilter($id, 'int');
			if($id > 0) {
				$p = array(($new ? "<" : ">") . '@' . $is => $id);
				$p['sql_order'] = ' `' . $is . '` ' . ($new ? 'DESC' : 'ASC') . ' ';
				if($new) {
					$p['sql_order'] = ' `' . $is . '` DESC ';
				}
				$info = $this->info($p);
				if($info) {
					$ret = $info['id'];
				}
			} else {
				$ret = DB::result_first("SELECT " . ($new ? "MAX" : "MIN") . "(`id`) FROM " . DB::table($this->table));
			}
		}
		return $ret;
	}

	
	function get_maps() {
		$cache_id = $this->cache_id('get_maps', 1);
		if(false === ($rets = cache_file('get', $cache_id))) {
			$rets = $this->get(array('sql_order'=>' `id` ASC ', 'result_list_key_is_pri'=>1));
			if($rets['list']) {
				$min = 1;
				$table_id = '';
				foreach($rets['list'] as $k=>$v) {
					$table_id = $v['id'];
					$v['max'] = $v['tid'];
					$v['min'] = $min;
					$min = $v['max'];
					$rets['list'][$k] = $v;
				}
				$rets['table_id'] = $table_id;
			} else {
				$rets = $rets ? $rets : array();
			}
			cache_file('set', $cache_id, $rets);
		}
		return $rets;
	}

}

?>