<?php
/**
 *
 * 记事狗核心入口类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: jishigou.php 5666 2014-04-25 01:53:16Z wuliyong $
 */

class jishigou {

	public $var = array();

	public $init_db = true;
	public $init_user = false;
	public $init_robot = true;

	public function jishigou() {
		if(!defined('IN_JISHIGOU')) {
			$this->_init_env();
			$this->_init_config();
			$this->_init_input();
			$this->_init_output();
		}
	}

	public function init() {
		if(!$this->var['initiated']) {
			$this->_init_app();
			$this->_init_db();
			$this->_init_user();
			$this->_init_robot();

			$this->var['initiated'] = true;
		}
	}

	public function run($type='') {
				$types = array(
			'index'=>array('mod_default'=>$this->var['config']['default_module'] ? $this->var['config']['default_module'] : 'topic'),
			'admin'=>array('mod_exit'=>1, 'tpl_default'=>'admin/'),
			'ajax'=>array('mod_default'=>'topic', 'tpl_default'=>'ajax/'),
			'api'=>array('mod_default'=>'test', 'mod_exit'=>1, 'tpl_default'=>'api/'),
			'imjiqiren'=>array('mod_default'=>'imjiqiren', 'mod_exit'=>1, 'tpl_default'=>'imjiqiren/'),
			'sms'=>array('mod_default'=>'sms', 'mod_exit'=>1, 'tpl_default'=>'sms/'),
			'widget'=>array('mod_default'=>'qun', 'mod_exit'=>1, 'tpl_default'=>'widget/'),
			'wap'=>array('mod_default'=>'topic', 'mod_path'=>'wap/modules/', ),

			'mobile'=>array('mod_default'=>'topic', 'mod_path'=>'mobile/modules/', ),
			'mobile_ajax'=>array('mod_default'=>'topic', 'mod_path'=>'mobile/modules/ajax/', ),
		);
		if(!isset($types[$type])) {
			if(empty($type)) {
				$type = 'index';
			} else {
				$types[$type] = array('mod_default' => 'index', 'mod_exit' => 1, 'tpl_default' => $type . '/');
			}
		}
		$types[$type]['modules_path'] = $modules_path = ROOT_PATH . ($types[$type]['mod_path'] ? $types[$type]['mod_path'] : ('modules/' . ('index' == $type ? '' : $type . '/')));
		$this->var['config']['jishigou_run_type'] = $type;
		$this->var['config']['jishigou_run_tpl_default'] = $types[$type]['tpl_default'];

				define('IN_JISHIGOU_' . strtoupper($type), true);

				if ($this->var['config']['upgrade_lock_time'] > 0 && true!==IN_JISHIGOU_UPGRADE && true!==IN_JISHIGOU_ADMIN) {
			if(($this->var['config']['upgrade_lock_time'] + 6000 > TIMESTAMP) ||
			(is_file(ROOT_PATH . './data/cache/upgrade.lock') &&
			@filemtime(ROOT_PATH . './data/cache/upgrade.lock') + 6000 > TIMESTAMP)) {
				die('System upgrade. Please wait...');
			}
		}

				if ($this->var['config']['site_closed'] && true!==IN_JISHIGOU_ADMIN) {
			if ('login' != $this->var['mod'] && ($site_closed_msg=file_get_contents(ROOT_PATH . 'data/cache/site_enable.txt'))) {
				exit($site_closed_msg);
			}
		}

				if ($this->var['config']['ipbanned_enable']) {
			if(false != ($ipbanned = jconf::get('access', 'ipbanned'))) {
				if(preg_match("~^({$ipbanned})~", $this->var['client_ip'])) {
					exit('Your IP has been banned access and registration.');
				}
				unset($ipbanned);
			}
		}

				if($this->var['config']['rewrite_enable'] && (true===IN_JISHIGOU_INDEX || true===IN_JISHIGOU_AJAX || true===IN_JISHIGOU_ADMIN)) {
			include(ROOT_PATH . 'include/rewrite.php');
		}

				$allow_gzip = 0;
		$un_gzip_mods = array('share'=>1, 'output'=>1, 'download'=>1, 'attachment'=>1, 'attach'=>1, );
		if(true===GZIP && true===IN_JISHIGOU_INDEX && !isset($un_gzip_mods[$this->var['mod']]) && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
			$allow_gzip = 1;
		}
		ob_start(($allow_gzip ? 'ob_gzhandler' : null));

		#if NEDU
		if (defined('NEDU_MOYO'))
		{
			nlogic('load/cooks')->gateway($type);
		}
		#endif

				$this->init();

				if(defined('APP_ROOT')) {
						$this->_init_user(1);
						$c = $this->_g('c', 'index');
			$a = $this->_g('a', 'index');
			$controller = $c . '_controller';
			$action = $a . '_action';
			Load::file('controller');
			if(!(@include_once APP_ROOT . 'controller/' . $c . '.class.php') && !class_exists($controller)) {
				error_404('controller ' . $c . ' is not exists');
			}
			$app_object = new $controller();
			if(method_exists($app_object, $action)) {
				jdefine('APP_ID', $c . '_' . $a);
				$app_object->$action();
			} else {
				error_404('action ' . $a . ' is not exists');
			}
		} else {
						if(!(@include_once $modules_path . 'master.mod.php') && !class_exists('MasterObject')) {
				error_404('modules path is invalid');
			}
			if(!(include $modules_path . ($this->_init_mod($types[$type])) . '.mod.php') && !class_exists('ModuleObject')) {
				error_404('mod is invalid');
			}
			$ModuleObject = new ModuleObject($this->var['config']);
		}

				
	}

	private function _init_env() {
		error_reporting(E_ERROR);
		@set_time_limit(300);
		if(PHP_VERSION < '5.3.0') {
			set_magic_quotes_runtime(0);
		}

		

		define('IN_JISHIGOU', true);
		define('ROOT_PATH', substr(dirname(__FILE__), 0, -8) . '/');
		define('PLUGIN_DIR', ROOT_PATH . 'plugin');
		define('RELATIVE_ROOT_PATH', './');
		define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
		define('TIMESTAMP', time());

		if(!defined('JISHIGOU_GLOBAL_FUNCTION') && !@include(ROOT_PATH . 'include/func/global.func.php')) {
			exit('global.func.php is not exists');
		}

		if(function_exists('ini_set')) {
			ini_set('memory_limit', '256M');
			ini_set('max_execution_time', 300);
			ini_set('arg_seperator.output', '&amp;');
			ini_set('magic_quotes_runtime', 0);
			ini_set('session.save_path', ROOT_PATH . 'data/temp/session/');
		}

		$superglobal = array(
				'GLOBALS' => 1,
				'_GET' => 1,
				'_POST' => 1,
						'_COOKIE' => 1,
				'_SERVER' => 1,
						'_FILES' => 1,
		);
		foreach($GLOBALS as $k=>$v) {
			if(!isset($superglobal[$k])) {
				$GLOBALS[$k] = null; unset($GLOBALS[$k]);
			}
		}

		global $_J;
		$_J = array(
			'timestamp' => TIMESTAMP,
			'time_start' => microtime(true),
			'client_ip' => client_ip(),
			'client_ip_port' => client_ip_port(),
			'uid' => 0,
			'username' => '',
			'nickname' => '',
			'role_id' => 0,
			'charset' => '',
			'site_name' => '',
			'site_url' => '',
			'wap_url' => '',
			'mobile_url' => '',
			'mod' => '',
			'code' => '',
		);

		$this->var = & $_J;
	}

	private function _init_config() {
		$config = jconf::get();

		define('CHARSET', $config['charset']);

		@header('Content-Type: text/html; charset=' . CHARSET);
		@header('P3P: CP="CAO PSA OUR"');

		if($config['install_lock_time'] < 1) {
			if (!is_file(ROOT_PATH . 'data/install.lock') &&
			is_file(ROOT_PATH . 'install.php')) {
				die("<meta http-equiv='refresh' content=\"1; URL='./install.php'\">
					<a href='./install.php'>Please click here for the installation of the system ... </a>");
			}
		}

		if(!isset($config['charset'])) {
			exit('config get invalid');
		}

		require ROOT_PATH . 'setting/constants.php';

				$config['sys_version'] = sys_version();
		$config['sys_published'] = SYS_PUBLISHED;
		if(!$config['wap_url']) {
			$config['wap_url'] = $config['site_url'] . "/wap";
		}
		if(!$config['mobile_url']) {
			$config['mobile_url'] = $config['site_url'] . "/mobile";
		}
				if($config['extra_domains']) {
			$http_host = (getenv('HTTP_HOST') ? getenv('HTTP_HOST') : $_SERVER['HTTP_HOST']);
			if($config['site_domain'] != $http_host && in_array($http_host, $config['extra_domains'])) {
				$site_url = rtrim(jhtmlspecialchars('http'.(443==$_SERVER['SERVER_PORT'] ? 's' : '').':/'.'/'.$http_host.preg_replace("/\/+/",'/',str_replace("\\",'/',dirname($_SERVER['PHP_SELF']))."/")),'/');
				if(true === IN_JISHIGOU_WAP || true === IN_JISHIGOU_MOBILE) {
					$site_url = str_replace(array('/wap', '/mobile'), '', $site_url);
				}
				$config['wap_url'] = str_replace($config['site_url'], $site_url, $config['wap_url']);
				$config['mobile_url'] = str_replace($config['site_url'], $site_url, $config['mobile_url']);
				$config['site_url'] = $site_url;
				$config['site_domain'] = $http_host;
			}
		}
		if(!$config['topic_cut_length']) {
			$config['topic_cut_length'] = 140;
			if(!isset($config['topic_input_length'])) {
				$config['topic_input_length'] = 140;
			}
		}
		$config['topic_input_length'] = (int) $config['topic_input_length'];

		Obj::register('config', $config);

		$load_configs = array(
			'robot' => 'robot_enable',
			'ad' => 'ad_enable',
			'credits' => 'extcredits_enable',
		);
		foreach($load_configs as $k=>$v) {
			if($config[$v]) {
				$config[$k] = jconf::get($k);
			}
		}
		$load_configs = array('modules', 'table', 'changeword');
		foreach($load_configs as $k) {
			$config[$k] = jconf::get($k);
		}
		$config['changeword']['n_weibo'] || $config['changeword']['n_weibo'] = '微博';
		$config['changeword']['p_weibo'] || $config['changeword']['p_weibo'] = '微博';
		$config['changeword']['weiqun'] || $config['changeword']['weiqun'] = '微群';
		$config['changeword']['dig'] || $config['changeword']['dig'] = '赞';
		$config['changeword']['username'] || $config['changeword']['username'] = '昵称、Email、个性域名';
		$config['changeword']['account'] || $config['changeword']['account'] = '帐号昵称';
		$config['seccode_comment'] || $config['seccode_comment'] = 0;
		$config['seccode_forward'] || $config['seccode_forward'] = 0;
		$config['in_publish_notice_js'] = $this->_php_js_arr($config['in_publish_notice'],0);
		$config['in_publish_notice_str'] = $this->_php_js_arr($config['in_publish_notice'],1);
		$config['on_publish_notice_str'] = $this->_php_js_arr($config['on_publish_notice'],1);
		$this->var['charset'] = strtolower($config['charset']);
		$this->var['db_charset'] = $config['db_charset'] = str_replace('-', '', $this->var['charset']);
		$this->var['site_name'] = $config['site_name'];
		$this->var['site_url'] = $config['site_url'];
		$this->var['wap_url'] = $config['wap_url'];
		$this->var['mobile_url'] = $config['mobile_url'];

		$this->var['config'] = & $config;
	}

	private function _init_input() {
		if (isset($_GET['GLOBALS']) || isset($_POST['GLOBALS']) || isset($_COOKIE['GLOBALS']) || isset($_FILES['GLOBALS'])) {
			die('request is invalid');
		}

		if($_GET) {
			if((true === IN_JISHIGOU_MOBILE || true === IN_JISHIGOU_AJAX) && 'utf-8' != $this->var['charset']) {
				$_GET = array_iconv('utf-8', $this->var['charset'], $_GET);
			}
			$_GET = jaddslashes($_GET);
		}
		if($_POST) {
			if((true === IN_JISHIGOU_MOBILE || true === IN_JISHIGOU_AJAX) && 'utf-8' != $this->var['charset']) {
				$_POST = array_iconv('utf-8', $this->var['charset'], $_POST);
			}
			$_POST = jaddslashes($_POST);
		}
		$_COOKIE = jaddslashes($_COOKIE);
		$_SERVER = jaddslashes($_SERVER);

	}

	private function _init_mod($options = array()) {
		$mod_default = ($options['mod_default'] ? $options['mod_default'] : 'index');
		$mod = $this->_g('mod');
		if(empty($mod)) {
			$mod = $mod_default;
		}
		if(!isset($this->var['config']['modules'][$mod]) && false == @file_exists($options['modules_path'] . $mod . '.mod.php')) {
			if($options['mod_exit']) {
				error_404('mod ' . $mod . ' is not exists');
			} else {
				if($mod) {
					$this->var['mod_original'] = $_POST['mod_original'] = $_GET['mod_original'] = $mod;
					$mod = 'topic';
				} else {
					$mod = $mod_default;
				}
			}
		}
		define('CURMODULE', $mod);
		$this->var['mod'] = $_POST['mod'] = $_GET['mod'] = $mod;
		$this->var['code'] = $_POST['code'] = $_GET['code'] = $this->_g('code');

		return $mod;
	}

	private function _init_output() {
				if('GET' == $_SERVER['REQUEST_METHOD'] && !empty($_SERVER['REQUEST_URI']) && true !== IN_JISHIGOU_API) {
			$temp = strtoupper(urldecode(urldecode($_SERVER['REQUEST_URI'])));
			if(strpos($temp, '<') !== false || strpos($temp, '"') !== false || strpos($temp, 'CONTENT-TRANSFER-ENCODING') !== false) {
				die('request is invalid');
			}
		}
	}

	private function _init_app() {
		$app_name = defined('APP_NAME') ? constant('APP_NAME') : $this->_g('app');
		$app_path = 'app/' . $app_name . '/';
		$app_root = ROOT_PATH . $app_path;
		if($app_name && is_dir($app_root)) {
			jdefine('APP_NAME', $app_name);
			jdefine('APP_PATH', $app_path);
			jdefine('APP_ROOT', $app_root);
		}
	}

	private function _init_db() {
		if($this->init_db) {
			$this->var['object_db'] = jclass('jishigou/mysql');
			$this->var['object_db']->connect();
			Obj::register('DatabaseHandler', $this->var['object_db']);

			$this->var['object_table'] = jclass('table');
		}
	}

	private function _init_user($init_user = 0) {
		if(($this->init_user || $init_user) && !isset($this->var['object_user'])) {
			$this->var['object_user'] = jclass('member');
			$this->var['object_user']->init();
			Obj::register('MemberHandler', $this->var['object_user']);
		}
	}

	private function _init_robot() {
				if($this->init_robot && $this->var['config']['robot'] &&
		
		(true === IN_JISHIGOU_INDEX || true === IN_JISHIGOU_WAP || true === IN_JISHIGOU_MOBILE)) {
			if($this->var['config']['robot']['turnon']) {
				$R = jlogic('robot');
				if(($robot_name = $R->isRobot())) {
					if ($this->var['config']['robot']['list'][$robot_name]['disallow']) {
						exit('Access Denied');
					}
					$R->statistic();

					$RL = jlogic('robot_log');
					$RL->setRobotName($robot_name);
					$RL->statistic();
					unset($RL);
				}
				unset($R);
			}
			unset($this->var['config']['robot']);
		}
	}

	private function _g($var, $ifemptyval = null) {
		$val = jget($var, 'txt');
		if(!$val) {
			$val = $_REQUEST[$var];
		}
		if(empty($val)) {
			return (is_null($ifemptyval) ? $val : $ifemptyval);
		} else {
			return str_safe($val);
		}
	}

	private function _php_js_arr($arr=array(), $type=0) {
		if($type) {
			$arr = explode("\r\n",$arr);
			$key = array_rand($arr,1);
			$str = $arr[$key];
		} else {
			$arr = explode("\r\n",$arr);
			$str = '';
			foreach($arr as $var) {
				$str .='"'.$var.'",';
			}
			$str .= ']';
			$str = str_replace(',]','',$str);
		}
		return $str;
	}

}


?>