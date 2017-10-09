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
 * @Date 2013-11-11 1051746431 15280 $
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
		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
			case 'docategory':
				$this->docategory();
				break;
			case 'delcat':
				$this->delcat();
				break;
			case 'edit':
				$this->edit();
				break;
			case 'doedit':
				$this->doedit();
				break;
			case 'move':
				$this->move();
				break;
			case 'domove':
				$this->domove();
				break;
			case 'config':
				$this->set();
				break;
			case 'doset':
				$this->doset();
				break;
			case 'updata':
				$this->updata();
				break;
			case 'channeltype':
				$this->channeltype();
				break;
			case 'index':
			default:
				$this->category();
				break;
		}
		$body = ob_get_clean();

		$this->ShowBody($body);

	}

	
	function updata()
	{
		jlogic('channel')->update_data();
		$this->Messager('操作成功了','admin.php?mod=channel');
	}

	
	function set()
	{
		$action = 'admin.php?mod=channel&code=doset';
		$c_l = $this->Config['channel_list'] == 'pic' ? 'pic' : 'txt';
		$channel_enable_html = $this->jishigou_form->YesNoRadio('channel[enable]',(int)($this->Config['channel_enable']));
		$channel_must_html = $this->jishigou_form->YesNoRadio('channel[must]',(int)($this->Config['channel_must']));
		$channels = jlogic('channel')->get_select_channel();
		$this->Config['channel_rtitle'] = $this->Config['channel_rtitle'] ? $this->Config['channel_rtitle'] : '新闻公告';
		include(template('admin/channel_info'));
	}

	function doset()
	{
		$channel = $this->Post['channel'];
		$config = array();
		$config['channel_enable'] = $channel['enable'] ? 1 : 0;
		$config['channel_must'] = $channel['must'] ? 1 : 0;
		$config['channel_recommend'] = $channel['recommend'] ? $channel['recommend'] : 0;
		$config['channel_rtitle'] = $channel['rtitle'] ? $channel['rtitle'] : '新闻公告';
		jconf::update($config);
		$this->Messager("修改成功");
	}

	

	function move()
	{
		$ch_id = jget('id','int','G');
		$channel_info = jlogic('channel')->id2category($ch_id);
		if(!$channel_info) {
			$this->Messager("您要移动的频道不存在!");
		}
		$have_child = jlogic('channel')->have_child($ch_id);
		if($have_child) {
			$this->Messager("您要移动的频道存在子频道，请先移动子频道!");
		}
		$action = 'admin.php?mod=channel&code=domove';
		$channels = jlogic('channel')->get_select_channel();
		include template('admin/channel_move');
	}

	function domove()
	{
		$ch_id = jget('id','int','P');
		$item_id = jget('item_id','int','P');
		$cutc = jget('cutc','int','P');
		$return = jlogic('channel')->move_channel($ch_id,$item_id,$cutc);
		if($return){
			$this->Messager('操作成功了','admin.php?mod=channel');
		}else{
			$this->Messager('操作失败，没有选择目标频道','admin.php?mod=channel');
		}
	}

	
	function edit()
	{
		$ch_id = jget('id','int','G');
		$channel_info = jlogic('channel')->id2category($ch_id);
		if(!$channel_info) {
			$this->Messager("您要编辑的频道信息不存在!");
		}
		$action = 'admin.php?mod=channel&code=doedit';
		$role_list = jlogic('channel')->get_user_role();
		$feed_html = $this->jishigou_form->YesNoRadio('feed',$channel_info['feed']);
		$recommend_html = $this->jishigou_form->YesNoRadio('recommend',$channel_info['recommend']);
		$channel_typehtml = jlogic('channel')->get_cattypeselect($channel_info['channel_typeid']);
		$radiohtml = $this->jishigou_form->YesNoRadio('verify',$channel_info['verify']);
		$purviewhtml = $this->jishigou_form->CheckBox('purview[]', $role_list, explode(',',$channel_info['purview']));
		$selecthtml = jlogic('channel')->get_catselect($channel_info['parent_id']);
		$filterhtml = $this->jishigou_form->CheckBox('filter[]', $role_list, explode(',',$channel_info['filter']));
		$list_html = $this->jishigou_form->Radio('display_list[]',array(array("name"=>"列表","value"=>"txt"),array("name"=>"瀑布流","value"=>"pic")),$channel_info['display_list']);
		$view_html = $this->jishigou_form->Radio('display_view[]',array(array("name"=>"最新发布","value"=>"post"),array("name"=>"最新被赞","value"=>"dig"),array("name"=>"最新回应","value"=>"mark"),array("name"=>"近期热赞","value"=>"ldig"),array("name"=>"推荐置顶","value"=>"top")),$channel_info['display_view']);
		$topictypehtml = $this->jishigou_form->Radio('topictype[]',array(array("name"=>"不做设置","value"=>"0"),array("name"=>"禁止转发","value"=>"8"),array("name"=>"禁止评论","value"=>"9"),array("name"=>"同时禁止评论与转发","value"=>"2")),$channel_info['topictype']);;
		$purpostviewhtml = $this->jishigou_form->CheckBox('purpostview[]', $role_list, explode(',',$channel_info['purpostview']));;
		include template('admin/channel_info');
	}

	function doedit()
	{
		$ch_id = jget('id','int','P');
		$channel_info = jlogic('channel')->id2category($ch_id);
		if(!$channel_info) {
			$this->Messager("您要编辑的频道信息不存在!");
		}
		$template = jget('template');
		if($template &&  !preg_match("/^[a-z]+[a-z0-9_]*[a-z0-9]+$/i", $template)){
			$this->Messager("模板文件名称不合法");
		}
		if($template && !jclass('jishigou/template')->exists('channel/'.$template)){
			$this->Messager("模板文件 channel/".$template.".html 不存在");
		}
		$managename = jget('managename');
		$managename = explode('|',$managename);
		$manageid = array();
        foreach ($managename as $key=>$one) {
            $uid = jtable('members')->val(array('nickname' => trim($one)), 'uid');
            if($uid){
                $manageid[] = $uid;
            }else{
                unset($managename[$key]);
            }
        }
        $manageid = $manageid ? implode(',', $manageid) : '';
        $managename = implode('|', $managename);
		$parent_id = $this->Post['parent_id'];
		$channel_typeid = $this->Post['channel_typeid'];
		$purpostview = is_array($this->Post['purpostview']) ? implode(',',$this->Post['purpostview']) : '';
		$topictype = in_array($this->Post['topictype'][0],array('0','2','8','9')) ? $this->Post['topictype'][0] : '0';
		$set_ary = array(
			'ch_name' => jget('ch_name'),
			'parent_id' => $parent_id,
			'display_order' => jget('display_order','int','P'),
			'description' => cutstr(jget('description'),1000),
			'purview' => is_array($this->Post['purview']) ? implode(',',$this->Post['purview']) : '',
			'purpostview' => $purpostview,
			'template' => $template,
			'feed'    => $this->Post['feed'],
			'recommend' => $this->Post['recommend'],
			'manageid' => $manageid,
			'managename' => $managename,
			'verify' => jget('verify','int'),
			'filter' => (jget('verify','int')==0) ? '' : (is_array($this->Post['filter']) ? implode(',',$this->Post['filter']) : ''),
			'display_list' => ($this->Post['display_list'][0]== 'pic') ? 'pic' : 'txt',
			'display_view' => in_array($this->Post['display_view'][0],array('post','dig','mark','ldig','top')) ? $this->Post['display_view'][0] : 'post',
			'topictype' => $topictype,
			'channel_typeid' => $channel_typeid,
		);
		$check_data = array(
			'tid' => $channel_typeid,
			'otid'=> $this->Post['oldchannel_typeid'],
			'pid' => $parent_id,
			'opid'=> $this->Post['oldparent_id'],
			'ppv' => $purpostview,
			'oppv'=> $this->Post['oldpurpostview'],
			'ttp' => $topictype,
			'ottp'=> $this->Post['oldtopictype'],
		);
		jlogic('channel')->update_catedata($ch_id,$set_ary,$check_data);
		if(!empty($_FILES['image']['name'])){
						jlogic('channel')->upload_pic($ch_id);
		}
				jlogic('channel')->update_category_cache();
		$this->Messager('操作成功了','admin.php?mod=channel');
	}

	
	function category()
	{
		$channeltype = jlogic('channel')->get_channel_type();
		$defaulttype = '<select name="new_tcat_type[]">';
		foreach($channeltype as $k => $v){
			$defaulttype .= '<option value="'.$k.'">'.$v.'</option>';
		}
		$defaulttype .= '</select>';
		$tree = jlogic('channel')->get_category_tree();
		include template('admin/channel');
	}

	function docategory()
	{
				$cat_ary = &$this->Post['cat'];
		if (!empty($cat_ary)) {
			$cat_order_ary = &$this->Post['cat_order'];
			$cat_recommend_ary = &$this->Post['cat_recommend'];
			$cat_feed_ary = &$this->Post['cat_feed'];
			foreach ($cat_ary as $key => $cat) {
				$ch_name = getstr($cat, 30, 1, 1);
								$display_order = intval($cat_order_ary[$key]);
				$recommend = intval($cat_recommend_ary[$key]);
				$feed = intval($cat_feed_ary[$key]);
				jlogic('channel')->update_category($key, $ch_name, $display_order,$recommend, $feed);
			}
		}
				$tcat_ary = &$this->Post['new_tcat'];
		if (!empty($tcat_ary)) {
			$tcat_order_ary = &$this->Post['new_tcat_order'];
			$tcat_type_ary = &$this->Post['new_tcat_type'];
			$this->_batch_add_category($tcat_ary, $tcat_order_ary,0,$tcat_type_ary);
		}
				$scat_ary = &$this->Post['new_scat'];
		if (!empty($scat_ary)) {
			$scat_order = &$this->Post['new_scat_order'];
			foreach ($scat_ary as $p => $cats) {
				$this->_batch_add_category($cats, $scat_order[$p], $p);
			}
		}
				jlogic('channel')->update_category_cache();
		$this->Messager('操作成功了');
	}

	
	function _batch_add_category($cat_ary, $order_ary, $parent_id = 0,$type_ary=array())
	{
		foreach ($cat_ary as $key => $cat) {
						$ch_name = getstr($cat, 30, 1, 1);
			if (empty($ch_name) || jlogic('channel')->category_exists($ch_name, $parent_id)) {
				continue;
			}
			if($type_ary[$key]){
				$channel_typeid = intval($type_ary[$key]);
			}else{
				$pchannelinfo = jlogic('channel')->id2category($parent_id);
				$channel_typeid = $pchannelinfo['channel_typeid'];
			}
			$display_order = intval($order_ary[$key]);
			jlogic('channel')->add_category($ch_name, $display_order, $parent_id,$channel_typeid);
		}
	}

	
	function delcat()
	{
		$ch_id = empty($this->Get['ch_id']) ? 0 : intval($this->Get['ch_id']);
		if (empty($ch_id)) {
			$this->Messager('没有指定频道ID');
		}
		$ret = jlogic('channel')->delete_category($ch_id);
				jlogic('channel')->update_category_cache();
		if ($ret == 1) {
			$this->Messager('删除频道成功');
		} else if ($ret == -1) {
			$this->Messager('当前频道不存在');
		} else if ($ret == -2) {
			$this->Messager('当前频道下面存在微博，不能被删除，请先移出该频道下的微博，然后再进行该操作！','',10);
		} else if ($ret == -3) {
			$this->Messager('当前频道存在子频道，不能被删除，请先删除子频道，然后再进行该操作！','',10);
		}
	}

	
	function channeltype()
	{
		$do=jget('do');
		if('delete'==$do){
			$ids = (array) ($this->Post['ids'] ? $this->Post['ids'] : $this->Get['ids']);
			if(!$ids) {
				$this->Messager("请指定要删除的对象");
			}
			$return = jlogic('channel')->delete_channel_type($ids);
			if($return){
				if($return['noids']){
					$this->Messager("类型[".jimplode($return['ids'])."]删除成功，类型[".jimplode($return['noids'])."]删除失败");
				}else{
					$this->Messager("频道类型(模型)删除成功");
				}
			}else{
				$this->Messager("删除失败，类型已被频道使用，不可删除");
			}
		}elseif('add'==$do){
			$channel_typename = strip_tags($this->Post['channel_typename']);
			$channel_type = strip_tags($this->Post['channel_type']);
			if(empty($channel_typename)){
				$this->Messager("请输入类型名称",-1);
			}
			if(empty($channel_type)){
				$this->Messager("请输入类型标识符",-1);
			}
			if(!preg_match("/^[a-z]+$/i", $channel_type)){
				$this->Messager("类型标识符[".$channel_type."]不合法",-1);
			}
			$return = jlogic('channel')->add_channel_type($channel_typename, $channel_type);
			if($return > 0){
				$this->Messager("添加成功",'admin.php?mod=channel&code=channeltype');
			}else{
				$this->Messager("频道类型名称已经存在或标识符不唯一",-1);
			}
		}elseif('modify'==$do){
			$ids = (int) $this->Get['ids'];
			$channel_typelist = jlogic('channel')->get_channel_typebyid($ids);
			$feature_list = jlogic('channel')->get_feature_formdata();
			$featurehtml = $this->jishigou_form->CheckBox('featureid[]', $feature_list, explode(',',$channel_typelist['featureid']));
		}elseif('domodify'==$do){
			$channel_typeid = (int) $this->Post['channel_typeid'];
			$channel_typename = strip_tags($this->Post['channel_typename']);
			$oldchannel_typename = $this->Post['oldchannel_typename'];
			$template = $this->Post['template'];
			$oldtemplate = $this->Post['oldtemplate'];
			$child_template = $this->Post['child_template'];
			$oldchild_template = $this->Post['oldchild_template'];
			$topic_template = $this->Post['topic_template'];
			$oldtopic_template = $this->Post['oldtopic_template'];
			$featureid = is_array($this->Post['featureid']) ? implode(',',$this->Post['featureid']) : '';
			$oldfeatureid = $this->Post['oldfeatureid'];
			$default_feature = $this->Post['default_feature'];
			$olddefault_feature = $this->Post['olddefault_feature'];
			if($channel_typename != $oldchannel_typename || $template != $oldtemplate || $child_template != $oldchild_template || $topic_template != $oldtopic_template || $featureid != $oldfeatureid || $default_feature != $olddefault_feature){
				if($channel_typename != $oldchannel_typename){
					$return = jlogic('channel')->check_channel_type_byname($channel_typename);
					if($return){
						$this->Messager("{$channel_typename} 类型已经存在");
					}
				}
				if($template != $oldtemplate || $child_template != $oldchild_template || $topic_template != $oldtopic_template){
					$this->_checktemplate(array($template,$child_template,$topic_template));
				}
				jlogic('channel')->modiy_channel_type($channel_typeid, $channel_typename, $template, $child_template, $topic_template, $featureid, $default_feature);
				$this->Messager("编辑成功",'admin.php?mod=channel&code=channeltype');
			}else{
				$this->Messager("没做任何修改",'admin.php?mod=channel&code=channeltype');
			}
		}else{
			$per_page_num = min(500, max(20, (int) (isset($_GET['pn']) ? $_GET['pn'] : $_GET['per_page_num'])));
			$info = jlogic('channel')->get_channel_typelist($per_page_num);
			$total_record = $info['total'];
			$page_arr = $info['page'];
			$channel_type_list = $info['list'];
		}
		include template('admin/channel_type');
	}
	function _checktemplate($template=array()){
		if($template && is_array($template)){
			foreach($template as $key => $val){
				if($val &&  !preg_match("/^[a-z]+[a-z0-9_]*[a-z0-9]+$/i", $val)){
					$this->Messager("模板文件名称[".$val."]不合法");
				}
				if($val && !jclass('jishigou/template')->exists('channel/'.$val)){
					$this->Messager("模板文件 channel/".$val.".html 不存在");
				}
			}
		}
	}
}
?>
