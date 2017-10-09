<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename feature.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013-11-11 749544217 2047 $
 */




if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	function ModuleObject($config){
		$this->MasterObject($config);
		$this->Execute();
	}

	function Execute(){
		ob_start();
		switch ($this->Code){
			case 'listfeature':
				$this->Listfeature();
				break;
			case 'modfeature':
				$this->Modfeature();
				break;
			case 'domodfeature':
				$this->Domodfeature();
				break;
			case 'ajaxgetfeature':
				$this->Ajaxgetfeature();
				break;
			default:
				$this->main();
				break;
		}
		response_text(ob_get_clean());
	}

	function main(){
		response_text("正在建设中……");
	}

		function Listfeature(){
		$channelid = jget('channelid');
		$featureid = jget('featureid');
		$html = jlogic('feature')->get_feature_select($channelid,$featureid);
		echo $html;
	}
		function Ajaxgetfeature(){
		$channelid = jget('channelid');
		$featureid = jget('featureid');
		$html = jlogic('feature')->get_feature_select($channelid,$featureid,'',0);
		echo $html;
	}

		function Modfeature(){
		$tid = jget('tid');
		$replyid = jget('replyid');
		$rloadtrue = jget('rloadtrue');
		$topicinfo = jlogic('topic')->Get($tid,'`item`,`item_id`,`featureid`,`relateid`','');
		$featurehtml = jlogic('feature')->get_feature_select($topicinfo['item_id'],$topicinfo['featureid']);
		include template('topic_feature_ajax');
	}

	function Domodfeature(){
		$tid = jget('tid');
		$relateid = jget('replyid');
		$featureid = jget('featureid');
		if($tid > 0){
			jlogic('feature')->set_topic_feature($tid,$relateid,$featureid);
			json_result('操作成功');
		}else{
			json_result('没做任何处理');
		}
	}
}