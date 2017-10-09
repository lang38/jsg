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
 * @Date 2013-11-11 1510399388 3540 $
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

	
	var $Position='';

	
	var $Module='index';

	
	var $Code='';

	var $hookall_temp = '';

	function MasterObject(&$config)
	{
		if(!$config['widget_enable']) {
			$msg = 'Widget功能没有启用';
			if(get_param('in_ajax')) {
				widget_error($msg);
			} else {
				exit($msg);
			}
		}


		$this->Config=$config;


				$this->Get     = &$_GET;
		$this->Post    = &$_POST;





		$this->Module = get_param('mod');
		$this->Code   = get_param('code');



				$this->DatabaseHandler = & Obj::registry('DatabaseHandler');

				$this->MemberHandler = jclass('member');
		$this->MemberHandler->init();
		if($this->MemberHandler->HasPermission($this->Module,$this->Code)==false)
		{
						widget_error($this->MemberHandler->GetError(), 203);
		}
		Obj::register("MemberHandler",$this->MemberHandler);


        		define("FORMHASH",substr(md5(substr(time(), 0, -4).$this->Config['auth_key']),0,16));
		if($_SERVER['REQUEST_METHOD']=="POST") {
			if($this->Post["FORMHASH"]!=FORMHASH) {
							}
		}
	}

	    function _page($total, $perpage)
    {
        $return  = array();

        $page_count = max(1,ceil($total / $perpage));
        if($this->Config['total_page_default'] > 1 && $page_count > $this->Config['total_page_default'])
        {
            $page_count = $this->Config['total_page_default'];
        }

        $page = max(1,min($page_count, (int) $this->Get['page']));
        $page_next = min($page + 1,$page_count);
        $page_previous = max(1,$page - 1);

        $offset = max(0, (int) (($page - 1) * $perpage));

        $return = array(
            'total' => $total,
            'perpage' => $perpage,
            'page_count' => $page_count,
            'page' => $page,
            'page_next' => $page_next,
            'page_previous' => $page_previous,
            'offset' => $offset,
            'limit' => $perpage,
        );

        return $return;
    }

}


function widget_output($result,$status='',$code=0)
{
	$outputs = array();
	if($status) {
		$outputs['status'] = $status;
        $outputs[$status] = true;
	}
    if($code) {
    	$outputs['code'] = $code;
    }

    $outputs['result'] = $result;

	$outputs = array_iconv($GLOBALS['_J']['charset'], 'utf-8', $outputs);

	ob_clean();
	echo json_encode($outputs);
}


function widget_error($msg,$code=0,$halt=true)
{
	widget_output($msg,'error',$code);
	$halt && exit;
}
?>