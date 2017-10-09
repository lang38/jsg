<?php
/**
 * @version $Id: pm.mod.php 5462 2014-01-18 01:12:59Z wuliyong $
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 私信模块
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	function ModuleObject($config)
	{
		$this->MasterObject($config);

		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
			case 'dopmsend':
				$this->DoPmSend();
				break;
			case 'pmsend':
				$this->PmSend();
				break;
			case 'delmsg':
				$this->delMsg();
				break;
			case 'delete':
				$this->Delete();
				break;
			case 'pm_manage':
				$this->PmManage();
				break;

			case 'to_admin':
				$this->to_admin();
				break;
			case 'to_admin_info':
				$this->to_admin_info();
				break;

			default:
				$this->Main();
				break;
		}
		$body = ob_get_clean();

		$this->ShowBody($body);

	}

	function Main() {
		$this->Messager(null, 'admin.php?mod=pm&code=pm_manage');
	}

	function PmManage()
	{

		$per_page_num = min(500, max(20, (int) (isset($_GET['pn']) ? $_GET['pn'] : $_GET['per_page_num'])));
		$where_list = array();
		$query_link = 'admin.php?mod=pm&code=pm_manage';

		$username = trim($this->Get['username']);
		$keyword  = trim($this->Get['keyword']);
		$tousername = trim($this->Get['tousername']);
		$where_list['inbox'] = " `folder` = 'inbox' ";
		if($username)
		{
			$where_list['msgnickname'] = "`msgnickname`='{$username}'";
			$query_link .= "&username=".urlencode($username);
		}
		if($tousername)
		{
			$where_list['tonickname'] = "`tonickname`='{$tousername}'";
			$query_link .= "&tousername=".urlencode($tousername);
		}
		if($keyword)
		{
			$where_list['keyword'] = build_like_query('subject,message',$keyword);
			$query_link .= "&keyword=".urlencode($keyword);
		}

		$where = (empty($where_list)) ? null : ' WHERE '.implode(' AND ',$where_list).' ';

		$sql = "select count(*) as `total_record` from `".TABLE_PREFIX."pms` {$where}";
		$result = mysql_query($sql);
		$total_records = mysql_fetch_array($result);
		$total_record = $total_records[0];

		$page_arr = page($total_record,$per_page_num,$query_link,array('return'=>'array',),'20 50 100 200,500');

		$sql = "select *,m1.nickname as msgnickname,m2.nickname as tonickname from `".TABLE_PREFIX."pms` p
				 left join `".TABLE_PREFIX."members` m1 on m1.uid = p.msgfromid
				 left join `".TABLE_PREFIX."members` m2 on m2.uid = p.msgtoid
				 {$where} order by `pmid` desc {$page_arr['limit']} ";
		$query = $this->DatabaseHandler->Query($sql);

		$pm_list=array();
		while($row=$query->GetRow())
		{
			$pm_list[]=$row;

		}

		include(template('admin/pm'));

	}

	
	function PmSend(){
				$per_page_num = min(500, max(20, (int) (isset($_GET['pn']) ? $_GET['pn'] : $_GET['per_page_num'])));
		$query_link = 'admin.php?mod=pm&code=pmsend';
		$param = array(
		    'per_page_num' => $per_page_num,
		    'query_link' => $query_link,
		);
		$return = jlogic('pm')->getNotice($param);
		extract($return);
		

		include(template('admin/admin_pmsend'));
	}

	
	function DoPmSend(){
		load::logic("pm");
		$PmLogic = new PmLogic();

		$return = $PmLogic->doPmSend($this->Post);
		$return = $return ? $return : '发送成功';

		$this->Messager($return,"admin.php?mod=pm&code=pmsend");
	}

	
	function delMsg(){
		$ids = (array) ($this->Post['ids'] ? $this->Post['ids'] : $this->Get['ids']);
		if(!$ids) {
			$this->Messager("请指定要删除的对象");
		}

		load::logic('pm');
		$PmLogic = new PmLogic();

		foreach ($ids as $key=>$value) {
			if($value==''){continue;}
			$PmLogic->delNotice($value);
		}

		$this->Messager('操作成功',"admin.php?mod=pm&code=pmsend");
	}

	function Delete()
	{
		$ids = (array) ($this->Post['ids'] ? $this->Post['ids'] : $this->Get['ids']);

		if(!$ids) {
			$this->Messager("请指定要删除的对象");
		}

		$pmid_list = jimplode($ids);

		load::logic('pm');
		$PmLogic = new PmLogic();

		$query  = $this->DatabaseHandler->Query("select distinct msgfromid,msgtoid,plid from ".TABLE_PREFIX."pms where pmid in ($pmid_list)");

		$sql = "delete from `".TABLE_PREFIX."pms` where `pmid` in ($pmid_list)";
		$this->DatabaseHandler->Query($sql);

		while ($rsdb = $query->GetRow()){
			$PmLogic->setNewList($rsdb['msgfromid'],$rsdb['msgtoid'],$rsdb['plid']);
			if($rsdb['msgfromid'] != $rsdb['msgtoid']){
				$PmLogic->setNewList($rsdb['msgtoid'],$rsdb['msgfromid'],$rsdb['plid']);
			}
		}

		$this->Messager($return ? $return : "操作成功");
	}

	function to_admin() {
		$pm_to_admin = jconf::get('pm_to_admin');
		$act = jget('act');
		if('add' == $act) {
			$to_admin = jget('to_admin', 'txt');
			if(empty($to_admin)) {
				$this->Messager('接收者昵称不能为空');
			}
			$ns = explode(',', $to_admin);
			$rets = jtable('members')->get(array(
				'sql_field' => 'uid, nickname',
				'nickname' => $ns,
				'result_count' => count($ns),
			));
			$to_uids = array();
			$to_admins = array();
			foreach($rets['list'] as $row) {
				$to_uids[$row['uid']] = $row['uid'];
				$to_admins[$row['nickname']] = $row['nickname'];
			}
			$name = jget('name', 'txt');
			if(empty($name)) {
				$name = "给 {$to_admin} 的信";
			}
			$enable = ($to_admins ? 1 : 0);
						$pm_to_admin['list'][] = array(
				'name' => $name,
				'to_admin' => implode(',', $to_admins),
				'to_uid' => implode(',', $to_uids),
				'to_uids' => $to_uids,
				'enable' => $enable,
			);
			jconf::set('pm_to_admin', $pm_to_admin);

			$this->Messager('添加成功');
		} elseif ('delete' == $act) {
			$id = jget('id', 'int');
			if(!isset($pm_to_admin['list'][$id])) {
				$this->Messager('请指定一个正确的ID');
			}
			if(is_array($pm_to_admin['list'][$id])) {
				unset($pm_to_admin['list'][$id]);
			}
			jconf::set('pm_to_admin', $pm_to_admin);

			$this->Messager('删除成功');
		}

		include template('admin/pm_to_admin');
	}

	function to_admin_info() {
		$pm_to_admin = jconf::get('pm_to_admin');
		$id = jget('id', 'int');
		if(!isset($pm_to_admin['list'][$id])) {
			$this->Messager('请指定一个正确的ID');
		}
		$info = $pm_to_admin['list'][$id];

		$link_to_list_radio = $this->jishigou_form->YesNoRadio('info[link_to_list]', (int) $info['link_to_list']);

		if(jpost('cronssubmit')) {
			$_info = jpost('info');

			$to_admin = $_info['to_admin'];
			if(empty($to_admin)) {
				$this->Messager('接收者昵称不能为空');
			}
			$ns = explode(',', $to_admin);
			$rets = jtable('members')->get(array(
				'sql_field' => 'uid, nickname',
				'nickname' => $ns,
				'result_count' => count($ns),
			));
			$to_uids = array();
			$to_admins = array();
			foreach($rets['list'] as $row) {
				$to_uids[$row['uid']] = $row['uid'];
				$to_admins[$row['nickname']] = $row['nickname'];
			}
			$to_uid = implode(',', $to_uids);
			$name = $_info['name'];
			if(empty($name)) {
				$name = "给 {$to_admin} 的信";
			}

			$_info['enable'] = 1;
			$_info['to_uid'] = $to_uid;
			$_info['to_uids'] = $to_uids;
			$_info['to_admin'] = implode(',', $to_admins);
			$_info['name'] = $name;
			$_info['dateline'] = TIMESTAMP;
			$_info['link_to_list'] = ($_info['link_to_list'] && is_numeric($to_uid) && $to_uid > 0);

			$notice_send = $_info['notice_send'];
			if($notice_send && ($notice_send_info = jsg_member_info($notice_send, 'nickname'))) {
				$_info['send_from'] = $notice_send_info['nickname'];
				$_info['send_from_uid'] = $notice_send_info['uid'];
				$_info['send_from_username'] = $notice_send_info['username'];
			}

			$pm_to_admin['list'][$id] = $_info;
			jconf::set('pm_to_admin', $pm_to_admin);

			$this->Messager('编辑成功');
		}

		include template('admin/pm_to_admin_info');
	}

}

?>
