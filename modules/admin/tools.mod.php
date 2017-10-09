<?php
/**
 *
 * 小工具管理模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: tools.mod.php 4131 2013-08-13 08:12:29Z wuliyong $
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
	
	
	function share_to_weibo() {
		$conf = jconf::get('share_to_weibo');
		$link_display_none_radio = $this->jishigou_form->YesNoRadio('link_display_none', (int) $conf['link_display_none']);
		if(jpost('settingsubmit')) {
			$conf['link_display_none'] = (jpost('link_display_none') ? 1 : 0);
			jconf::set('share_to_weibo', $conf);
			$this->Messager('设置成功了');
		}
		
		include template();
	}
	
	
	function weibo_show() {
		$conf = jconf::get('weibo_show');
		$link_display_none_radio = $this->jishigou_form->YesNoRadio('link_display_none', (int) $conf['link_display_none']);
		if(jpost('settingsubmit')) {
			$conf['link_display_none'] = (jpost('link_display_none') ? 1 : 0);
			jconf::set('weibo_show', $conf);
			$this->Messager('设置成功了');
		}
		
		include template();
	}

}
