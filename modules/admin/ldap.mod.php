<?php
/**
 * 文件名： ldap.mod.php
 * 作  者：　狐狸 <foxis@qq.com>
 * @version $Id: ldap.mod.php 3727 2013-05-28 03:35:00Z wuliyong $
 * 功能描述： ldap for JishiGou
 * 版权所有： Powered by JishiGou ldap 1.0.0 (a) 2005 - 2099 Cenwor Inc.
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
			case 'ldap_save':
                {
                    $this->DoSave();
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
		if(@is_file(ROOT_PATH . 'include/class/ldap.class.php')){
			$ldap_server_enable = true;
			$ldap = jclass('ldap')->initialize();

			$ldap_enable = $this->jishigou_form->YesNoRadio('ldap_enable',(int) ($ldap['enable']));
		}else{
			$ldap_server_enable = false;
		}
		include(template('admin/ldap'));
	}

    function DoSave()
    {
		$msg = array(
			'1' => "修改成功。",
			'0' => "修改失败！",
			'-1' => "您的系统<font color='red'>不支持</font>该功能，请检查您服务器是否安装和配置了<font color='red'>php_ldap.dll</font>模块！",
			'-2' => "<font color='red'>无法连接域服务器</font>，请检查您填写的服务器地址或端口是否正确！",
			'-3' => "您没有填写<font color='red'>AD域服务器地址</font>，请返回重新填写！",
			'-4' => "您没有填写<font color='red'>您的域帐号或填写错误</font>，请返回重新填写！"
		);
		$return = 0;
		if(@is_file(ROOT_PATH . 'include/class/ldap.class.php')){
			$return = jclass('ldap')->adsave($this->Post['ldap_email'],$this->Post['ldap_enable'],$this->Post['ldap_host'],$this->Post['ldap_port']);
		}
        $this->Messager($msg[$return],'',5);
    }
}

?>
