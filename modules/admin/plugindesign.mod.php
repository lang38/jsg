<?php
/**
 * 文件名：plugindesign.mod.php
 * @version $Id: plugindesign.mod.php 5834 2014-09-22 02:40:06Z wuliyong $
 * 作者：狐狸<foxis@qq.com>
 * 功能描述：插件设计
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

jfunc('api');

class ModuleObject extends MasterObject
{
	
	function ModuleObject(& $config)
	{
		$this->MasterObject($config);
		$this->Execute();
	}
	
	function Execute()
	{
		ob_start();
		$id = jget('id','int');
		$plugin = jlogic('plugin')->getpluginbyid($id);
		if(PLUGINDEVELOPER > 0){
		if(empty($plugin['pluginid']))
		{
			$this->Messager("操作失败（找不到您要设计的插件）");
		}
		$this->pluginid = $id;
		$this->pluginconfig = "插件设计";
		$this->pluginname = $plugin['name'];
		switch($this->Code)
		{
			case 'design':
				$this->Design();
				break;
			case 'adddesign':
				$this->Adddesign();
				break;
			case 'modules':
				$this->Modules();
				break;
			case 'addmodules':
				$this->Addmodules();
				break;
			case 'vars':
				$this->Vars();
				break;
			case 'addvar':
				$this->Addvar();
				break;
			case 'export':
				$this->Export();
				break;
			case 'config':
				$this->config();
				break;
			case 'addconfig':
				$this->Addconfig();
				break;
			default:
				$this->Main();
		}
		}
		$body = ob_get_clean();

		$this->ShowBody($body);

	}

	function Main()
	{
		include(template('admin/plugin_design'));
	}

	function Design()
	{
		$id = jget('id','int');
		$plugin_info = jlogic('plugin')->getpluginbyid($id);
		$infos = true;
		include(template('admin/plugin_design_editor'));
	}

	function Adddesign()
	{
		$id = jget('id','int');
		$data = array();
		$data['name'] = cutstr(trim($this->Post['plugin_name']),40);
		$data['identifier'] = cutstr(trim($this->Post['identifier']),40);
		$data['version'] = cutstr(trim($this->Post['version']),10);
		$data['copyright'] = cutstr(trim($this->Post['copyright']),80);
		$data['description'] = cutstr($this->Post['description'],100);
		$data['directory'] = $data['identifier'].'/';
		$oldidentifier = jget('old_identifier');
		if(empty($data['name'])){
			$this->Messager("插件名不能为空");
		}
		if($oldidentifier == $data['identifier']){
			$is_exists = false;
		}else{
			$is_exists = jtable('plugin')->info(array('identifier'=>$data['identifier']));
		}

		if($is_exists != false || empty($data['identifier']) || !preg_match("/^[a-z]+[a-z0-9_]*[a-z0-9]+$/i", $data['identifier'])){
			$this->Messager("插件唯一识别符不合法，或不能为空");
		}
		if(empty($data['version'])){
			$this->Messager("插件版本号不能为空");
		}
		if(empty($data['copyright'])){
			$this->Messager("版权信息不能为空");
		}
		$result = jtable('plugin')->update($data, array('pluginid'=>$id));
		$this->Messager("插件设计完善成功", 'admin.php?mod=plugindesign&code=design&id='.$id);
	}

	function Config()
	{
		$id = jget('id','int');
		$vid = jget('vid','int');
		$pluginvar = jlogic('plugin')->getpluginvarbyvid($vid);
		$modvars = true;
		if($pluginvar['type'] == 'select' || $pluginvar['type'] == 'selects' || $pluginvar['type'] == 'checkbox'){$extradisplay = '';}else{$extradisplay = 'none';}
		$plugin = jconf::get('plugin');
		include(template('admin/plugin_design_editor'));
	}

	function Addconfig()
	{
		$id = jget('id','int');
		$vid = jget('vid','int');
		$data = array();
		$data['variable'] = trim($this->Post['variable']);
		if(!preg_match("/^[a-z]+[a-z_]*[a-z]+$/i", $data['variable'])){
			$this->Messager("变量命名不合法", 'admin.php?mod=plugindesign&id='.$id.'&code=config&vid='.$vid);
		}
		if(jlogic('plugin')->checkvar_by_pluginid($id,$vid,$data['variable'])){
			$this->Messager("变量名不合法，与现有变量名重复", 'admin.php?mod=plugindesign&id='.$id.'&code=config&vid='.$vid);
		}
		$data['title'] = trim($this->Post['title']);
		$data['description'] = trim($this->Post['description']);
		$data['type'] = trim($this->Post['type']);
		if($data['type'] == 'select' || $data['type'] == 'selects' || $data['type'] == 'checkbox')
		{
			$data['extra'] = $this->Post['extra'];
		}
		else
		{
			$data['extra'] = '';
		}
		$result = jtable('pluginvar')->update($data, array('pluginvarid'=>$vid));
		$this->Messager("插件设计完善成功", 'admin.php?mod=plugindesign&code=vars&id='.$id);
	}

	function Modules()
	{
		$id = jget('id','int');
		$row = jlogic('plugin')->getpluginbyid($id);
		$mod_ary = unserialize($row['modules']);
		if($mod_ary){
			foreach($mod_ary as $key => $var){
				if($var['modtype'] == '5'){
					$mod_ary[$key]['phpstr'] = ".class.php";
					$mod_ary[$key]['n_style'] = "none";
				}else{
					$mod_ary[$key]['n_style'] = "";
					if($var['modtype'] == '1'){
						$mod_ary[$key]['phpstr'] = ".mod.php";
					}else{
						$mod_ary[$key]['phpstr'] = ".inc.php";
					}
				}
				if($var['modtype'] == '4'){
					$mod_ary[$key]['r_style'] = "none";
				}else{
					$mod_ary[$key]['r_style'] = "";
				}
				if($var['modtype'] == '3'){
					$mod_ary[$key]['i_style'] = '';
				}else{
					$mod_ary[$key]['i_style'] = "none";
				}
			}
		}
		$modules = true;
		$plugin = jconf::get('plugin');
		include(template('admin/plugin_design_editor'));
	}

	function Addmodules()
	{
		$id = jget('id','int');
		$modulesnew = $modulefile = $data = array();
		if(is_array($this->Post['mod_filenew']))
		{
			foreach($this->Post['mod_filenew'] as $moduleid => $module){
				if(!isset($this->Post['delete'][$moduleid]) && !empty($this->Post['mod_filenew'][$moduleid]) && ($this->Post['modtypenew'][$moduleid] == '5' ? true : !empty($this->Post['mod_namenew'][$moduleid])))
				{
					$modulesnew[] = array(
						'modtype'	=> trim($this->Post['modtypenew'][$moduleid]),
						'mod_file'	=> trim($this->Post['mod_filenew'][$moduleid]),
						'mod_name'	=> trim($this->Post['mod_namenew'][$moduleid]),
						'mod_icon'	=> trim($this->Post['mod_iconnew'][$moduleid]),
						'role_id'	=> trim($this->Post['role_idnew'][$moduleid]),
					);
				}
			}
		}

		if(!empty($this->Post['newmod_file']) && ($this->Post['newmodtype'] == '5' ? true : !empty($this->Post['newmod_name']))){
			$modulesnew[] = array(
				'modtype'	=> trim($this->Post['newmodtype']),
				'mod_file'	=> trim($this->Post['newmod_file']),
				'mod_name'	=> trim($this->Post['newmod_name']),
				'mod_icon'	=> trim($this->Post['newmod_icon']),
				'role_id'	=> trim($this->Post['newrole_id']),
			);
		}

		foreach($modulesnew as $val){
			if(!preg_match("/^[a-z]+[a-z0-9_]*[a-z0-9]+$/i", $val['mod_file'])){
				$this->Messager("程序模块文件命名不合法", 'admin.php?mod=plugindesign&code=modules&id='.$id);
			}
			if(in_array($val['modtype'],array('2','3','4'))){
				$modulefile[] = $val['mod_file'];
			}
		}

		if($modulefile && count(array_unique($modulefile)) != count($modulefile)){
			$this->Messager("程序模块文件名不合法，与现有变量名重复", 'admin.php?mod=plugindesign&code=modules&id='.$id);
		}
		unset($modulefile);

		$data['modules'] = addslashes(serialize($modulesnew));

		$result = jtable('plugin')->update($data, array('pluginid'=>$id));
		$this->Messager("插件设计完善成功", 'admin.php?mod=plugindesign&code=modules&id='.$id);
	}

	function Vars()
	{
		$id = jget('id','int');
		$plugin_var = jlogic('plugin')->getpluginvarbyid($id);
		$vars = true;
		$plugin = jconf::get('plugin');
		include(template('admin/plugin_design_editor'));
	}

	function Addvar()
	{
		$id = jget('id','int');
		$var_data = array();
		$var_data['pluginid'] = $id;
		$var_data['displayorder'] = trim($this->Post['newdisplayorder']);
		$var_data['title'] = trim($this->Post['newtitle']);
		$var_data['variable'] = trim($this->Post['newvariable']);
		$var_data['type'] = trim($this->Post['newtype']);

		if(!empty($var_data['title']) || !empty($var_data['variable ']))
		{
			if(jlogic('plugin')->checkvar_by_pluginid($id,0,$var_data['variable'])){
				$this->Messager("变量名不合法，与现有变量名重复", 'admin.php?mod=plugindesign&code=vars&id='.$id);
			}elseif(!preg_match("/^[a-z]+[a-z_]*[a-z]+$/i", $var_data['variable'])){
				$this->Messager("变量命名不合法", 'admin.php?mod=plugindesign&code=vars&id='.$id);
			}else{
				jtable('pluginvar')->insert($var_data);
			}
		}

		if($ids = jimplode($this->Post['delete']))
		{
			$sql = "DELETE FROM `" . TABLE_PREFIX . "pluginvar` WHERE `pluginvarid` IN ($ids)";
			$this->DatabaseHandler->Query($sql);
		}

		if(is_array($this->Post['displayordernew'])) {
			foreach($this->Post['displayordernew'] as $vid => $displayorder) {
				$data['displayorder'] = $displayorder;
				jtable('pluginvar')->update($data, array('pluginvarid'=>$vid));
			}
		}

		$this->Messager("插件设计完善成功", 'admin.php?mod=plugindesign&code=vars&id='.$id);
	}

	function Export()
	{
		$id = jget('id','int');
		$plugin_info = jlogic('plugin')->getpluginbyid($id);
		if($plugin_info){
			$export_ary = array();
			$export_ary['Title'] = 'JishiGou! Plugin';
			$export_ary['Version'] = SYS_VERSION;
			$export_ary['Time'] = my_date_format(time());
			$export_ary['Data']['plugin']['available'] = 0;
			$export_ary['Data']['plugin']['name'] = $plugin_info['name'];
			$export_ary['Data']['plugin']['identifier'] = $plugin_info['identifier'];
			$export_ary['Data']['plugin']['description'] = $plugin_info['description'];
			$export_ary['Data']['plugin']['directory'] = $plugin_info['directory'];
			$export_ary['Data']['plugin']['copyright'] = $plugin_info['copyright'];
			$export_ary['Data']['plugin']['version'] = $plugin_info['version'];
			$export_ary['Data']['plugin']['__modules'] = unserialize($plugin_info['modules']);

						$plugin_var = jlogic('plugin')->getpluginvarbyid($id);
			foreach($plugin_var as $temp => $val)
			{
				$export_ary['Data']['var'][$temp]['displayorder'] = $val['displayorder'];
				$export_ary['Data']['var'][$temp]['title'] = $val['title'];
				$export_ary['Data']['var'][$temp]['description'] = $val['description'];
				$export_ary['Data']['var'][$temp]['variable'] = $val['variable'];
				$export_ary['Data']['var'][$temp]['type'] = $val['type'];
				$export_ary['Data']['var'][$temp]['value'] = $val['value'];
				$export_ary['Data']['var'][$temp]['extra'] = $val['extra'];
			}

						$plugindir = PLUGIN_DIR . '/'.$plugin_info['directory'];
			if(file_exists($plugindir.'/install.php')) {
				$export_ary['installfile'] = 'install.php';
			}
						if(file_exists($plugindir.'/upgrade.php')) {
				$export_ary['upgradefile'] = 'upgrade.php';
			}
						if(file_exists($plugindir.'/uninstall.php')){
				$export_ary['uninstallfile'] = 'uninstall.php';
			}

			$xml = api_xml_serialize($export_ary, true);
			$filename = strtolower(str_replace(array('!', ' '), array('', '_'), 'JishiGou! Plugin')).'_'.$plugin_info['identifier'].'.xml';
			ob_end_clean();
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
			header('Cache-Control: no-cache, must-revalidate');
			header('Pragma: no-cache');
			header('Content-Encoding: none');
			header('Content-Length: '.strlen($xml));
			header('Content-Disposition: attachment; filename='.$filename);
			header('Content-Type: text/xml');
			echo $xml;
			exit;
		}else{
			$this->Messager("未找到该插件", 'admin.php?mod=plugin');
		}
	}
}
?>