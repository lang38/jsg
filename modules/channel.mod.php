<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename channel.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2014-04-25 14:36:59 295315547 1734934010 6033 $
 */







if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	function ModuleObject($config)
	{
		$this->MasterObject($config);
		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch ($this->Code){
			default:
				$this->main();
				break;
		}
		$body=ob_get_clean();
		$this->ShowBody($body);
	}

	
	function main()
	{
		$member = jsg_member_info(MEMBER_ID);
		if(!($this->Channel_enable && $this->Config['channel_enable'])){$this->Messager("网站没有开启频道功能",null);}
		$channel_id = jget('id','int','G');
				if(!$channel_id){
			$formchannel = true;
			$channeltoptopic =  jlogic('channel')->getChannelTopTopic();
			$channelrectopic =  jlogic('channel')->getChannelRecTopic();
			$channellist =  jlogic('channel')->getChannelAll();
			$userfanstop =  jlogic('channel')->getUserFansTop();
			$this->Title = '频道首页';
			$this->MetaKeywords = '频道首页';
			$this->MetaDescription = '微博频道,频道首页';
			include template('channel/channel_index');
		}else{
		$this->Channel = ($channel_id ==0) ? '' : $channel_id;
				if(MEMBER_ID > 0) {
			jlogic('member')->clean_new_remind('channel_new', MEMBER_ID);
		}
		$cachefile = jconf::get('channel');
		$channel_channels = is_array($cachefile['channels']) ? $cachefile['channels'] : array();
		$channel_types = is_array($cachefile['channel_types']) ? $cachefile['channel_types'] : array();
		$channel_template = $channel_types[$channel_id]['template'] ? $channel_types[$channel_id]['template'] : 'channel';
		$my_channels = jlogic('channel')->mychannel(MEMBER_ID);		if(in_array($channel_id,array_keys($channel_channels))){
			$channel_buddy = follow_channel($channel_id,in_array($channel_id,array_keys($my_channels)));
		}
		$thischannels = jlogic('channel')->getThisChannel($channel_id);
		$my_cannot_view_chids = jlogic('channel')->get_my_cannot_view_chids();
		$item_ids = ($channel_channels[$channel_id] && is_array($channel_channels[$channel_id])) ? $channel_channels[$channel_id] : array(0);
		if($my_cannot_view_chids){
			foreach($item_ids as $key => $val){
				if(in_array($val,$my_cannot_view_chids)){
					unset($item_ids[$key]);
				}
			}
		}
		if(empty($item_ids)){
			$this->Messager("您没有权限查看该频道下的微博",null);
		}
		$this->Title = $thischannels['name'].' - 频道 ';
		$this->MetaKeywords = $thischannels['name'];
		$this->MetaDescription = $thischannels['description'] ? $thischannels['description'] : $thischannels['name'];
				$channel_description = $thischannels['description'];
				$channel_managers = jlogic('channel')->get_channel_manager($channel_id);
				$channel_nav = $thischannels['navhtml'];
		$per_page_num = 20;
		$where = $order = '';
		$topic_type = array('first','channel');
		$featurelist = $channel_types[$channel_id]['feature'];
		$filter_view = $filter_list = $channel_buddy = '';
		$filter_list = in_array($this->Get['list'],array('pic','txt')) ? $this->Get['list'] : '';
		$filter_url = $filter_list ? '&list='.$filter_list : '';
		if($featurelist){
			$filter_view = (int)$this->Get['view'];
			if(!$featurelist[0]){
								$featurelist = array(0=>'等待处理')+$featurelist;
			}
			$featureid = (int)$this->Get['view'];
			if(isset($_GET['view'])){
				$where = " featureid = '{$featureid}' ";
				$cncss[$featureid] = 'boxNavselect';
			}else{
				$cncss['all'] = 'boxNavselect';
			}
		}else{
			$time = TIMESTAMP - 7*24*3600;
			$filter_list = $filter_list ? $filter_list : ($thischannels['list'] ? $thischannels['list'] : 'txt');
			$filter_view = in_array($this->Get['view'],array('post','dig','mark','ldig','top')) ? $this->Get['view'] : '';
			$filter_view = $filter_view ? $filter_view : ($thischannels['view'] ? $thischannels['view'] : 'post');
			if($filter_view == 'mark'){
				$order = 'lastupdate DESC';
			}elseif($filter_view == 'dig'){
				$where = 'digcounts > 0';
				$order = 'lastdigtime DESC';
			}elseif($filter_view == 'ldig'){
				$where = 'digcounts > 0 AND lastdigtime >= '.$time;
				$order = 'digcounts DESC,lastdigtime DESC';
			}
		}
		$filter_url .= $filter_view ? '&view='.$filter_view : '';
				if($filter_view == 'top' && !$channel_types[$channel_id]['channel_type']){
			$where = $item_ids ? "tr.item = 'channel' AND tr.item_id IN(".implode(",",$item_ids).")" : "tr.item = 'channel'";
			$options = array('where'=>$where,'perpage'=>$per_page_num,'type'=>$topic_type);
			$info = jlogic('topic_list')->get_recd_list($options);
		}else{
			$options = array('order'=>$order,'item'=>'channel','item_id'=>$item_ids,'where'=>$where,'perpage'=>$per_page_num,'type'=>$topic_type);
			$info = jlogic('topic_list')->get_data($options);
		}
		$topics = array();
		$total_record = 0;
		if (!empty($info)) {
			$topics = $info['list'];
			$total_record = $info['count'];
			if($info['page']){
				$page_arr = $info['page'];
			}
		}
		$topics_count = 0;
		if ($topics) {
			$topics_count = count($topics);
			if (!$topic_parent_disable) {
								$parent_list = jlogic('topic')->GetParentTopic($topics, ('mycomment' == $this->Code));
							}
			$relate_list = jlogic('topic')->GetRelateTopic($topics);
		}

						if($filter_list=='pic'){
			if($page_arr['html']){
				$ajax_num = ceil($total_record/$per_page_num);
			}
			foreach ($topics as $key => $row) {				if($row['parent_id'] || $row['top_parent_id']) {
					unset($topics[$key]);
				}
			}
			$topic_pic_keys = array('ji','shi','gou','img');
			$params['id'] = base64_encode(serialize($item_ids));
			include template('channel/channel_pic');
		}else{
			$child_channel = jlogic('channel')->get_child_channel($channel_id);
			include template('channel/'.$channel_template);
		}
		}
	}
}
?>
