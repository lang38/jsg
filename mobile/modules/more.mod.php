<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename more.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013-11-11 299152845 1091 $
 */




if (!defined('IN_JISHIGOU')) {
    exit('Access Denied');
}

class ModuleObject extends MasterObject
{
	var $CacheConfig;

	function ModuleObject($config)
	{
		$this->MasterObject($config);
		$this->CacheConfig = jconf::get('cache');
				Mobile::is_login();
		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch ($this->Code) {
			case 'introduce':
				$this->introduce();
				break;
			case "about":
				$this->about();
				break;
			default:
				$this->main();
		}
		$body=ob_get_clean();
		echo $body;
	}

	function main()
	{
		include(template('more'));
	}

	function about()
	{
		include(template('about'));
	}

	function introduce(){
		$http_user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);

		$is_android = (false !== strpos($agent, 'android')) ? true : false;

		$is_iphone = (false !== strpos($agent, 'iphone')) ? true : false;
		include(template('introduce'));
	}
}
?>