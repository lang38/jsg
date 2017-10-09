<?php

/**
 * 文件名： api_xml.func.php
 * 作  者：　狐狸 <foxis@qq.com>
 * @version $Id: api.func.php 5678 2014-05-09 09:50:03Z wuliyong $
 * 功能描述： api for JishiGou
 * 版权所有： Powered by JishiGou API 1.0.0 (a) 2005 - 2099 Cenwor Inc.
 * 公司网站： http://cenwor.com
 * 产品网站： http://jishigou.net
 */

if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

if(!function_exists('api_is_special_chars'))
{
	function api_is_special_chars($v) {
		$cs = array('<', '>', '&', '"', "'", );
		foreach($cs as $c) {
			if(false !== strpos($v, $c)) {
				return true;
			}
		}
		return false;
	}
}
if(!function_exists('api_xml_unserialize'))
{
	function api_xml_unserialize(&$xml, $isnormal = FALSE) {
		$xml_parser = new ApiXMLObject($isnormal);
		$data = $xml_parser->parse($xml);
		$xml_parser->destruct();
		return $data;
	}
}
if(!function_exists('api_xml_serialize'))
{
	function api_xml_serialize($arr, $htmlon = false, $isnormal = false, $level = 1)
	{
		$s = ($level == 1 ? "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n<root>\r\n" : '');
		$space = str_repeat("\t", $level);
		foreach($arr as $k => $v)
		{
			if(!is_array($v))
			{
                $html = ($htmlon ? true : (is_numeric($v) ? false : api_is_special_chars($v)));
								$s .= $space.($isnormal ? "<$k>" : "<item id=\"$k\">").($html ? '<![CDATA[' : '').$v.($html ? ']]>' : '').($isnormal ? "</$k>" : "</item>")."\r\n";
			}
			else
			{
				$s .= $space.($isnormal ? "<$k>" : "<item id=\"$k\">")."\r\n".api_xml_serialize($v, $htmlon, $isnormal, $level + 1).$space.($isnormal ? "</$k>" : "</item>")."\r\n";
			}
		}
		$s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);

		return ($level == 1 ? $s."</root>" : $s);
	}
}

class ApiXMLObject {

	var $parser;
	var $document;
	var $stack;
	var $data;
	var $last_opened_tag;
	var $isnormal;
	var $attrs = array();
	var $failed = FALSE;

	function __construct($isnormal) {
		$this->ApiXMLObject($isnormal);
	}

	function ApiXMLObject($isnormal) {
		$this->isnormal = $isnormal;
		$this->parser = xml_parser_create('ISO-8859-1');
		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
		xml_set_object($this->parser, $this);
		xml_set_element_handler($this->parser, 'open','close');
		xml_set_character_data_handler($this->parser, 'data');
	}

	function destruct() {
		xml_parser_free($this->parser);
	}

	function parse(&$data) {
		$this->document = array();
		$this->stack	= array();
		return xml_parse($this->parser, $data, true) && !$this->failed ? $this->document : '';
	}

	function open(&$parser, $tag, $attributes) {
		$this->data = '';
		$this->failed = FALSE;
		if(!$this->isnormal) {
			if(isset($attributes['id']) && !is_string($this->document[$attributes['id']])) {
				$this->document  = &$this->document[$attributes['id']];
			} else {
				$this->failed = TRUE;
			}
		} else {
			if(!isset($this->document[$tag]) || !is_string($this->document[$tag])) {
				$this->document  = &$this->document[$tag];
			} else {
				$this->failed = TRUE;
			}
		}
		$this->stack[] = &$this->document;
		$this->last_opened_tag = $tag;
		$this->attrs = $attributes;
	}

	function data(&$parser, $data) {
		if($this->last_opened_tag != NULL) {
			$this->data .= $data;
		}
	}

	function close(&$parser, $tag) {
		if($this->last_opened_tag == $tag) {
			$this->document = $this->data;
			$this->last_opened_tag = NULL;
		}
		array_pop($this->stack);
		if($this->stack) {
			$this->document = &$this->stack[count($this->stack)-1];
		}
	}

}


?>