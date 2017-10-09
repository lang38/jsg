<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename other.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2014 1369159804 1040 $
 */




if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class ModuleObject extends MasterObject{

	function ModuleObject($config){

		$this->MasterObject($config);

		$this->Execute();

	}

	
	function Execute(){
		ob_start();
		switch ($this->Code) {
			case 'introduce':
				$this->Introduce();
				break;
			default:
				$this->Mail();
				break;
		}
		$body=ob_get_clean();
		$this->ShowBody($body);
	}

	function Mail(){
		$this->Messager("页面不存在",null);
	}

	
	function Introduce(){
		$http_user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);

		$is_android = (false === strpos($http_user_agent, 'android')) ? false : true;

		$is_iphone = (false === strpos($http_user_agent, 'iphone')) ? false : true;

		include template('wap_introduce');

	}
}