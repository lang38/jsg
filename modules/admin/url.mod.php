<?php
/**
 *
 * 外链跳转管理模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: url.mod.php 3740 2013-05-28 09:38:05Z wuliyong $
 */


if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}


class ModuleObject extends MasterObject {

	
	var $auto_run = true;

	function ModuleObject($config) {
		$this->MasterObject($config);
	}

	
	function index() {
		;

		
		include template();
	}

	
	function setting() {
		
		if(jget('settingsubmit')) {
			$url = jget('url');
			$url['status_default'] = (int) $url['status_default'];
			foreach($url['status_set'] as $k=>$v) {
				$url['status_set'][$k] = (int) $v;
			}
			jconf::set('url', $url);

			$this->Messager('设置成功');
		}


		
		$url = jconf::get('url');
		$options = array(
			1 => array('value' => 1, 'name' => '加入白名单（允许访问）队列<br />'),
			0 => array('value' => 0, 'name' => '加入常规（默认）队列<br />'),
			-1 => array('value' => -1, 'name' => '加入黑名单（禁止访问）队列<br />'),
		);
		$status_default_radio = $this->jishigou_form->Radio('url[status_default]', $options, (int) $url['status_default']);
		$options = array(
			0 => array('value' => 0, 'name' => '直接跳转到相应的链接地址（推荐用于白名单（允许访问）队列）<br />'),
			1 => array('value' => 1, 'name' => '给出可以点击的链接地址，需要点击后才能跳转（推荐用于常规（默认）队列）<br />'),
			2 => array('value' => 2, 'name' => '给出链接地址，需要自行复制链接地址到浏览器地址栏中进行访问（推荐用于常规（默认）队列）<br />'),
			3 => array('value' => 3, 'name' => '禁止访问（推荐用于黑名单（禁止访问）队列）<br />'),
		);
		$status_set_allow_radio = $this->jishigou_form->Radio('url[status_set][1]', $options, (int) $url['status_set'][1]);
		$status_set_normal_radio = $this->jishigou_form->Radio('url[status_set][0]', $options, (int) $url['status_set'][0]);
		$status_set_disallow_radio = $this->jishigou_form->Radio('url[status_set][-1]', $options, (int) $url['status_set'][-1]);

		
		include template();
	}

	
	function manage() {
		$p = array(
			'perpage' => 100,
			'page_url' => 'admin.php?mod=url&code=manage',
			'sql_order' => ' `id` DESC ',
		);
		$id = jget('id', 'int');
		if($id > 0) {
			$p['id'] = $id;
			$p['page_url'] .= "&id=$id";
		}
		$key = jget('key');
		if($key) {
			$p['key'] = $key;
			$p['page_url'] .= "&key=$key";
		}
		$url = jget('url');
		if($url) {
			$p['sql_where'] = " MATCH (`url`) AGAINST ('{$url}') ";
			$p['page_url'] .= "&url=$url";
		}
		$site_id = jget('site_id', 'int');
		if($site_id > 0) {
			$p['site_id'] = $site_id;
			$p['page_url'] .= "&site_id=$site_id";
		}
		$order = jget('order');
		if($order && in_array($order, array('dateline', 'open_times'))) {
			$p['sql_order'] = " `{$order}` DESC ";
			$p['page_url'] .= "&order=$order";
		}
		$rets = jlogic('url')->get($p);

		include template();
	}

	
	function do_manage() {
		$id = jget('id', 'int');
		$ids = jget('ids');
		if(!$ids && $id < 1) {
			$this->Messager('请先指定要操作的对象');
		}
		$ids = (array) ($id > 0 ? $id : $ids);

		$info = array();
		if($id > 0) {
			$info = jlogic('url')->get_info_by_id($id);
		}

		$action = jget('action');
		if('delete' == $action) {
			jlogic('url')->delete(array('id' => $ids));
		} elseif('status') {
			$status = jget('status', 'int');
			jlogic('url')->set_status($ids, $status);
			if($info && ($site = jlogic('site')->get_info_by_id($info['site_id']))) {
				if(jget('confirm')) {
					jlogic('url')->set_status(array('site_id'=>$site['id']), $status);
					jlogic('site')->set_status(array('id'=>$site['id']), $status);
				} else {
					$url = "admin.php?mod=url&code=do_manage&action=status&status=$status&id=$id&confirm=1";
					$this->Messager("已经设置成功，<a href='{$url}'>点此可以将此站点 {$site['host']} 下的所有URL链接地址都设置为相同的状态</a><br />（默认不点击时将为您跳转回列表页面）。", '', 5);
				}
			}
		}

		$this->Messager('操作成功', 'admin.php?mod=url&code=manage');
	}

}
?>
