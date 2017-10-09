<?php
/**
 *
 * 连续登录错误被限制的IP地址设置及列表模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: failedlogins.mod.php 5013 2013-11-15 06:09:11Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}


class ModuleObject extends MasterObject {

	
	function ModuleObject($config) {
		$this->MasterObject($config, 1);
	}

	function index() {
		$failedlogins = jconf::get('failedlogins');
		if(!$failedlogins) {
			$failedlogins = array(
				'limit' => 15,
				'time' => 15,
				'white_list' => array(),
			);
			jconf::set('failedlogins', $failedlogins);
		}
		$failedlogins_white_list = ((is_array($failedlogins['white_list']) && count($failedlogins['white_list'])) ? implode("\n", $failedlogins['white_list']) : '');
		


		
		$p = array();
		$p['result_count'] = 500;
		$ip = jget('ip');
		if($ip) {
			$p['ip'] = $ip;
		}
		$order = jget('order');
		if($order && in_array($order, array('ip', 'count', 'lastupdate'))) {
			$p['sql_order'] = " `{$order}` DESC ";
		}
		$rets = jtable('failedlogins')->get($p);


		include template('admin/failedlogins_index');
	}

	function modify() {
		if(jget('settingsubmit')) {
			$limit = jget('limit', 'int');
			if($limit < 1) {
				$limit = 15;
			}
			$time = jget('time', 'int');
			if($time < 1) {
				$time = 15;
			}
			$white_list = jget('white_list');
			if($white_list) {
				$white_list = explode("\n", $white_list);
				foreach($white_list as $k=>$v) {
					$white_list[$k] = trim($v);
				}
				$white_list = array_remove_empty(array_unique($white_list));
			}
			$failedlogins = array(
				'limit' => $limit,
				'time' => $time,
				'white_list' => $white_list,
			);
			jconf::set('failedlogins', $failedlogins);
		}

		$this->Messager('设置成功', 'admin.php?mod=failedlogins&code=index');
	}

	function delete() {
		$ip = jget('ip', 'txt');
		if($ip) {
			jtable('failedlogins')->delete(array('ip'=>$ip));
		}
		$this->Messager('操作成功', 'admin.php?mod=failedlogins&code=index');
	}

	function clean() {
		jtable('failedlogins')->truncate();

		$this->Messager('已经清空所有登录错误的IP地址', 'admin.php?mod=failedlogins&code=index');
	}

}

?>
