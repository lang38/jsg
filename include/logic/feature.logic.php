<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename feature.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2014 402887679 6402 $
 */




if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class FeatureLogic
{
	function FeatureLogic() {
	}

	function is_exists($id = 0)
	{
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('feature')." WHERE `featureid` = '{$id}'");
		return $count > 0 ? true : false;
	}

	function id2feature($id)
	{
		$feature = array();
		$feature = DB::fetch_first("SELECT * FROM ".DB::table('feature')." WHERE `featureid` = '{$id}'");
		return $feature;
	}

	function id2subject($id)
	{
		static $featurename;
		if($featurename[$id]){
			$subject = $featurename[$id];
		}else{
			$subject = DB::result_first("SELECT `featurename` FROM ".DB::table('feature')." WHERE `featureid` = '{$id}' ");
			$featurename[$id] = $subject;
		}
		return $subject;
	}

	function update_feature_cache(){
		$features = array();
		$query = DB::query("SELECT * FROM ".DB::table('feature'));
		while ($value = DB::fetch($query)) {
			$features[$value['featureid']] = $value['featurename'];
		}
		jconf::set('feature', $features);
	}

	function get_feature(){
		$features = array();
		$features = jconf::get('feature');
		if (empty($features)) {
			$this->update_feature_cache();
			$features = jconf::get('feature');
		}
		return $features;
	}

		function get_feature_select($ch_id=0,$tfid=0,$divid='',$notajax=1){
		$html = '';
		$features = array();
		if($ch_id > 0){
			$channel_typeinfo = jlogic('channel')->get_channel_typeinfo_byid($ch_id);
			$features = $channel_typeinfo['feature'] ? $channel_typeinfo['feature'] : array();
		}
		if(!('admin'==MEMBER_ROLE_TYPE || $ch_id > 0 && in_array(MEMBER_ID,explode(',',$channel_typeinfo['manageid'])))){
			return $html;
		}
		if(empty($features)){
			$features = $this->get_feature();
						if($features){				$features = array(0=>'不设属性')+$features;
			}
		}else{
			if(!$features[0]){
								$features = array(0=>'等待处理')+$features;
			}
		}
		if($features){
			if($notajax){
				$html = '<select name="'.$divid.'featureid" id="'.$divid.'featureid" style="margin:0;padding:2px;box-shadow:none; margin-bottom:6px;">';
			}
			foreach($features as $key => $val){
				if($key == $tfid){
					$html .= '<option value="'.$key.'" selected="selected">'.$val.'</option>';
				}else{
					$html .= '<option value="'.$key.'">'.$val.'</option>';
				}
			}
			if($notajax){
				$html .= '</select>';
			}
		}
		return $html;
	}

	function get_featurelist($per_page_num=20){
		$query_link = 'admin.php?mod=feature';
		$sql = "select count(*) as `total_record` from ".DB::table('feature');
		$total_record = DB::result_first($sql);
		$page_arr = page($total_record,$per_page_num,$query_link,array('return'=>'array'),'20 50 100 200,500');
		$sql = "select * from ".DB::table('feature')." order by `featureid` desc {$page_arr['limit']}";
	 	$query = DB::Query($sql);
		$feature_list = array();
		while(false != ($row = $query->GetRow())){
			$feature_list[] = $row;
		}
		return array('total'=>$total_record, 'page'=>$page_arr, 'list'=>$feature_list);
	}

	function add_feature($name=''){
		$query = DB::Query("select * from ".DB::table('feature')." where `featurename` = '{$name}' ");
		$featurelist = $query->GetRow();
		if($featurelist){
			return -1;
		}
		DB::Query("insert into ".DB::table('feature')."(`featurename`) values ('{$name}')");
		$this->update_feature_cache();
		return 1;
	}

	function delete_feature($ids=array()){
		foreach($ids as $k => $v){			if(in_array($v,array(1,2,3))){
				unset($ids[$k]);
			}
		}
		if($ids){
			DB::Query("delete from ".DB::table('feature')." where `featureid` in(".jimplode($ids).")");
			DB::Query("update ".DB::table('topic')." set  `featureid` = '0' where `featureid` in(".jimplode($ids).")");			foreach($ids as $v){				DB::Query("UPDATE ".DB::table('channel_type')." SET featureid = TRIM(BOTH ',' FROM REPLACE(CONCAT(',',featureid,','),',".$v.",',',')) WHERE CONCAT(',',featureid,',') LIKE '%,".$v.",%'");
			}
			$this->update_feature_cache();
			jlogic('channel')->update_category_cache();
		}
	}

	function modify_feature($id=0,$name=''){
		$query = DB::Query("select * from ".DB::table('feature')." where `featurename` = '{$name}' ");
		$featurelist = $query->GetRow();
		if($featurelist){
			return -1;
		}
		DB::Query("update ".DB::table('feature')." set  `featurename` = '{$name}'  where `featureid`='$id'");
		$this->update_feature_cache();
		jlogic('channel')->update_category_cache();
		return 1;
	}

		function set_topic_feature($tid=0,$relateid=0,$featureid=0){
		$topicinfo = jlogic('topic')->Get($tid,'`item`,`item_id`,`uid`,`relateid`,`featureid`','');
		if($topicinfo && $topicinfo['item']=='channel' && $topicinfo['item_id']>0){
			$ch_typeinfo = jlogic('channel')->get_channel_typeinfo_byid($topicinfo['item_id']);
			if(in_array($ch_typeinfo['channel_type'],array('ask','idea')) && ('admin'==MEMBER_ROLE_TYPE || ($ch_typeinfo['manageid'] && in_array(MEMBER_ID,explode(',',$ch_typeinfo['manageid']))))){
				DB::Query("update ".DB::table('topic')." set  `relateid` = '{$relateid}', `featureid` = '{$featureid}'  where `tid`='$tid'");
			}
			if(!($relateid == $topicinfo['relateid'] && $featureid == $topicinfo['featureid'])){
				$msg = '您提出的';
				$msg .= $ch_typeinfo['channel_type'] == 'ask' ? '问题' : '建议';
				if($relateid != $topicinfo['relateid']){
					$msg .= $relateid == 0 ? '，原有答复已被取消' : ($topicinfo['relateid'] == 0 ? '，已经有了答复' : '重新给予了答复');
				}
				if($featureid != $topicinfo['featureid']){
					$msg .= '，状态变更为“'.($ch_typeinfo['feature'][$featureid] ? $ch_typeinfo['feature'][$featureid] : '等待处理').'”';
				}
				postpmsms($topicinfo['uid'],$tid,$msg);
			}
		}
	}
}