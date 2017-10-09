<?php
/**
 * 文件名：plugin.mod.php
 * @version $Id: plugin.mod.php 3729 2013-05-28 04:33:29Z chenxianfeng $
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 微博插件独立模块外壳
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

		$this->Execute();
	}
	function Execute()
	{
		ob_start();
		$this->Main();
		$body=ob_get_clean();
		$this->ShowBody($body);
	}
	function Main()
    {
		global $_J;
		if(!isset($_J['plugins'])) {
			jlogic('plugin')->loadplugincache();
		}
		$pluginid = jget('id');
		if(!empty($pluginid)) {
			list($identifier, $module) = explode(':', $pluginid);
			$module = $module !== NULL ? $module : $identifier;
		}
		$member = $this->_member();

		if(!is_array($_J['plugins']['hookmod']) || !array_key_exists($pluginid, $_J['plugins']['hookmod'])) {
			$this->Messager("插件不存在或已关闭");
		}
		if(empty($identifier) || !preg_match("/^[a-z]+[a-z0-9_]*[a-z]+$/i", $identifier) || !preg_match("/^[a-z]+[a-z0-9_]*[a-z]+$/i", $module)) {
			$this->Messager("未定义的操作");
		}
		if(@!file_exists($modfile = PLUGIN_DIR.'/'.$identifier.'/'.$module.'.mod.php')) {
			$this->Messager("插件模块文件(".$modfile.")不存在或者插件文件不完整");
		}
		if($_J['plugins']['hookmod'][$pluginid]['role_id'] && 'admin' != MEMBER_ROLE_TYPE){
			$this->Messager("您没有权限进行该操作");
		}
		$this->Title = $_J['plugins']['hookmod'][$pluginid]['navname'];
		include $modfile;
    }
	function _member()
	{
		$member = $this->TopicLogic->GetMember(MEMBER_ID);
		return $member;
	}
}
?>