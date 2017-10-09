<?php
/**
 * 文件名：job.mod.php
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 用户岗位模块。
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
			case 'delete':
				$this->Delete();
				break;
			case 'add':
				$this->Add();
				break;
			case 'modify':
				$this->Modify();
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
		if(@is_file(ROOT_PATH . 'include/logic/cp.logic.php') && @is_file(ROOT_PATH . 'include/logic/job.logic.php') && $this->Config['company_enable']){
			$per_page_num = min(500, max(20, (int) (isset($_GET['pn']) ? $_GET['pn'] : $_GET['per_page_num'])));
			$info = jlogic('job')->get_joblist($per_page_num);
			$total_record = $info['total'];
			$page_arr = $info['page'];
			$job_list = $info['list'];
			include(template('admin/job'));
		}else{
			if(!(@is_file(ROOT_PATH . 'include/logic/cp.logic.php') || @is_file(ROOT_PATH . 'include/logic/job.logic.php'))){
				$cp_not_install = true;
			}
			include(template('admin/cp_ad'));
		}
	}

	function Add()
	{
		$jobname = strip_tags($this->Post['jobname']);
		if(empty($jobname)){
			$this->Messager("请输入岗位名称",-1);
		}
		$return = jlogic('job')->add_job($jobname);
		if($return > 0){
			$this->Messager("添加成功",'admin.php?mod=job');
		}else{
			$this->Messager("{$jobname} 岗位已经存在",-1);
		}
	}

	function Modify()
	{
		$ids = (int) $this->Get['ids'];
		$action = "admin.php?mod=job&code=domodify";
		$joblist = jlogic('job')->id2job($ids);
		include template('admin/job');
	}

	function DoModify()
	{
		$jobid = (int) $this->Post['jobid'];
		$jobname = strip_tags($this->Post['jobname']);
		$oldjobname = $this->Post['oldjobname'];
		if($jobname != $oldjobname){
			$return = jlogic('job')->modify_job($jobid,$jobname);
			if($return > 0){
				$this->Messager("编辑成功",'admin.php?mod=job');
			}else{
				$this->Messager("{$jobname} 岗位已经存在");
			}
		}else{
			$this->Messager("没做任何修改",'admin.php?mod=job');
		}
	}

	function Delete()
	{
		$ids = (array) ($this->Post['ids'] ? $this->Post['ids'] : $this->Get['ids']);
		if(!$ids) {
			$this->Messager("请指定要删除的对象");
		}
				jlogic('job')->delete_job($ids);
		$this->Messager("操作成功");
	}
}
?>