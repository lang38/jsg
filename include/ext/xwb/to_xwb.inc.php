<?php
if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}


			$_POST['syn_to_sina'] = 1;
            $sina = jconf::get('sina');
            if($sina['oauth2_enable']) {
            	$uid = (int) $data['uid'];
            	if($uid < 1) return ;
            	$tid = (int) $data['tid'];
            	if($tid < 1) return ;
            	$content = array_iconv($GLOBALS['_J']['charset'], 'utf-8', trim(strip_tags($data['content'])));
            	if(!$content) return ;
            	$totid = (int) $data['totid'];
            	$imageid = (int) $data['imageid'];

            	$topic = jtable('topic')->row($tid);
            	if(!$topic) return ;
            	if('sina' == $topic['from']) return ;

            	//内容后附加链接地址
            	$link = get_full_url($GLOBALS['_J']['config']['site_url'], 'index.php?mod=topic&code=' . $tid);
				$length = 140 - ceil(strlen( urlencode($link) ) * 0.5) ;   //2个字母为1个字
				$content = sina_weibo_substr($content, $length);
            	$content .= ' ' . $link;

            	$xwb_bind_info = DB::fetch_first("select * from ".DB::table('xwb_bind_info')." where `uid`='$uid'");
            	if(!$xwb_bind_info) return ;

            	$xwb_bind_topic = DB::fetch_first("select * from ".DB::table('xwb_bind_topic')." where `tid`='$tid'");
            	if($xwb_bind_topic) return ;

            	DB::query("insert into ".DB::table('xwb_bind_topic')." (`tid`) values ('$tid')");

            	$p = array(
            		'access_token' => $xwb_bind_info['access_token'],
            	);
            	$rets = array();
            	if($totid < 1) {
            		$p['status'] = $content;
            		if($imageid > 0) {
            			/*
            			//高级接口，需要额外的申请
            			$p['url'] = topic_image($imageid, 'original', 0);
            			$rets = sina_weibo_api('2/statuses/upload_url_text', $p);
            			*/
            			$p['pic'] = topic_image($imageid, 'original', 1);
            			if($GLOBALS['_J']['config']['ftp_on']) {
            				$p['pic'] = RELATIVE_ROOT_PATH . 'data/cache/temp_images/topic/' . $p['pic'];
            				if(!is_file($p['pic'])) {
            					$ppic = topic_image($imageid, 'original', 0);
            					if(false !== strpos($ppic, ':/'.'/')) {
	            					$temp_image = dfopen($ppic, 99999999, '', '', true, 3, $_SERVER['HTTP_USER_AGENT']);
	            					if(!$temp_image) {
	            						jio()->MakeDir(dirname($p['pic']));
	            						jio()->WriteFile($p['pic'], $temp_image);
	            					}
            					}
            				}
            			}
            			if(is_image($p['pic'])) {
            				$rets = sina_weibo_api('2/statuses/upload', $p, 'POST', null, 1);
            			} else {
            				unset($p['pic']);
            				$rets = sina_weibo_api('2/statuses/update', $p);
            			}
            		} else {
            			$rets = sina_weibo_api('2/statuses/update', $p);
            		}
            	} else {
            		if(false == ($xbt = DB::fetch_first("select * from ".DB::table('xwb_bind_topic')." where `tid`='$totid'"))) {
            			return ;
            		}
            		if('sina' == $xbt['from']) {
            			return ;
            		}
            		if($xbt['mid'] < 1) {
            			return ;
            		}
            		$p['id'] = $xbt['mid'];
            		if(in_array($topic['type'], array('both', 'forward'))) {
            			$p['status'] = $content;
            			$p['is_comment'] = ('both' == $topic['type']) ? 1 : 0;
            			$rets = sina_weibo_api('2/statuses/repost', $p);
            		} else {
            			$p['comment'] = $content;
            			$rets = sina_weibo_api('2/comments/create', $p);
            		}
            	}
            	if($rets['error'] && $rets['error_code']) {
            		if(jget('debug')) {
            			debug($rets);
            		}
            		jlog('to_xwb.inc', $rets);
            	}
            	$mid = ($rets['idstr'] ? $rets['idstr'] : $rets['id']);
            	if($mid > 0) {
            		DB::query("replace into ".DB::table('xwb_bind_topic')." (`tid`, `mid`) values ('$tid', '$mid')");
            	}
            } else {
	            $GLOBALS['jsg_tid'] = $data['tid'];
	            $GLOBALS['jsg_totid'] = $data['totid'];
	            $GLOBALS['jsg_message'] = $data['content'];
	            $GLOBALS['jsg_imageid'] = $data['imageid'];

	            require_once(ROOT_PATH . 'include/ext/xwb/sina.php');
	            require_once(XWB_plugin::hackFile('newtopic'));
            }

?>