<?php
/**
 *
 * 用户相关的数据库逻辑操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: user.logic.php 5462 2014-01-18 01:12:59Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

/**
 *
 * 用户相关的数据库逻辑操作类
 *
 * @author 狐狸<foxis@qq.com>
 *
 */
class UserLogic {


	function UserLogic() {
		;
	}

	function face($p = array(), $modify = 1) {
		global $_J;

		$pic_file = (($p['pic_file'] && is_image($p['pic_file'])) ? $p['pic_file'] : '');
		$pic_url = (($p['pic_url'] && false!==strpos($p['pic_url'], ':/'.'/')) ? $p['pic_url'] : '');
    	$p['pic_field'] = ($p['pic_field'] ? $p['pic_field'] : 'face');
    	$pic_field = (($p['pic_field'] && $_FILES[$p['pic_field']]) ? $p['pic_field'] : '');
    	if(!$pic_file && !$pic_url && !$pic_field) {
    		return jerror('pic is empty', 0);
    	}

		$uid = ($p['uid'] ? (int) $p['uid'] : MEMBER_ID);
		if($uid < 1) {
			return jerror('请指定一个用户ID', -1);
		}

		$member = jsg_member_info($uid);
		if(!$member) {
			return jerror('用户已经不存在了', -2);
		}

		if(!$_J['config']['edit_face_enable'] && $member['__face__'] && 'admin' != MEMBER_ROLE_TYPE) {
			return jerror('不允许用户修改头像', -3);
		}

		$src_x = max(0,(int) $p['x']);
        $src_y = max(0,(int) $p['y']);
        $src_w = max(0,(int) $p['w']);
        $src_h = max(0,(int) $p['h']);

        $image_path = RELATIVE_ROOT_PATH . 'images/'.($_J['config']['face_verify'] ? 'face_verify' : 'face').'/' . face_path($uid);
        $image_name = $uid . '_b.jpg';
        $image_file = $image_path . $image_name;
        $image_file_small = $image_path . $uid . '_s.jpg';
        $image_file_temp = $image_path . $uid . '_t.jpg';
        if(!is_dir($image_path)) {
        	jmkdir($image_path);
        }

        if(!$modify && is_image($image_file)) {
        	return jerror('头像已经存在了', -4);
        }

        if($pic_file) {
        	$src_file = $pic_file;
        } elseif ($pic_url) {
        	$image_data = dfopen($pic_url, 99999999, '', '', true, 3, $_SERVER['HTTP_USER_AGENT']);
        	if($image_data) {
        		jio()->WriteFile($image_file, $image_data);
        		if(is_image($image_file)) {
        			$src_file = $image_file;
        		}
        	}
        } elseif ($pic_field) {

			jupload()->init($image_path,$pic_field,true,false);

			jupload()->setNewName($image_name);
			$result = jupload()->doUpload();
			if($result && is_image($image_file)) {
				$src_file = $image_file;
			}
        }

        if(!is_image($src_file)) {
        	return jerror('源头像不存在了，请上传正确的图片文件', -5);
        }

        
        $w = max(50,min(128,($src_w > 50 ? $src_w : 200)));
        $make_result = makethumb($src_file,$image_file,$w,$w,0,0,$src_x,$src_y,$src_w,$src_h);

        
        $make_result = makethumb($src_file,$image_file_small,50,50,0,0,$src_x,$src_y,$src_w,$src_h);

        
        $face_url = '';
        if($_J['config']['ftp_on']) {
            $ftp_key = randgetftp();
			$get_ftps = jconf::get('ftp');
            $face_url = $get_ftps[$ftp_key]['attachurl'];
            $ftp_result = ftpcmd('upload',$image_file,'',$ftp_key);
            if($ftp_result > 0) {
                ftpcmd('upload',$image_file_small,'',$ftp_key);

                jio()->DeleteFile($image_file);
                jio()->DeleteFile($image_file_small);
            }
        }

        if($_J['config']['face_verify']) {
	        
	        $count = DB::result_first("SELECT COUNT(1) FROM ".DB::table('members_verify')." WHERE `uid`='$uid'");
	        if($count){
		        $sql = "update `".TABLE_PREFIX."members_verify` set `face_url`='{$face_url}', `face`='{$image_file_small}' where `uid`='$uid'";
	        }else{
	        	$sql = "insert into `".TABLE_PREFIX."members_verify` (`uid`,`nickname`,`face_url`,`face`) values('$uid','{$member['nickname']}','{$face_url}','{$image_file_small}')";
	        }
	        DB::query($sql);

	        
        	if($_J['config']['notice_to_admin']) {
				$pm_post = array(
					'message' => $member['nickname']." 修改了头像进入审核，<a href='admin.php?mod=verify&code=fs_verify' target='_blank'>点击</a>进入审核。",
					'to_user' => str_replace('|',',',$_J['config']['notice_to_admin']),
				);
								$admin_info = jsg_member_info(1);
				jlogic('pm')->pmSend($pm_post,$admin_info['uid'],$admin_info['username'],$admin_info['nickname']);
			}
        } else {
	        
	        $sql = "update `".TABLE_PREFIX."members` set `face_url`='{$face_url}', `face`='{$image_file_small}' where `uid`='$uid'";
			DB::query($sql);

	        
	        if($_J['config']['extcredits_enable'] && $uid > 0) {
				
				update_credits_by_action('face', $uid);
			}
        }
        return true;
	}

    
    function getChannelUserTop(){
                $channelList = jlogic('channel')->mychannel(MEMBER_ID);
        $channel = array();
        if($channelList){
            foreach ($channelList as $k => $v) {
                $channel[$k] = $v['ch_id'];
            }
        }
        $uid = jlogic('channel')->getChannelUser($channel);

        if($uid){
            $members = jlogic('topic')->GetMember(" WHERE `uid` in (".jimplode($uid).") ORDER BY `fans_count` DESC limit 10 ","`uid`,`ucuid`,`username`,`validate`,`validate_category`,`face`,`nickname`");
        }

        return $members;
    }

    
    function getDigUser(){
        $dateline = time() - 7*24*3600;
        $sql = "select `uid`,count(*) as num FROM `".DB::table('topic_dig')."` WHERE `dateline` > '$dateline' GROUP BY uid ORDER BY num DESC , dateline DESC  ";

        $query = DB::query($sql);
        $uid = array();
        while ($rs = DB::fetch($query)) {
            $uid[$rs['uid']] = $rs['uid'];
        }

        if($uid){
            $members = jlogic('topic')->GetMember(" WHERE `uid` in (".jimplode($uid).") ORDER BY `fans_count` DESC limit 10 ","`uid`,`ucuid`,`username`,`validate`,`validate_category`,`face`,`nickname`");
        }
        return $members;
    }
}

?>