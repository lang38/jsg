<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename dig.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2014 621975392 14133 $
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
		$this->TopicLogic = jlogic('topic');
		ob_start();
		switch($this->Code)
		{
			case 'dig':
				$this->dig();
				break;
			case 'ajax':
				$this->getajax();
				break;
			case 'cnews':
				$this->cnews();
				break;
			case 'newtopicnums':
				$this->newtopicnums();
				break;
			case 'feednews':
				$this->feednews();
				break;
			case 'user':
				$this->getuser();
				break;
			case 'rcduser':
				$this->getrcduser();
				break;
			default:
				$this->Main();
				break;
		}
		response_text(ob_get_clean());
	}

	function Main()
	{
		response_text("error");
	}

	function dig()
	{
		$tid = jget('tid','int','P');
		$uid = jget('uid','int','P');
		if($tid > 0 && $uid > 0){
			$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('topic_dig')." WHERE tid='{$tid}' AND uid = '".MEMBER_ID."'");
			if($count > 0){
				echo 'no,';
			}else{
				$topic_info =DB::fetch_first("SELECT `uid`,`content`,`item_id` FROM ".DB::table('topic')." WHERE tid='{$tid}'");
				$uid = $topic_info['uid'];
				if($uid == MEMBER_ID){
					echo 'not,';
				}else{
					jtable('topic_more')->update_diguids($tid);										DB::query("update `".DB::table('members')."` set `digcount` = digcount + 1,`dig_new` = dig_new + 1 where `uid`='{$uid}'");
					$ary = array('tid' => $tid,'uid' => MEMBER_ID,'touid'=> $uid,'dateline' => time());
					DB::insert('topic_dig', $ary, true);
										jtable('topic')->update_digcounts($tid);
                    if(jconf::get('contest_available')){
                         if('contest' == DB::result_first("SELECT `item` FROM ".DB::table('topic')." WHERE tid={$tid}")){
                             jlogic('contest_entries')->update_dig($tid);
                         }
                    }
										$credits = jconf::get('credits');
					update_credits_by_action('topic_dig',MEMBER_ID);
					update_credits_by_action('my_dig',$uid);
										if($GLOBALS['_J']['config']['feed_type'] && is_array($GLOBALS['_J']['config']['feed_type']) && in_array('dig',$GLOBALS['_J']['config']['feed_type']) && $GLOBALS['_J']['config']['feed_user'] && is_array($GLOBALS['_J']['config']['feed_user']) && array_key_exists(MEMBER_ID,$GLOBALS['_J']['config']['feed_user'])){
						$feed_msg = cut_str($topic_info['content'],30,'');
						feed_msg('leader','dig',$tid,$feed_msg,$topic_info['item_id']);
					}
					echo 'yes,'.$this->js_show_msg(1);
				}
			}
		}
	}

	function cnews()
	{
		$num = jget('num','int','P') ? jget('num','int','P') : 5;
		$cid = $this->Config['channel_recommend'] ? $this->Config['channel_recommend'] : 0;
		$ctitle = $this->Config['channel_rtitle'] ? $this->Config['channel_rtitle'] : '推荐新闻';
		$cachefile = jconf::get('channel');
		$channel_channels = is_array($cachefile['channels']) ? $cachefile['channels'] : array();
		if($cid && $channel_channels[$cid]){
			$sql = "SELECT `tid`,`content`,`dateline` FROM `".TABLE_PREFIX."topic` WHERE `item` = 'channel' AND `item_id` IN(".jimplode($channel_channels[$cid]).") AND `type` = 'first' ORDER BY `dateline` DESC LIMIT ".$num;
			$topic_list = array();$query = DB::query($sql);
			while ($rs = DB::fetch($query)){
				$rs['dateline'] = my_date_format($rs['dateline'],'m/d');
				$rs['content'] = cut_str(str_replace("\r\n",'',strip_tags($rs['content'])),30);
				$topic_list[$rs['tid']] = $rs;
			}
		}
		$html = '<h3 class="news"><span>'.$ctitle.'</span></h3><ul class="news_notice">';
		if($topic_list){
			foreach($topic_list as $val){
				$html .= '<li class="cnt"><p><a href="'.jurl('index.php?mod=topic&code='.$val['tid']).'" target="_blank">'.$val['content'].'</a></p><span class="count">'.$val['dateline'].'</span></li>';
			}
		}else{
			$html .= '<li class="cnt"><p>没有相关内容</p></li>';
		}
		$html .= '</ul>';
		echo $html;
	}

	function feednews()
	{
		$num = jget('num','int','P') ? jget('num','int','P') : 6;
		$feed_list = jlogic('feed')->get_feed($num);
		$html = '';
		if($feed_list){
			foreach($feed_list as $val){
				if($val['item']=='积分兑换' || $val['action']=='兑换了'){
					$modurl = 'mall';
					$codeurl = 'goodsinfo&id='.$val['tid'];
				}else{
					$modurl = 'topic';
					$codeurl = $val['tid'];
				}
				$html .= '<li><p><span>【'.$val['item'].'】</span><a href="'.($val['uid'] > 0 ? jurl('index.php?mod='.$val['uid']) : 'javascript:void()').'">'.$val['nickname'].'</a>&nbsp;'.$val['action'].'&nbsp;<a href="'.jurl('index.php?mod='.$modurl.'&code='.$codeurl).'" target="_blank">'.$val['msg'].'</a></p></li>';
			}
		}else{
			$html .= '<li><p>没有相关内容</p></li>';
		}
		echo $html;
	}

	function newtopicnums(){
		$cookie_nums = $cookie_sys_nums = $cookie_channel_nums = $dstr = '';
		$ary_sys_nums = array();
		if(MEMBER_ID > 0){
			$d_ary_sys_nums = jsg_member_info(MEMBER_ID);
			$d_k_ary_back = array('newpm'=>'my_pm','comment_new'=>'comment_my','fans_new'=>'','at_new'=>'at_my','favoritemy_new'=>'favorite_my','dig_new'=>'dig_my','channel_new'=>'my_channel','company_new'=>'','vote_new'=>'','qun_new'=>'my_qun','event_new'=>'','topic_new'=>'my_tag_topic','event_post_new'=>'','fenlei_post_new'=>'');
			$d_k_ary = jconf::get('nav_notice');
			$d_k_ary = $d_k_ary && is_array($d_k_ary) ? $d_k_ary : $d_k_ary_back;
			$d_n_ary = array_keys($d_k_ary);
			foreach($d_ary_sys_nums as $k => $v){
				if(in_array($k,$d_n_ary) && $d_k_ary[$k]){
					$ary_sys_nums[$d_k_ary[$k]] = $v;
				}
			}
			unset($d_ary_sys_nums);
			$ary_channel_nums = channel_topic_num();
			if($ary_channel_nums){
				foreach($ary_channel_nums as $ke => $va){
					$ary_sys_nums['channel_'.$ke] = $va;
				}
			} 
		}
		echo json_encode($ary_sys_nums);
	}

	function getajax()
	{
						$cachefile = jconf::get('channel');
		$channel_channels = is_array($cachefile['channels']) ? $cachefile['channels'] : array();
		$type = in_array($this->Post['type'],array('recommend_top','week_dig','month_dig','dig_user_follow','recommend','recommend20')) ? $this->Post['type'] : '';
		$itemid = jget('id','int','P');$uid = jget('uid','int','P');$num = jget('num','int','P');
		if($itemid && $channel_channels[$itemid]){
			$ids = implode(",",$channel_channels[$itemid]);
			$where = " AND `item_id` IN(" . jimplode($channel_channels[$itemid]) . ") ";
		}else{
			$where = "";
		}
		$time = TIMESTAMP;
		$topic_list = array();
		if($type == 'month_dig'){
			$time = $time - 30*24*3600;
			$cache_id = 'dig-30days-top10-'.$itemid;
		}elseif($type == 'week_dig'){
			$time = $time - 7*24*3600;
			$cache_id = 'dig-7days-top10-'.$itemid;
		}elseif($type == 'recommend_top'){
			$cache_id = 'channel-recommend-top10-'.$itemid;
		}elseif($type == 'dig_user_follow'){
			$cache_id = 'dig_user_follow-'.$itemid;
		}elseif($type == 'recommend'){
			$cache_id = 'all-recommend-top10';
		}elseif($type == 'recommend20'){
			$cache_id = 'all-recommend-top20';
		}else{
			echo '<font color=red>开发中......</font>';exit;
		}
		if(false === ($topic_list = cache_file('get', $cache_id))){
			$isquery = true;
			if($type == 'dig_user_follow'){
								$query = DB::query("SELECT uid FROM ".TABLE_PREFIX."topic_dig WHERE tid = '".$itemid."'");
				while ($rs = DB::fetch($query)){$diguids[] = $rs['uid'];}				$query = DB::query("SELECT tid,count(tid) as nums FROM ".TABLE_PREFIX."topic_dig WHERE tid<>'".$itemid."' AND uid IN(".jimplode($diguids).") group by tid ORDER BY nums DESC LIMIT 10");
				while ($rs = DB::fetch($query)){$digftids[] = $rs['tid'];}				if(count($digftids) == 0){$isquery = false;}
				$sql = "SELECT `tid`,`content` FROM `".TABLE_PREFIX."topic` WHERE `type` = 'first' AND tid IN(".jimplode($digftids).") ORDER BY digcounts DESC ";
			}elseif($type == 'recommend_top'){
				$sql = "SELECT tid,r_title as content FROM `".TABLE_PREFIX."topic_recommend` WHERE r_title <> '' AND item = 'channel' ".$where." and (expiration>".time()." OR expiration=0) ORDER BY recd DESC,dateline DESC LIMIT 10 ";
			}elseif($type == 'recommend'){
				$sql = "SELECT tid,r_title as content FROM `".TABLE_PREFIX."topic_recommend` WHERE r_title <> '' AND (expiration>".time()." OR expiration=0) ORDER BY recd DESC,dateline DESC LIMIT 10 ";
			}elseif($type == 'recommend20'){
				$sql = "SELECT tid,r_title as content FROM `".TABLE_PREFIX."topic_recommend` WHERE r_title <> '' AND (expiration>".time()." OR expiration=0) ORDER BY recd DESC,dateline DESC LIMIT 20 ";
			}else{
				$sql = "SELECT `tid`,`content` FROM `".TABLE_PREFIX."topic`
					WHERE `digcounts` > 0 AND `item` = 'channel' ".$where." AND `dateline` >= $time AND `type` = 'first'
					ORDER BY `digcounts` DESC, `lastdigtime` DESC LIMIT 10 ";
			}
			$topic_list = array();
			if($isquery){
				$query = DB::query($sql);
				while ($rs = DB::fetch($query)){
					if($type == 'month_dig' || $type == 'week_dig'){
						$userdata = $this->_getuserfortid($rs['tid']);
						$rs['uid'] = $userdata['uid'];
						$rs['username'] = $userdata['username'];
						$rs['nickname'] = $userdata['nickname'];
						$rs['face'] = $userdata['face'];
					}					
					$rs['scontent'] = cut_str(strip_tags($rs['content']),18);
					$rs['lcontent'] = cut_str(strip_tags($rs['content']),36);
					$topic_list[$rs['tid']] = $rs;
				}
			}
			cache_file('set', $cache_id, $topic_list, 36000);
		}
		if($type == 'month_dig' || $type == 'week_dig'){
			$html = '';
			if($topic_list){
				$i = 1;
				foreach ($topic_list as $rs) {
					$html .= "<li><i class='hotlist{$i}'>{$i}</i><div class='list_face'><img onerror='javascript:faceError(this);' src='{$rs['face']}'></div><div class='list_con'><p><a href='".jurl("index.php?mod={$rs['username']}")."' title='点击访问【{$rs['nickname']}】的主页'>{$rs['nickname']}</a></p><p><a href='".jurl("index.php?mod=topic&code={$rs[tid]}")."' title='{$rs['content']}'>{$rs['scontent']}</a></p></div></li>";
					$i++;
					if($i > $num){break;}
				}
			}else{
				$html .= '没有找到相关'.$this->Config['changeword']["n_weibo"];
			}
		}else{
			$html = '<ul class="hot_reply_b">';
			if($topic_list){
				$i = 1;
				foreach ($topic_list as $rs) {
					$html .= "<li><b class='hrb_{$i}'>{$i}</b><a href='".jurl("index.php?mod=topic&code={$rs[tid]}")."' title='点此查看详情' target='_blank'>{$rs['lcontent']}</a></li>";
					$i++;
				}
			}else{
				$html .= '没有找到相关'.$this->Config['changeword']["n_weibo"];
			}
			$html .= '</ul>';
		}
		echo $html;
	}

	function getuser()
	{
		$tid = jget('tid','int','P');$num = jget('num','int','P');
		if($tid > 0 && $num > 0){
			$dig_users = array();
			$user = $this->TopicLogic->GetDigUids($tid);
			if($user && is_array($user) && count($user) == $num){
				if($num>5){$i = $num-5;$user = array_slice($user,$i,5);}
			}
						else{
				$query = DB::query("SELECT uid FROM `".TABLE_PREFIX."topic_dig`	WHERE tid = '".$tid."' ORDER BY id DESC LIMIT 5");
				while ($rs = DB::fetch($query)){
					$user[$rs['uid']] = $rs['uid'];
				}
				$user = array_slice($user,0,5);			}
						$dig_users = $this->TopicLogic->GetMember($user, "`uid`,`username`,`nickname`,`face`");
			foreach($dig_users as $k => $v){
				array_splice($dig_users[$k],4,3);
			}
			$html = '<script type="text/javascript">$(document).ready(function(){$(".digusername").bind("mouseover", function(){$(this).show();});$(".digusername").bind("mouseout", function(){$(this).hide();});});</script>';
			$html .= '<div class="digusername"><div class="list"><p>'.$this->Config['changeword']['dig'].'者</p><ul>';
			foreach($dig_users as $u){
				$html .= '<li><a href="'.jurl('index.php?mod='.$u['username']).'"><img src="'.$u['face'].'" onerror="javascript:faceError(this);" onmouseover="get_dig_user_choose(\''.$u['nickname'].'\','.$u['uid'].','.$tid.')"></a><p><a href="index.php?mod='.$u['username'].'" onmouseover="get_dig_user_choose(\''.$u['nickname'].'\','.$u['uid'].','.$tid.')">'.$u['nickname'].'</a></p></li>';
			}
			$html .= '<span style="clear:both;" /></ul></div><div id="diguserface"></div></div>';
			echo $html;
		}
	}

	function getrcduser()
	{
		$tid = jget('tid','int','P');
		if($tid > 0){
			$rcduser = DB::fetch_first("SELECT dateline,r_nickname FROM ".DB::table('topic_recommend')." WHERE tid='{$tid}'");
			$html = '<script type="text/javascript">$(document).ready(function(){$(".rcdusername").bind("mouseover", function(){$(this).show();});$(".rcdusername").bind("mouseout", function(){$(this).hide();});});</script>';
			$html .= '<div class="rcdusername"><div style="text-align:center;background:#fdffd2;border:1px solid #ffecb0;padding:1px 10px;width:380px;overflow:hidden; margin:3px 0 0 80px"><p>推荐人：'.$rcduser['r_nickname'].'，推荐时间：'.my_date_format($rcduser['dateline'],"Y-m-d H:i").'，<a href="'.jurl('index.php?mod=topic&code=recd').'">查看更多推荐</a></p></div></div>';
			echo $html;
		}
	}

	function _getuserfortid($tid=0){
		if($tid > 0){
			$user = DB::fetch_first("SELECT t.uid,m.username,m.nickname FROM ".DB::table('topic')." AS t LEFT JOIN ".DB::table('members')." AS m ON t.uid = m.uid WHERE t.tid='{$tid}'");
			$user['face'] = face_get($user['uid']);
			return $user;
		}
	}
}
?>