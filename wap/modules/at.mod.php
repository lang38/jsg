<?php
/**
 *
 * @AT模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: at.mod.php 3855 2013-06-18 07:45:42Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject {

	
	function ModuleObject($config) {
		$this->MasterObject($config, 1);
	}

	function index() {
		$p = array(
			'page_num' => 10,
		);
		$p['page_url'] = 'index.php?mod=at';
		$rets = jlogic('topic_mention')->get_at_my_topic($p);
		if(is_array($rets) && $rets['error']) {
			$this->Messager($rets['result'], null);
		}

		$member = $rets['member'];
		$this->Title = "@提到我的";
		include template('at_index');
	}

}