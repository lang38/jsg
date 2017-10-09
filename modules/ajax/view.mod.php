<?php
/**
 * 文件名：view.mod.php
 * @version $Id: view.mod.php 3619 2013-05-15 06:38:08Z wuliyong $
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 查看浏览模块
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $ID = 0;

	function ModuleObject($config)
	{
		$this->MasterObject($config);


		$this->ID = max(0, (int) ($this->Post['id'] ? $this->Post['id'] : $this->Get['id']));


		$this->Execute();
	}

	function Execute()
	{
		switch($this->Code)
		{

            case 'topic_content':
            	$this->TopicContentView();
            	break;
			case 'left_nav':
				$this->LeftNav();
				break;
			case 'top_notice':
				$this->TopNotice();
				break;

			default:
				$this->Main();
				break;
		}
	}

	function Main()
    {
        response_text('正在建设中……');
    }

		function LeftNav()
    {
        $type = jget('display','int','P');
		if($type){
			jsg_setcookie('leftnav','block');
		}else{
			jsg_setcookie('leftnav','none');
		}
    }

		function TopNotice()
    {
        $type = jget('display','int','P');
		if($type){
			jsg_setcookie('topnotice','block');
		}else{
			jsg_setcookie('topnotice','none');
		}
    }

    function TopicContentView()
    {

    	$TopicLogic = jlogic('topic');

    	$tid = is_numeric($this->ID) ? $this->ID : 0;
    	if($tid < 1)
    	{
    		js_alert_output('ID 不能为空');
    	}

    	$topic_info = $TopicLogic->Get($tid);
    	if(!$topic_info)
    	{
    		js_alert_output('内容已经不存在了');
    	}

    	$parent_list = array();
		if($topic_info['parent_id'])
		{
			$parent_id_list = array
			(
				$topic_info['parent_id'],
				$topic_info['top_parent_id'],
			);

			if($parent_id_list)
			{
				$parent_list = $TopicLogic->Get($parent_id_list);
			}
		}

				$sid = max(0, (int) ($this->Post['sid'] ? $this->Post['sid'] : $this->Get['sid']));
		if($sid < 1 || $sid==$tid)
		{
			unset($sid);
		}

		$TPT_ = ('TPT_' != $this->Post['TPT_'] ? $this->Post['TPT_'] : '');
		$topic_view = $this->Post['topic_view'] ? 1 : 0;
		$together_view = 1;
    	include(template('topic_content_view_ajax'));
    }

    function _check_login()
    {


		if(MEMBER_ID < 1)
		{
			json_error("请先登录或者注册一个帐号");
		}
    }

}

?>
