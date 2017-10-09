<?php
/**
 *
 * 记事狗REWRITE相关
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: rewrite.php 3831 2013-06-07 08:18:28Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

$_rewrite = jconf::get('rewrite');
if($_rewrite['mode']) {
	global $jishigou_rewrite;
	if(is_null($jishigou_rewrite)) {
		$jishigou_rewrite = jclass('jishigou/rewrite');
		if($_rewrite['abs_path']) $jishigou_rewrite->absPath = $_rewrite['abs_path'];
		if($_rewrite['gateway']) $jishigou_rewrite->gateway = $_rewrite['gateway'];
		if($_rewrite['extention']) $jishigou_rewrite->extention = $_rewrite['extention'];
		if($_rewrite['arg_separator']) $jishigou_rewrite->argSeparator = $_rewrite['arg_separator'];
		if($_rewrite['var_separator']) $jishigou_rewrite->varSeparator = $_rewrite['var_separator'];
		if($_rewrite['prepend_var_list']) $jishigou_rewrite->prependVarList = $_rewrite['prepend_var_list'];
		if($_rewrite['var_replace_list']) $jishigou_rewrite->varReplaceList = (array)$_rewrite['var_replace_list'];
		if($_rewrite['value_replace_list']) $jishigou_rewrite->valueReplaceList = (array)$_rewrite['value_replace_list'];
		
	}
	if(true === IN_JISHIGOU_INDEX || true === IN_JISHIGOU_AJAX) {
		$jishigou_rewrite->parseRequest($_rewrite['request']);
	}
}
?>