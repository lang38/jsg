<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename ajax.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013 1025708773 1957 $
 */




if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject {

	function ModuleObject($config) {
		
		$this->MasterObject($config, 1);
	}

	
	function mail_send_test() {
		$k = jget('k','int');

		$smtp = jconf::get('smtp');

		$test_smtp = $smtp['smtp'][$k];


		jfunc('mail');
		$ret = _send_mail_by_smtp($test_smtp['mail'],$this->Config['site_name'] . '-' . date('Y-m-d H:i:s'),'邮件测试正文---'.date('Y-m-d H:i:s'),$test_smtp);
		if($ret) {
			echo '发送成功。';
		} else {
			echo '发送不成功，请检查配置是否正确。';
		}
		exit;
	}

    
    function static_data_refresh() {
        $type = jget('type');
        if(!in_array($type, array('app', 'content', 'login', 'other', 'role', 'user', 'verify'))) {
        	exit('type is invalid');
        }        
        
        $ret = array();
        $other_logic = jlogic('other');        
        $func = 'get'.  ucfirst($type).'Statistics';
        if(method_exists($other_logic, $func)) {
        	$cache_id = 'misc/'.$type.'_statistics';
	        cache_file('rm',$cache_id);
	        if($type == 'other'){
	            cache_file('rm','misc/data_length');
	        }
        
        	$ret = $other_logic->$func();
        }        

        #生成html代码
        $head_html = "<tr class='altbg1'>";
        $body_html = "<tr class='altbg2'>";
        if($ret) {
            foreach ($ret['data'] as $k => $v) {
                $head_html .= "<td>{$v['name']}</td>";
                $body_html .= "<td>{$v['num']}</td>";
            }
        }
        $head_html .= "</tr>";
        $body_html .= "</tr>";

        echo $head_html.$body_html;
        exit;
    }
}

?>
