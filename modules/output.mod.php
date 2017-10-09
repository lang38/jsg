<?php

/**
 *
 * 微博输出模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: output.mod.php 5571 2014-02-25 02:48:09Z wuliyong $
 */

if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}


class ModuleObject extends MasterObject
{
	var $in_ajax = 0;
	var $allow_item = array('qun', 'url', 'company', );


	
	function ModuleObject($config)
	{
		$this->MasterObject($config);

		$this->in_ajax = $this->_input('in_ajax');


		$this->Execute();

	}

	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
			case 'url_js':
				$this->UrlJs();
				break;
			case 'url_iframe':
				$this->UrlIframe();
				break;
			case 'url_iframe_post':
				$this->UrlIframePost();
				break;

			case 'tag':
				$this->Tag();
				break;

			case 'topic':
				$this->Topic();

			default :
				{
					$this->Main();
					break;
				}
		}
		$val = ob_get_clean();

		$this->_output($val);
	}

	function Main() {
		;
			}

	function UrlJs() {
		$hash = '';
		$info = array();
		$hash_verify = 0;
		$id = (int) $this->_input('id', 0, 0);
		$per_page_num = (int) $this->_input('per_page_num', 0, 0);
		$content_default = jhtmlspecialchars(strip_tags(get_safe_code($this->_input('content_default', 0, ''))));
		if($id > 0) {
			$info = DB::fetch_first("select * from ".DB::table('output')." where `id`='$id'");
			if($info) {
				$hash = trim($this->_input('hash', 0, ''));
				if($info['hash'] == $hash) {
					$hash_verify = 1;
					DB::query("update ".DB::table('output')." set `open_times`=`open_times`+1 where `id`='$id'");
				}

				if($per_page_num < 1 || $per_page_num > 200) {
					$per_page_num = $info['per_page_num'];
				}
				if(!$content_default) {
					$content_default = $info['content_default'];
				}
			}
		}
		if($per_page_num < 1) {
			$per_page_num = 10;
		}

		$target_id = jhtmlspecialchars(strip_tags($this->_input('target_id', 1, 'jishigou_div')));


				$width = jget('width');
		if(!$width) {
			$width = $info['width'];
		}
		if(!$width || !$this->_is_wh($width)) {
			$width = '100%';
		}
		$height = jget('height');
		if(!$height) {
			$height = $info['height'];
		}
		if(!$height || !$this->_is_wh($height)) {
			$height = '1000px';
		}


				$item = jget('item', 'txt');
		$item_id = jget('item_id', 'int');
		if($item_id < 1 || !in_array($item, $this->allow_item)) {
			$item = '';
			$item_id = 0;
		}


		rewriteDisable();
		include template('output/output_url_js');
		exit;
	}

	function UrlIframe() {
		$hash = '';
		$info = array();
		$hash_verify = 0;
		$id = (int) $this->_input('id', 0, 0);
		$per_page_num = (int) $this->_input('per_page_num', 0, 0);
		$content_default = jhtmlspecialchars(strip_tags(get_safe_code($this->_input('content_default', 0, ''))));
		if($id > 0) {
			$info = DB::fetch_first("select * from ".DB::table('output')." where `id`='$id'");
			if($info) {
				$hash = trim($this->_input('hash', 0, ''));
				if($info['hash'] == $hash) {
					$hash_verify = 1;
									}
			}
		}
		if(!$hash_verify) {
			if(true === DEBUG && get_param('debug')) {
				;
			} else {
				exit('id or hash is invalid');
			}
		}
		if($info['per_page_num'] > 0) {
			$info['per_page_num'] = (($per_page_num > 0 && $per_page_num <= 200) ? $per_page_num : $info['per_page_num']);
		}
		$info['per_page_num'] = max(0, (int) $info['per_page_num']);
		$info['content_default'] = ($content_default ? $content_default : $info['content_default']);


		$url_info = array();
		$item = jget('item', 'txt');
		if(!in_array($item, $this->allow_item)) {
			$item = 'url';
		}
		$item_id = (int) $this->_input('item_id', 0, 0);
		if('url' == $item) {
			if($item_id  < 1) {
				$url = $this->_input('url', 1);
				$title = $this->_input('title', 1);

				$url_info = jlogic('url')->info($url, $title);
			} else {
				$url_info = jlogic('url')->get_info_by_id($item_id);
				$url = $url_info['url'];
				$title = $url_info['title'];
			}
			if(!$url_info) {
				exit('url is invalid');
			}
			$item_id = $url_info['id'];
		}
		if($item_id < 1) {
			exit('item_id is invalid');
		}


		if($info['lock_host']) {
			$host_verify = 0;
			$lock_hosts = explode("\n", $info['lock_host']);
			foreach($lock_hosts as $v) {
				$v = trim($v);
				if(false !== strpos($url, $v)) {
					$host_verify = 1;
					break;
				}
			}
			if(!$host_verify) {
				exit('host is invalid');
			}
		}


		$page_url = "index.php?mod=output&code=url_iframe&id=$id&hash=$hash&item=$item&item_id=$item_id&per_page_num=$per_page_num&content_default=".urlencode($content_default);
		$total_record = 0;
		$topic_list = $page_arr = $parent_list = array();
		if($info['per_page_num'] > 0) {
			$param = array(
					'perpage' => $info['per_page_num'],
					'page_url' => $page_url,
					'page_extra' => ' target="_self" ',
					'where' => " item='$item' AND item_id='$item_id' ",
			);
			$get_datas = jlogic('topic_list')->get_data($param);
			if (!empty($get_datas)) {
				$total_record = $get_datas['count'];
				$topic_list = $get_datas['list'];
				$page_arr = $get_datas['page'];

				if($topic_list) {
					$parent_list = jlogic('topic')->GetParentTopic($topic_list);
				}
			}
		}


		$url_encode = urlencode($url);
		$this->Title = $title;
		rewriteDisable();


				$tpl_file = 'output/output_url_iframe';
		if($info['tpl_enable'] && $info['tpl_file']) {
			$tpl_file = $info['tpl_file'];
		}
		include template($tpl_file);
	}

	function UrlIframePost() {
		$content = $this->_input('content', $this->in_ajax);
		$item_id = max(0, (int) $this->_input('item_id'));
		$item = jget('item', 'txt');
		if($item_id < 1 || !in_array($item, $this->allow_item)) {
			$item = '';
			$item_id = 0;
		}
		$imageid = $this->_input('imageid', $this->in_ajax, '');
		$totid = max(0, (int) $this->_input('totid'));
		$type = $this->_input('type');
		if('qun' == $item && $item_id > 0 && $totid < 1 && 'reply' == $type) {
			$type = 'qun';
		}

		$datas = array(
			'item' => $item,
			'item_id' => $item_id,
			'imageid' => $imageid,
			'totid' => $totid,
			'type' => $type,
			'content' => $content,
		);
		$rets = jlogic('topic')->Add($datas);

		$error = 0;
		$message = '';
		if(is_array($rets)) {
			$message = "【发布成功】";
			if($rets['tid'] < 1) {
				if($rets['msg']) {
					$message .= $rets['msg'];
				} else {
					$message .= implode(',', $rets);
				}
			}
		} else {
			$error = 1;
			$message = $rets ? $rets : "发布失败";
		}

		if($this->in_ajax) {
			if($error) {
				json_error($message);
			} else {
				json_result($message, $rets);
			}
		}

		$this->_message($message, '', 0);
	}

	function Tag() {
		$rets = array();
		$tags = jget('tags');
		if($tags) {
			$rets = jtable('tag')->get(array(
				'name' => (array) $tags,
				'result_count' => 10,
				'sql_field' => '`name`, `topic_count`',
			));
		} else {
			$rets = jlogic('data_block')->hot_tag_recommend();
		}
		if($rets['list']) {
			$rets['list'] = array_values($rets['list']);
		}
		$this->_output($rets, 1, 'json_encode');
	}

	
	function Topic() {
				$rets = array();
		$rets_get = false;
		$options = array();
		$topic_list = array();
		$uid = MEMBER_ID;
		if($uid < 1) {
			exit();
		}
		$type = jget('type');
		$tpl_file = jget('tpl') ? 'output/'.jget('tpl') : 'output/output_topic';		$limit = (int) (jget('limit') ? jget('limit') : jget('count'));
		if($limit < 1 || $limit > 200) {
			$limit = 10;
		}
		$options['count'] = $limit;

				if('home' == $type) {			$options['uid'] = get_buddyids($uid, $this->Config['topic_myhome_time_limit']);
			$options['uid'][$uid] = $uid;
            $options['type'] = 'first';
		} elseif ('at' == $type) {			$options['tid'] = jtable('topic_mention')->get_ids(array(
				'uid' => $uid,
				'sql_order' => ' `id` DESC ',
				'result_count' => $limit,
			), 'tid');
		} elseif ('reply' == $type) {			$options['where'] = " `type` IN ('reply','both') AND `totid`>'0' ";
		} elseif ('recommend' == $type) {			$rets_get = true;
			$p = $options;
			$p['where'] = " tr.recd > '0' ";
			$rets = jlogic('topic_list')->get_recd_list($p);
		} else {			
		}

				if(!$rets_get) {
			$rets = jlogic('topic_list')->get_data($options);
		}
		if($rets) {
			$topic_list = $rets['list'];
		}
		if($topic_list) {
			$parent_list = jlogic('topic')->GetParentTopic($topic_list);
		}

				$output = jget('output');
		if('json' == $output) {
			echo json_encode($rets);
		} else {
			include(template($tpl_file));
		}
	}

	function _message($message='', $redirect_to='', $stop_time=5) {
		if($message && !$redirect_to) {
			$redirect_to = referer();
		}

		if(!$message && $redirect_to) {
			header("Location: $redirect_to");
		}

		$stop_time = max(0, (int) $stop_time);


		include template('output/output_message');
	}

	function _input($var=null, $is_utf8=0, $ifemptyval=null) {
		$val = get_param($var);

		if($is_utf8) {
						$val = get_safe_code($val);
		}

		if(!$val) {
			$val = $ifemptyval;
		}

		return $val;
	}

	function _output($val, $to_utf8=0, $val_func='', $halt=1) {
		if($to_utf8) {
			$val = array_iconv($this->Config['charset'], 'utf-8', $val);
		}
		if($val_func) {
			$val = $val_func($val);
		}
		echo($val);
		if($halt && true !== DEBUG) {
			exit;
		}
	}

	
	function _is_wh($i) {
		if($i && !is_array($i)) {
			if(is_numeric($i)) {
				return true;
			} else {
				$i = trim((string) $i);
				if('%' == substr($i, -1) && is_numeric(substr($i, 0, -1))) {
					return true;
				} elseif ('px' == strtolower(substr($i, -2)) && is_numeric(substr($i, 0, -2))) {
					return true;
				}
			}
		}
		return false;
	}

}

?>
