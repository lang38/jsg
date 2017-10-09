<?php

/**
 * 文件名： channel.mod.php
 * 作  者：　狐狸 <foxis@qq.com>
 * @version $Id: channel.mod.php 5141 2013-12-02 07:34:07Z wuliyong $
 * 功能描述： api for JishiGou
 * 版权所有： Powered by JishiGou API 1.0.0 (a) 2005 - 2099 Cenwor Inc.
 * 公司网站： http://cenwor.com
 * 产品网站： http://jishigou.net
 */


if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
    
	function ModuleObject($config)
	{
		$this->MasterObject($config);
		$this->ChannelLogic = jlogic('channel');
        $this->Execute();
	}

	function Execute()
	{
		switch($this->Code)
		{
			case 'channel':
                {
                    $this->channel();
                    break;
                }
			case 'topic':
                {
                    $this->topic();
                    break;
                }
			case 'followtopic':
                {
                    $this->followtopic();
                    break;
                }
			case 'followadd':
                {
                    $this->followadd();
                    break;
                }
			case 'followdel':
                {
                    $this->followdel();
                    break;
                }
			case 'isfollow':
				{
					$this->isfollow();
					break;
				}
			default :
    			{
    				$this->Main();
    				break;
    			}
		}
	}

	function Main()
	{
		api_output('channel api is ok');
	}

	
	function channel()
	{
		$uid = max(0, (int) $this->Inputs['uid'] ? $this->Inputs['uid'] : $this->user['uid']);
		$type = in_array($this->Inputs['type'],array('first','second','channels','mychannel','trees')) ? $this->Inputs['type'] : '';		$return = jconf::get('channel');
		unset($return['channel_types']);$trees = array();
		$return['mychannel'] = $this->ChannelLogic->mychannel($uid);
		$my_buddy_channel_ids = is_array($return['mychannel']) ? array_keys($return['mychannel']) : array();
		foreach($return as $key => $val){
			if(in_array($key,array('first','second')) && $val && is_array($val)){
				foreach($val as $v){
					unset($return[$key][$v['ch_id']]['in_home']);
					$return[$key][$v['ch_id']]['hasbuddy'] = ($my_buddy_channel_ids && in_array($v['ch_id'],$my_buddy_channel_ids)) ? 1 : 0;
				}
			}
		}
		if($return['first'] && is_array($return['first'])){
			foreach($return['first'] as $key => $val){
				$trees[$key] = array('id'=>$val['ch_id'],'name'=>$val['ch_name']);
				if($return['second'] && is_array($return['second'])){
					foreach($return['second'] as $k => $v){
						if($v['parent_id'] == $val['ch_id']){
							$trees[$key]['child_channel'][] = array('id'=>$v['ch_id'],'name'=>$v['ch_name']);
						}
					}
				}
				if(!$trees[$key]['child_channel']){
					$trees[$key]['child_channel'] = array();
				}
			}
			$return['trees'] = $trees;
		}
		foreach($return as $key => $val){
			if(!in_array($key,array('channels'))){
				$return[$key] = is_array($val) ? array_merge($val) : array();
			}
		}				
		if($type){
			$return = $return[$type];
		}
		api_output($return);
	}

	
	function isfollow()
	{
		$ch_id = max(0, (int) $this->Inputs['id']);		$uid = max(0, (int) $this->Inputs['uid'] ? $this->Inputs['uid'] : $this->user['uid']);
		if($ch_id > 0){
			$mychannels = $this->ChannelLogic->mychannel($uid);
			$ch_ids = array_keys($mychannels);
			$return = in_array($ch_id,$ch_ids) ? 1 : 0;
			api_output($return);
		}else{
			api_error('channel id is empty', 10);
		}
	}

	
	function topic()
	{
		$id = jget('id','int','G');		$channels = jconf::get('channel');
		if(strlen($this->Inputs['id']) == 0){
			if($channels['channels']){
				$ids = array();
			}else{
				$ids = array(0);
			}
		}else{
			if($channels['channels'] && is_array($channels['channels']) && $channels['channels'][$id]){
				$ids = $channels['channels'][$id];
				$my_cannot_view_chids = jlogic('channel')->get_my_cannot_view_chids();
				if($ids && $my_cannot_view_chids){
					foreach($ids as $key => $val){
						if(in_array($val,$my_cannot_view_chids)){
							unset($ids[$key]);
						}
					}
				}
				if(empty($ids)){
					api_error('channel id is fobidden', 19);
				}
			}else{
				$ids = array(0);
			}
		}
		$featurelist = $channels['channel_types'][$id]['feature'];
		$navmenu = array();
		if($featurelist){
			if(!$featurelist[0]){
				$featurelist = array(0=>'待处理')+$featurelist;
			}
			foreach($featurelist as $key => $val){				$navmenu[$key]['id'] = $key;
				$navmenu[$key]['name'] = $val;
			}
			$order = '`dateline` desc';
		}else{
			$orderby = in_array($this->Inputs['orderby'],array('new','dig', 'hot_dig','rec')) ? $this->Inputs['orderby'] : 'new';			if($orderby == 'dig'){
				$order = ' `lastdigtime` desc ';
			} elseif('hot_dig' == $orderby) {
				$order = ' `digcounts` DESC, `lastdigtime` DESC ';
			} else{
				$order = ' `dateline` desc' ;
			}
		}
		if($orderby == 'rec'){
			$sql_wheres = array("type"=>"`type`='first'");
			$tids = array(0);
			if($ids){
				$query = DB::query("SELECT tid FROM ".DB::table('topic_recommend')." WHERE item='channel' AND item_id IN(".jimplode($ids).")");
			}elseif($my_cannot_view_chids){
				$query = DB::query("SELECT tid FROM ".DB::table('topic_recommend')." WHERE item='channel' AND item_id NOT IN(".jimplode($my_cannot_view_chids).")");
			}else{
				$query = DB::query("SELECT tid FROM ".DB::table('topic_recommend')." WHERE item='channel'");
			}
			while (false != ($row = DB::fetch($query))) {
				$tids[] = $row['tid'];
			}
			$sql_wheres['tid'] = "`tid` IN(".jimplode($tids).")";
		}else{
			$sql_wheres = array("type"=>"`type` IN('first','forward','both','channel')","item"=>"`item`='channel'");
			if($ids) {
				$sql_wheres['item_id'] = "`item_id` IN(".jimplode($ids).")";
			}elseif($my_cannot_view_chids){
				$sql_wheres['item_id'] = "`item_id` NOT IN(".jimplode($my_cannot_view_chids).")";
			}
                        if('hot_dig' == $orderby) {
				$sql_wheres[] = " `digcounts`>'0' AND `lastdigtime`>'" . (TIMESTAMP - 86400 * 7) . "' ";
			} elseif ('dig' == $orderby) {				$sql_wheres[] = " `digcounts`>'0' ";
			}
			if($featurelist && isset($this->Inputs['orderby'])){
				$featureid = (int)$this->Inputs['orderby'];
				$sql_wheres['featureid'] = " `featureid` = '{$featureid}' ";
			}
		}
		$this->_topic_list('new',$sql_wheres,$order,array(),array('menu'=>array_merge($navmenu)));
	}

	
	function followtopic()
	{
		$uid = max(0, (int) ($this->Inputs['uid'] ? $this->Inputs['uid'] : $this->user['uid']));
		$orderby = in_array($this->Inputs['orderby'],array('new','dig','rec')) ? $this->Inputs['orderby'] : 'new';		if($orderby == 'dig'){
			$order = '`lastdigtime` desc';
		}else{
			$order = '`dateline` desc';
		}
		$my_cannot_view_chids = jlogic('channel')->get_my_cannot_view_chids();
		$my_buddy_channel = $this->ChannelLogic->mychannel($uid);
		$channels = jconf::get('channel');
		$channel_channels = $channels['channels'];
		$channel_ids = array();
		if(empty($my_buddy_channel)){
			$channel_ids = array(0);
		}else{
			$my_chs = array_keys($my_buddy_channel);
			foreach($my_chs as $val){
				$channel_ids = array_merge($channel_ids,$channel_channels[$val]);
			}
			$channel_ids = array_unique($channel_ids);
			if($my_cannot_view_chids){
				foreach($channel_ids as $key => $val){
					if(in_array($val,$my_cannot_view_chids)){
						unset($channel_ids[$key]);
					}
				}
			}
						jlogic('member')->clean_new_remind('channel_new', $uid);
		}
		if($orderby == 'rec'){
			$sql_wheres = array("type"=>"`type`='first'");
			$tids = array(0);
			if($channel_ids){
				$query = DB::query("SELECT tid FROM ".DB::table('topic_recommend')." WHERE item='channel' AND item_id IN(".jimplode($channel_ids).")");
				while (false != ($row = DB::fetch($query))) {
					$tids[] = $row['tid'];
				}
				$sql_wheres['tid'] = "`tid` IN(".jimplode($tids).")";
			}else{
				$sql_wheres['tid'] = "`tid` = '0'";
			}
			
		}else{
			$sql_wheres = array("type"=>"`type`='first'","item"=>"`item`='channel'");
			if($channel_ids){
				$sql_wheres['item_id'] = "`item_id` IN(".jimplode($channel_ids).")";
			}else{
				$sql_wheres['item_id'] = "`item_id` = '0'";
			}
		}
		$this->_topic_list('followtopic',$sql_wheres,$order);
	}

	
	function followadd()
	{
		$id = max(0, (int) $this->Inputs['id']);		$isexists = jlogic('channel')->is_exists($id);
		$hasbuddy = jlogic('channel')->channel_isbuddy($id);
		$can_buddy = jlogic('channel')->can_view_topic($id);
		if($isexists && $can_buddy && !$hasbuddy){
			jlogic('channel')->buddy_channel($id,1);
		}
				api_output('followadd is ok');
	}

	
	function followdel()
	{
		$id = max(0, (int) $this->Inputs['id']);		jlogic('channel')->buddy_channel($id,0);
		api_output('followdel is ok');
	}

}
?>