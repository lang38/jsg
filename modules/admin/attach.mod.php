<?php

/**
 * 文件名： attach.mod.php
 * 作  者：　狐狸 <foxis@qq.com>
 * @version $Id: attach.mod.php 5263 2013-12-13 07:55:28Z chenxianfeng $
 * 功能描述： attach for JishiGou
 * 版权所有： Powered by JishiGou attach 1.0.0 (a) 2005 - 2099 Cenwor Inc.
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
		ob_start();
		switch($this->Code)
		{
			case 'do_modify_setting':
                {
                    $this->DoModifySetting();
                    break;
                }
			default :
    			{
    				$this->Main();
    				break;
    			}
		}
		$this->ShowBody(ob_get_clean());
	}

	function Main()
	{
        $attach = $attach_config = jconf::get('attach');
        if(!($attach_config && $attach_config['request_file_type']))
        {
            $attach = $attach_config = array
            (
                'enable' => 1,
				'qun_enable' => 1,
				'request_file_type' => 'zip|rar|txt|doc|xls|pdf|ppt|docx|xlsx|pptx',
            	'request_size_limit' => 2000,
				'request_files_limit' => 3,
				'score_min' => 1,
				'score_max' => 20,
				'no_score_user' => '2',
            );

            jconf::set('attach',$attach_config);
        }


		$role_list = array();
		$query = DB::query("select `name`, `id` as `value` from ".DB::table('role')." where `id`!='1' order by `type` desc, `id` asc");
		$v = 0;
		while (false != ($row = DB::fetch($query))) {
			$v = $row['value'];
			$role_list[$v] = $row;
		}
		$filterhtml = $this->jishigou_form->CheckBox('no_score_user[]', $role_list, explode(',',$attach_config['no_score_user']));
		$attach_enable_radio = $this->jishigou_form->YesNoRadio('attach[enable]',(int) ($attach_config['enable'] && $this->Config['attach_enable']));
		$qun_attach_enable_radio = $this->jishigou_form->YesNoRadio('attach[qun_enable]',(int) ($attach_config['qun_enable'] && $this->Config['qun_attach_enable']));

        include(template('admin/attach'));
	}

    function DoModifySetting()
    {
        $attach = $this->Post['attach'];
        $attach_config_default = $attach_config = jconf::get('attach');
        $attach_config['enable'] = ($attach['enable'] ? 1 : 0);
		$attach_config['qun_enable'] = ($attach['qun_enable'] ? 1 : 0);
		$attach_config['request_file_type'] = jlogic('attach')->type_filter($attach['request_file_type']);
        $attach_config['request_size_limit'] = min(max(1,(int)$attach['request_size_limit']),51200);
        $attach_config['request_files_limit'] = min(max(1,(int)$attach['request_files_limit']),50);
		$attach_config['score_min'] = max(1,(int)$attach['score_min']);
		$attach_config['score_max'] = max(1,(int)$attach['score_max']);
		if($attach_config['score_max'] < $attach_config['score_min']){$attach_config['score_max'] = $attach_config['score_min'];}
		$attach_config['no_score_user'] = is_array($this->Post['no_score_user']) ? implode(',',$this->Post['no_score_user']) : '';

        $config = array();
		if($attach_config_default != $attach_config)
        {
        	jconf::set('attach',$attach_config);
        }

        if($attach_config['enable']!=$this->Config['attach_enable']){$config['attach_enable'] = $attach_config['enable'];}
		if($attach_config['qun_enable']!=$this->Config['qun_attach_enable']){$config['qun_attach_enable'] = $attach_config['qun_enable'];}
		if($attach_config['request_file_type']!=$this->Config['attach_file_type']){$config['attach_file_type'] = $attach_config['request_file_type'];}
		if($attach_config['request_size_limit']!=$this->Config['attach_size_limit']){$config['attach_size_limit'] = $attach_config['request_size_limit'];}
		if($attach_config['request_files_limit']!=$this->Config['attach_files_limit']){$config['attach_files_limit'] = $attach_config['request_files_limit'];}
		if($config)
        {
            jconf::update($config);
        }

        $this->Messager("修改成功");
    }
}

?>
