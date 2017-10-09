<?php
/**
 *
 * API相关操作模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: api.mod.php 5238 2013-12-11 09:07:22Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}


class ModuleObject extends MasterObject {

	var $id = 0;
	var $info = array();

    
	function ModuleObject($config) {
		$this->MasterObject($config);

        $this->Execute();
	}

	function Execute() {
		ob_start();
		switch($this->Code) {
			case 'do_modify_setting': {
                    $this->DoModifySetting();
                    break;
                }

            case 'modify': {
                    $this->Modify();
                    break;
                }

            case 'do_modify': {
                    $this->DoModify();
                    break;
                }

            case 'status0':
            case 'status1': {
                    $this->Status();
                    break;
                }

            case 'delete': {
                    $this->Delete();
                    break;
                }

            case 'reset_app_secret':
            	$this->ResetAppSecret();
            	break;

			default : {
    				$this->Main();
    				break;
    			}
		}
		$this->ShowBody(ob_get_clean());
	}

    
	function Main() {
        $api = $api_config = jconf::get('api');
        if(!$api_config) {
            $api_config = array (
                'enable' => 0,
            	'from_enable' => 1,
            );

            jconf::set('api',$api_config);
        }



		$app_enable_radio = $this->jishigou_form->YesNoRadio('api[enable]',(int) ($api_config['enable'] && $this->Config['api_enable']));
		$app_from_enable_radio = $this->jishigou_form->YesNoRadio('api[from_enable]', (int) $api_config['from_enable']);

		$app_list = array();
		$rets = jtable('app')->get(array(
			'sql_order' => ' `last_request_time` DESC ',
			'page_num' => 50,
		));
		if($rets['list']) {
	        foreach($rets['list'] as $row) {
	            $row['last_request_time_html'] = my_date_format($row['last_request_time']);
	            $row['status_html'] = ($row['status'] ? '正常' : '暂停');

	            $app_list[] = $row;
	        }
		}
		$page_html = $rets['page']['html'];
		$total_record = $rets['count'];

        include(template('admin/api'));
	}

    function DoModifySetting() {
        $app_name_new = trim(strip_tags($this->Post['app_name_new']));
        if($app_name_new) {
        	$p = array();
            $p['app_key'] = jtable('app')->rand_key($this->jsgAuthKey . serialize($_SERVER) . TIMESTAMP . random(128));
            if(($info = jtable('app')->info($p))) {
            	$this->Messager('app_key已经存在，请重试');
            }
            $p['app_secret'] = md5($app_key . random(128));
            $p['status'] = 1;
            $p['uid'] = MEMBER_ID;
            $p['username'] = MEMBER_NICKNAME;
            $p['app_name'] = $app_name_new;
            $p['create_time'] = TIMESTAMP;

            $app_id = jtable('app')->insert($p, 1);

            $this->Messager(null, "admin.php?mod=api&code=modify&id=$app_id");
        }

        $api = $this->Post['api'];

        $api_config_default = $api_config = jconf::get('api');
        $api_config['enable'] = ($api['enable'] ? 1 : 0);
        $api_config['from_enable'] = ($api['from_enable'] ? 1 : 0);
        $api_config['request_times_day_limit'] = (is_numeric($api['request_times_day_limit']) ? $api['request_times_day_limit'] : 0);

        if($api_config_default != $api_config) {
        	jconf::set('api',$api_config);
        }

        if($api_config['enable']!=$this->Config['api_enable']) {
            jconf::update('api_enable', $api_config['enable']);
        }


        $this->Messager("修改成功");
    }

    function Modify() {
        $this->_init_info();

        $id = $this->id;
        $app = $this->info;



        $app_show_from_radio = $this->jishigou_form->YesNoRadio('app_show_from', $app['show_from']);
        $app_status_radio = $this->jishigou_form->YesNoRadio('app_status',$app['status']);


        include(template('admin/api'));
    }

    function DoModify() {
        $this->_init_info();

        $p = array(
        	'app_name' => strip_tags(jget('app_name')),
        	'source_url' => strip_tags(jget('source_url')),
        	'app_desc' => strip_tags(jget('app_desc')),
        	'show_from' => jget('app_show_from') ? 1 : 0,
        	'status' => jget('app_status') ? 1 : 0,
        );
        jtable('app')->update($p, $this->id);

        jtable('app')->cache_rm($this->id);

        $this->Messager("修改成功");
    }

    function Status() {
        $this->_init_info();

        $p = array();
        $p['status'] = ('status0' == $this->Code ? 0 : 1);
        jtable('app')->update($p, $this->id);

		jtable('app')->cache_rm($this->id);

        $this->Messager("设置成功");
    }

    function Delete() {
        $this->_init_info();

        if($this->id) {
			jtable('app')->delete($this->id);
		}

        $this->Messager("删除成功");
    }

    function ResetAppSecret() {
    	$this->_init_info();

    	$p = array();
    	$p['app_secret'] = jtable('app')->rand_key($this->info['app_key'] . $this->info['app_secret'] . random(128));
    	jtable('app')->update($p, $this->id);

    	$this->Messager("【APP SECRET】重置成功");
    }

    function _init_info() {
    	$id = jget('id', 'int');
    	if($id < 1) {
    		$this->Messager("请指定一个ID", null);
    	}
    	$this->id = $id;

    	$info = jtable('app')->info($id);
    	if(!$info) {
    		$this->Messager("请指定一个正确的ID", null);
    	}
    	$this->info = $info;
    }

}

?>
