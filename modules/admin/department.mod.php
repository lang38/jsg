<?php

/**
 * 部门管理
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
		$cids = jget('cids');
		$id = jget('id');
		if(is_array($cids)){
			foreach($cids as $val){
				if($val){$cid = $val;}
			}
		}elseif($id){
			$rowd = $this->CpLogic->Getrow($id,'department');
			$cid = $rowd['cid'];
			$row = $this->CpLogic->Getrow($rowd['cid'],'company');
			if($rowd['upid']){
				$ids = $rowd['upid'];
			}
			$cids = explode(',',$row['upid']);
			if($cids[0]>0){
				$cids[] = $cid;
			}else{
				$cids[0] = $cid;
			}
		}else{
			$cids = array(0);
			$cid = 0;
		}
		if(@is_file(ROOT_PATH . 'include/logic/cp.logic.php') && $this->Config['company_enable'] && $this->Config['department_enable']){
			$selectco = '';
			$i = 0;
			foreach($cids as $k => $v){
				$j = $k+1;
				if($v || $i == 0){
					$company = $this->CpLogic->get_list_company($i,'id ASC');
					$selectco .= '<span id="nextcompany_'.$k.'"><select name="cids[]" onchange="listnextc(this,\''.$j.'\');"><option value="">请选择...</option>';
					foreach($company as $val){
						if($val['id'] == $v){
							$selectco .= '<option value="'.$val['id'].'" selected>'.$val['name'].'</option>';
						}else{
							$selectco .= '<option value="'.$val['id'].'">'.$val['name'].'</option>';
						}
					}
					$selectco .= '</select></span>';
				}
				$i = $v;
			}
			if($cid){$lists = $this->CpLogic->GetTable('department',0,$cid);}else{$lists = '';}
			$action = '';
			include(template('admin/department'));
		}else{
			if(!is_file(ROOT_PATH . 'include/logic/cp.logic.php')){
				$cp_not_install = true;
			}
			include(template('admin/cp_ad'));
		}
	}
	function Add()
	{
		$pid = (int)$this->Get['did'];
		if($pid){$dep = $this->CpLogic->Getone($pid,'department');}
		$cid = (int)$this->Get['cid'];
		if($cid>0){
			$cep = $this->CpLogic->Getone($cid);
		}else{
			$cid = -1;
			$cep = '总部';
		}
		$action = 'add';
		$formpost = 'admin.php?mod=department&code=save';
		include(template('admin/department'));
	}
	function Mod()
	{
		$did = (int)$this->Get['id'];
		$department = $this->CpLogic->Getrow($did,'department');
		if($department['parentid']){$dep = $this->CpLogic->Getone($department['parentid'],'department');}
		if($department['cid']>0){
			$cep = $this->CpLogic->Getone($department['cid']);
		}else{
			$cep = '总部';
		}
		$leaders = jlogic('cp')->get_manager_users('department','leader',$did);
		$managers = jlogic('cp')->get_manager_users('department','manager',$did);
		$action = 'mod';
		$formpost = 'admin.php?mod=department&code=msave';
		include(template('admin/department'));
	}
	function Save()
	{
		$data = $this->Post;
		if($data['cid'] && $data['name'] && ($this->CpLogic->check_codding($data['coding'])) && ($id=$this->CpLogic->create('department',$data))){
			$this->Messager("添加成功","admin.php?mod=department&id=".$id);
		}else{
			$this->Messager("添加失败，数据填写不完整或不合法！");
		}
	}
	function Msave()
	{
		$data = $this->Post;
		if($data['name'] && $data['id'] && ($this->CpLogic->check_codding($data['coding'])) && ($id=$this->CpLogic->modify('department',$data))){
			$this->Messager("修改成功","admin.php?mod=department&id=".$id);
		}else{
			$this->Messager("修改失败，数据填写不完整或不合法！");
		}
	}
	function Del()
	{
		$id = (int)$this->Get['id'];
		if($this->CpLogic->delete('department',$id)){
			$this->Messager("删除成功");
		}else{
			$this->Messager("删除失败，有下属部门，不可直接删除；要删除，请先删除下属部门！");
		}
	}
	function Cache()
	{
		$return = $this->CpLogic->countdata('department');
		if($return){
			$this->Messager("更新成功");
		}
	}
}
?>