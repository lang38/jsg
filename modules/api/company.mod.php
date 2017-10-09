<?php

/**
 * 文件名： company.mod.php
 * 作  者：　狐狸 <foxis@qq.com>
 * @version $Id: company.mod.php 4552 2013-09-22 08:22:22Z chenxianfeng $
 * 功能描述： api for JishiGou
 * 版权所有： Powered by JishiGou API 1.0.0 (a) 2005 - 2099 Cenwor Inc.
 * 公司网站： http://cenwor.com
 * 产品网站： http://jishigou.net
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
		switch($this->Code)
		{
			case 'topic':
                $this->topic();
                break;
            case 'getcompany':
				$this->getcompany();
				break;
			case 'getdepartment':
				$this->getdepartment();
				break;
			case 'getjob':
				$this->getjob();
				break;
			case 'company':
                $this->company();
                break;
			case 'department':
                $this->department();
                break;
			default :
				$this->Main();
				break;
		}
	}

	function Main()
	{
		api_output('company api is ok');
	}

		function company(){
		$cid = jget('cid','int');
		$child_companys = jlogic('cp')->get_down_cp('company',$cid);
		api_output(array_merge($child_companys));
	}

		function department(){
		$cid = jget('cid','int');		$did = jget('did','int');		$child_departments = jlogic('cp')->get_down_cp('department',$did,$cid);
		api_output(array_merge($child_departments));
	}

	
	function topic()
	{
		$user = $this->_init_user(MEMBER_ID);
		$id = (int)$user['companyid'];		if($id == 0){
			api_output('You do not belong to any company, please join a company');exit;
		}
		$companyid = $user['companyid'];
		$company = $user['company'];
		$cid = jget('cid','int');
		if($cid > 0 && $cid != $id){			$istrue = jlogic('cp')->is_exists($cid);
			if(!$istrue){
				api_output('The company does not exist');exit;
			}
			$child_companys = jlogic('cp')->get_down_cp('company',$id);
			if($child_companys){
				$child_ids = array_keys($child_companys);
				if(in_array($cid,$child_ids)){
					$id = $cid;
					$companyid = $cid;
					$company = jlogic('cp')->Getone($cid);
				}else{
					api_output('This company Does not belong Your Subordinate companys');exit;
				}
			}else{
				api_output('Your company has no Subordinate companys');exit;
			}
		}
		$company_info = array(
			'id'=>$companyid,
			'name'=>$company,
		);

				if($id == $user['companyid']){
			jlogic('member')->clean_new_remind('company_new', MEMBER_ID);
		}

		$uids = jlogic('cp')->getcpuids('company',$id);
		$sql_wheres = array("uid"=>"`uid` IN(".jimplode($uids).")");
		$sql_wheres['type'] = " `type` = 'company' ";
		$this->_topic_list('new',$sql_wheres,$order,array(),array('company'=>array_merge($company_info)));
	}

	function getcompany(){
		$company_parentid = jget('id','int');
		$limit = 100;
		$sql_where['parentid'] = 0;
		if ($company_parentid > 0) {
			$sql_where['parentid'] = $company_parentid;
		}
		$sql_where['page_num'] = $limit;
		$sql_where['sql_field'] = 'id,parentid,name';
		$data = jtable('company')->get($sql_where);
		$rets = $data['list'];

		foreach ($rets as $key => $value) {
			$have_nextcompany = jtable('company')->info(array('parentid'=>$value['id'])) ? 1 : 0;
			$rets[$key]['nextnode'] = $have_nextcompany;
		}
		api_output($rets);
	}

	function getdepartment(){
		$company_id = jget('id','int');
		$limit = 100;
		if ($company_id > 0) {
			$sql_where['cid'] = $company_id;
		}
		$sql_where['page_num'] = $limit;
		$sql_where['sql_field'] = 'id,cid,parentid,name';
		$data = jtable('department')->get($sql_where);
		$rets = $data['list'];
		api_output($rets); 
	}

	function getjob(){
		$data = jlogic('job')->get_job();
		$datas = array();
		foreach($data as $k => $v){
			$datas[]=array('id'=>$k,'name'=>$v);
		}
		$rets = $datas;
		api_output($rets);
	}
}
?>