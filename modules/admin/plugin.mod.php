<?php
/**
 * 文件名：plugin.mod.php
 * @version $Id: plugin.mod.php 5834 2014-09-22 02:40:06Z wuliyong $
 * 作者：狐狸<foxis@qq.com>
 * 功能描述：插件管理
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
		switch($this->Code)
		{
			case 'add':
				$this->Add();
				break;
			case 'config':
				$this->Config();
				break;
			case 'configsave':
				$this->Configsave();
				break;
			case 'del':
				$this->Del();
				break;
			case 'manage':
				$this->Manage();
				break;
			case 'design':
				$this->Design();
				break;
			case 'adddesign':
				$this->Adddesign();
				break;
			case 'action':
				$this->Action($_GET['tyle']);
				break;
			case 'install':
				$this->Install();
				break;
			case 'uninstall':
				$this->Uninstall();
				break;
			case 'upgrade':
				$this->Upgrade();
				break;
			case 'publish':
				$this->Publish();
				break;
			default:
				$this->Main();
		}
		$body = ob_get_clean();

		$this->ShowBody($body);

	}
	function Manage()
	{
		$pluginconfig = "插件管理中心";
		$pluginid = jget('id');
		$identifier = jget('identifier');
		$pmod = jget('pmod');
		if($pluginid){
			$plugininfo = jlogic('plugin')->getpluginbyid($pluginid);
		}else{
			$plugininfo = jlogic('plugin')->getpluginbyidentifier($identifier);
		}
		if(!$plugininfo){
			$this->Messager("插件未找到", -1);
		}
		$pluginid = $pluginid ? $pluginid : $plugininfo['pluginid'];
		$identifier = $identifier ? $identifier : $plugininfo['identifier'];
		$pluginvar = jlogic('plugin')->getpluginvarbyid($pluginid);
		$hasthismodule = false;
		$pluginname = $plugininfo['name'];
		$mod_ary = unserialize($plugininfo['modules']);
		if(is_array($mod_ary))
		{
			foreach($mod_ary as $module)
			{
				if($module['modtype'] == '4')
				{
					$pluginmenu[] = array(
						'identifier'  => $plugininfo['identifier'],
						'pmod'  => $module['mod_file'],
						'name' => $module['mod_name'],
					);
				}
				if($pmod == $module['mod_file']){
					$hasthismodule = true;
				}
			}
		}
		if(!$hasthismodule){
			$this->Messager("插件中不存在该模块", -1);
		}
		$template = $identifier.':'.$pmod;
		if(@!is_file(PLUGIN_DIR .'/'.$identifier.'/template/'.$pmod.'.html')){
			$template = 'plugin/'.$identifier.'/'.$pmod;		}
		if(@!file_exists($pluginfile = PLUGIN_DIR .'/'.$identifier.'/'.$pmod.'.inc.php')){
			$this->Messager("插件模块文件(".$pluginfile.")不存在或者插件文件不完整", -1);
		}else{
			include_once($pluginfile);
		}
		include(template('admin/plugin_manage'));
	}
	function Config()
	{
		$pluginid = jget('id','int');
		$pluginconfig = "插件设置";
		$plugininfo = jlogic('plugin')->getpluginbyid($pluginid);
		$pluginname = $plugininfo['name'];
		$mod_ary = unserialize($plugininfo['modules']);
		if(is_array($mod_ary))
		{
			foreach($mod_ary as $module)
			{
				if($module['modtype'] == '4')
				{
					$pluginmenu[] = array(
						'identifier'  => $plugininfo['identifier'],
						'pmod'  => $module['mod_file'],
						'name' => $module['mod_name'],
					);
				}
			}
		}
		$pluginvar = jlogic('plugin')->getpluginvarbyid($pluginid);
		if($pluginvar)
		{
			$id = jget('id','int','G');
			foreach($pluginvar as $temp => $var)
			{
				$var['variable'] = 'varsnew['.$var['variable'].']';
				if($var['type'] == 'text')
				{
					$pluginvar[$temp]['pluginvar'] = "<input type=\"text\" name=\"".$var['variable']."\" value=\"".$var['value']."\" size=\"40\">";
				}
				elseif($var['type'] == 'radio')
				{
					$str = "<input name=\"".$var['variable']."\" class=\"radio\" type=\"radio\" value=\"1\" ".($var['value'] == '1' ? 'CHECKED' : '').">是&nbsp;&nbsp;<input name=\"".$var['variable']."\" class=\"radio\" type=\"radio\" value=\"0\" ".($var['value'] == '0' ? 'CHECKED' : '').">否";
					$pluginvar[$temp]['pluginvar'] = $str;
				}
				elseif($var['type'] == 'textarea')
				{
					$pluginvar[$temp]['pluginvar'] = "<textarea name=\"".$var['variable']."\" rows=\"5\" cols=\"50\">".$var['value']."</textarea>";
				}
				elseif($var['type'] == 'select')
				{
					$str = "<select name=\"".$var['variable']."\"><option value=\"\">请选择...</option>";
					foreach(explode("\n", $var['extra']) as $key => $option)
					{
						$option = trim($option);
						if(strpos($option, '=') === FALSE) {
							$key = $option;
						} else {
							$item = explode('=', $option);
							$key = trim($item[0]);
							$option = trim($item[1]);
						}
						$str .= "<option value=\"".$key."\" ".($var['value'] == $key ? 'selected' : '').">".$option."</option>";
					}
					$str .= "</select>";
					$pluginvar[$temp]['pluginvar'] = $str;
				}
				elseif($var['type'] == 'selects')
				{
					$pluginvar[$temp]['description'] .= '<br>按住 CTRL 多选';
					$var['value'] = unserialize($var['value']);
					$var['value'] = is_array($var['value']) ? $var['value'] : array($var['value']);
					$var['value'] = implode(",",$var['value']);
					$str = "<select name=\"".$var['variable']."[]\"  size=\"10\" multiple=\"multiple\"><option value=\"\">请选择...</option>";
					foreach(explode("\n", $var['extra']) as $key => $option)
					{
						$option = trim($option);
						if(strpos($option, '=') === FALSE) {
							$key = $option;
						} else {
							$item = explode('=', $option);
							$key = trim($item[0]);
							$option = trim($item[1]);
						}
						$str .= "<option value=\"".$key."\" ".(in_array($key,explode(",",$var['value'])) ? 'selected' : '').">".$option."</option>";
					}
					$str .= "</select>";
					$pluginvar[$temp]['pluginvar'] = $str;
				}
				elseif($var['type'] == 'checkbox')
				{
					$var['value'] = unserialize($var['value']);
					$var['value'] = is_array($var['value']) ? $var['value'] : array($var['value']);
					$str = '';
					foreach(explode("\n", $var['extra']) as $key => $option)
					{
						$option = trim($option);
						if(strpos($option, '=') === FALSE) {
							$key = $option;
						} else {
							$item = explode('=', $option);
							$key = trim($item[0]);
							$option = trim($item[1]);
						}
						$str .= "<input type=\"checkbox\" name=\"".$var['variable']."[]\" class=\"radio\" value=\"".$key."\" ".(in_array($key, $var['value']) ? 'CHECKED' : '').">".$option."&nbsp;&nbsp;";
					}
					$pluginvar[$temp]['pluginvar'] = $str;
				}
				else
				{
					$sql = "select id,name FROM " . TABLE_PREFIX.'role' . " WHERE id>1";
					$query = $this->DatabaseHandler->Query($sql);
					while($role_row = $query->GetRow())
					{
						$role_list[] = array('name' => $role_row['name'], 'value' => $role_row['id']);
					}
					if($var['type'] == 'usergroup'){
						$str = "<select name=\"".$var['variable']."\"><option value=\"\">请选择...</option>";
						foreach($role_list as $key => $role)
						{
							$str .= "<option value=\"".$role['value']."\" ".($var['value'] == $role['value'] ? 'selected' : '').">".$role['name']."</option>";
						}
					}else{
						$pluginvar[$temp]['description'] .= '<br>按住 CTRL 多选';
						$var['value'] = unserialize($var['value']);
						$var['value'] = is_array($var['value']) ? $var['value'] : array($var['value']);
						$var['value'] = implode(",",$var['value']);
						$str = "<select name=\"".$var['variable']."[]\"  size=\"10\" multiple=\"multiple\"><option value=\"\">请选择...</option>";
						foreach($role_list as $key => $role)
						{
							$str .= "<option value=\"".$role['value']."\" ".(in_array($role['value'],explode(",",$var['value'])) ? 'selected' : '').">".$role['name']."</option>";
						}
					}
					$str .= "</select>";
					$pluginvar[$temp]['pluginvar'] = $str;
				}
			}
		}
		include(template('admin/plugin_config'));
	}
	function Configsave()
	{
		$id = jget('id','int');
		$pluginvars = $data = array();
		$pluginvardata = jlogic('plugin')->getpluginvarbyid($id);
		foreach($pluginvardata as $val){
			$pluginvars[] = $val['variable'];
		}
		unset($pluginvardata);
		if(is_array($_POST['varsnew'])) {
			foreach($_POST['varsnew'] as $variable => $value) {
				if(in_array($variable,$pluginvars)) {
					$data['value'] = trim($value);
					if(is_array($value)) {
						$data['value'] = addslashes(serialize($value));
					}
					jtable('pluginvar')->update($data, array('pluginid'=>$id, 'variable'=>$variable));
				}
			}
		}
		$this->Messager("插件设置成功", 'admin.php?mod=plugin&code=config&id='.$id);
	}
	function Main()
	{
		if($this->Code == 'designing'){
			$where = ' where available = 2 ';
			$navtitle = '设计中插件';
		}else{
			$where = ' where available < 2 ';
			$navtitle = '已安装插件';
		}
				$sql = "select count(*) as `total` from `".TABLE_PREFIX."plugin` " . $where;
		$total = DB::result_first($sql);

		$page_num=10;
		$p=max($_GET['p'],1);
		$offset=($p-1)*$page_num;
		$pages=page($total, $page_num, '', array('var'=>'p'));

		$sql = "select *  FROM ".TABLE_PREFIX."plugin ". $where ." LIMIT $offset,$page_num";
		$query = $this->DatabaseHandler->Query($sql);
		$i = 0;
		while(false != ($row = $query->GetRow()))
		{
			$plugin_list[$i] = $row;
			$plugin_list[$i]['uninstall'] = "admin.php?mod=plugin&code=uninstall&plugindir=".str_replace('/', '',$row['directory'])."&id=".$row['pluginid'];
			$plugin_list[$i]['publish'] = "admin.php?mod=plugin&code=publish&id=".$row['pluginid'];
			$plugin_list[$i]['upgrade'] = "admin.php?mod=plugin&code=upgrade&id=".$row['pluginid'];
			$sqls = "select * FROM ".TABLE_PREFIX."pluginvar WHERE pluginid = '{$row['pluginid']}'";
			$querys = $this->DatabaseHandler->Query($sqls);
			$vars = $querys->GetRow();
			$plugin_list[$i]['pluginvar'] = $vars;
			$mod_ary = unserialize($row['modules']);
			if(is_array($mod_ary))
			{
				foreach($mod_ary as $module)
				{
					if($module['modtype'] == '4')
					{
						$plugin_list[$i]['pluginmenu'][] = array(
							'identifier'  => $row['identifier'],
							'pmod'  => $module['mod_file'],
							'name' => $module['mod_name'],
						);
					}elseif($module['modtype'] == '1'){
						$plugin_list[$i]['pluginurl'][] = array(
							'url' => str_replace('http:/'.'/'.$this->Config['site_domain'].'/','',jurl('index.php?mod=plugin&id='.$row['identifier'].':'.$module['mod_file'])),
							'name' => $module['mod_name']
						);
					}
				}
			}
			$plugin_list[$i]['logo'] = file_exists(PLUGIN_DIR.'/'.$row['directory'].'logo.png') ? str_replace(ROOT_PATH,'',PLUGIN_DIR.'/').$row['directory'].'logo.png' : 'static/image/plugin_logo.png';
			$plugin_ary[$temp]['dir']  = $val['plugin']['directory'];
			$i++;
		}

		include(template('admin/plugin_list'));
	}
	function Add()
	{
		$installsdir = jlogic('plugin')->getplugindir();
		if(!is_array($installsdir)){$installsdir = array();}
		$pluginsdir = dir(PLUGIN_DIR);
		while(($file = $pluginsdir->read()) !== false){
			if(!in_array($file, array('.', '..')) && is_dir(PLUGIN_DIR.'/'.$file) && !in_array($file.'/', $installsdir)){
				$filedir = PLUGIN_DIR . '/'.$file;
				$d = dir($filedir);
				while($f = $d->read()){
					if(preg_match('/^jishigou\_plugin\_'.$file.'.xml$/', $f)){
						$xml_url = $filedir.'/jishigou_plugin_'.$file.'.xml';
						$fp = fopen($xml_url, 'r');
						$xmldata = fread($fp, filesize($xml_url));
						fclose($fp);
						$plugin_ary = array();
						$plugindata = api_xml_unserialize($xmldata);
						$plugin_all[] = $plugindata['Data'];
					}
				}
			}
		}

		if(is_array($plugin_all)){
		foreach($plugin_all as $temp => $val)
		{
			$plugin_ary[$temp]['name']   = $val['plugin']['name'];
			$plugin_ary[$temp]['logo'] = file_exists(PLUGIN_DIR.'/'.$val['plugin']['directory'].'logo.png') ? str_replace(ROOT_PATH,'',PLUGIN_DIR).'/'.$val['plugin']['directory'].'logo.png' : 'static/image/plugin_logo.png';
			$plugin_ary[$temp]['dir']  = $val['plugin']['directory'];
			$plugin_ary[$temp]['install_url'] = "admin.php?mod=plugin&code=install&plugindir=".str_replace('/', '',$val['plugin']['directory']);
		}}

		$pluginsdir->close();
		include(template('admin/plugin_add'));
	}

	function Design()
	{
		if(PLUGINDEVELOPER > 0){
			include(template('admin/plugin_design'));
		}
	}

	function Adddesign()
	{
		if(PLUGINDEVELOPER > 0)
		{
		$data = array();
		$data['name'] = cutstr(trim($this->Post['plugin_name']),40);
		$data['identifier'] = cutstr(trim($this->Post['identifier']),40);
		$data['version'] = cutstr(trim($this->Post['version']),10);
		$data['copyright'] = cutstr(trim($this->Post['copyright']),80);
		$data['description'] = cutstr($this->Post['description'],100);
		$data['available'] = 2;
		if(empty($data['name'])){
			$this->Messager("插件名不能为空");
		}
		$is_exists = jtable('plugin')->info(array('identifier'=>$data['identifier']));

		if($is_exists != false || empty($data['identifier']) || !preg_match("/^[a-z]+[a-z0-9_]*[a-z0-9]+$/i",$data['identifier'])){
			$this->Messager("插件唯一识别符不合法，或不能为空");
		}
		if(empty($data['version'])){
			$this->Messager("插件版本号不能为空");
		}
		if(empty($data['copyright'])){
			$this->Messager("版权信息不能为空");
		}
		$data['directory'] = $data['identifier'].'/';
		$result_id = jtable('plugin')->insert($data,1);
				$filedir = PLUGIN_DIR . '/'.$data['directory'];
		$tempdir = PLUGIN_DIR . '/'.$data['directory'] .'template/';


		if (!is_dir($filedir))
		{
			jio()->MakeDir($filedir);
		}
		if (!is_dir($tempdir))
		{
			jio()->MakeDir($tempdir);
		}
		if($result_id != false)
		{
			$this->Messager("插件设计成功,请完善你的设计", 'admin.php?mod=plugindesign&code=design&id='.$result_id);
		}else{
			$this->Messager("添加失败");
		}
		}
	}

	
	function Action($tyle)
	{
		$_data = array(
			'available' => ($tyle == 'stop') ? 0:1
			);
		$id = jget('id','int');
		$plugininfo = jlogic('plugin')->getpluginbyid($id);
		if(!$plugininfo){
			$this->Messager("该插件不存在！");
		}
		$identifier = $plugininfo['identifier'];
		if($_data['available']==1){
			if (!is_dir($filedir = PLUGIN_DIR . '/'.$plugininfo['directory'])){
				$this->Messager("插件目录".$filedir."不存在！");
			}
			$modules = unserialize($plugininfo['modules']);
			foreach($modules as $module){
				if($module['modtype']==1){
					$mtype = '.mod';
				}elseif($module['modtype']==5){
					$mtype = '.class';
				}else{
					$mtype = '.inc';
				}
				if(@!file_exists($modfile = PLUGIN_DIR.'/'.$identifier.'/'.$module['mod_file'].$mtype.'.php')) {
					$this->Messager("插件模块文件(".$modfile.")不存在");
				}
			}
		}
		$ok_messager = ($tyle == 'stop') ? "插件已成功关闭":"插件已成功开启";
		$result = jtable('plugin')->update($_data, array('pluginid'=>$id));
		if($result != false)
		{
			$this->Messager($ok_messager, 'admin.php?mod=plugin');
		}
		else
		{
			$this->Messager("操作失败");
		}
	}

	
	function Install()
	{
		global $_J;
		$plugindir = $this->Get['plugindir'];
		$filedir = PLUGIN_DIR . '/'.$plugindir;
		$xml_url = $filedir.'/jishigou_plugin_'.$plugindir.'.xml';
		$fp = fopen($xml_url, 'r');
		$xmldata = fread($fp, filesize($xml_url));
		$plugindata_all = api_xml_unserialize($xmldata);
		if(!is_array($plugindata_all) || jlogic('plugin')->errorplugindata($plugindata_all['Data'])){
			$this->Messager("安装失败（插件代码可疑或有错误，系统禁止安装）", 'admin.php?mod=plugin&code=add');
		}

		if($this->Get['submit'] != 'yes'){
			$pname = $plugindata_all['Data']['plugin']['name'];
			if($plugindata_all['license'] || $plugindata_all['intro']){
				$license = '';
				if($plugindata_all['license']){
					$license .= strip_tags($plugindata_all['license'],'<b>,<i>,<u>,<p>,<a>');
				}
				if($plugindata_all['intro']){
					$license = $license ? $license.'<br><br>' : '';
					$license .= strip_tags($plugindata_all['intro'],'<b>,<i>,<u>,<p>,<a>');
				}
				$license = nl2br($license);
			}else{
				if($plugindata_all['Version'] != $_J['config']['sys_version']){
					$license = '<br><br><center>本插件适用版本为'.$plugindata_all['Version'].'，您当前正在使用的系统版本是'.$_J['config']['sys_version'].'<br>您确定要安装吗？</center><br><br>';
				}else{
					$license = '<br><br><center>您确定要安装该插件吗？<br>如果此插件是从记事狗官方下载的，则为绿色插件，您可放心安装</center><br><br>';
				}
			}
			include(template('admin/plugin_install'));
			exit;
		}

		$plugindata = $plugindata_all['Data']['plugin'];

		$vardata = empty($plugindata_all['Data']['var']) ? array() : $plugindata_all['Data']['var'];
		$installfile = $plugindata_all['installfile'];

		if($installfile)
		{
						if(file_exists($filedir.'/'.$installfile))
			{
				$sql = '';
				include($filedir.'/'.$installfile);

				if($sql)
				{
					$sqls = str_replace("\r","\n",str_replace("{jishigou}",TABLE_PREFIX,$sql));
					foreach(explode(";\n", trim($sqls)) as $sql)
					{
						$query = trim($sql);
						if(!empty($query))
						{
							if(strtoupper(substr($query, 0, 12)) == 'CREATE TABLE')
							{
								$query = $this->_sql_createtable($query, $this->Config['charset']);
							}

							$this->DatabaseHandler->Query($query);
						}
					}
				}
			}else{
				$this->Messager("安装失败（找不到安装文件".$installfile."，无法安装）", 'admin.php?mod=plugin&code=add');
			}
		}

				$data['name'] = $plugindata['name'];
		$data['available'] = $plugindata['available'];
		$data['directory'] = $plugindata['directory'];
		$data['identifier'] = $plugindata['identifier'];
		$data['version'] = $plugindata['version'];
		$data['copyright'] = $plugindata['copyright'];
		$data['modules'] = addslashes(serialize($plugindata['__modules']));
		$data['description'] = $plugindata['description'];

		$result_id = jtable('plugin')->insert($data, 1);

				foreach($vardata as $temp => $value)
		{
			$var_data['pluginid'] = $result_id;
			$var_data['title'] = $value['title'];
			$var_data['description'] = $value['description'];
			$var_data['variable'] = $value['variable'];
			$var_data['type'] = $value['type'];
			$var_data['extra'] = $value['extra'];
			$var_data['value'] = $value['value'];
			$var_data['displayorder'] = $value['displayorder'];
			jtable('pluginvar')->insert($var_data);
		}

		$menustr = '';
		if(is_array($plugindata['__modules'])){
			foreach($plugindata['__modules'] as $var){
				if($var['modtype'] == '1'){
					$menustr .= '<br>导航名称：<font color="blue">'.$var['mod_name'].'</font><br>英文名称：<font color="blue">'.$data['identifier'].'_'.$var['mod_file'].'</font><br>链接地址：<font color="blue">'.jurl('index.php?mod=plugin&id='.$data['identifier'].':'.$var['mod_file']).'</font>';
				}
			}
		}
		if($menustr){
			$this->Messager("已成功安装 (".$plugindata['name'].") 插件！<br>该插件有独立模块，请在导航菜单里添加如下菜单：".$menustr, 'admin.php?mod=plugin',60);
		}else{
			$this->Messager("已成功安装 (".$plugindata['name'].") 插件", 'admin.php?mod=plugin');
		}
	}

	
	function Uninstall()
	{
		$id = jget('id','int');
		$plugin_info = jlogic('plugin')->getpluginbyid($id);
		if($plugin_info['available'] == 1)
		{
			$this->Messager("卸载失败（此插件启动中，如卸载请先关闭本插件）", 'admin.php?mod=plugin');
		}

		$plugindir = $this->Get['plugindir'];
		$filedir = PLUGIN_DIR . '/'.$plugindir;
		$xml_url = $filedir.'/jishigou_plugin_'.$plugindir.'.xml';
		$fp = fopen($xml_url, 'r');
		$xmldata = fread($fp, filesize($xml_url));
		$plugindata_all = api_xml_unserialize($xmldata);
		$uninstallfile = $plugindata_all['uninstallfile'];
		if($uninstallfile)
		{
						if(file_exists($filedir.'/'.$uninstallfile)) {
				include($filedir.'/'.$uninstallfile);
				$sqls = str_replace("\r","\n",str_replace("{jishigou}",TABLE_PREFIX,$sql));
				foreach(explode(";\n", trim($sqls)) as $sql)
				{
					$query = trim($sql);
					if(!empty($query))
					{
						if(strtoupper(substr($query, 0, 12)) == 'CREATE TABLE')
						{
							$query = $this->_sql_createtable($query, $this->Config['charset']);
						}

						$this->DatabaseHandler->Query($query);
					}
				}
			}else{
				$this->Messager("卸载失败（卸载文件".$uninstallfile."丢失，无法卸载）", 'admin.php?mod=plugin');
			}
		}

		$sql = "DELETE FROM `" . TABLE_PREFIX . "plugin` WHERE `pluginid` = '$id'";
		$result = $this->DatabaseHandler->Query($sql);
				$sql = "DELETE FROM `" . TABLE_PREFIX . "pluginvar` WHERE `pluginid` = '$id'";
		$result = $this->DatabaseHandler->Query($sql);

		if($result != false)
		{
			$this->Messager("插件已经成功卸载", 'admin.php?mod=plugin');
		}
		else
		{
			$this->Messager("操作失败");
		}
	}

	
	function Upgrade()
	{
		$id = jget('id','int');
		$plugin_info = jlogic('plugin')->getpluginbyid($id);
		if($plugin_info['available'] == 1)
		{
			$this->Messager("升级失败（此插件启动中，如升级请先关闭本插件）", 'admin.php?mod=plugin');
		}

		$plugindir = $plugin_info['identifier'];
		$nowver = !empty($plugin_info['version']) ? $plugin_info['version'] : 0;
		$filedir = PLUGIN_DIR . '/'.$plugindir;
		$xml_url = $filedir.'/jishigou_plugin_'.$plugindir.'.xml';
		$fp = fopen($xml_url, 'r');
		$xmldata = fread($fp, filesize($xml_url));
		$plugindata_all = api_xml_unserialize($xmldata);
		$upgradefile = $plugindata_all['upgradefile'];
		$newver = $plugindata_all['Data']['plugin']['version'];
		$upgrade = ($newver > $nowver) ? true : false;
		$data = array();
		$data['version'] = $newver;
		if($upgrade)
		{
			if($upgradefile){
								if(file_exists($filedir.'/'.$upgradefile)) {
					include($filedir.'/'.$upgradefile);
					$sqls = str_replace("\r","\n",str_replace("{jishigou}",TABLE_PREFIX,$sql));
					foreach(explode(";\n", trim($sqls)) as $sql)
					{
						$query = trim($sql);
						if(!empty($query))
						{
							$this->DatabaseHandler->Query($query);
						}
					}
				}else{
					$this->Messager("升级失败（升级文件".$upgradefile."丢失，无法升级）", 'admin.php?mod=plugin');
				}
			}
			jtable('plugin')->update($data, array('pluginid'=>$id));
			$this->Messager("插件已经从".$nowver."成功升级到".$newver, 'admin.php?mod=plugin');
		}
		else
		{
			$this->Messager("此插件无需升级，请上传新版本后再执行本操作", 'admin.php?mod=plugin');
		}
	}

	function Publish(){
		$id = jget('id','int');
		$plugin_info = jlogic('plugin')->getpluginbyid($id);
		if(!$plugin_info) {
			$this->Messager("操作失败！");
		}
		$plugindir = PLUGIN_DIR . '/'.$plugin_info['directory'];
		$tempdir = PLUGIN_DIR . '/'.$plugin_info['directory'] .'template/';
		if (!is_dir($plugindir)){
			jio()->MakeDir($plugindir);
		}
		if (!is_dir($tempdir)){
			jio()->MakeDir($tempdir);
		}
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
		if(is_array($plugin_var)){
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
		}
		$export_ary['installfile'] = 'install.php';
		$export_ary['upgradefile'] = 'upgrade.php';
		$export_ary['uninstallfile'] = 'uninstall.php';
		$xmldata = api_xml_serialize($export_ary, true);
		$filename = $plugindir.'jishigou_plugin_'.$plugin_info['identifier'].'.xml';
		$len = jio()->WriteFile($filename, $xmldata);
		if(false === $len) {
			$this->Messager("文件无法写入,请检查是否有可写权限。");
		}
		$data="<?php\r\nif(!defined('IN_JISHIGOU')) {\r\n    exit('invalid request');\r\n}\r\n?>";
		jio()->WriteFile($plugindir.$export_ary['installfile'], $data);
		jio()->WriteFile($plugindir.$export_ary['upgradefile'], $data);
		jio()->WriteFile($plugindir.$export_ary['uninstallfile'], $data);
		if(is_array($export_ary['Data']['plugin']['__modules'])){
			foreach($export_ary['Data']['plugin']['__modules'] as $var){
				if($var['modtype'] == 1){
					jio()->WriteFile($plugindir.$var['mod_file'].'.mod.php', $data);
				}elseif($var['modtype'] == 5){
					jio()->WriteFile($plugindir.$var['mod_file'].'.class.php', $data);
				}else{
					jio()->WriteFile($plugindir.$var['mod_file'].'.inc.php', $data);
					jio()->WriteFile($tempdir.$var['mod_file'].'.html', '');
				}
			}
		}
		$sql = "DELETE FROM `" . TABLE_PREFIX . "plugin` WHERE `pluginid` =  '$id'";
		$result = $this->DatabaseHandler->Query($sql);
		$sql = "DELETE FROM `" . TABLE_PREFIX . "pluginvar` WHERE `pluginid` =  '$id'";
		$result = $this->DatabaseHandler->Query($sql);
		$this->Messager("插件发布成功，请进入插件目录<br>".$plugindir."<br>编辑相关文件代码，使其具备你所需要的功能！",'',10);
	}
	function Del()
	{
		$id = jget('id','int');
		$plugin_info = jlogic('plugin')->getpluginbyid($id);
		$directory = $plugin_info['directory'];
		$sql = "DELETE FROM `" . TABLE_PREFIX . "plugin` WHERE `pluginid` =  '$id'";
		$result = $this->DatabaseHandler->Query($sql);
				$sql = "DELETE FROM `" . TABLE_PREFIX . "pluginvar` WHERE `pluginid` =  '$id'";
		$result = $this->DatabaseHandler->Query($sql);

		$filedir = PLUGIN_DIR . '/'.$directory;
		$tempdir = PLUGIN_DIR . '/'.$directory .'template';

		jio()->RemoveDir($tempdir);
		jio()->RemoveDir($filedir);

		if($result != false)
		{
			$this->Messager("插件已经成功删除", 'admin.php?mod=plugin');
		}
		else
		{
			$this->Messager("操作失败");
		}
	}

	
	function _sql_createtable($sql, $dbcharset)
	{
		$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
		$type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'MYISAM';
		$dbcharset = strtolower($dbcharset);
		if('utf-8' == $dbcharset)
		{
			$dbcharset = 'utf8';
		}

	    $search = ' character set gbk collate gbk_bin ';
	    if(false!==strpos($sql,$search))
	    {
	        if(mysql_get_server_info() <= '4.1')
	        {
	            $sql = str_replace($search, ' binary ', $sql);
	        }
	        else
	        {
	            if('gbk'!=$dbcharset)
	            {
	                $sql = str_replace($search, " character set {$dbcharset} collate {$dbcharset}_bin ", $sql);
	            }
	        }
	    }

		return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
			(mysql_get_server_info() > '4.1' ? " ENGINE=$type DEFAULT CHARSET=$dbcharset" : " TYPE=$type");
	}
}
?>