<?php

/**
 * 文件名： api.func.php
 * 作  者：　狐狸 <foxis@qq.com>
 * @version $Id: api.func.php 5831 2014-09-17 02:06:35Z wuliyong $
 * 功能描述： api for JishiGou
 * 版权所有： Powered by JishiGou API 1.0.0 (a) 2005 - 2099 Cenwor Inc.
 * 公司网站： http://cenwor.com
 * 产品网站： http://jishigou.net
 */

if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

jfunc('api_xml');


function api_output($result,$status='',$code=0)
{
	$outputs = array();
	if($status) {
		$outputs['status'] = $status;
        $outputs[$status] = true;
	}
    if($code) {
        $outputs['code'] = "{$code}";
    } else {
    	$outputs['code'] = '200';
    }
    $outputs['result'] = $result;
    
    if(jget('debug_input')) {
    	$outputs['DEBUG_INPUT_G'] = $_GET;
    	$outputs['DEBUG_INPUT_P'] = $_POST;
    	$outputs['DEBUG_INPUT_C'] = $_COOKIE;
    	$outputs['DEBUG_PHP_INPUT'] = file_get_contents('php:/'.'/input');
    }
    if(jget('debug_server')) {
    	$outputs['DEBUG_SERVER'] = $_SERVER;
    }
	$outputs = array_iconv($GLOBALS['_J']['charset'],'utf-8',$outputs);

	ob_clean();
	if('json'==API_OUTPUT) {
		$api_output_json_header = (bool) (isset($_POST['__API__']['output_json_header']) ? $_POST['__API__']['output_json_header'] : $_GET['__API__']['output_json_header']);
		if($api_output_json_header) {
			header("Content-type: application/json;charset=utf-8");
		} else {
			header("Content-type: text/html;charset=utf-8");
		}
		$outputs_json = json_encode($outputs);
		$api_output_jsonp_callback = (isset($_POST['__API__']['output_jsonp_callback']) ? $_POST['__API__']['output_jsonp_callback'] : $_GET['__API__']['output_jsonp_callback']);
		if($api_output_jsonp_callback) {
			echo $api_output_jsonp_callback . '(' . $outputs_json . ');';
		} else {
			echo $outputs_json;
		}
	} elseif('serialize_base64'==API_OUTPUT) {
        header("Content-type: text/html;charset=utf-8");
		echo base64_encode(serialize($outputs));
    } else {
		$api_output_xml_htmlon = (bool) (isset($_POST['__API__']['output_xml_htmlon']) ? $_POST['__API__']['output_xml_htmlon'] : $_GET['__API__']['output_xml_htmlon']);
		$api_output_xml_isnormal = (bool) (isset($_POST['__API__']['output_xml_isnormal']) ? $_POST['__API__']['output_xml_isnormal'] : $_GET['__API__']['output_xml_isnormal']);
		header("Content-type: application/xml;charset=utf-8");
		echo api_xml_serialize($outputs, $api_output_xml_htmlon, $api_output_xml_isnormal);
	}
}


function api_error($msg,$code=0,$halt=true)
{
	api_output($msg,'error',$code);

	$halt && exit;
}


?>