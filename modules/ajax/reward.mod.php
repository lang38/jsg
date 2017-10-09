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
 * @Date 2014 2024577227 8473 $
 */


if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class ModuleObject extends MasterObject {

	var $Type = 'normal';

	function ModuleObject($config) {
		$this->MasterObject($config);

		if(!$this->Config['event_open']){
			$this->_image_error("管理员已关闭有奖转发功能");
		}
		if(MEMBER_ID < 1){
			$this->_image_error('你需要先登录才能继续本操作');
		}

		$this->Execute();
	}

	function Execute(){
		ob_start();
		switch ($this->Code) {
			case 'detail':
				$this->RewardDetail();
				break;
			case 'addPrize':
				$this->addPrize();
				break;
			case 'delprize':
				$this->delPrize();
				break;
			case 'showtab':
				$this->showTab();
				break;
			case 'dodraw':
				$this->DoDraw();
				break;
			default:
								break;
		}
		response_text(ob_get_clean());
	}

	
	function RewardDetail(){
		$rid = jget('rid','int');
		$tid = jget('tid','int');

		if(!$rid || !$tid){
			response_text('无效的有奖转发ID');
		}

		$reward = jlogic('reward')->getRewardInfo($rid);

		if(!$reward){
			response_text('无效的有奖转发ID');
		}


		if($reward['time_lesser'] > 0){
			$hours = $reward['time_lesser'] % 86400;
			$day = floor($reward['time_lesser'] / 86400) ? floor($reward['time_lesser'] / 86400) .'天' : '';

			$i = $hours % 3600;
			$hours = floor($hours / 3600) ? floor($hours / 3600) .'时' : '';

			$i = floor($i / 60) ? floor($i / 60) .'分' : '';

			$reward['time_lesser'] = '距离转发结束还有 ' . $day . $hours . $i;
		} else {
			$reward['time_lesser'] = '有奖转发结束';
		}
		if ($reward['rules']['tag']) {
			$content = '#'.implode('##',$reward['rules']['tag']).'#';
		}

		include template('widget/widget_reward_view');
	}

	
	function DoDraw(){
		#有奖转发活动ID
		$rid = (int) get_param('rid');
		#奖品等级
		$pid = get_param('pid');

		if(!$rid){ exit('请确认你要抽奖的有奖转发活动或奖品等级'); }

		$ret = jlogic('reward')->DoDraw($rid,$pid);
		if(!$ret){
			exit('没有抽出符合条件的用户。');
		} elseif (is_string($ret)){
			exit($ret);
		} elseif (is_array($ret)) {
			$html = '<table cellspacing="1" width="100%" align="center" class="tableborder">
					   <tr>
					     <td width="30%">昵称</td><td width="30%">等级</td><td width="40%">奖品</td>
					   </tr>';
			foreach ($ret as $k=>$v) {
				$html .= '<td><a href="index.php?mod='.$v['username'].'" target="_blank">'.$v['nickname'].'</td><td>'.$v['prize_name'].'</td><td>'.$v['prize'].'</td>';
			}
			$html .= '</table>';
		} else {
			exit('未知错误。');
		}

		exit($html);
	}

	function _image_error($msg)
	{
		if('normal' == $this->Type)
		{
						echo "<script type='text/javascript'>window.parent.MessageBox('warning', '{$msg}');</script>";
			exit ;
		}
		else
		{
			json_error($msg);
		}
	}

	function addPrize(){
		$id = (int) $this->Get['id'];


		$file = 'image_'.$id;
        if($_FILES[$file]['name']){

						$name = time();
			$image_name = $name.MEMBER_ID.".jpg";
			$image_path = RELATIVE_ROOT_PATH . 'images/reward/'.face_path(MEMBER_ID);
			$image_file = $image_path . $image_name;

			if (!is_dir($image_path))
			{
				jio()->MakeDir($image_path);
			}

			jupload()->init($image_path,$file,true);

			jupload()->setNewName($image_name);
			$result=jupload()->doUpload();

			if($result)
	        {
				$result = is_image($image_file);
			}
			if(!$result)
	        {
				unlink($image_file);
		        echo "<script type='text/javascript'>";
				echo "parent.document.getElementById('message').style.display='none';";
		        echo "</script>";
				$this->_image_error(jupload()->_error);
				exit();
			}
			image_thumb($image_file,$image_file,100,100);
			if($this->Config['ftp_on']) {
	            $ftp_key = randgetftp();
				$get_ftps = jconf::get('ftp');
	            $site_url = $get_ftps[$ftp_key]['attachurl'];
	            $ftp_result = ftpcmd('upload',$image_file,'',$ftp_key);
	            if($ftp_result > 0) {
	                jio()->DeleteFile($image_file);
	                $image_file = $site_url .'/'. str_replace('./','',$image_file);
	            }
	        }
			DB::query("insert into `".TABLE_PREFIX."reward_image` set `uid` = '".MEMBER_ID."',`image` = '$image_file' ");
			$image_id = DB::insert_id();
        } else {
	        echo "<script type='text/javascript'>";
			echo "alert('没有图片');";
	        echo "</script>";
	        exit();
        }

        echo "<script type='text/javascript'>";

		echo "parent.document.getElementById('message').style.display='none';";
		echo "parent.document.getElementById('show_image_$id').src='{$image_file}';";
		echo "parent.document.getElementById('show_image_$id').style.display='block';";
		echo "parent.document.getElementById('hid_image_$id').value='{$image_id}';";
        echo "</script>";
        exit;
	}

	
	function delPrize(){
		$iid = (int) $this->Get['iid'];
		if($iid < 1){
			return '';
		}

		$ret = DB::fetch_first("select `uid`,`image` from `".TABLE_PREFIX."reward_image` where `id` = '$iid'");
		$image = $ret['image'];
		$uid = $ret['uid'];
		if($uid < 1 || $uid != MEMBER_ID){
			return '';
		}
		if($image){
			unlink($image);
		}
		DB::query("delete from `".TABLE_PREFIX."reward_image` where id = '$iid'");

		return '删除成功';
	}

	function showTab(){
		$html = '';
		$rid = (int) get_param('fid');
		$id = (int) get_param('id');

		if(!$rid || !$id){
			return '<div>未知错误。</div>';
		} else if(MEMBER_ID < 1){

			return '<div>需要登录才可以查看有奖信息。</div>';

		} else {
			#转发规则
			if($id == 1){
				$html .= '<p>1、有奖转发由发起者设定奖品、转发原文以及可参加抽奖条件； </p>';
				$html .= '<p>2、活动发起者不得向参与者收取费用，否则视为无效。活动中奖结果均以系统自动下发的中奖通知为准。如出现拒绝兑现情况，将视为虚假活动；  </p>';
				$html .= '<p>3、所有符合转发条件的用户都可以免费参加转发及抽奖；</p>';
				$html .= '<p>4、发起人将在结束后，使用系统工具对有效转发者进行抽奖，并公布中奖名单；  </p>';
				$html .= '<p>5、中奖结果随机产生，本着"公平、工作、公开"的原则，即开即奖；  </p>';
				$html .= '<p>6、中奖者请务必及时联系发起者领取奖品； </p>';
				$html .= '<p>7、禁止任何不正当手段（以获奖为目的，恶意注册多个帐号）及舞弊行为参与本活动，一经发现，活动发起人有权取消该用户的获奖资格；  </p>';
				$html .= '<p>8、如中奖用户删除了参与本活动的转发微博，活动发起人有权取消其获奖资格；</p>';
				$html .= '<p>9、本次转发活动解释权归发起人所有；</p>';
				$html .= '<p>10、发起者应严格保密获奖用户提交的相关联系信息，严禁外泄。如出现问题，责任由发起者承担；</p>';
				$html .= '<p>11、如中奖用户删除了参与本活动的转发微博，活动发起人有权取消其获奖资格；</p>';
			}

			#中奖名单
			else if($id == 2){
				$reward = jlogic('reward')->getRewardInfo($rid);
				if(!$reward){$html .= '<div>有奖转发活动无效。</div>';}

				$prize = $reward['prize'];

				$where = " where u.`rid` = '$rid' ";
				$pid = get_param('pid');
				if(isset($pid)) {
					$where .= " and u.`pid` = '$pid' ";
					$selected[$pid] = ' selected ';
				}

				$page = empty($this->Get['page']) ? 0 : (int) $this->Get['page'];
				$perpage = 8;
				if ($page == 0) {
					$page = 1;
				}
				$start = ($page - 1) * $perpage;
				$sql = "select u.*,m.username,m.nickname
						 from `".TABLE_PREFIX."reward_win_user` u
						 left join `".TABLE_PREFIX."members` m  on m.uid = u.uid
						 $where
						 limit $start,$perpage";
				$query = DB::query($sql);
				while($rs = DB::fetch($query)){
					$rs['dateline'] = date('Y-m-d H:i:s',$rs['dateline']);
					$prize_user_list[$rs['uid']] = $rs;
					$prize_user_list[$rs['uid']]['prize_name'] = $prize[$rs['pid']]['prize_name'];
					$prize_user_list[$rs['uid']]['prize'] = $prize[$rs['pid']]['prize'];
				}
				$count = DB::result_first('select count(*) from `'.TABLE_PREFIX."reward_win_user` u $where ");

				$param = array('rid'=>$rid,'id'=>$id,'pid'=>$pid);
				$multi = ajax_page($count, $perpage, $page, 'manage',$param);
			}

			#我的奖品
			else if ($id == 3) {
				$my_prize = jlogic('reward')->getUserPrize($rid);
			}
		}

		include_once template('reward/reward_tab_ajax');
	}
}
?>