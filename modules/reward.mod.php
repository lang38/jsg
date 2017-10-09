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
 * @Date 2014 2027781385 7756 $
 */



if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class ModuleObject extends MasterObject {

	function ModuleObject($config) {
		$this->MasterObject($config);
		if(!$this->Config['reward_open']){
			$this->Messager("管理员已关闭有奖转发功能","index.php?mod=topic");
		}
		if(MEMBER_ID < 1){
			$this->Messager("请先<a onclick='ShowLoginDialog(); return false;'>点此登录</a>或者<a onclick='ShowLoginDialog(1); return false;'>点此注册</a>一个帐号",null);
		}

		$this->Execute();
	}

	function Execute(){
		ob_start();
		switch ($this->Code) {
			case 'add':
				$this->AddReward();
				break;
			case 'doadd':
				$this->DoAddReward();
				break;
			case 'del':
				$this->DeleteReward();
				break;
			case 'detail':
				$this->GetRewardDetail();
				break;
			case 'user':
				$this->getRewardUser();
				break;
			case 'myreward':
			case 'freward':
			case 'joinedreward':
			default:
				$this->main();
				break;
		}
		$body = ob_get_clean();
		$this->ShowBody($body);
	}

	function main(){
		$select_reward = true;
		$option = array(
			'page' => true,
			'per_page_num' => 10,
			'page_url' => 'index.php?mod=reward',
			'_config' => array('return'=>'array'),
		);

		$RewardLogic = jlogic('reward');

		if($this->Code == 'myreward'){
			$option['uid'] = MEMBER_ID;
			$option['page_url'] = 'index.php?mod=reward&code=myreward';
		} else if ($this->Code == 'freward') {
			$option['uid'] = get_buddyids(MEMBER_ID);
			if(!$option['uid']){
				$select_reward = false;
			}
			$option['page_url'] = 'index.php?mod=reward&code=freward';
		} else if ($this->Code == 'joinedreward') {
			$rid = $RewardLogic->getJoinedRewardRid(MEMBER_ID);
			$option['id'] = $rid;
			if(!$option['id']){
				$select_reward = false;
 			}
			$option['page_url'] = 'index.php?mod=reward&code=joinedreward';
		}
		$myreward = $this->Code == 'myreward' ? 'tago' : 'tagn';
		$freward = $this->Code == 'freward' ? 'tago' : 'tagn';
		$mainreward = $this->Code == '' ? 'tago' : 'tagn';
		$joinedreward = $this->Code == 'joinedreward' ? 'tago' : 'tagn';

		if($select_reward){
			extract($RewardLogic->getRewardList($option));
		}
		$count = $count ? $count : 0;

		$param = array(
			'recd' => 1,
			'limit' => 10,
		);
		$recd_reweard = $RewardLogic->getRewardList($param);


		$member = jsg_member_info(MEMBER_ID);
		$this->Title = '有奖转发';
		include template('reward/reward_mian');
	}

	
	function GetRewardDetail(){
		$id = (int) get_param('id');
		if($id < 1){
			$this->Messager('你查看的有奖转发信息不存在或已删除',-1);
		}

		$reward = jlogic('reward')->getRewardInfo($id);
		if(!$reward){
			$this->Messager('你查看的有奖转发信息不存在或已删除',-1);
		}

		if ($reward['rules']['tag']) {
			$content = '#'.implode('##',$reward['rules']['tag']).'#';
		}

		#有奖转发活动的参与者（显示N个）
		$param = array(
			'rid' => $id,
			'limit' => 9,
		);

		$ret = jlogic('reward')->getRewardUser($param);
		$play_member = $ret['user'];
		$play_num = $ret['count'];

				jfunc('app');
		$gets = array(
			'mod' => 'reward',
			'code' => "detail",
			'id' => $id,
		);
		$page_url = 'index.php?'.url_implode($gets);

		$options = array(
			'page' => true,
			'perpage' => 5,				'page_url' => $page_url,
		);
		$topic_info = app_get_topic_list('reward', $id, $options);
		$topic_list = array();
		if (!empty($topic_info)) {
			$topic_list = $topic_info['list'];
			$page_arr['html'] = $topic_info['page']['html'];
			$no_from = true;
		}

				$this->item = 'reward';
		$this->item_id = $id;

				$set_qun_closed = 1;
		$set_event_closed = 1;
		$set_fenlei_closed = 1;
		$set_vote_closed = 1;
		if(DB::result_first("select id from `".TABLE_PREFIX."reward_user` where uid = '".MEMBER_ID."' and rid='".$this->item_id."' and`on` = 1")){
            $isReward=true;
        }
		$member = jsg_member_info(MEMBER_ID);
		$this->Title = cut_str($reward['title'],10);
		include_once template('reward/reward_datail');
	}

	
	function getRewardUser(){
		$my_member = jsg_member_info(MEMBER_ID);
		$rid = (int) get_param('rid');
		if($rid < 1){
			$this->Messager('无效的有奖转发。',-1);
		}
		#有奖转发活动的参与者（显示N个）
		$param = array(
		    'page' => true,
			'per_page_num' => 20,
			'url' => 'index.php?mod=reward&code=user&rid='.$rid,
			'rid' => $rid,
		);

		$ret = jlogic('reward')->getRewardUser($param);
		$member = $ret['user'];
		$count = $ret['count'];
		$page_arr = $ret['page_arr'];

		$this->Title = '有奖转发参与者';
		include(template('reward/reward_user'));
	}

	function AddReward(){

		$id = (int) get_param('id');
		if($id > 0){
			$reward = jlogic('reward')->getRewardInfo($id);

            if($reward['uid'] != MEMBER_ID){
                $this->Messager('你无编辑权限',-1);
            }
			if(!$reward){
			    $this->Messager('你查看的有奖转发信息不存在或已删除',-1);
			}
			#数据还原
			if($reward['rules']['tag']){
				$tag = implode('|',$reward['rules']['tag']);
			}
			if($reward['rules']['user']){
				foreach ($reward['rules']['user'] as $key => $val) {
					$nickname = $nickname ? $nickname.'|'.$val['nickname'] : $val['nickname'];
				}
			}
			$at_num = $reward['rules']['at_num'];
			$num = count($reward['prize']) -1;
		}

		$member = jsg_member_info(MEMBER_ID);
		$this->Title = '发起有奖转发';
		include template('reward/reward_create');
	}

	function DoAddReward(){
		$id = (int) get_param('id');
		$data = array(
            'tid'=>jget('tid','int'),
			'content' => jget('content1','html'),
            'event_image'=>  get_param('event_image'),
			'prize_name' => get_param('prize_name'),
			'prize' => get_param('prize'),
			'prize_num' => get_param('prize_num'),
			'prize_image' => get_param('prize_image'),
			'rules'=> get_param('rules'),
		);
		$title = jget('title','txt');
		if(!trim($title)) $this->Messager("标题必须要有哦...",-1);
		$data['title'] = trim($title);

		$fromt = get_param('fromt');
		if(!trim($fromt)) $this->Messager("开始时间必须要有哦...",-1);
		$data['fromt'] =strtotime($fromt);

		$tot = get_param('tot');
		if(!trim($tot)) $this->Messager("结束时间必须要有哦...",-1);
		$data['tot'] =strtotime($tot);

		if($data['fromt'] >= $data['tot']) $this->Messager("开始时间必须早于结束时间哦...",-1);
        $data['prize_name']= array_filter($data['prize_name']);
		if(!$data['prize_name']) $this->Messager("此次转发没有奖励吗？",-1);

        $data['prize_image']= array_filter($data['prize_image']);
		if(!$data['prize_image']) $this->Messager("奖品没有图片吗？",-1);

        $data['prize']= array_filter($data['prize']);
		if(!$data['prize']) $this->Messager("奖品名字还没有填写！",-1);

		$data['topic'] = jget('topic','html');
		if (!$id) {
			if(!$data['topic']){ $this->Messager('需要用户转发你哪条微博？',-1);}
		}

		$rid = jlogic('reward')->add($data,$id);

		if(is_string($rid)){
			$this->Messager($rid,-1);
		} else {
			$msg = $id ? '有奖转发修改成功' : '有奖转发发布成功';
			$this->Messager($msg,"index.php?mod=reward&code=detail&id=$rid");
		}
	}

	
	function DeleteReward(){
		$id = (int) get_param('id');
		$ret = jlogic('reward')->DoDelete($id);
		if($ret){
			$this->Messager('删除成功','index.php?mod=reward');
		} else {
			$this->Messager('你要删除的有奖转发信息不存在或已删除',-1);
		}
	}
}
?>