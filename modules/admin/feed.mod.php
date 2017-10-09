<?php
/**
 * 动态提醒管理模块
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: feed.mod.php 4139 2013-11-04 08:12:29Z chenxianfeng $
 */

if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject {

	function ModuleObject($config) {
		$this->MasterObject($config);
		$this->Execute();
	}

	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
			case 'doleader':
				$this->doleader();
				break;
			case 'del':
				$this->del();
				break;
			case 'delall':
				$this->delall();
				break;
			case 'delm':
				$this->delm();
				break;
			case 'delw':
				$this->delw();
				break;
			case 'leader':
				$this->leader();
				break;
			case 'setting':
				$this->setting();
				break;
			case 'doset':
				$this->doset();
				break;
			default:
				$this->main();
				break;
		}
		$body = ob_get_clean();
		$this->ShowBody($body);
	}

	function main(){
		$sql = "select count(*) from ".TABLE_PREFIX."feed_log ";
		$total_record = DB::result_first($sql);
		$per_page_num = min(500, max(20, (int) (isset($_GET['pn']) ? $_GET['pn'] : $_GET['per_page_num'])));
		$page_arr = page($total_record,$per_page_num,$query_link,array('return'=>'array',),'20 50 100 200 500');
		$sql = "select * from ".TABLE_PREFIX."feed_log order by id desc	$page_arr[limit] ";
		$query = $this->DatabaseHandler->Query($sql);
		$feedlist = array();
		while (false != ($rs = $query->GetRow())){
			$rs['dateline'] = my_date_format($rs['dateline']);
			$feedlist[$rs['id']] = $rs;
		}
		$action = "admin.php?mod=feed&code=del";
		include(template('admin/feed_main'));
	}

	function del(){
		$ids = jget('ids');
		if($ids){
			jlogic('feed')->delete_feed(array('ids'=>$ids));
			$this->Messager("删除成功",'admin.php?mod=feed');
		}else{
			$this->Messager("请选择要删除的对象",'admin.php?mod=feed');
		}
	}

	function delall(){
		jlogic('feed')->delete_feed(array('all'=>1));
		$this->Messager("删除成功",'admin.php?mod=feed');
	}

	function delw(){
		jlogic('feed')->delete_feed(array('week'=>1));
		$this->Messager("删除成功",'admin.php?mod=feed');
	}

	function delm(){
		jlogic('feed')->delete_feed(array('month'=>1));
		$this->Messager("删除成功",'admin.php?mod=feed');
	}
	
	function leader()
	{
		$action = 'admin.php?mod=feed&code=doleader';
		$cat_ary = jconf::get('feed');
		$feed_type = $cat_ary['type'];
		$type_list = array(
			array('value' => 'post', 'name' => '微博'),
			array('value' => 'reply', 'name' => '评论'),
			array('value' => 'forward', 'name' => '转发'),
			array('value' => 'dig', 'name' => '赞'),
			array('value' => 'favorite', 'name' => '收藏')
		);
		$feed_type_html = $this->jishigou_form->CheckBox('feed_type[]', $type_list,$feed_type);
		$feed_nicknames = $cat_ary['user'];
		include(template('admin/feed_leader'));
	}

	function setting()
	{
		$feed_set_tem = true;
		$action = 'admin.php?mod=feed&code=doset';
		$feed_enable_html = $this->jishigou_form->YesNoRadio('feed_must',(int)($this->Config['feed_must']));
		include(template('admin/feed_leader'));
	}

	function doset()
	{
		$config = array();
		$feed_must = (int)$this->Post['feed_must'];
		$config['feed_must'] = $feed_must;
		jconf::update($config);
		$this->Messager("修改成功",'admin.php?mod=feed&code=setting');
	}

	function doleader()
	{
		$config = array();
		$config['feed_type'] = array();
		$feed_type = $this->Post['feed_type'];
		if($feed_type){
			$config['feed_type'] = $feed_type;
		}
		$config['feed_user'] = array();
		$feed_nicknames = $this->Post['feed_nicknames'];
		if($feed_nicknames){
			$feed_nicknames = explode("\r\n",$feed_nicknames);
			foreach ($feed_nicknames as $key=>$one) {
				$uid = jtable('members')->val(array('nickname' => trim($one)), 'uid');
				if($uid){
					$config['feed_user'][$uid] = $one;
				}else{
					unset($feed_nicknames[$key]);
				}
			}
		}
		$cat_ary = array('type'=>$feed_type,'user'=>implode("\r\n",$feed_nicknames));
		jconf::set('feed', $cat_ary);
		jconf::update($config);
		$this->Messager("修改成功",'admin.php?mod=feed&code=leader');
	}

}
