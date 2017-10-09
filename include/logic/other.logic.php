<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename other.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2014 1111402082 27973 $
 */



if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class OtherLogic
{

	
	function __construct() {

	}

	function act_list() {
		if(!jconf::get('share_to_weibo', 'link_display_none')) {
			$act_list = array('share'=>'分享到微博',);
		}

		if(!$GLOBALS['_J']['config']['is_qmd'] && !$GLOBALS['_J']['config']['qmd_link_display']) {
			;
		} else {
			$act_list['qmd'] = '签名档';
		}

		if(!jconf::get('weibo_show', 'link_display_none')) {
			$act_list['show'] = array('name'=>'微博秀','link_mod'=>'show','link_code'=>'show',);
		}

		

        $act_list['medal'] = array('name'=>'勋章','link_mod'=>'other','link_code'=>'medal',);

        if(!$GLOBALS['_J']['config']['sms_enable'] && !$GLOBALS['_J']['config']['sms_link_display']) {
        	;
        } else {
        	$act_list['sms'] = '短信';
        }

        return $act_list;
	}

	
	function qmd_list($uid=0,$pic_path='')
	{
		$uid = is_numeric($uid) ? $uid : 0;

		if ($GLOBALS['_J']['config']['is_qmd']) {

			$TopicLogic = jlogic('topic');

						$condition = " where `type` = 'first' and `uid`='{$uid}' order by `dateline` desc limit 1 ";
			$topic_list = $TopicLogic->Get($condition);

			if($topic_list) {
				foreach ($topic_list as $v) {
					$topic = $v;
				}
			} else {
				return false;
			}

			$temp_face = ((true === UCENTER_FACE && true === UCENTER) ? $topic['face_small'] : $topic['face_original']);
			if(false === strpos($temp_face, ':/'.'/')) {
				$member_face = $temp_face;
			} else {
				$field = 'temp_face';
				$image_path = RELATIVE_ROOT_PATH . './data/cache/' . $field . '/' . face_path($topic['uid']);
				$image_file_small = $image_path.$topic['uid'] . "_s.jpg";

				if(!file_exists($image_file_small)) {
					if (!is_dir($image_path)) {
						jio()->MakeDir($image_path);
					}

					$temp_image = dfopen($temp_face, 999999, '', '', true, 5, $_SERVER['HTTP_USER_AGENT']);
					if($temp_image) {
						jio()->WriteFile($image_file_small, $temp_image);
					}

					if(!is_image($image_file_small)) {
						@copy(ROOT_PATH . 'images/noavatar.gif', $image_file_small);
					}
				}

				$member_face =  $image_file_small;
			}

			$content = strip_tags($topic['content']);
			$content = str_replace(array("\r\n", "\n", "\r", "\t", "  "), ' ', $content);
			$content = cut_str($content,74);

			$qmd_data = array(
    			'uid' => $topic['uid'],
    			'nickname' => $topic['nickname'],
    			'validate' => $topic['validate'],
    			'face' => $member_face,
    			'content' => $content,
    			'dateline' => date('m月d日 H:i', $topic['addtime']),
			);

			$qmd_list = $this->qmd_img_list($pic_path, $qmd_data);

			return $qmd_list;
		}

		return false;

	}

	
	function qmd_img_list($pic_path='',$qmd_data)
	{
		if(!$pic_path || !$qmd_data) {
			return '';
		}

				$topic_content1 = cutstr($qmd_data['content'], 43);
		$topic_content2 = substr($qmd_data['content'], strlen($topic_content1));

		$content1 = $this->_entities($topic_content1);
		$content2 = $this->_entities($topic_content2);


				$topic_url = $GLOBALS['_J']['config']['site_url'];

				$topic_date = $this->_entities($qmd_data['dateline']);

				$nickname = $this->_entities($qmd_data['nickname']);

				$bg = imagecreatefromjpeg($pic_path);

				$content_white = imagecolorallocate($bg, 80, 80, 80);

		$font_white = imagecolorallocate($bg, 125, 125, 125);

				$url_white = imagecolorallocate($bg, 75, 167, 213);


				imagettftext($bg, 10,0, 108, 23, $url_white, $GLOBALS['_J']['config']['qmd_fonts_url'], $nickname);

		imagettftext($bg, 9, 0, 108, 43, $content_white, $GLOBALS['_J']['config']['qmd_fonts_url'], $content1);
		if($content2) {
			imagettftext($bg, 9, 0, 108, 64, $content_white, $GLOBALS['_J']['config']['qmd_fonts_url'], $content2);
		}

		imagettftext($bg, 9, 0, 288, 80, $font_white, $GLOBALS['_J']['config']['qmd_fonts_url'], $topic_date);
		imagettftext($bg, 9, 0, 108, 94, $url_white, $GLOBALS['_J']['config']['qmd_fonts_url'], $topic_url);
		


				$user_src = $qmd_data['face'];
		if(!is_image($user_src)) {
			return '';
		}


		list($width,$height, $image_type) = getimagesize($user_src);

				$user_src_im = null;
		if(1 == $image_type) {
			$user_src_im = imagecreatefromgif($user_src);
		} elseif (3 == $image_type) {
			$user_src_im = imagecreatefrompng($user_src);
		} else {
			$user_src_im = imagecreatefromjpeg($user_src);
		}

				if (true === UCENTER_FACE && true === UCENTER) {
						if($height <= 60) {
				$new_face_width = 75 ;
				$new_face_height = 75;
			} else {
				$new_face_width = round($width / 1.5);
				$new_face_height = round($height / 1.5);
			}
		} else {
			$new_face_width = 75;
			$new_face_height = 75;
		}


				$thumb = imagecreatetruecolor($new_face_width,$new_face_height);
		imagecopyresampled($thumb,$user_src_im,0,0,0,0,$new_face_width,$new_face_height,$width,$height);

				$box = imagecreatefrompng('./images/kuang.png');
		imagecopyresampled($thumb,$box,0,0,0,0,$new_face_width,$new_face_height,80,80);

				$dst_x = 20;
		$dst_y  = 12;


		$src_x = 0;
		$src_y = 0;

		$alpha = 100;

				imagecopymerge($bg,$thumb,$dst_x,$dst_y,$src_x,$src_y,$new_face_width,$new_face_height,$alpha);


				$qmd_file_url = $GLOBALS['_J']['config']['qmd_file_url'];

		$image_path = $qmd_file_url. face_path($qmd_data['uid']);
		$image_file =  $image_path . $qmd_data['uid'] . '_o.gif';
		if(!is_dir($image_path)) {
			jio()->MakeDir($image_path);
		}

				if(function_exists('imagegif')) {
			imagegif($bg,$image_file);
		} elseif(function_exists('imagepng')) {
			imagepng($bg, $image_file);
		} elseif (function_exists('imagejpeg')) {
			imagejpeg($bg, $image_file);
		} else {
			return '';
		}

		imagedestroy($bg);
		imagedestroy($thumb);
		imagedestroy($box);
		if($v) {
			imagedestroy($v);
		}
		imagedestroy($user_src_im);

		jio()->DeleteFile($user_src);

		$site_url = '';
		if($GLOBALS['_J']['config']['ftp_on']) {
			$ftp_key = randgetftp();
			$get_ftps = jconf::get('ftp');
			$site_url = $get_ftps[$ftp_key]['attachurl'];

			$ftp_result = ftpcmd('upload', $image_file,'',$ftp_key);
			if($ftp_result > 0) {
				jio()->DeleteFile($image_file);

				$image_file = $site_url . '/' . $image_file;
			}
		}


		$sql = "update `".TABLE_PREFIX."members` set `qmd_url`='{$image_file}' where `uid`='".$qmd_data['uid']."' ";
		DB::query($sql);


		return $image_file;

	}

	
	function autoCheckMedal($medalid,$uid=0){
		$dateline = time();

		$uid = $uid ? (int) $uid : MEMBER_ID;
				$sql = "select * from `".TABLE_PREFIX."members` Where `uid`='$uid'";
		$members = DB::fetch_first($sql);

				$medalid = (int) $medalid;
		$sql = "select * from `".TABLE_PREFIX."medal` Where `id` = '{$medalid}'";
		$medal_list = DB::fetch_first($sql);
		$medal_value = @unserialize($medal_list['conditions']);

				if($medal_value['type'] == 'topic')
		{
						$sql ="select * from `".TABLE_PREFIX."topic` where `uid` = '".$uid."' and `type` = 'first' order by `dateline` desc limit 1";
			$topic_list = DB::fetch_first($sql);

			if($topic_list){
				$return = $this->_chackmdealday($topic_list['dateline'],$medal_value['day'],'first',$uid);
			} else{
				$return = '2';
			}

		}

				if($medal_value['type'] == 'reply')
		{
			$sql = "select * from `".TABLE_PREFIX."topic` Where `type` = 'reply' and `uid` = '".$uid."' order by 'dateline' desc limit 0,1";
			$reply_list = DB::fetch_first($sql);

			if($reply_list){
				$return = $this->_chackmdealday($reply_list['dateline'],$medal_value['day'],'reply',$uid);
			} else{
				$return = '2';
			}
		}

				if ($medal_value['type'] == 'invite') {
			if ($medal_value['invite'] > $members['invite_count']) {
				$return = 2;
			} else{
				$return =  1;
			}
		}

				if ($medal_value['type'] == 'fans') {
			if($medal_value['fans'] > $members['fans_count']) {
				$return = $medal_value['fans'] .'>'. $members['fans_count'];
			} else{
				$return =  1;
			}
		}

				if ($medal_value['type'] == 'sign') {
			$credits = $GLOBALS['_J']['config']['credits_filed'];
			if($medal_value['sign'] > $members[$credits]) {
				$return = 2;
			} else{
				$return =  1;
			}
		}

				if($medal_value['type'] == 'tag')
		{
			$tag = trim($medal_value['tagname']);

			$sql = "select `id`,`name` from `".TABLE_PREFIX."tag` Where `name` = '{$tag}' ";

			$tags=DB::fetch_first($sql);

			if($tags)
			{
				$sql = "select `item_id`,`tag_id` from `".TABLE_PREFIX."topic_tag` Where `tag_id` = '{$tags['id']}' ";
				$query = DB::query($sql);
				$topicids = array();
				while($row = DB::fetch($query))
				{
					$topicids[$row['item_id']] = $row['item_id'];
				}
			}
			if($topicids)
			{
				$sql = "select `tid`,`uid`,`content` from `".TABLE_PREFIX."topic` where `tid` in ('".implode("','",$topicids)."') and `uid` = '".$uid."' limit 0,1";

				$topiclist=DB::fetch_first($sql);
			}
			if($topiclist){
				$return = 1;
			}
			else{
				$return = '2';
			}

		}

				if($return == 1)
		{
			$return = $this->giveUserMedal($medalid,$members);
		}

		return $return;
	}

	
	function giveUserMedal($medalid,$members){
		$sql = "select * from `".TABLE_PREFIX."user_medal` where `medalid` = '{$medalid}' and `uid` = '".$members['uid']."' limit 0,1";

		$user_medal=DB::fetch_first($sql);
		if($user_medal)
		{
			return 3;
		}
				$sql = "insert into `".TABLE_PREFIX."user_medal` (`uid`,`nickname`,`medalid`,`dateline`) values ('{$members['uid']}','{$members['nickname']}','{$medalid}','".time()."')";
		DB::query($sql);

		if(!empty($members['medal_id']))
		{
						$sql = "select * from `".TABLE_PREFIX."user_medal` where `uid` = '{$members['uid']}'";
			$query = DB::query($sql);
			$user_medal_id = array();
			while (false != ($row = $query->GetRow())) {
				$user_medal_id[] = $row['medalid'];
			}

			$user_medal_id = implode(",",$user_medal_id);
		}

		$user_medal = $user_medal_id ? $user_medal_id : $medalid;

				$sql = "update `".TABLE_PREFIX."members` set  `medal_id`='{$user_medal}'  where `uid` = '".$members['uid']."'";
		DB::query($sql);

				$sql = "update `".TABLE_PREFIX."medal` set  `medal_count`=`medal_count`+1  where `id` = '{$medalid}'";
		DB::query($sql);
		return 1;
	}

		function _chackmdealday($date_time=0,$chackday=0,$check_type='',$uid=0){
		$uid = $uid ? $uid : MEMBER_ID;
		$endtime = $date_time ? $date_time : time();
		$topic_start_time = $endtime - (86400 * $chackday);

		$sql = "select `dateline`,`tid` from `".TABLE_PREFIX."topic` Where `dateline` >= '{$topic_start_time}' and `dateline` <= '{$endtime}' and `type` = '{$check_type}' and `uid` = '".$uid."' order by 'dateline' desc ";
		$query = DB::query($sql);
		$topic_date =array();
		while (false != ($row = $query->GetRow())){
			$topic_date[] = date("Ymd",$row['dateline']);
		}

		for ($j = 0; $j < count($topic_date); $j++){
			if($topic_date[$j] == $topic_date[$j+1]){
				unset($topic_date[$j+1]);
			}
		}

		$user_topic_date = array_unique($topic_date);
		$user_topic_date = implode(',',$user_topic_date);
		$user_topic_date = explode(',',$user_topic_date);
		sort($user_topic_date);

		if(count($user_topic_date) < $chackday){
			return 2;
		}

		if($chackday > 1){
			for($i=0; $i < count($user_topic_date) - 1  ; $i++){
				if($user_topic_date[$i] + 1 != $user_topic_date[$i+1]){
					return 2;
				}
			}
			return true;
		}

				elseif($user_topic_date){
			return true;
		}else{
			return 2;
		}
	}

	
	function getSignTag() {
		return jtable('sign_tag')->get_sign_tag();
	}

	
	function getLoginStatistics(){
		$cache_id = 'misc/login_statistics';
		if (false === ($login_list_r = cache_file('get', $cache_id))) {
			$login_list=array(
				't_r'=>array('name'=>'<b>今日注册</b>','time'=>'today','code'=>'regdate'),
				'y_r'=>array('name'=>'<b>昨日注册</b>','time'=>'yesterday','code'=>'regdate'),
				'w_r'=>array('name'=>'<b>一周注册</b>','time'=>'week','code'=>'regdate'),
				'm_r'=>array('name'=>'<b>一月注册</b>','time'=>'month','code'=>'regdate'),
				't_l'=>array('name'=>'<b>今日登录</b>','time'=>'today','code'=>'lastactivity'),
				'y_l'=>array('name'=>'<b>昨日登录</b>','time'=>'yesterday','code'=>'lastactivity'),
				'w_l'=>array('name'=>'<b>一周登录</b>','time'=>'week','code'=>'lastactivity'),
				'm_l'=>array('name'=>'<b>一月登录</b>','time'=>'month','code'=>'lastactivity'),
			);
			$login_list_r['data'] = $login_list;
			foreach ($login_list as $k => $v) {
				if ($v['time'] == 'today') $where = " {$v['code']} > '".mktime(0,0,0,date(m),date('d'),date('Y'))."' ";
				if ($v['time'] == 'yesterday') $where = " {$v['code']} < '".mktime(0,0,0,date(m),date('d'),date('Y'))."' and {$v['code']} > '".mktime(0,0,0,date(m),date('d')-1,date('Y'))."' ";
				if ($v['time'] == 'week') $where = " {$v['code']} > '" .  strtotime('-1 week') . "' ";
				if ($v['time'] == 'month') $where = " {$v['code']} > '" . strtotime('-1 month') . "' ";

				$login_list_r['data'][$k]['num'] = DB::result_first("  select count(*) from `".TABLE_PREFIX."members` where $where ");
			}
			$login_list_r['time'] = time();

			cache_file('set', $cache_id, $login_list_r, 3600);
		}
		return $login_list_r;
	}

	
	function getContentStatistics(){
       $cache_id = 'misc/content_statistics';
		if (false === ($content_list_r = cache_file('get', $cache_id))) {
			$content_list=array(
				't_t'=>array('name'=>'<b>今日发布</b>','time'=>'today','code'=>'dateline','url'=>'admin.php?mod=topic&code=topic_manage'),
				'y_t'=>array('name'=>'<b>昨日发布</b>','time'=>'yesterday','code'=>'dateline','url'=>'admin.php?mod=topic&code=topic_manage'),
				'w_t'=>array('name'=>'<b>一周发布</b>','time'=>'week','code'=>'dateline','url'=>'admin.php?mod=topic&code=topic_manage'),
				'm_t'=>array('name'=>'<b>一月发布</b>','time'=>'month','code'=>'dateline','url'=>'admin.php?mod=topic&code=topic_manage'),
				'first'=>array('name'=>'<b>原创发布</b>','type'=>'first','url'=>'admin.php?mod=topic&code=topic_manage'),
				'forward'=>array('name'=>'<b>仅转发</b>','type'=>'forward','url'=>'admin.php?mod=topic&code=topic_manage'),
				'reply'=>array('name'=>'<b>仅评论</b>','type'=>'reply','url'=>'admin.php?mod=topic&code=topic_manage'),
				'both'=>array('name'=>'<b>评论转发</b>','type'=>'both','url'=>'admin.php?mod=topic&code=topic_manage'),
			);
			$content_list_r['data'] = $content_list;
			foreach ($content_list as $k => $v) {
				if ($v['time'] == 'today') $where = " `{$v['code']}` > '".mktime(0,0,0,date(m),date('d'),date('Y'))."' ";
				elseif ($v['time'] == 'yesterday') $where = " `{$v['code']}` < '".mktime(0,0,0,date(m),date('d'),date('Y'))."' and `{$v['code']}` > '".mktime(0,0,0,date(m),date('d')-1,date('Y'))."' ";
				elseif ($v['time'] == 'week') $where = " `{$v['code']}` > '" .  strtotime('-1 week') . "' ";
				elseif ($v['time'] == 'month') $where = " `{$v['code']}` > '" . strtotime('-1 month') . "' ";
				elseif ($v['type']) $where = " `type` = '{$v['type']}' ";
                else  continue;
				$content_list_r['data'][$k]['num'] = DB::result_first("  select count(*) from `".TABLE_PREFIX."topic` where $where ");
                if($v['url']) $other_list_r['data'][$k]['num'] = "<a href='{$v['url']}'>".$other_list_r['data'][$k]['num']."</a>";
			}
			$content_list_r['time'] = time();

			cache_file('set', $cache_id, $content_list_r, 3600);
		}
		return $content_list_r;
	}

    
    function getVerifyStatistics() {
    	if(false === ($data_verify = cache_file('get', ($cache_id = 'misc/verify_statistics')))) {
    		$options = array(
	    		array('name'=>'微博', 'table'=>'topic_verify', 'where'=>'`managetype`="0"', 'url'=>'admin.php?mod=topic&code=verify'),
	    		array('name'=>'活动', 'table'=>'event', 'where'=>'`verify`="0"', 'url'=>'admin.php?mod=event&code=verify'),
	    		array('name'=>'投票', 'table'=>'vote', 'where'=>'`verify`="0"', 'url'=>'admin.php?mod=vote&code=verify'),
	    		array('name'=>'头像签名', 'table'=>'members_verify', 'url'=>'admin.php?mod=verify'),
	    		array('name'=>'举报', 'table'=>'report', 'url'=>'admin.php?mod=report'),
	    		array('name'=>'V认证', 'table'=>'validate_category_fields', 'where'=>'`is_audit`="0"', 'url'=>'admin.php?mod=vipintro'),
	    		array('name'=>'待审会员', 'table'=>'members', 'where'=>'`role_id`="5"', 'url'=>'admin.php?mod=member&code=waitvalidate'),
	    	);
	    	$data_verify = $data = array();
	    	foreach($options as $row) {
	    		$count = DB::result_first('SELECT COUNT(1) FROM ' . DB::table($row['table']) . ($row['where'] ? ' WHERE ' . $row['where'] : ''));
	    		$data['name'][] = $row['name'];
	    		$data['count'][] = "<a href='{$row['url']}'>{$count}</a>";
	    	}
	    	$data_verify['data'] = $data;
	    	$data_verify['time'] = TIMESTAMP;
	    	cache_file('set', $cache_id, $data_verify, 600);
    	}
    	return $data_verify;
    }

    
    function getAppStatistics(){
       $cache_id = 'misc/app_statistics';
		if (false === ($app_list_r = cache_file('get', $cache_id))) {
			$app_list=array(
				'topic'=>array('name'=>'<b>微博数量</b>','table'=>'topic','url'=>'admin.php?mod=topic&code=topic_manage'),
				'event'=>array('name'=>'<b>活动数量</b>','table'=>'event','url'=>'admin.php?mod=event&code=manage'),
				'vote'=>array('name'=>'<b>投票数量</b>','table'=>'vote','url'=>'admin.php?mod=vote&code=index'),
				'qun'=>array('name'=>'<b>微群数量</b>','table'=>'qun','url'=>'admin.php?mod=qun&code=manage'),
				'image'=>array('name'=>'<b>图片数量</b>','table'=>'topic_image'),
				'attach'=>array('name'=>'<b>附件数</b>','table'=>'topic_attach'),
				'video'=>array('name'=>'<b>视频数量</b>','table'=>'topic_video'),
				'music'=>array('name'=>'<b>音乐数量</b>','table'=>'topic_music'),
			);
			$app_list_r['data'] = $app_list;
			foreach ($app_list as $k => $v) {
				$app_list_r['data'][$k]['num'] = DB::result_first("  select count(*) from `".TABLE_PREFIX."{$v['table']}` ");

                if($v['url']) $other_list_r['data'][$k]['num'] = "<a href='{$v['url']}'>".$other_list_r['data'][$k]['num']."</a>";
			}
			$app_list_r['time'] = time();
			cache_file('set', $cache_id, $app_list_r, 1800);
		}
		return $app_list_r;
    }

    
    function getOtherStatistics(){
		$other_list=array(
			'member'=>array('name'=>'<b>会员总数</b>','table'=>'members','url'=>'admin.php?mod=member&code=newm'),
			'sessions'=>array('name'=>'<b>在线人数</b>','table'=>'sessions','url'=>'admin.php?mod=sessions'),
			'tag'=>array('name'=>'<b>话题数量</b>','table'=>'tag','url'=>'admin.php?mod=tag&code=list'),
			'attach'=>array('name'=>'<b>附件大小</b>','table'=>'topic_attach','code'=>'filesize'),
			'image'=>array('name'=>'<b>图片大小</b>','table'=>'topic_image','code'=>'filesize'),
						            'database'=>array('name'=>'<b>数据库大小</b>','database'=>1,'url'=>'admin.php?mod=db&code=optimize','title'=>"点击优化"),
		);
       $cache_id = 'misc/other_statistics';
		if (false === ($other_list_r = cache_file('get', $cache_id))) {
			$other_list_r['data'] = $other_list;
			foreach ($other_list as $k => $v) {
                if ($v['table'] && !$v['code']) {
                    $other_list_r['data'][$k]['num'] = DB::result_first("  select count(*) from `".TABLE_PREFIX."{$v['table']}` ");
                } else if ($v['table'] && $v['code']) {
                    $other_list_r['data'][$k]['num'] = DB::result_first("  select sum(`{$v['code']}`) from `".TABLE_PREFIX."{$v['table']}` ");
                    $other_list_r['data'][$k]['num'] =  jio()->SizeConvert($other_list_r['data'][$k]['num']);
                } else if ($v['database']){
                                        $cache_id1 = "misc/data_length";
                    if (false === ($data_length = cache_file('get', $cache_id1))) {
                        $sys_config = jconf::get();
                        $sql="show table status from `{$sys_config['db_name']}` like '".TABLE_PREFIX."%'";
                        $query = DB::query($sql,"SKIP_ERROR");
                        $data_length=0;
                        while ($row = DB::fetch($query))
                        {
                            $data_length+=$row['Data_length']+$row['Index_length'];
                        }
                        if($data_length>0)
                        {

                            $data_length=jio()->SizeConvert($data_length);
                        }

                        cache_file('set', $cache_id1, $data_length, 3600);
                    }

                    $other_list_r['data'][$k]['num'] = $data_length;
                }
                if($v['title']){
                    $title = "title='{$v['title']}'";
                }
                if($v['url']) $other_list_r['data'][$k]['num'] = "<a href='{$v['url']}' $title>".$other_list_r['data'][$k]['num']."</a>";
			}
			$other_list_r['time'] = time();
			cache_file('set', $cache_id, $other_list_r, 3600);
		}
		return $other_list_r;
    }

    
    function getUserStatistics(){
        $cache_id = 'misc/user_statistics';
        if(false === ($user_list_r = cache_file('get',$cache_id))){
	        $user_list = array(
	            'sina'=>array('name'=>'<b>新浪用户</b>','table'=>'xwb_bind_info'),
	            'qq'=>array('name'=>'<b>腾讯用户</b>','table'=>'qqwb_bind_info'),
	            'renren'=>array('name'=>'<b>人人网用户</b>','table'=>'renren_bind_info'),
	            'kaixin'=>array('name'=>'<b>开心网用户</b>','table'=>'kaixin_bind_info'),
	            'yy'=>array('name'=>'<b>YY用户</b>','table'=>'yy_bind_info'),
	            'tel'=>array('name'=>'<b>手机用户</b>','table'=>'sms_client_user'),
	            'buddys'=>array('name'=>'<b>关注总数</b>','table'=>'buddys'),
	        );
            $user_list_r['data'] = $user_list;
            foreach ($user_list as $k => $v) {
                if ($v['table']) {
                    $user_list_r['data'][$k]['num'] = DB::result_first('select count(*) from `'.TABLE_PREFIX.$v['table'].'`');
                }

                 if($v['url']) $other_list_r['data'][$k]['num'] = "<a href='{$v['url']}'>".$other_list_r['data'][$k]['num']."</a>";
            }
            $user_list_r['time'] = time();
            cache_file('set',$cache_id,$user_list_r,3600);
        }

        return $user_list_r;
    }

    
    function getRoleStatistics(){
        $cache_id = 'misc/role_statistics';
        if(false === ($role_list_r = cache_file('get', $cache_id))){
            #取得所有的角色类型
            $role_list = array();
            $sql = "select `id`,`name` from `".TABLE_PREFIX."role` ";
            $query = DB::query($sql);
            while ($rs = DB::fetch($query)) {
                $role_list[$rs['id']] = $rs['name'];
            }
            $role_list_r = array();
            if($role_list) {
            	$sql = "select `role_id`,count(*) as num from `".TABLE_PREFIX."members` GROUP BY `role_id` ";
	            $query = DB::query($sql);
	            while ($rs = DB::fetch($query)) {
	            	$id = $rs['role_id'];
	            	if ($id > 0 && $role_list[$id]) {
		                $role_list_r['data'][$rs['role_id']]['name'] = "<b>".$role_list[$id]."</b>";
		                $role_list_r['data'][$rs['role_id']]['num'] = '<a href="admin.php?mod=member&code=dosearch&role_ids=' . $id . '">' . $rs['num'] . '</a>';
	            	}
	            }
	            $role_list_r['time'] = TIMESTAMP;
            }
            cache_file('set',$cache_id,$role_list_r,3600);
        }

        return $role_list_r;
    }

	function _entities($string, $iconv = 1) {
		if($iconv) {
			$string = array_iconv($GLOBALS['_J']['charset'], 'UTF-8', $string);
		}
	    $len = strlen($string);
	    $buf = "";
	    for($i = 0; $i < $len; $i++){
	        if (ord($string[$i]) <= 127){
	            $buf .= $string[$i];
	        } else if (ord ($string[$i]) <192){
	            	            $buf .= "&#xfffd";
	        } else if (ord ($string[$i]) <224){
	            	            $buf .= sprintf("&#%d;",
	                ((ord($string[$i + 0]) & 31) << 6) +
	                (ord($string[$i + 1]) & 63)
	            );
	            $i += 1;
	        } else if (ord ($string[$i]) <240){
	            	            $buf .= sprintf("&#%d;",
	                ((ord($string[$i + 0]) & 15) << 12) +
	                ((ord($string[$i + 1]) & 63) << 6) +
	                (ord($string[$i + 2]) & 63)
	            );
	            $i += 2;
	        } else {
	            	            $buf .= sprintf("&#%d;",
	                ((ord($string[$i + 0]) & 7) << 18) +
	                ((ord($string[$i + 1]) & 63) << 12) +
	                ((ord($string[$i + 2]) & 63) << 6) +
	                (ord($string[$i + 3]) & 63)
	            );
	            $i += 3;
	        }
	    }
	    return $buf;
	}

}
?>