<?php

/**
 *
 * 企业目录模块(单位、部门、岗位)
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: enterpriselist.mod.php 4576 2013-09-25 08:24:40Z chengxiaojiao $
 */


if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}


class ModuleObject extends MasterObject
{
	var $cache_ids_limit = 300;

    
	function ModuleObject($config)
	{
		$this->MasterObject($config);
		
        $this->Execute();
	}

	function Execute()
	{
		switch($this->Code)
		{
			case 'getcompany':
				$this->getcompany();
				break;
			case 'getdepartment':
				$this->getdepartment();
				break;
			case 'getjob':
				$this->getjob();
				break;
			default :
    			$this->Main();
    			break;
		}
	}

    
	function Main()
	{
		api_output('enterpriselist api is ok');
	}

}

?>
