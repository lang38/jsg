<?php
/**
 *
 * 外链跳转模块
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

	function ModuleObject($config) {
		$this->MasterObject($config, 1);
	}

	function index() {
		$val = $this->Code;
		$key = $val;
		if (!$key && strlen($key) < 1) {
			$this->Messager("请指定URL链接地址", null);
		}

		$url_info = jlogic('url')->get_info_by_key($key);
		if (!$url_info || !$url_info['url']) {
			$this->Messager("请指定一个正确的URL链接地址", null);
		}
		jlogic('url')->set_open_times($url_info['id'], '+1');

		$url = jconf::get('url');
		$status = (int) $url['status_set'][(int) $url_info['status']];
		if(3 === $status) {
			$this->Messager('您要访问的URL链接地址已经不存在或是被禁止访问了', null);
		} elseif (2 === $status) {
			$this->Messager('您要访问的链接地址为（安全性未知，请谨慎访问）<br />' . $url_info['url'], null);
		} elseif (1 === $status) {
			$this->Messager("您要访问的链接地址为（安全性未知，请谨慎访问）<br />
				<a href='{$url_info['url']}' target=_blank title='点击访问 {$url_info['url']}'>{$url_info['url']}</a>
				（<a href='{$url_info['url']}' target=_blank  title='点击访问 {$url_info['url']}'>点此访问</a>）", null);
		} else {
						$this->Messager(null, $url_info['url']);
		}
	}

}
?>
