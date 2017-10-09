<?php
/**
 *
 * 后台角色操作模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: role.mod.php 5571 2014-02-25 02:48:09Z wuliyong $
 */

if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class ModuleObject extends MasterObject
{
	
	var $ID = 0;

	
	var $ModuleList;

	var $RoleIds = array(1,2,3,4,5,7,108,109,110,111,112,113,114,115,116,117,118);

	
	function ModuleObject($config)
	{
		$this->MasterObject($config);
		$this->ID = (int)$this->Get['id']?(int)$this->Get['id']:(int)$this->Post['id'];

		$sql="SELECT name,module from ".TABLE_PREFIX.'role_module';
		$query = $this->DatabaseHandler->Query($sql);
		while ($row=$query->GetRow()) {
			$this->ModuleList[$row['module']]=$row['name'];
		}

				$this->smods = array('role', 'role_action', 'role_module', 'db');


		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
			case 'copy':
				$this->Copy();
				break;

			case 'add':
				$this->Add();
				break;
			case 'doadd':
				$this->DoAdd();
				break;

			case 'admin':
			case 'modify':
				$this->Modify();
				break;
			case 'domodify':
				$this->DoModify();
				break;

			case 'delete':
				$this->delete();
				break;

			case 'do_modify_by_admin':
				$this->DoModifyByAdmin();
				break;

			default:
				$this->Main();
				break;
		}
		$body = ob_get_clean();

		$this->ShowBody($body);

	}

	
	function Main()
	{
		$role_type=in_array($this->Get['type'],array('admin','normal'))
		?$this->Get['type']
		:'normal';
		$sql="select *
			FROM
				".TABLE_PREFIX.'role'."
			WHERE
				`type`='{$role_type}'
			ORDER BY
				`creditshigher` ASC, `creditslower` ASC, `rank` ASC, `id` ASC";
		$query = $this->DatabaseHandler->Query($sql);
		$role_list = array();
		$role_ids = array();
		while(false != ($row = $query->GetRow())) {
			$role_list[] = $row;
			$role_ids[] = $row['id'];
		}

		$this->_experience();


				if('admin' == $role_type) {
			$p = array(
				'role_id' => $role_ids,
				'count' => 9999,
			);
			$rets = jsg_member_get($p, 0);
			$admin_users = $rets['list'];
		}

		if($this->Config['jishigou_founder']) {
			$p = array(
				'uid' => explode(',', $this->Config['jishigou_founder']),
				'count' => 999,
			);
			$rets = jsg_member_get($p);
			$founder_users = $rets['list'];
		}


		include template('admin/role_list');

	}


	function Copy() {
		$id = (int) get_param('id');
		if($id < 1) {
			$this->Messager("请指定一个要Copy的对象");
		}

		$role_info = DB::fetch_first("select * from ".DB::table('role')." where `id`='$id'");
		if(!$role_info) {
			$this->Messager("请指定一个正确的ID");
		}

		$datas = $role_info;
		$datas['system'] = 0;		unset($datas['id']);
		$new_id = DB::insert('role', $datas, 1);

		if($new_id > 0) {
			$this->Messager("复制成功，现在为您跳转到编辑页面", "admin.php?mod=role&code=modify&id=$new_id");
		} else {
			$this->Messager("复制失败");
		}
	}


	
	function Add()
	{

		$action="admin.php?mod=role&code=doadd";
		$title="添加";
		$sql="SELECT * FROM ".TABLE_PREFIX.'role_action';
		$query = $this->DatabaseHandler->Query($sql);
		$privilege_list=$query->GetAll();

		$options=array(
		array('name'=>'管理员组','value'=>'admin'),
		array('name'=>'普通用户组','value'=>'normal')
		);


		$type_select=$this->jishigou_form->Select('type',$options);

		$privileges=explode(',',$role_info['privilege']);
		foreach($privilege_list as $key=>$privilege)
		{
			if($privilege['allow_all']==1 && false === JISHIGOU_FOUNDER)
			{
				$privilege['disabled']=" disabled";
			}

			$module_name=isset($this->ModuleList[$privilege['module']])
			?$this->ModuleList[$privilege['module']]
			:"[其它]权限";

			if(in_array($privilege['id'],$privileges) or
			$privileges[0]=="*" or
			$privilege['allow_all']==1)
			{
				$privilege['checked']=" checked";
			}

			$privilege['link']="admin.php?mod=role_action&code=modify&id=".$privilege['id'];

			$privilege['name']=strpos($privilege['action'],"_other")!==false?"<font color='#660099'>{$privilege['name']}</font>":$privilege['name'];
			$module_list[($privilege['is_admin'] ? "后台权限" : "前台权限")][$module_name][]=$privilege;
		}
		krsort($module_list);

		include template('admin/role_info');
	}

	
	function DoAdd()
	{
		$n = jpost('name', 'txt');
		if(empty($n)) {
			$this->Messager('名称不能为空', -1);
		}
		if(false != (jtable('role')->info(array('name' => $n)))) {
			$this->Messager('名称已经存在了', -1);
		}

		$data = array(
			'name'=>$n,
			'type'=>$this->Post['type'],
			'creditshigher'=>$this->Post['creditshigher'],
			'creditslower'=>$this->Post['creditslower'],
			'privilege'=>implode(',',(array)$this->Post['privilege']),
			'system' => 0,
		);

		$result = jtable('role')->insert($data,1);
		if($result)
		{
			$this->_experience();
			if(!empty($_FILES['icon']['name'])){
				$this->upload_pic($result);
			}
			$this->Messager("添加成功",'admin.php?mod=role');
		}
		else
		{
			$this->Messager("添加失败");
		}
	}


	
	function Modify()
	{
				$role_info = DB::fetch_first("SELECT * FROM ".DB::table('role')." WHERE `id`='{$this->ID}'");
				if(!$role_info) {
			$this->Messager("您要编辑的角色信息已经不存在!");
		}

		$action="admin.php?mod=role&code=domodify";
		$title="编辑用户组权限";
		$wheres = array();
		if(true !== JISHIGOU_FOUNDER) {
			$wheres[] = " `module` NOT IN ('".implode("','", $this->smods)."') ";
		}
		if('normal'==$role_info['type']) {
			$wheres[] = " `is_admin`='0' ";
		}
		$where = ($wheres ? (" WHERE " . implode(" AND ", $wheres)) : "");
		$sql="SELECT * FROM ".TABLE_PREFIX.'role_action'.$where;
		$query = $this->DatabaseHandler->Query($sql);
		$privilege_list=$query->GetAll();

		$privileges=explode(',',$role_info['privilege']);
		foreach($privilege_list as $privilege) {
			if($privilege['allow_all']==1 && false === JISHIGOU_FOUNDER) {
				$privilege['disabled']=" disabled ";
			}

			$module_name=isset($this->ModuleList[$privilege['module']])
			?$this->ModuleList[$privilege['module']]
			:"[其它]权限";

			if(in_array($privilege['id'],$privileges) or
			$privileges[0]=="*" or
			$privilege['allow_all']==1) {
				$privilege['checked']=" checked ";
			}

			$privilege['link']="admin.php?mod=role_action&code=modify&id=".$privilege['id'];

			$privilege['name']=strpos($privilege['action'],"_other")!==false?"<font color='#660099'>{$privilege['name']}</font>":$privilege['name'];
			$module_list[($privilege['is_admin'] ? "后台权限" : "前台权限")][$module_name][]=$privilege;
		}
		krsort($module_list);


		if($this->ID > 1) {
			$role_list_default = array();
			$role_list_default[0] = array('value'=>0, 'name'=>'<b>0、不限制，允许所有</b>',);
			$role_list_default[-1] = array('value'=>-1, 'name'=>'-1、限制，只允许自身',);
			$role_list_default[-2] = array('value'=>-2, 'name'=>'-2、不允许，限制所有',);
			$role_list_default[-3] = array('value'=>-3, 'name'=>'-3、自定义设置（请在下面所列的用户组选项中进行选择）<br /><br />',);

			$role_list = array();
			$query = DB::query("select `name`, `id` as `value` from ".DB::table('role')." where `id`!='1' order by `type` desc, `id` asc");
			$v = 0;
			while (false != ($row = DB::fetch($query))) {
				$v = $row['value'];

				$role_list[$v] = $row;
			}
			$role_list[$v]['name'] .= '<br /><br />';

			foreach($role_info as $k=>$v) {
				if($v && 'allow_' == substr($k, 0, 6)) {
					$v = explode(',', $v);
					$role_info[$k] = $v;
				}
			}


		}


		$tpl = 'admin/role_info';
		if(true === DEBUG && true===JISHIGOU_FOUNDER && 2==$this->ID && 'admin'==$this->Code) {
			$tpl = 'admin/role_info_admin';
		}
		include template($tpl);
	}


	
	function DoModify()
	{
		$role = jtable('role')->info($this->ID);
		if ($role==false) {
			$this->Messager("该角色已经不存在了", null);
		}

		$n = jpost('name', 'txt');
		if(empty($n) || (($_info = jtable('role')->info(array('name'=>$n))) && $_info['id']!=$role['id'])) {
			$this->Messager('名称不能为空，或已经存在了', -1);
		}

		$query = DB::query("select * from ".DB::table('role_action'));
		$role_action_list = array();
		$sids = array();
		while(false != ($row = DB::fetch($query))) {
			$role_action_list[$row['id']] = $row;
			if(in_array($row['module'], $this->smods)) {
				$sids[$row['id']] = $row['id'];
			}
		}

		$iiddss = array();
		if($this->Post['privilege']) {
			foreach((array) $this->Post['privilege'] as $iid) {
				$iid = (int) $iid;
				if($iid > 0 && isset($role_action_list[$iid])) {
					$iiddss[$iid] = $iid;
				}
			}
			if(true !== JISHIGOU_FOUNDER) {
								$role_pids = array();
				foreach(explode(',', $role['privilege']) as $oid) {
					$role_pids[$oid] = $oid;
				}
				foreach($sids as $sid) {
					if(isset($role_pids[$sid])) {
						$iiddss[$sid] = $sid;
					} else {
						unset($iiddss[$sid]);
					}
				}
			}
			sort($iiddss);
		}


		$data=array(
			'id'=>$this->ID,
			'name'=>$n,
			'creditshigher'=>(int) $this->Post['creditshigher'],
			'creditslower'=>(int) $this->Post['creditslower'],
			'privilege'=>implode(',',$iiddss),
		);
		$data = $this->_process_allows($role, $data);

		jtable('role')->update($data);

		if($result===false)
		{
			$this->Messager("编辑失败");
		}
		else
		{
			jtable('role')->cache_rm($this->ID);
			$this->_experience();
			if(!empty($_FILES['icon']['name'])){
				$this->upload_pic($this->ID);
			}
			$this->Messager("编辑成功");
		}

	}

	function delete() {
		$id = jget('id', 'int');
		if($id < 1) {
			$this->Messager('请指定一个您要删除的用户组ID');
		}
		$info = jtable('role')->info($id);
		if(!$info) {
			$this->Messager('您要删除的用户组已经不存在了');
		}
		if(true === JISHIGOU_FOUNDER && !$info['system']) {
			$count = jtable('members')->count(array('role_id' => $id));
			if($count > 0) {
				$this->Messager("您要删除的用户组下面还有存在用户，不能直接删除；请先编辑此用户组下面的用户到其他用户组下，再执行此操作。");
			} else {
				jtable('role')->delete($id);
			}
			$this->Messager('操作成功');
		} else {
			$this->Messager('只有网站创始人能删除用户自定义添加的用色组');
		}
	}


	
	function _experience()
	{
		$sql="select *
			FROM
				".TABLE_PREFIX.'role'."
			ORDER BY
				`creditshigher` ASC, `creditslower` ASC, `rank` ASC, `id` ASC";

		$query = $this->DatabaseHandler->Query($sql);
		$experience_list = array();
		$rank = 0;
		while(false != ($row = $query->GetRow()))
		{
			if(('normal' == $row['type']) && ($row['creditshigher'] > 0 || $row['creditslower'] > 0))
			{
				if($rank != $row['rank'])
				{
					$this->DatabaseHandler->Query("update ".TABLE_PREFIX."role set `rank`='$rank' where `id`='{$row['id']}'");

					$row['rank'] = $rank;
				}

				$rank +=1;

				if($row['rank'] > 0)
				{
					$experience_list[$row['rank']] = array(
						'level' => $row['rank'],
						'start_credits' => $row['creditshigher'],
						'order' => $row['rank'],
						'enable' => 1,
					);
				}
			}
			else
			{
				if($row['rank'])
				{
					$this->DatabaseHandler->Query("update ".TABLE_PREFIX."role set `rank`='0' where `id`='{$row['id']}'");

					$row['rank'] = 0;
				}
			}
		}

				if($experience_list) {
			$experience = jconf::get('experience');
			if($experience_list != $experience['list']) {
				$experience['list'] = $experience_list;

				jconf::set('experience', $experience);
			}
		}
	}

	function _process_allows($role, $data = array(), $posts = array()) {
		$posts = ($posts ? $posts : $this->Post);

		foreach($posts as $k=>$v) {
			if('allow_' == substr($k, 0, 6)) {
				$vv = implode(',', $v);
				$vs = array();
				if(jsg_find($vv, 0)) {
					$vs[] = 0;
				} elseif (jsg_find($vv, -1)) {
					$vs[] = -1;
					$vs[] = $role['id'];
				} elseif (jsg_find($vv, -2)) {
					$vs[] = -2;
				} else {
					foreach($v as $i) {
						$i = (int) $i;
						if($i > 0) {
							$vs[] = $i;
						}
					}
					if($vs) {
						$vs[] = -3;
					}
				}

				$vss = 0;
				if($vs) {
					array_unique($vs);
					sort($vs);

					$vss = implode(',', $vs);
				}
				$data[$k] = $vss;
			}
		}

		return $data;
	}

	

	function upload_pic($id){

		$image_name = $id.".gif";
		$image_path = RELATIVE_ROOT_PATH . 'images/role/';
		$image_file = $image_path . $image_name;

		if (!is_dir($image_path))
		{
			jio()->MakeDir($image_path);
		}

		jupload()->init($image_path,'icon',true);
		jupload()->setMaxSize(1000);
		jupload()->setNewName($image_name);
		$result=jupload()->doUpload();

		if($result)
        {
			$result = is_image($image_file);
		}
		if(!$result)
        {
			unlink($image_file);
			return false;
		}else{
			DB::update('role', array('icon' => $image_file), array('id' => $id));
		}
		return true;
	}

}

?>