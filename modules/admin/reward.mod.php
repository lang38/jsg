<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename reward.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2014 1724081437 4627 $
 */




if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class ModuleObject extends MasterObject {

	function ModuleObject($config) {
		$this->MasterObject($config);
		$this->Execute();
	}

	function Execute(){
		ob_start();
		switch ($this->Code) {
			case 'delete':
				$this->delete();
				break;
			case 'edit':
				$this->Edit();
				break;
			case 'save':
				$this->Save();
				break;
			case 'on_off':
				$this->onOff();
				break;
			default:
				$this->main();
				break;
		}
		$body = ob_get_clean();
		$this->ShowBody($body);
	}

	function main(){
		$option = array();
		$option['page_url'] ='admin.php?mod=reward';

		$id = (int) get_param('id');
		if($id) {
			$option['id'] = $id;
			$option['page_url'] .= "&id=$id";
		} else {
			unset($id);
		}

		$title = trim(get_param('title'));
		if(isset($title)) {
			$option['title'] = $title;
			$option['page_url'] .= "&title=$title";
		}

		$timefrom = get_param('timefrom');
		$fromt = strtotime($timefrom);
		if($fromt) {
			$option['fromt'] = $fromt ;
			$option['page_url'] .= "&timefrom=$timefrom";
		}

		$timeto = get_param('timeto');
		$tot = strtotime($timeto);
		if($tot) {
			$option['tot'] = $tot ;
			$option['page_url'] .= "&timeto=$timeto";
		}

		$nickname1 = trim(get_param('nickname'));
		$uid = DB::result_first("select `uid` from `".TABLE_PREFIX."members` where `nickname` = '$nickname1'");
		if($uid > 0) {
			$option['uid'] = $uid;
			$option['page_url'] .= "&nickname=$nickname1";
			$nickname = $nickname1 ;
		}

		$option['page'] = true;
		$option['per_page_num'] = min(500,max((int) $_GET['per_page_num'],(int) $_GET['pn'],20));;

		$option['_config'] = array('return'=>'array');
		$option['page_set'] = '20 30 40 50 100 200';

		extract(jlogic('reward')->getRewardList($option));
		$count = $count ? $count : 0;

		$member = jsg_member_info(MEMBER_ID);
		$this->Title = '有奖转发';
		include template('admin/reward_mian');
	}

	
	function delete(){
		$vid = get_param('vid');
		$id = (int) get_param('id');
		if($id) {
			$ids[] = $id;
			$vid[] = $id;
		}
		$ids = get_param('ids');
		$rid = get_param('up_id');

		$RewardLogic = jlogic('reward');

		if($vid){
			DB::query("update `".TABLE_PREFIX."reward` set `recd` = 0 where `id` in (".jimplode(',',$vid).")");
			foreach ($vid as $k => $v) {
				if($ids && in_array($v,$ids)){
					$RewardLogic->DoDelete($v);
				} else if($rid && in_array($v,$rid)){
					$recd_id[$v] = $v;
				}
			}
			if($recd_id){
				DB::query("update `".TABLE_PREFIX."reward` set `recd` = 1 where `id` in (".jimplode(',',$recd_id).")");
			}
		}

		$this->Messager('操作成功','admin.php?mod=reward');
	}

    
    public function onOff(){
        $onOff = (int) $this->Get["reward_on_off"];
        jconf::update("reward_open",$onOff);

        $this->Messager('操作成功','admin.php?mod=reward');
    }

		public function Edit(){
		$id = jget('id','int');
		$is_exists = jlogic('reward')->is_exists($id);
		if($is_exists){
			$reward = jlogic('reward')->getRewardInfo($id);
			$recd_html = $this->jishigou_form->YesNoRadio('recd',(int)($reward['recd']));
			include template('admin/reward_edit');
		}else{
			$this->Messager('没有找到相关数据','admin.php?mod=reward');
		}
	}

		public function Save(){
		$data = array();
		$title = get_param('title');
		if(!trim($title)) $this->Messager("标题必须要有哦...",-1);
		$data['title'] = trim($title);
		$fromt = get_param('fromt');
		if(!trim($fromt)) $this->Messager("开始时间必须要有哦...",-1);
		$data['fromt'] =strtotime($fromt);
		$tot = get_param('tot');
		if(!trim($tot)) $this->Messager("结束时间必须要有哦...",-1);
		$data['tot'] =strtotime($tot);
		if($data['fromt'] >= $data['tot']) $this->Messager("开始时间必须早于结束时间哦...",-1);
		$content = get_param('content1');
		if(!trim($content)) $this->Messager("活动描述必须要有哦...",-1);
		$data['content'] = trim($content);
		$id = (int)$this->Post['id'];
		$data['recd'] = (int)$this->Post['recd'];
		if($id > 0){$is_exists = jlogic('reward')->is_exists($id);}
		if($is_exists){
			$return = jlogic('reward')->update($data,TABLE_PREFIX.'reward',"where `id`='".$id."'");
			$this->Messager('操作成功','admin.php?mod=reward&code=edit&id='.$id);
		}else{
			$this->Messager('操作失败，您要编辑的对象不存在','admin.php?mod=reward');
		}
	}
}

?>