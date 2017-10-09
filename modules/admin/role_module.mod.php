<?php
/**
 *
 * 后台动作模块设
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: role_module.mod.php 5046 2013-11-20 07:50:10Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject
{

	var $ID = 0;

	
	function ModuleObject($config)
	{
		$this->MasterObject($config);

		$this->ID = max(0, (int) get_param('id'));

		if(true!==JISHIGOU_FOUNDER) {
			$this->Messager("您没有相应的权限，仅限网站创始人访问", null);
		}


		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
			case 'modify':
				$this->Main();
				break;
			case 'domodify':
				$this->DoModify();
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
		$this->Modify();
	}

	function Modify()
	{
		$sql="SELECT * from ".TABLE_PREFIX."role_module order by module";
		$query = $this->DatabaseHandler->Query($sql);
		$module_list=array();
		while ($row=$query->GetRow())
		{
			$module_list[$row['module']]=$row['name'];
		}
		include(template('admin/role_module'));
	}

	function DoModify()
	{
				if(($new_module=trim($this->Post['new_module']))
		&& (trim($new_module_name=$this->Post['new_module_name'])))
		{
			jtable('role_module')->replace(array("module"=>$new_module,"name"=>$new_module_name));
		}

				$module_list=(array)$this->Post['module'];
		foreach ($module_list as $module)
		{
			jtable('role_module')->replace($module);
		}

				$delete_list=(array)$this->Post['delete'];
		if($delete_list)
		{
			$module_in = " `module` IN (" . jimplode($delete_list) . ") ";
			DB::query("DELETE FROM ".TABLE_PREFIX."role_module where ".$module_in);
						$sql="DELETE FROM ".TABLE_PREFIX."role_action where ".$module_in;
			$this->DatabaseHandler->Query($sql);
		}


		$this->Messager("修改成功");
	}
}
?>
