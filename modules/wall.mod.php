<?php
/**
 * 文件名：wall.mod.php
 * @version $Id: wall.mod.php 5422 2014-01-15 09:19:15Z chenxianfeng $
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 墙模块
 */

/**
 * ModuleObject
 *
 * @package www.jishigou.com
 * @author 狐狸<foxis@qq.com>
 * @copyright 2010
 * @version $Id: wall.mod.php 5422 2014-01-15 09:19:15Z chenxianfeng $
 * @access public
 */
if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $WallId = 0;

	var $WallLogic;

	var $TopicLogic;


	function ModuleObject($config)
	{
		$this->MasterObject($config);

		$this->WallId = max(0, (int) $this->Get['wall_id']);

		Load::logic('wall');
		$this->WallLogic = new WallLogic();


		$this->TopicLogic = jlogic('topic');

		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch ($this->Code)
        {
        	case 'control':
        		$this->Control();
        		break;

        	case 'screen':
        		$this->Screen();
        		break;

			default:
				$this->Main();
		}
		$body=ob_get_clean();

		$this->ShowBody($body);
	}

	function Main()
	{
		$this->Messager(null, 'index.php?mod=wall&code=control');
	}

	
	function Control()
	{
		
		if(MEMBER_ID < 1)
		{
			$this->Messager("请先<a onclick='ShowLoginDialog(); return false;'>点此登录</a>或者<a onclick='ShowLoginDialog(1); return false;'>点此注册</a>一个帐号",null);
		}

		
		$wall_info = $this->WallLogic->get_wall_info(MEMBER_ID, 1, 1);

		$wall_id = $wall_info['id'];
		if($wall_id < 1)
		{
			$this->Messager('未知错误',null);
		}

		
		$type_list = array(
			0 => array('name' => '搜话题', 'tips' => '加#话题#的微博能更快的和同类信息汇聚，<br />
给你们的上墙定个#话题#，让大家都来写这个#话题#。', ),
			1 => array('name' => '搜关键词', 'tips' => '通过关键词找到你想要的微博，丰富你的上墙素材!', ),
			3 => array('name' => '搜帐号', 'tips' => '想要将某用户的微博作为上墙的素材？<br />
通过搜索Ta的帐号，找到Ta的微博吧!', ),
		);
		
		$type = max(0, (int) $this->Get['type']);
		$type_info = $type_list[$type];

		
		$key = trim(strip_tags($this->Get['key']),' ,#');

		
		$wall_material_list = $this->WallLogic->get_wall_material($wall_id, $type);

		
		$key_list = array();
		if($wall_material_list)
		{
			foreach($wall_material_list as $v)
			{
				
				if(!$key)
				{
					$key = $v['key'];
				}

				$vv = array();
				$vv['name_urlen'] = urlencode($v['key']);
				$vv['name'] = $v['key'];
				$vv['name_disp'] = $v['key'];
				if(!$type)
				{
					$vv['name_disp'] = "#{$v['key']}#";
				}
				$key_list[] = $vv;
			}
		}


		
		$topic_list = array();
		if($key)
		{
			$method = "_get_topic_list_{$type}";
			if(method_exists($this, $method))
			{
				$ret = $this->$method($key);
				if($ret)
				{
					extract($ret);
				}
			}
		}


		
		include(template('wall/wall_control'));
		exit;
	}

	
	function _get_topic_list_0($key)
	{
		$ret = array();

		$tag_info = DB::fetch_first("select * from ".DB::table('tag')." where `name`='".addslashes($key)."'");
		if(!$tag_info)
		{
			return $ret;
		}

		$tag_id = $tag_info['id'];
		$total_record = $tag_info['topic_count'];
		$per_page_num = 20;
		$query_link = "index.php?mod=wall&code=control&type=0&key=".urlencode($key);
		$page_arr = page($total_record,$per_page_num,$query_link,array('return'=>'Array'));

		$query = DB::query("select * from ".DB::table('topic_tag')." where `tag_id`='$tag_id' order by `item_id` desc {$page_arr['limit']}");
		$topic_ids = array();
		while(false != ($row = DB::fetch($query)))
		{
			$topic_ids[$row['item_id']] = $row['item_id'];
		}

		$topic_list = array();
		if($topic_ids)
		{
			$topic_list = $this->TopicLogic->Get($topic_ids);
		}

		if($topic_list)
		{
			foreach ($topic_list as $row)
			{
				unset($topic_ids[$row['tid']]);
			}

			if ($topic_ids)
			{
				$topic_ids_count = count($topic_ids);
				$total_record = $total_record - $topic_ids_count;

				DB::query("delete from ".DB::table('topic_tag')." where `item_id` in('".implode("','",$topic_ids)."')");

				if($total_record>=0 && $tag_info)
				{
					DB::query("update ".DB::table('tag')." set `topic_count`=`topic_count` - $topic_ids_count where `id`='{$tag_info['id']}'");
				}
			}

						$parent_list = $this->TopicLogic->GetParentTopic($topic_list);
			
			$ret['tag_info'] = $tag_info;
			$ret['total_record'] = $total_record;
			$ret['page_arr'] = $page_arr;
			$ret['topic_list'] = $topic_list;
			$ret['parent_list'] = $parent_list;
		}

		return $ret;
	}
	
	function _get_topic_list_2($key)
	{
		;
	}
	
	function _get_topic_list_1($key)
	{
		$ret = array();

		$akey = addslashes($key);

		$sql_where = build_like_query('`content`,`content2`', $akey);
		$cache_id = 'wall/_get_topic_list_1-' . $akey;
		if(false === ($total_record = cache_file('get', ($cid = $cache_id . '-count')))) {
			$total_record = DB::result_first("select count(*) as `count` from ".DB::table('topic')." where $sql_where ");
			cache_file('set', $cid, $total_record, 300);
		}
		if($total_record > 0) {
			$per_page_num = 20;
			$query_link = "index.php?mod=wall&code=control&type=1&key=".urlencode($key);
			$page_arr = page($total_record,$per_page_num,$query_link,array('return'=>'Array'));

			if(false === ($topic_list = cache_file('get', ($cid = $cache_id . '-list-' . $page_arr['limit'])))) {
				$topic_list = $this->TopicLogic->Get(" where $sql_where order by `dateline` desc {$page_arr[limit]}");
				cache_file('set', $cid, $topic_list, 300);
			}

			if($topic_list)
			{
								$parent_list = $this->TopicLogic->GetParentTopic($topic_list);
				
				$ret['total_record'] = $total_record;
				$ret['page_arr'] = $page_arr;
				$ret['topic_list'] = $topic_list;
				$ret['parent_list'] = $parent_list;
			}
		}

		return $ret;
	}
	
	function _get_topic_list_3($key)
	{
		$ret = array();

		$akey = addslashes($key);

		$total_record = 0;
		$member_info = DB::fetch_first("select * from ".DB::table('members')." where `username`='$akey' or `nickname`='$akey' limit 1");
		if(!$member_info) {
			$cache_id = 'wall/_get_topic_list_3-' . $akey;
			if(false === ($member_list = cache_file('get', $cache_id))) {
				$member_list = DB::fetch_all("SELECT `uid`, `topic_count` FROM ".DB::table('members')."
					WHERE `username` LIKE '%{$akey}%' OR `nickname` LIKE '%{$akey}%'
					ORDER BY `lastactivity` DESC
					LIMIT 10");

				cache_file('set', $cache_id, $member_list, 3600);
			}
			if(!$member_list) {
				return $ret;
			} else {
				$uids = array();
				foreach($member_list as $row) {
					$uids[$row['uid']] = $row['uid'];
					$total_record += $row['topic_count'];
				}
			}
		} else {
			$uids = $member_info['uid'];
			$total_record = $member_info['topic_count'];
		}

		if($uids && $total_record > 0) {
			$per_page_num = 20;
			$query_link = "index.php?mod=wall&code=control&type=3&key=".urlencode($key);
			$rets = jtable('member_topic')->get_tids($uids, array(
				'type'=>array('both', 'forward', 'reply'),
				'sql_order' => ' `dateline` DESC ',
				'result_count' => $total_record,
				'per_page_num' => $per_page_num,
				'page_url' => $query_link,
			), 1);
			if($rets) {
				if($member_info) {
					$ret['member_info'] = $member_info;
				}
				$ret['total_record'] = $total_record;
				$ret['page_arr'] = $rets['page'];
				$ret['topic_list'] = $this->TopicLogic->Get($rets['ids']);
				$ret['parent_list'] = $this->TopicLogic->GetParentTopic($ret['topic_list']);
			}
		}

		return $ret;
	}

	
	function Screen()
	{
		$wall_id = max(0, (int) ($this->Post['wall_id'] ? $this->Post['wall_id'] : $this->Get['wall_id']));
		$wall_info = $this->WallLogic->get_wall_info($wall_id);
		if(!$wall_info)
		{
			$this->Messager('微博墙已经不存在了',null);
		}

		include(template('wall/wall_screen'));
		exit;
	}


}


?>
