<?php

/**
 * 单位管理
 *
 * @author 狐狸<foxis@qq.com>
 * @package JishiGou
 */
if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $CpLogic;
	
	function ModuleObject($config)
	{
		$this->MasterObject($config);
		if(@is_file(ROOT_PATH . 'include/logic/cp.logic.php')){
			$this->CpLogic = jlogic('cp');
		}
		$this->Execute();
	}
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
			case 'add':
				$this->Add();
				break;
			case 'mod':
				$this->Mod();
				break;
			case 'del':
				$this->Del();
				break;
			case 'save':
				$this->Save();
				break;
			case 'msave':
				$this->Msave();
				break;
			case 'cache':
				$this->Cache();
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
		$id = jget('id');
		if(@is_file(ROOT_PATH . 'include/logic/cp.logic.php') && $this->Config['company_enable']){
			$lists = $this->CpLogic->GetTable();
			if($lists){
				if($id){
					$row = $this->CpLogic->Getrow($id,'company');
					if($row['upid']){
						$ids = $row['upid'];
					}
				}
				$action = '';
			}else{
				$data = array(
					'parentid' => 0,
					'name' => '总部'
				);
				$this->CpLogic->create('company',$data);
				$lists = $this->CpLogic->GetTable();
			}
			include(template('admin/company'));
		}else{
			if(!is_file(ROOT_PATH . 'include/logic/cp.logic.php')){
				$cp_not_install = true;
			}
			include(template('admin/cp_ad'));
		}
	}
	function Add()
	{
		$pid = jget('cid','int','G');
		if($pid){
			$cep = $this->CpLogic->Getone($pid);
		}
		$action = 'add';
		$formpost = 'admin.php?mod=company&code=save';
		include(template('admin/company'));
	}
	function Mod()
	{
		$cid = jget('id','int','G');
		$company = $this->CpLogic->Getrow($cid);
		$pid = $company['parentid'];
		if($pid){$cep = $this->CpLogic->Getone($pid);}
		$leaders = jlogic('cp')->get_manager_users('company','leader',$cid);
		$managers = jlogic('cp')->get_manager_users('company','manager',$cid);
		$action = 'mod';
		$formpost = 'admin.php?mod=company&code=msave';
		include(template('admin/company'));
	}
	function Save()
	{
		$data = $this->Post;
		if($data['name'] && ($this->CpLogic->check_codding($data['coding'])) && ($id=$this->CpLogic->create('company',$data))){
			$this->Messager("添加成功","admin.php?mod=company&id=".$id);
		}else{
			$this->Messager("添加失败，数据填写不完整或不合法！");
		}
	}
	function Msave()
	{
		$data = $this->Post;
		if($data['name'] && $data['id'] && ($this->CpLogic->check_codding($data['coding'])) && ($id=$this->CpLogic->modify('company',$data))){
			$this->Messager("修改成功","admin.php?mod=company&id=".$id);
		}else{
			$this->Messager("修改失败，数据填写不完整或不合法！");
		}
	}
	function Del()
	{
		$id = jget('id','int','G');
		if($this->CpLogic->delete('company',$id)){
			$this->Messager("删除成功");
		}else{
			$this->Messager("删除失败，有下属单位，不可直接删除；要删除，请先删除下属单位！");
		}
	}
	function Cache()
	{
		$return = $this->CpLogic->countdata('company');
		if($return){
			$this->Messager("更新成功");
		}
	}
}
?>