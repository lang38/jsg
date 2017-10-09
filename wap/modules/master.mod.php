<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename master.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2014 104691202 6882 $
 */


if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class MasterObject
{
	
	var $Config=array();
	var $Get,$Post,$Files,$Request,$Cookie,$Session;

	
	var $DatabaseHandler;
	
	var $MemberHandler;


	
	var $Title='';

	var $MetaKeywords='';

	var $MetaDescription='';

	
	var $Position='';

	
	var $Module='index';

	
	var $Code='';

	var $auto_run = false;

	function MasterObject(&$config, $auto_run = false)
	{
		if(!$config['wap']) {
			include(ROOT_PATH . 'wap/include/error_wap.php');
			exit;
		}

		$this->Config=$config;
		require_once ROOT_PATH . 'wap/include/func/wap_global.func.php';

				$this->Get     = &$_GET;
		$this->Post    = &$_POST;





		$this->Module = get_param('mod');
		$this->Code   = get_param('code');

				$this->DatabaseHandler = & Obj::registry('DatabaseHandler');

				$this->MemberHandler = jclass('member');
		$this->MemberHandler->init();
		if($this->MemberHandler->HasPermission($this->Module,$this->Code)==false) {
			$member_error = $this->MemberHandler->GetError();
			$member_error = array_iconv($this->Config['charset'],'utf-8',$member_error);
			$this->Messager($member_error,null);
		}

		
		if(!in_array($this->Module, array('member', 'login', 'other', ))) {
			$visit_rets = $this->MemberHandler->visit();
			if($visit_rets['error']) {
								$this->Messager(null, 'index.php?mod=login&referer=' . urlencode('index.php?' . $_SERVER['QUERY_STRING']));
			}
		}

		$this->Title=$this->MemberHandler->CurrentAction['name'];		Obj::register("MemberHandler", $this->MemberHandler);


		
		if($this->auto_run || $auto_run) {
			$this->auto_run();
		}
	}
	function auto_run() {
		ob_start();
		
		if($this->Code && method_exists('ModuleObject', $this->Code)) {
			$this->{$this->Code}();
		} else {
			$this->Code = $_POST['code'] = $_GET['code'] = 'index';
			$this->index();
		}
		$this->ShowBody(ob_get_clean());
	}

	
	function Messager($message, $redirectto='',$time = -1,$return_msg=false,$js=null)
	{
		global $jishigou_rewrite;


		if ($time===-1) $time = (is_numeric($this->Config['msg_time'])?$this->Config['msg_time']:5);
		$to_title=($redirectto==='' or $redirectto==-1)?"返回上一页":"跳转到指定页面";
		if($redirectto===null)
		{
			$return_msg=$return_msg===false?"  ":$return_msg;
		}
		else
		{
			$redirectto=($redirectto!=='')?$redirectto:($from_referer=referer());
			if(str_exists($redirectto,'mod=login','code=register','/login','/register'))
			{
				$referer='&referer='.urlencode('index.php?'.'mod=plaza');

				jsg_setcookie('referer','index.php?'.'mod=plaza');
			}
			if (is_numeric($redirectto)!==false and $redirectto!==0)
			{
				if($time!==null){
					$url_redirect="<script language=\"JavaScript\" type=\"text/javascript\">\r\n";
					$url_redirect.=sprintf("window.setTimeout(\"history.go(%s)\",%s);\r\n",$redirectto,$time*1000);
					$url_redirect.="</script>\r\n";
				}
				$redirectto="javascript:history.go({$redirectto})";

			}
			else
			{
				if($message===null)
				{
					@header("Location: $redirectto"); #HEADER跳转
				}
				if($time!==null)
				{
					$url_redirect = ($redirectto?'<meta http-equiv="refresh" content="' . $time . '; URL=' . $redirectto . '">':null);
				}
			}
		}

		$title="消息提示:".(is_array($message)?implode(',',$message):$message);

		$title=strip_tags($title);
		if($js!="") {
			$js="<script language=\"JavaScript\" type=\"text/javascript\">{$js}</script>";
		}
		$additional_str = $url_redirect.$js;

		$this->Title = '操作提示';

				$return_Url = $_SERVER['HTTP_REFERER'];

		include template('messager');

		exit;
	}

	function ShowBody($body)
	{
		echo $body;
		
		if($this->MemberHandler) {
			$this->MemberHandler->UpdateSessions();
		}
		
		if (upsCtrl()->ccDSP()) {
			echo "P"."o"."w"."e"."r"."e"."d"." b"."y"." J"."i"."s"."h"."i"."G"."o"."u";
		}
	}

    function _topicLogicGet($ids,$fields='*',$process='Make',$table="",$prikey='tid') {
        $data = $this->TopicLogic->Get($ids,$fields,$process,$table,$prikey);

        if($data) {
            $data = wap_iconv($data);
        }

        return $data;
    }
    function _topicLogicMakeAll($topic) {
    	return wap_iconv($this->TopicLogic->MakeAll($topic));
    }
    function _topicLogicGetMember($ids,$fields = '*') {
        $data = $this->TopicLogic->GetMember($ids,$fields);

        if($data) {
            $data = wap_iconv($data);
        }

        return $data;
    }

        function _longtextLogic($ids) {
        $data = jlogic('longtext')->get_info($ids);

        if($data) {
            $data = wap_iconv($data);
        }

        return $data;
    }



         function _PmLogic($folder,$page='')
    {
        $data = $this->PmLogic->getPmList($folder,$page);

        if($data)
        {
            $data = wap_iconv($data);
        }

        return $data;
    }

        function _GetHistory($uid=0,$touid=0,$page='')
    {
        $data = $this->PmLogic->getHistory($uid,$touid,$page);

        if($data)
        {
            $data = wap_iconv($data);
        }

        return $data;
    }


     	function _TopicListLogic($param='')
    {
        $data = $this->TopicListLogic->get_recd_list($param);

        if($data)
        {
            $data = wap_iconv($data);
        }

        return $data;
    }

}
?>