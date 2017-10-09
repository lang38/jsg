<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename plugin.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2014-09-17 16:01:50 1601568971 646540010 10982 $
 */







if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class PluginLogic
{
	function PluginLogic()
	{
	}
	
		function allplugindata($where = ' WHERE `available` < 2 ') {
		$cache_id = 'plugin/allplugindata_' . md5($where);
		if(false === ($plugins = cache_file('mget', $cache_id))) {
			$plugins = array();
			$query = DB::query("SELECT * FROM ".DB::table('plugin').$where);
			while ($value = DB::fetch($query)) {
				$plugins[] = $value;
			}
			cache_file('mset', $cache_id, $plugins, 300);
		}
		return $plugins;
	}

	function getpluginbyid($id){
		$plugin = array();
		$query = DB::query("SELECT * FROM ".DB::table('plugin')." WHERE `pluginid` = '$id'");
		$plugin = $query->GetRow();
		return $plugin;
	}

	function getpluginbyidentifier($identifier){
		$plugin = array();
		$query = DB::query("SELECT * FROM ".DB::table('plugin')." WHERE `identifier` = '$identifier'");
		$plugin = $query->GetRow();
		return $plugin;
	}

	function getpluginvarbyvid($vid){
		$pluginvar = array();
		$query = DB::query("SELECT * FROM ".DB::table('pluginvar')." WHERE `pluginvarid` = '$vid'");
		$pluginvar = $query->GetRow();
		return $pluginvar;
	}

	function getplugindir(){
		$plugindir = array();
		$query = DB::query("SELECT directory FROM ".DB::table('plugin')." ORDER BY pluginid DESC");
		while ($value = DB::fetch($query)){
			$plugindir[] = $value['directory'];
		}
		return $plugindir;
	}

	function getpluginvarbyid($id){
		$pluginvar = array();
		$query = DB::query("SELECT * FROM ".DB::table('pluginvar')." WHERE `pluginid` = '$id' ORDER BY displayorder ASC");
		while ($value = DB::fetch($query)){
			$pluginvar[] = $value;
		}
		return $pluginvar;
	}

	function count_by_pluginid($pluginid) {
		return DB::result_first("SELECT COUNT(*) FROM ".DB::table('pluginvar')." WHERE pluginid='".$pluginid."'");
	}

	function checkvar_by_pluginid($id,$vid,$var) {
		return DB::result_first("SELECT COUNT(*) FROM ".DB::table('pluginvar')." WHERE pluginid='".$id."' AND variable = '$var' AND pluginvarid <> '$vid'");
	}

		function errorplugindata($data){
		$return = false;
		if(!is_array($data)){
			$return = true;
		}else{
			$data_module = $data_var = array();
			foreach($data as $key => $val){
				if($key == 'plugin'){
					if(!preg_match("/^[a-z]+[a-z0-9_]*[a-z0-9]+$/i",$val['identifier']) || $val['directory'] != $val['identifier'].'/'){
						$return = true;break;
					}else{
						foreach($val['__modules'] as $vl){
							if(!preg_match("/^[a-z]+[a-z0-9_]*[a-z0-9]+$/i",$vl['mod_file'])){
								$return = true;break;
							}elseif(in_array($vl['modtype'],array('2','3','4'))){
								$data_module[] = $vl['mod_file'];
							}
						}
					}
				}elseif($key == 'var'){
					foreach($val as $v){
						if(!preg_match("/^[a-z]+[a-z_]*[a-z]+$/i",$v['variable'])){
							$return = true;break;
						}else{
							$data_var[] = $v['variable'];
						}
					}
				}
			}
			if(($data_module && count($data_module) != count(array_unique($data_module))) || count($data_var) != count(array_unique($data_var))){
				$return = true;
			}
		}
		return $return;
	}

	function loadplugincache(){
		global $_J;
		list($_J['plugins'],$_J['hookscript']) = $this->get_cachedata_setting_plugin();
	}

		function get_cachedata_setting_plugin($method = '') {
		$hookfuncs = array('common', 'reg', 'login', 'posttopic', 'printtopic', 'printuser', 'deletemember', 'deletetopic');		$data = array();
		$data['plugins'] =  $data['hookscript'] = array();
		$data['plugins']['func'] = $data['plugins']['available'] = array();
		foreach($this->allplugindata() as $plugin) {
			$available = !$method && $plugin['available'] || $method && ($plugin['available'] || $method == $plugin['identifier']);
			$plugin['modules'] = unserialize($plugin['modules']);
			if($available) {
				$data['plugins']['available'][] = $plugin['identifier'];
			}
			$plugin['directory'] = $plugin['directory'].((!empty($plugin['directory']) && substr($plugin['directory'], -1) != '/') ? '/' : '');
			if(is_array($plugin['modules'])) {
				foreach($plugin['modules'] as $k => $module) {
					if($available && isset($module['mod_file'])) {
						$k = $url = '';
						switch($module['modtype']) {
							case 1:
								$k = 'hookmod';
							case 2:
							case 3:
								$k = !$k ? 'hookmenu' : $k;
								if($module['modtype'] >1 ) $url = 'settings&code=';
								$url = 'index.php?mod='.$url.'plugin&id='.$plugin['identifier'].':'.$module['mod_file'];
								$data['plugins'][$k][$plugin['identifier'].':'.$module['mod_file']] = array('role_id' => $module['role_id'], 'navname' => $module['mod_name'], 'url' => $url, 'img' => $plugin['mod_icon'], 'type' => $module['modtype']);
								break;
							case 4:
								break;
							case 5:
								$k = 'hookscript';
								$script = $plugin['directory'].$module['mod_file'];
								@include_once ROOT_PATH.'./plugin/'.$script.'.class.php';
								$classes = get_declared_classes();
								$haveclass = '';
								$namekey = 'plugin_'.$plugin['identifier'];
								$cnlen = strlen($namekey);
								foreach($classes as $classname) {
									if(substr($classname, 0, $cnlen) == $namekey) {
										$haveclass = $classname;
										break;
									}
								}
								$hookmethods = get_class_methods($haveclass);
								if(is_array($hookmethods)){
									foreach($hookmethods as $funcname) {
										if(in_array($funcname, $hookfuncs)) {
											$data['plugins']['func'][$funcname] = true;
										}
										$v = explode('_', $funcname);
										$curscript = $v[0];
										if(!($curscript == 'global' || in_array($curscript,$hookfuncs)) || $classname == $funcname) {
											continue;
										}
										if(!@in_array($script, $data[$k][$curscript]['module'])) {
											$data[$k][$curscript]['module'][$plugin['identifier']] = $script;
											$data[$k][$curscript]['role_id'][$plugin['identifier']] = $module['role_id'];
										}
										if(preg_match('/\_output$/', $funcname)) {
											$varname = preg_replace('/\_output$/', '', $funcname);											
											$data[$k][$curscript]['outputfuncs'][$varname][] = array('displayorder' => $module['displayorder'], 'func' => array($plugin['identifier'], $funcname));
										}else{
											$data[$k][$curscript]['funcs'][$funcname][] = array('displayorder' => $module['displayorder'], 'func' => array($plugin['identifier'], $funcname));
										}
									}
								}
								break;
						}
					}
				}
			}
		}
		foreach($data['hookscript'] as $curscript => $scriptdata) {
			if(is_array($scriptdata['funcs'])) {
				foreach($scriptdata['funcs'] as $funcname => $funcs) {
					$tmp = array();
					foreach($funcs as $k => $v) {
						$tmp[$k] = $v['func'];
					}
					$data['hookscript'][$curscript]['funcs'][$funcname] = $tmp;
				}
			}
			if(is_array($scriptdata['outputfuncs'])) {
				foreach($scriptdata['outputfuncs'] as $funcname => $funcs) {
					$tmp = array();
					foreach($funcs as $k => $v) {
						$tmp[$k] = $v['func'];
					}
					$data['hookscript'][$curscript]['outputfuncs'][$funcname] = $tmp;
				}
			}
		}
		return array($data['plugins'], $data['hookscript']);
	}

	function pluginmodule($pluginid) {
		global $_J;
		if(!isset($_J['plugins'])) {
			$this->loadplugincache();
		}
		list($identifier, $module) = explode(':', $pluginid);
		if(!is_array($_J['plugins']['hookmenu']) || !array_key_exists($pluginid, $_J['plugins']['hookmenu'])) {
			return array('0','插件不存在或已关闭');
		}
		if(empty($identifier) || !preg_match("/^[a-z]+[a-z0-9_]*[a-z]+$/i", $identifier) || !preg_match("/^[a-z]+[a-z0-9_]*[a-z]+$/i", $module)) {
			return array('0','未定义的操作');
		}
		if(@!file_exists(PLUGIN_DIR.($modfile = '/'.$identifier.'/'.$module.'.inc.php'))) {
			return array('0','插件模块文件('.PLUGIN_DIR.$modfile.')不存在或者插件文件不完整');
		}
		if($_J['plugins']['hookmenu'][$pluginid]['role_id'] && 'admin' != MEMBER_ROLE_TYPE) {
			return array('0','您没有权限进行该操作');
		}
		return array('1',$_J['plugins']['hookmenu'][$pluginid]['navname'],PLUGIN_DIR.$modfile);
	}

	function runhooks($script = '') {
		if($script) {
			global $_J;
			if(!isset($_J['plugins'])) {
				$this->loadplugincache();
			}
			if($_J['plugins']['func']['common']) {
				$this->hookscript('common', 'funcs', array(), 'common');
			}
			$this->hookscript($script);
		}
	}

	function hookscript($script, $type = 'funcs', &$param = array(), $func = '') {
		global $_J;
		static $pluginclasses;
		if(!isset($_J['plugins'])) {
			$this->loadplugincache();
		}
		if(!isset($_J['hookscript'][$script][$type])) {
			return;
		}
		foreach((array)$_J['hookscript'][$script]['module'] as $identifier => $include) {
			$hooksadminid[$identifier] = !$_J['hookscript'][$script]['role_id'][$identifier] || ($_J['hookscript'][$script]['role_id'][$identifier] && 'admin' == MEMBER_ROLE_TYPE);
			if($hooksadminid[$identifier]) {
				@include_once ROOT_PATH.'./plugin/'.$include.'.class.php';
			}
		}
		if(@is_array($_J['hookscript'][$script][$type])) {
			$_J['inhookscript'] = true;
			$funcs = !$func ? $_J['hookscript'][$script][$type] : array($func => $_J['hookscript'][$script][$type][$func]);
			foreach($funcs as $hookkey => $hookfuncs) {
				foreach($hookfuncs as $hookfunc) {
					if($hooksadminid[$hookfunc[0]]) {
						$classkey = 'plugin_'.$hookfunc[0];
						if(!class_exists($classkey)) {
							continue;
						}
						if(!isset($pluginclasses[$classkey])) {
							$pluginclasses[$classkey] = new $classkey;
						}
						if(!method_exists($pluginclasses[$classkey], $hookfunc[1])) {
							continue;
						}
						$return = $pluginclasses[$classkey]->$hookfunc[1]($param);

						if(is_array($return)) {
							if(!isset($_J['pluginhooks'][$hookkey]) || is_array($_J['pluginhooks'][$hookkey])) {
								foreach($return as $k => $v) {
									$_J['pluginhooks'][$hookkey][$k] .= $v;
								}
							}
						} else {
							if(!is_array($_J['pluginhooks'][$hookkey])) {
								$_J['pluginhooks'][$hookkey] .= $return;
							} else {
								foreach($_J['pluginhooks'][$hookkey] as $k => $v) {
									$_J['pluginhooks'][$hookkey][$k] .= $return;
								}
							}
						}
					}
				}
			}
		}
		$_J['inhookscript'] = false;
	}

	function hookscriptoutput() {
		global $_J;
		if(!isset($_J['plugins'])) {
			$this->loadplugincache();
		}
		if(!empty($_J['hookscriptoutput'])) {
			return;
		}
		$this->hookscript('global', 'outputfuncs');
		$_J['hookscriptoutput'] = true;
	}
}
?>