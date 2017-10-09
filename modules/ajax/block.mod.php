<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename block.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013-11-11 1564150060 718 $
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



		$this->TopicLogic = jlogic('topic');

		if(MEMBER_ID < 1){
			exit("你需要先登录才能继续本操作");
		}

		$this->Execute();
	}

	
	function Execute()
	{
        ob_start();
        switch($this->Code){

        	default:
        		break;
		}
        response_text(ob_get_clean());
	}

}
?>