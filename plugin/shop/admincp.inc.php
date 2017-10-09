<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename admincp.inc.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013-11-11 1955937025 1227 $
 */


if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}
if($ids = jimplode($this->Post['delete'])){
	$query = DB::query("SELECT `imageid` FROM ".DB::table('topic_shop')." WHERE id IN ($ids)");
	while($value = DB::fetch($query)) {
		if($value['imageid'] > 0){
			DB::query("DELETE FROM ".DB::table('topic_image')." WHERE id = '".$value['imageid']."'");		}
	}
	DB::query("DELETE FROM ".DB::table('topic_shop')." WHERE id IN ($ids)");
	$this->Messager("商品删除成功", 'admin.php?mod=plugin&code=manage&identifier=shop&pmod=admincp');
}
$action = 'admin.php?mod=plugin&code=manage&identifier=shop&pmod=admincp';
$count = DB::result_first("SELECT count(*) FROM ".DB::table('topic_shop'));
$gets = array(
	'mod' => 'plugin',
	'code' => 'manage',
	'identifier' => 'shop',
	'pmod' => 'admincp',
);
$page_url = 'admin.php?'.url_implode($gets);
$per_page_num = 50;
$shops = array();
if($count > 0){
	$page_arr = page($count,$per_page_num,$page_url,array('return'=>'array'));
	$query = DB::query("SELECT * FROM ".DB::table('topic_shop')." ORDER BY id DESC {$page_arr['limit']}");
	while($value = DB::fetch($query)) {
		$value['dateline'] = date('Y-m-d H:i:s',$value['dateline']);
		$shops[] = $value;
	}
}
?>