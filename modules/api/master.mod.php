<?php
/**
 * 文件名： master.mod.php
 * 作     者： 狐狸 <foxis@qq.com>
 * @version $Id: master.mod.php 5834 2014-09-22 02:40:06Z wuliyong $
 * 功能描述： api for JishiGou
 * 版权所有： Powered by JishiGou API 1.0.0 (a) 2005 - 2099 Cenwor Inc.
 * 公司网站： http://cenwor.com
 * 产品网站： http://jishigou.net
 */

if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class MasterObject
{
	
	var $Config=array();

	
	var $Inputs = array();

	
	var $DatabaseHandler;

	var $MemberHandler;

	var $ip = '';

	var $timestamp = '';

	var $failedlogins = array();

	var $Module = '';

	var $Code = '';

	var $__API__ = array();

	var $app_key = '';

	var $app_secret = '';

	var $app = array();

	var $user = array();

	var $TopicLogic;

	
	var $__inputs__ = array();

	var $auto_run = false;


	
	function MasterObject(&$config, $auto_run = false)
	{
		if(!$config['api_enable']) {
			exit('api_enable is invalid');
		} else {
			$config['api'] = jconf::get('api');
		}

		$this->Config = $config;

		$this->timestamp = TIMESTAMP;
		$this->ip = $GLOBALS['_J']['client_ip'];


				jfunc('api');


				$this->init_inputs();


				$this->init_db();


				if('oauth2' != $this->Module) {
			$this->init_app();
		}


				$this->MemberHandler = jclass('member');

		
		if(!$this->_is_public_mod()) {
						$this->init_user();
		} else {
			$uid = 0; $pas = '';
			if('oauth2' == $this->Module && ($cookie_auth = jsg_getcookie('auth'))) {
				list($pas, $uid) = explode("\t", authcode($cookie_auth, 'DECODE'));
			}
			$this->MemberHandler->FetchMember($uid, $pas);
		}

		Obj::register("MemberHandler", $this->MemberHandler);


				$this->TopicLogic = jlogic('topic');


		
		if($this->auto_run || $auto_run) {
			$this->auto_run();
		}
	}
	function auto_run() {
				
		if($this->Code && method_exists('ModuleObject', $this->Code)) {
			$this->{$this->Code}();
		} else {
						$this->index();
		}
			}


	
	function init_inputs() {
		$inputs = array();
		if ($_GET) {
			foreach ($_GET as $_k=>$_v) {
				$inputs[$_k] = $_v;
			}
		}
		if ($_POST) {
			foreach ($_POST as $_k=>$_v) {
				$inputs[$_k] = $_v;
			}
		}

		if(!$inputs) {
			api_error('inputs is empty',10);
		}


		
		
		

				if(!$inputs['__API__']) {
			foreach($inputs as $k=>$v) {
				if('__API__' == substr($k, 0, 7)) {
					$_k = substr($k, 7);
					if($_k) {
						$_POST['__API__'][$_k] = $inputs['__API__'][$_k] = $v;
					}
					unset($_POST[$k], $_GET[$k], $inputs[$k]);
				}
			}
		}

				if('oauth2' == $inputs['mod']) {
			$inputs['__API__']['output'] = 'json';
		}

		$this->__inputs__ = $inputs;

		$charsets = array('gbk'=>1,'gb2312'=>1,'utf-8'=>1,);
		$charset = trim(strtolower($inputs['__API__']['charset']));
		$charset = (($charset && isset($charsets[$charset])) ? $charset : 'utf-8');
		define('API_INPUT_CHARSET',     $charset);

		$inputs = array_iconv($charset,$this->Config['charset'],$inputs, 1);

		$outputs = array('xml'=>1,'json'=>1,'serialize_base64'=>1,);
		$output = trim(strtolower($inputs['__API__']['output']));
		$output = (($output && isset($outputs[$output])) ? $output : 'xml');
		define('API_OUTPUT',        $output);

		$auth_types = array('jauth1'=>1, 'jauth2'=>1, 'oauth2'=>1, );
		$auth_type = trim(strtolower($inputs['__API__']['auth_type']));
		$auth_type = (($auth_type && isset($auth_types[$auth_type])) ? $auth_type : 'jauth1');
		define('API_AUTH_TYPE', 	$auth_type);

		$this->Module = $inputs['mod'];

		$this->Code = $inputs['code'];

		$this->__API__ = $inputs['__API__'];

		$this->Inputs = $inputs;


		unset($inputs);
	}

	
	function init_db() {
		$this->DatabaseHandler = & Obj::registry('DatabaseHandler');
		if(!$this->DatabaseHandler) {
			api_error('db is invalid',11);
		}
	}


	
	function init_app() {
		if($this->ip) {
			$fls = jconf::get('failedlogins');
			if($fls['white_list'] && in_array($this->ip, $fls['white_list'])) {
				;
			} else {
				if($fls['limit'] < 1) {
					$fls['limit'] = 1000;
				}
				if($fls['time'] < 1) {
					$fls['time'] = 10;
				}
				$this->failedlogins = DB::fetch_first("select * from ".TABLE_PREFIX."failedlogins where `ip`='{$this->ip}' ");
				if($this->failedlogins) {
										if(($this->failedlogins['lastupdate'] + ($fls['time'] * 1)) > $this->timestamp) {
												if($this->failedlogins['count'] > ($fls['limit'] * 1)) {
							api_error('ip is invalid',12);
						} else {
							DB::query("DELETE FROM ".TABLE_PREFIX.'failedlogins'." WHERE `lastupdate`<'".($this->timestamp - $fls['time'] - 1)."'", 'UNBUFFERED');
						}
					} else {
												DB::query("delete from ".TABLE_PREFIX."failedlogins where `ip`='{$this->ip}'");
					}
				}
			}
		}

		$error = '';
		$app = array();
		$this->app_key = ($this->__API__['app_key'] ? $this->__API__['app_key'] : $this->Inputs['client_id']);

		if($this->app_key && preg_match('~^[\w\d]{2,32}$~i', $this->app_key)) {
			$app = DB::fetch_first("select * from ".TABLE_PREFIX."app where `app_key`='{$this->app_key}'");
		}

		if(!$app) {
			$error = 'app_key is invalid';
		} else {
			define('IN_JISHIGOU_LOGIN_TYPE', $app['app_name']);

			
			if(!$this->_is_public_mod()) {
				if('jauth2' == API_AUTH_TYPE) {
					$auth_sign = $this->__API__['auth_sign'];
					$sign = $this->_sign($this->__inputs__, $app['app_secret'], 'auth_sign');
					if($sign != $auth_sign) {
						$error = 'auth_sign is invalid';
					}
				} elseif('oauth2' == API_AUTH_TYPE) {
					$access_token = ($this->__API__['access_token'] ? $this->__API__['access_token'] : $this->Inputs['access_token']);
					if($access_token) {
						$token_info = DB::fetch_first("SELECT * FROM ".DB::table('api_oauth2_token')." WHERE `client_id`='{$this->app_key}' AND `access_token`='$access_token'");
						if($token_info) {
							if($token_info['expires'] && $token_info['expires'] < TIMESTAMP) {
								$error = 'access_token is expires';
							} else {
								if($token_info['uid'] > 0) {
									$this->user = DB::fetch_first("SELECT * FROM ".DB::table('members')." WHERE `uid`='{$token_info['uid']}'");
									if(!$this->user) {
										$error = 'token uid is invalid';
									}
								} else {
									$error = 'token uid is empty';
								}
							}
						} else {
							$error = 'access_token is invalid';
						}
					} else {
						$error = 'access_token is empty';
					}
				} else {
					$this->app_secret = trim($this->__API__['app_secret']);
					if(!$this->app_secret ||
					($app['app_secret']!=$this->app_secret)) {
						$error = 'app_secret is invalid';
					}
				}
			}
		}
		unset($this->__inputs__);

		if($error) {
			if(!$this->_is_public_mod() && $this->ip) {
								if($this->failedlogins) {
					DB::query("update ".TABLE_PREFIX."failedlogins set `count`=`count`+1,`lastupdate`='".$this->timestamp."' where `ip`='{$this->ip}'");
				} else {
					DB::query("replace into ".TABLE_PREFIX."failedlogins (`ip`,`count`,`lastupdate`) values ('{$this->ip}','1','".$this->timestamp."')");
				}
			}

			api_error((is_string($error) ? $error : 'app_key or app_secret is invalid'), 13);
		}

		$this->_update_app_request($app);

		if($app['status'] < 1) {
			api_error('app status is invalid',14);
		}

				if($this->Config['api']['request_times_day_limit'] > 0 && $app['request_times_day'] > $this->Config['api']['request_times_day_limit']) {
			api_error('api request_times_day is invalid',16);
		}

				;

		$this->app = $app;
	}

	
	function init_user() {
		if(!$this->user) {
			$username = trim($this->__API__['username']);
			$password = trim($this->__API__['password']);
			if(!$username || !$password ||
				!($user = DB::fetch_first("select * from ".TABLE_PREFIX."members where `nickname`='{$username}'")) ||
				((md5($user['nickname'] . $user['password'])!=$password) &&
					(md5(array_iconv($this->Config['charset'],API_INPUT_CHARSET,$user['nickname']) . $user['password'])!=$password) &&
					(md5($password . $user['salt']) != $user['password']) &&
					($password != $user['password']))) {
				if($this->ip) {
										if($this->failedlogins) {
						DB::query("update ".TABLE_PREFIX."failedlogins set `count`=`count`+1,`lastupdate`='".$this->timestamp."' where `ip`='{$this->ip}'");
					} else {
						DB::query("replace into ".TABLE_PREFIX."failedlogins (`ip`,`count`,`lastupdate`) values ('{$this->ip}','1','".$this->timestamp."')");
					}
				}

				api_error('username or password is invalid',15);
			}
			$this->user = $user;
		}

		$this->MemberHandler->FetchMember($this->user['uid'], $this->user['password']);
	}

	function _page($total, $count = null) {
		if(is_null($count)) {
			$count = (int) ($this->Inputs['count'] ? $this->Inputs['count'] : $this->Inputs['limit']);
		}
		$count = max(0,min(200, $count));
		if(!$count) {
			$count = 20;
		}

		$page_count = max(1,ceil($total / $count));
		if($this->Config['total_page_default'] > 1 && $page_count > $this->Config['total_page_default']) {
			$page_count = $this->Config['total_page_default'];
		}

		$page = max(1,min($page_count, (int) $this->Inputs['page']));
		$page_next = min($page + 1,$page_count);
		$page_previous = max(1,$page - 1);

		$offset = max(0, (int) (($page - 1) * $count));

		return array(
            'total' => $total,             'count' => $count,             'page_count' => $page_count,             'page' => $page,             'page_next' => $page_next,             'page_previous' => $page_previous,             'offset' => $offset,             'limit' => $count, 		);
	}

	function _topic($sql_where='', $topics = null) {
		if(is_null($topics)) {
			$topics = $this->TopicLogic->Get($sql_where);
		}

		$is_one_row = 0;
		if($topics) {
			if(is_numeric($sql_where)) {
				$is_one_row = 1;
				$topics = $this->_process_topic($topics);
			} else {
				$is_one_row = 0;
				foreach($topics as $k=>$v) {
					if(is_array($v)) {
						$v = $this->_process_topic($v);
						$topics[$k] = $v;
					}
				}
				$topics = array_values($topics);
			}
		}
				$topics = buddy_follow_html($topics, 'uid', (1 == $this->__API__['v'] ? '' : 'follow_html'), $is_one_row);

		return $topics;
	}

	function _process_topic($v) {
		$unsets = array('password', 'random', '__face__', 'salt');
						$v1keeps = array('tid', 'uid', 'nickname', 'username', 'raw_content', 'face', 'vip_pic',
			'longtextid', 'longtext', 'image_list', 'roottid', 'replys', 'forwards', 'digcounts',
			'totid', 'touid', 'tousername', 'tonickname', 'addtime', 'from', 'type', 'from_string',
			'ch_id', 'ch_name', 'ch_purview', 'parent_id', 'is_follow_relation', 'item', 'item_id', 'item_name',
			'favorite_members', 'favorite_time','anonymous','featureid','relateid','channel_type','topic_feature_status');

		if($v['totid']) {
			if(1 != $this->__API__['v']) { 				$row = $this->TopicLogic->Get($v['totid'], '*', 'Make', '', 'tid', 1);
				if($row) {
					if($row['image_list']) {
						$row['image_list'] = array_values($row['image_list']);
					}
					$v['to_topics'] = $row;
				}
			}

			$row = $this->TopicLogic->Get($v['roottid'], '*', 'Make', '', 'tid', 1);
			if($row) {
				if($row['image_list']) {
					$row['image_list'] = array_values($row['image_list']);
				}
				$v['root_topics'] = $row;
			}
		}
		if($v['relateid']) {			$row = $this->TopicLogic->Get($v['relateid'], '*', 'Make', '', 'tid', 1);
			if($row) {
				if($row['image_list']) {
					$row['image_list'] = array_values($row['image_list']);
				}
				$v['relate_topics'] = $row;
			}
		}
		if($v['image_list']) {
			$v['image_list'] = array_values($v['image_list']);
		}

		if(1 == $this->__API__['v'] && $v1keeps) {
			$r = array();
			foreach($v1keeps as $k) {
				if(isset($v[$k])) {
					$r[$k] = $v[$k];
				}
				if(isset($v['root_topics'][$k])) {
					$r['root_topics'][$k] = $v['root_topics'][$k];
				}
				if(isset($v['relate_topics'][$k])) {
					$r['relate_topics'][$k] = $v['relate_topics'][$k];
				}
			}
			$v = $r;
		} elseif ($unsets) {
			foreach($unsets as $s) {
				if(isset($v[$s])) {
					unset($v[$s]);
				}
				if(isset($v['to_topics'][$s])) {
					unset($v['to_topics'][$s]);
				}
				if(isset($v['root_topics'][$s])) {
					unset($v['root_topics'][$s]);
				}
				if(isset($v['relate_topics'][$s])) {
					unset($v['relate_topics'][$s]);
				}
			}
		}

		return $v;
	}

	function _topic_list($type='', $sql_wheres=array(), $order='',$recods=array(),$append=array()) {
		$sql_wheres['type'] = $sql_wheres['type'] ? $sql_wheres['type'] : "`type` IN('first','forward','both')";
		$id_max = max(0,(int) $this->Inputs['id_max']);
		if($id_max) {
			$sql_wheres[] = "`tid`<='$id_max'";
		}
		$id_min = max(0,(int) $this->Inputs['id_min']);
		if($id_min) {
			$sql_wheres[] = "`tid`>'$id_min'";
		}
		$order = $order ? $order : '`dateline` desc';
		$type = ($type ? $type : 'new');
		$dateline = 0;
		if('hot_forward' == $type) {
			$dateline = 1;
			$sql_wheres['type'] = "`type`='first'";
			$order = '`forwards` desc, `dateline` desc';
			$sql_wheres[] = '`forwards`>0';
		} elseif ('hot_reply' == $type) {
			$dateline = 1;
			$sql_wheres['type'] = "`type`='first'";
			$order = '`replys` desc, `dateline` desc';
			$sql_wheres[] = '`replys`>0';
		}
		if($dateline) {
			$dateline = max(0, (int) $this->Inputs['dateline']);
			$dateline = ($dateline && in_array($dateline, array(1, 7, 14, 30))) ? $dateline : 7;
			$sql_wheres[] = "`dateline`>='".(TIMESTAMP - $dateline * 86400)."'";
		}
		$item = $this->Inputs['item'];
		$item_id = max(0, (int) $this->Inputs['item_id']);
		if($item && in_array($item, array('qun')) && $item_id > 0) {
			$sql_wheres['type'] = "`type`!='reply'";
			jfunc('app');
			$tids = app_itemid2tid($item, $item_id);
			$sql_wheres[] = "`tid` in (".jimplode($tids).")";
		}
		$where = ($sql_wheres ? " where " . implode(" and ", $sql_wheres) : "");

		$rets = array();
		$total = DB::result_first("select count(*) as `count` from ".TABLE_PREFIX."topic $where ");
		if($total) {
			$rets = $this->_page($total);
			$rets['topics'] = $this->_topic(" $where order by $order limit {$rets[offset]}, {$rets[count]} ");
			if($recods && is_array($recods)){
				foreach($rets['topics'] as $key => $val){
					foreach($recods as $v){
						if($val['tid']==$v['tid']){
							$rets['topics'][$key]['title'] = $v['title'];
							$rets['topics'][$key]['rec_time'] = $v['rec_time'];
							break;
						}
					}
				}
			}
		}
		if($append && is_array($append)){
			$rets = array_merge($append,$rets);
		}
		api_output($rets);
	}

	
	function _user($uids = array(), $fields = array(), $order_by_uids = true)
	{
		$is_array = false;
		$is_numeric = false;

		if(is_numeric($uids)) {
			$is_numeric = true;
		} elseif(is_array($uids)) {
			$is_array = true;
			$_tmps = array();
			foreach($uids as $row) {
				$uid = (is_numeric($row) ? $row : ((is_array($row) && isset($row['uid'])) ? $row['uid'] : 0));
				$uid = max(0, (int) $uid);
				if($uid > 0) {
					$_tmps[$uid] = $uid;
				}
			}
			$uids = $_tmps;
			unset($_tmps);
		}
		if(!$uids) {
			return array();
		}
		$uids = (array) $uids;

		$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."members M
			left join ".TABLE_PREFIX."memberfields MF on MF.uid=M.uid
		where M.`uid` in (".jimplode($uids).")");
		$datas = array();
		$fields_default = array('uid','username','nickname','email','gender','face','province','city','ucuid','role_id','role_type',
			'topic_count','fans_count','follow_count','topic_favorite_count','tag_favorite_count',
			'validate','aboutme','signature','level', 'vip_pic', 'vip_info', 'nedu','companyid','company','department','departmentid','job','jobid');
				if($this->Config['company_enable']) {
			$fields_default = array_merge($fields_default, array('companyid', 'company', 'departmentid', 'department', 'jobid', 'job',));
		}
		$fields = ($fields ? $fields : $fields_default);
		while(false != ($user = $query->GetRow())) {
			$face = face_get($user, 'original');
			if(false===strpos($face,':/'.'/')) {
				$face = $this->Config['site_url'] . '/' . $face;
			}
			$user['face'] = $face;
			$user = jsg_member_make_validate($user);
			#if NEDU
			if (defined('NEDU_MOYO'))
			{
				$user['nedu'] = nlogic('user/package')->data($user['uid']);
			}
			#endif
			$row = array();
			if($fields) {
				foreach($fields as $field) {
					if(isset($user[$field])) {
						$row[$field] = $user[$field];
					}
				}
			} else {
				$row = $user;
			}

			if($is_numeric) {
				$datas = $row;
			} else {
				$datas[] = $row;
			}
		}
				if($order_by_uids && $datas && $is_array && is_array($uids)) {
			$_datas = array();
			foreach($uids as $_uid) {
				foreach($datas as $_k=>$_row) {
					if($_uid == $_row['uid']) {
						$_datas[$_k] = $_row;
					}
				}
			}
			$datas = array_values($_datas);
		}
		$datas = buddy_follow_html($datas, 'uid', '', $is_numeric);

		return $datas;
	}

	function _init_user($uid=null)
	{
		$uid = max(0,(int) (isset($uid) ? $uid : $this->user['uid']));
		if(!$uid)
		{
			api_error('uid is empty',100);
		}

		$user = $this->_user($uid);
		if(!$user)
		{
			api_error('uid is invalid',101);
		}

		return $user;
	}

	function _update_app_request($app = array())
	{
		$updates = array();
		$updates['request_times'] = "`request_times`=`request_times`+1";
		$updates['last_request_time'] = "`last_request_time`='{$this->timestamp}'";
		$updates['request_times_day'] = "`request_times_day`=`request_times_day`+1";
		$updates['request_times_week'] = "`request_times_week`=`request_times_week`+1";
		$updates['request_times_month'] = "`request_times_month`=`request_times_month`+1";
		$updates['request_times_year'] = "`request_times_year`=`request_times_year`+1";
		if(my_date_format($this->timestamp,'Ymd')!=my_date_format($app['last_request_time'],'Ymd'))
		{
			$updates['request_times_day'] = "`request_times_day`=1";
			$updates['request_times_last_day'] = "`request_times_last_day`='{$app['request_times_day']}'";

			if(my_date_format($this->timestamp,'YW')!=my_date_format($app['last_request_time'],'YW'))
			{
				$updates['request_times_week'] = "`request_times_week`=1";
				$updates['request_times_last_week'] = "`request_times_last_week`='{$app['request_times_week']}'";
			}

			if(my_date_format($this->timestamp,'Ym')!=my_date_format($app['last_request_time'],'Ym'))
			{
				$updates['request_times_month'] = "`request_times_month`=1";
				$updates['request_times_last_month'] = "`request_times_last_month`='{$app['request_times_month']}'";

				if(my_date_format($this->timestamp,'Y')!=my_date_format($app['last_request_time'],'Y'))
				{
					$updates['request_times_year'] = "`request_times_year`=1";
					$updates['request_times_last_year'] = "`request_times_last_year`='{$app['request_times_year']}'";
				}
			}
		}

		DB::query("update `".TABLE_PREFIX."app` set ".implode(" , ",$updates)." where `id`='{$app['id']}'");
	}

	function _sign($p, $secret_key, $signk = 'auth_sign') {
		unset($p['__API__'][$signk]);

		$str = '';
		krsort($p);
		reset($p);
		foreach($p as $k=>$v) {
			if(is_array($v)) {
				krsort($v);
				reset($v);
				foreach($v as $_k=>$_v) {
					$str .= ("{$k}[{$_k}]={$_v}");
				}
			} else {
				$str .= ("{$k}={$v}");
			}
		}
		$signv = md5($str . $secret_key);

		return $signv;
	}

	private function _is_public_mod() {
				$mods = array('test'=>1,'public'=>1,'oauth2'=>1);
		if($this->Module && isset($mods[$this->Module])) {
			return true;
		}
		return false;
	}

}

?>