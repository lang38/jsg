<?php
/**
 * 文件名：plugin.mod.php
 * @version $Id: plugin.mod.php 3700 2013-05-27 07:27:54Z wuliyong $
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 插件模块单独用到的AJAX类
 */
if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{

	function ModuleObject(& $config)
	{
		$this->MasterObject($config);
		$this->Main();
	}

	function Main()
	{
		if(MEMBER_ID < 1) {
            response_text('登录后才能继续操作');
        }
		global $_J;
		if(!isset($_J['plugins'])) {
			jlogic('plugin')->loadplugincache();
		}
		$pluginid = jget('id');
		if(!empty($pluginid)) {
			list($identifier, $module) = explode(':', $pluginid);
			$module = $module !== NULL ? $module : $identifier;
		}
		if(!is_array($_J['plugins']['hookmod']) || !array_key_exists($pluginid, $_J['plugins']['hookmod'])) {
			response_text("插件不存在或已关闭");
		}
		if(empty($identifier) || !preg_match("/^[a-z]+[a-z0-9_]*[a-z]+$/i", $identifier) || !preg_match("/^[a-z]+[a-z0-9_]*[a-z]+$/i", $module)) {
			response_text("未定义的操作");
		}
		if(@!file_exists($modfile = PLUGIN_DIR.'/'.$identifier.'/'.$module.'.mod.php')) {
			response_text("插件模块文件(".$modfile.")不存在或者插件文件不完整");
		}
		if($_J['plugins']['hookmod'][$pluginid]['role_id'] && 'admin' != MEMBER_ROLE_TYPE){
			response_text("您没有权限进行该操作");
		}
		include $modfile;
	}
}
?>