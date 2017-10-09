<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename area.func.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013-11-11 506484886 657 $
 */


if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}



function area_config_to_json() {
	$config['area'] = jconf::get('area');

	$json = "";
	foreach($config['area'] as $key=>$val) {
		$j = '';
		foreach($val as $k=>$v) {
			$j .= "'{$k}':'{$v}',";
		}
		$j = trim($j,' ,');

		$json .= "'{$key}':{'key':'{$key}','values':{{$j}}},";
	}
	$json = trim($json,',');
	$json = "{'请选择…':{'key':'0','defaultvalue' : '0','values':{'请选择…':'0'}},{$json}}";

	return $json;
}



?>