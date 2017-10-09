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
 * @Date 2013 1951833748 3451 $
 */


if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class MasterObject
{
	
	var $Config=array();
	var $Get;
	var $Post;
	var $Cookie;
	var $Session;

	
	var $DatabaseHandler;
	
	var $MemberHandler;


	
	var $Title='';

	
	var $Module='index';

	
	var $Code='';
	var $Channels = array();
	var $Channel_enable;
	var $yxm_title='';
	var $yxm_html='';
        var $auto_run = false;

	function MasterObject(&$config, $auto_run = false)
	{
		global $_J;
		$this->Config=$config;		
		$this->Get     =  &$_GET;
		$this->Post    =  &$_POST;
		$this->Module = get_param('mod');
		$this->Code   = get_param('code');

		

				$this->DatabaseHandler = & Obj::registry('DatabaseHandler');

		
		if(!jget('uninitmember')) { 			$this->initMemberHandler();
		}

				if($this->Config['seccode_enable']>1 && $this->Config['seccode_pub_key'] && $this->Config['seccode_pri_key']){
			$this->yxm_html = jlogic('seccode')->GetYXM();
			$this->yxm_title = jlogic('seccode')->TitleYXM();
		}
		if(!isset($_J['plugins'])) {
			jlogic('plugin')->loadplugincache();
		}
		runhooks('global');


		
		if($this->auto_run || $auto_run) {
			$this->auto_run();
		}
	}
	function auto_run() {
		ob_start();
		
		if($this->Code && method_exists('ModuleObject', $this->Code)) {
			$this->{$this->Code}();
		} else {
			exit('method ' . $this->Code . ' is not exists');
		}
		response_text(ob_get_clean());
	}

	function initMemberHandler() {
		$this->MemberHandler = jclass('member');
		$member = $this->MemberHandler->init();
		
		Obj::register("MemberHandler", $this->MemberHandler);
		return $member;
	}

	function js_show_msg($a=0)
	{
		$return = "{$GLOBALS['schedule_html']}";

		if($GLOBALS['jsg_schedule_mark'] || jsg_getcookie('jsg_schedule'))
		{
			$return .= jsg_schedule();
		}

		if(($js_show_msg=($GLOBALS['js_show_msg'] ? $GLOBALS['js_show_msg'] : jsg_getcookie('js_show_msg'))) && !$GLOBALS['js_show_msg_executed'])
		{
			$GLOBALS['js_show_msg_executed'] = 1;
			jsg_setcookie('js_show_msg','',-311040000);
			unset($GLOBALS['js_show_msg'],$_COOKIE['js_show_msg']);

						$return .= "<script language='javascript'>
            	$(document).ready(function(){show_message('{$js_show_msg}');});
            </script>";
		}

		$return .= '<script type="text/javascript">
$(document).ready(function(){
		$("ul.imgList img, div.avatar img.lazyload").lazyload({
		skip_invisible : false,
		threshold : 200,
		effect : "fadeIn"
	});
});
</script>';

		if($a) {
			$return = $js_show_msg;
		}

		return $return;
	}
}
?>