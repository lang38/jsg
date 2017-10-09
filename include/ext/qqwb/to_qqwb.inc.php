<?php
if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

			if(false == ($qqwb_config = qqwb_init())) {
				return ;
			}

			$tid = (is_numeric($tid) ? $tid : 0);
            if($tid < 1)
            {
                return ;
            }

            $uid = (is_numeric($uid) ? $uid : 0);
            if($uid < 1)
            {
                return ;
            }

            $topic = DB::fetch_first("select * from ".DB::table('topic')." where `tid`='$tid'");
            if(!$topic) return ;
            if('qqwb' == $topic['from']) return ;

            //对表情进行替换
            if(false!==strpos($content,'['))
            {
                $face_config = jconf::get('face');

                if(false===strpos($content,'#['))
                {
                    if (preg_match_all('~\[(.+?)\]~', $content, $match))
                    {
                        foreach($match[1] as $k=>$v)
                        {
                            if(isset($face_config[$v]))
                            {
                                $content = str_replace($match[0][$k], '/' . $v, $content);
                            }
                        }
                    }
                }
            }
            $content = array_iconv($GLOBALS['_J']['config']['charset'],'UTF-8',trim(strip_tags($content)));
            if(!$content)
            {
                return ;
            }
            $content .= ' ' . get_full_url($GLOBALS['_J']['config']['site_url'],'index.php?mod=topic&code=' . $tid);


            $qqwb_bind_topic = DB::fetch_first("select * from ".TABLE_PREFIX."qqwb_bind_topic where `tid`='$tid'");
            if($qqwb_bind_topic)
            {
                return ;
            }

            $qqwb_bind_info = DB::fetch_first("select * from ".TABLE_PREFIX."qqwb_bind_info where `uid`='$uid'");
            if(!$qqwb_bind_info)
            {
                return ;
            }

            if(!$qqwb_bind_info['qqwb_username'] || !$qqwb_bind_info['access_token'] || !$qqwb_bind_info['openid'])
            {
                return ;
            }
            
            $QQAuth = qqwb_oauth($qqwb_bind_info['access_token'],$qqwb_bind_info['openid']);
            
            $t_result = array();
            if($totid < 1)
            {
                //视频、音乐待添加

            	$imageid = (int) $imageid; //取出第一个图片ID
                if($imageid > 0 && ($topic_image = jlogic('image')->get_info($imageid))) {
					$tpic = topic_image($imageid, 'original', 1);
					if($GLOBALS['_J']['config']['ftp_on']) {
						$tpic = RELATIVE_ROOT_PATH . 'data/cache/temp_images/topic/' . $tpic;
						if(!is_file($tpic)) {
							$ppic = topic_image($imageid, 'original', 0);
							if(false !== strpos($ppic, ':/'.'/')) {
								$temp_image = dfopen($ppic, 99999999, '', '', true, 3, $_SERVER['HTTP_USER_AGENT']);
								if(!$temp_image) {
									jio()->MakeDir(dirname($tpic));
									jio()->WriteFile($tpic, $temp_image);
								}
							}
						}
					}
					if(is_file($tpic) &&
						$ps = getimagesize($tpic) &&
						$p_data = file_get_contents($tpic)) {
							$p_name = basename($topic_image['name'] ? $topic_image['name'] : $tpic);
							if(!$p_name) {
								$p_name = mt_rand();
							}
							$p_name = array_iconv($GLOBALS['_J']['config']['charset'],'UTF-8',$p_name);
							$pic = array($ps['mime'],$p_name,$p_data);
							$t_result = $QQAuth->tAddPic($content,$pic);
					} else {
						$t_result = $QQAuth->tAdd($content);
					}
                }
                else
                {
                    $t_result = $QQAuth->tAdd($content);
                }
            }
            else
            {
                $reid = DB::result_first("select `qqwb_id` from ".TABLE_PREFIX."qqwb_bind_topic where `tid`='$totid'");
                if($reid < 1)
                {
                    return ;
                }

                $t_result = $QQAuth->tReply($reid,$content);
            }
			if($t_result['errcode']) {
				if(jget('debug')) {
            		debug($t_result);
            	}
            	jlog('to_qqwb.inc', $t_result);
			}
            $qqwb_id = (($t_result['data']['id'] && is_numeric($t_result['data']['id'])) ? $t_result['data']['id'] : 0);
            if($qqwb_id > 0)
            {
                $return = DB::query("replace into ".TABLE_PREFIX."qqwb_bind_topic (`tid`,`qqwb_id`) values ('$tid','$qqwb_id')");
            }

?>