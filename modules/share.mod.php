<?php

/**
 * 站外调用
 *
 * @author 狐狸<foxis@qq.com>
 * @package jishigou.net
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

		$this->CacheConfig = jconf::get('cache');


		$this->Execute();
	}


	

	function Execute()
	{
		switch($this->Code)
		{
			case 'share':
				$this->ShareLink();
				break;
			case 'show':
				$this->Show();
				break;
			case 'doshare':
				$this->DoShareLink();
				break;
			case 'endshare':
				$this->EndShare();
				break;
			case 'doshare':
				$this->DoShareLink();
				break;
			case 'recommend':
				$this->iframe_recommend();
				break;
            case 'upload':
                $this->upload();
                break;
			default:
				$this->Main();
				break;
		}

		exit;
	}

		function Main()
	{

		die('建设中。。。');

	}

		function ShareLink()
	{

		$action = 'index.php?mod=share&code=doshare';

		$url     = $this->Get['url'];
		$sbuject = array_iconv('utf-8',$this->Config['charset'],$this->Get['t']);

		$content = $sbuject.' '.$url;

		$return_url = $_SERVER["QUERY_STRING"];

		$this->Title = "分享到" . $this->Config['site_name'];

		include  template('social/share');

	}

		function DoShareLink()
	{
		$action = 'index.php?mod=share&code=doshare';


		        $content = trim(strip_tags((string) $this->Post['content']));

        		$f_rets = filter($content);
		if($f_rets && $f_rets['error']){
          $filter_msg = str_replace("\'",'',$f_rets['msg']);
        }

	 	        $content_length = strlen($content);
        if ($content_length < 2){
            $filter_msg =  "内容不允许为空";
        }

        		$return = $this->TopicLogic->Add($content);

		if(is_array($return))
		{
			$this->Messager(NULL,"{$this->Config['site_url']}/index.php?mod=share&code=endshare");
		}
		else
		{
						$content = trim(strip_tags((string) $this->Post['return_content']));
						$error = $return ? $return : $filter_msg;
			include  template('share');
		}
	}

		function EndShare() {

		include  template('share');
	}


		function iframe_recommend() {
		$ids = (int) (jget('ids') ? jget('ids') : jget('id'));
		if($ids < 1) {
			exit('ids is empty');
		}


				$sql = "select * from `".TABLE_PREFIX."share` where `id` = '{$ids}' ";
    	$query = $this->DatabaseHandler->Query($sql);
    	$sharelist = $query->GetRow();
    	if(!$sharelist) {
    		exit('ids is invalid');
    	}


    			$share = @unserialize($sharelist['show_style']);
				$topic_charset = $share['topic_charset'];
				$share['limit'] = max(0, (int) $share['limit']);
		if($share['limit'] < 1 || $share['limit'] > 200) {
			$share['limit'] = 20;
		}
				$share['string'] = max(0, (int) $share['string']);

		$order = ' `dateline` DESC ';
		$condition = '';
		$rets = $topic_list = array();
		
		if('live' == $sharelist['type']) {
			            			$rr = jlogic('live')->get_script_out_list(1,$share['limit'],$share['string']);
			$rets = $live_list = $rr;
		} else {
			
			$user_topic_list = '';
			if('topic' == $sharelist['type']) {
				$nickname = '';
				if($sharelist['nickname']) {
					$nickname = $sharelist['nickname'];
				} else {
					$nickname = get_safe_code(jget('nickname'));
				}
				if($nickname) {
					$ns = explode('|', $nickname);
					$uids = jtable('members')->get_ids(array(
						'nickname' => $ns,
						'result_count' => count($ns),
					), 'uid');
					if(!$uids) {
						exit('nickname is invalid');
					}
					$user_topic_list = " `uid` IN (" . jimplode($uids) . ") ";
				}
			}

			
			$tag_condition = '';
			if('tag' == $sharelist['type']) {
				$tag = '';
				if($sharelist['tag']) {
					$tag = $sharelist['tag'];
				} else {
					$tag = get_safe_code(jget('tag'));
				}
				if($tag) {
					$ts = explode('|', $tag);
					$tagids = jtable('tag')->get_ids(array(
						'name' => $ts,
						'result_count' => count($ts),
					), 'id');
					if(!$tagids) {
						exit('tag is invalid');
					}
					$tids = jtable('topic_tag')->get_ids(array(
						'tag_id' => $tagids,
						'result_count' => $share['limit'],
						'sql_order' => ' `dateline` DESC ',
					), 'item_id');
					if($tids) {
						$tag_condition = " `tid` IN (" . jimplode($tids) . ") ";
						$topic_list = jlogic('topic')->Get($tids);
					}
				}
			}

			
			$channel_condition = '';
			if('channel' == $sharelist['type']) {
				$cids = array();
				if($share['channel']['name']) {
					$cname = $share['channel']['name'];
				} else {
					$channel_id = jget('channel_id', 'int');
					if($channel_id > 0) {
						$cids[$channel_id] = $channel_id;
					} else {
						$cname = (jget('channel') ? jget('channel') : (jget('channel_name') ? jget('channel_name') : jget('ch_name')));
					}
				}
				if($cname) {
					$ns = explode('|', $cname);
					$cids = jtable('channel')->get_ids(array(
						'ch_name' => $ns,
						'result_count' => count($ns),
					), 'ch_id');
					if(!$cids) {
						exit('channel is invalid');
					}
				}
				if($cids) {
					$condition = " `item`='channel' AND `item_id` IN(" . jimplode($cids) . ") ";
				}
			}

			
			if('recommend' == $sharelist['type']) {
				$p = array(
					'sql_order' => ' `dateline` DESC ',
					'result_count' => $share['limit'],
				);
				$its = array();
				$item = $share['recommend']['item'];
				if($item) {
					$its = $share['recommend']['item_id'];
				} else {
					$item = jget('item', 'txt');
					$its = jget('item_id', 'txt');
				}
				if(in_array($item, array('qun', 'channel')) && $its) {
					${$item_id . '_id'} = $its;
					$p['item_id'] = explode('|', $its);
					$p['item'] = $item;
				}
				$tids = jtable('topic_recommend')->get_ids($p, 'tid');
				if($tids) {
					$topic_list = jlogic('topic')->Get($tids);
				}
                foreach ($topic_list as $key_one=>$topic_one){
                    $sql = "select r_title from `".TABLE_PREFIX."topic_recommend` where tid = ".$topic_one['tid'];
                    $topic_list[$key_one]['recd_title'] = DB::result_first($sql);
                }
			}

			
			if('live_weibo' == $sharelist['type']) {
				$live_id = (int) $share['live_weibo']['item_id'];
				$live_item = $share['live_weibo']['item'];
				if($live_id > 0) {
					$live_info = jlogic('live')->id2liveinfo($live_id);
					$live_uids = array();
					foreach($live_info['all'] as $k=>$v) {
						$live_uids[$k] = $k;
					}
					$p = array(
						'limit' => $share['limit'],
					);
					if('live_all' == $live_item) {
						;
					} elseif ('live_other' == $live_item) {
						$p['where'] = ' `uid` NOT IN (' . jimplode($live_uids) . ') ';
					} else {
						$p['where'] = " `uid` IN (" . jimplode($live_uids) . ") ";
					}
					jfunc('app');
					$rets = app_get_topic_list('live', $live_id, $p);
					$topic_list = $rets['list'];
				}
			}


			if($tag_condition) {
				$condition = " {$tag_condition} AND `type` = 'first' ";
			} elseif($user_topic_list) {
				$condition = " {$user_topic_list} AND `type` = 'first' ";
			}
			if(!$condition) {
								$condition = " `type` = 'first' ";
			}
						$gorder = jget('order');

			$where = " WHERE {$condition} ORDER BY {$order} LIMIT {$share['limit']} ";
			if(!$topic_list) {
				$topic_list = $this->TopicLogic->Get($where);
			}
			if($topic_list) {
				                if(!$share['hold_tags']){                    foreach($topic_list as $k=>$v) {
                        $topic_list[$k]['content'] = stripslashes($topic_list[$k]['content']);
                        $topic_list[$k]['content'] = strip_tags($topic_list[$k]['content']);
                        if($share['string'] > 0) {
                            $topic_list[$k]['content'] = cut_str($topic_list[$k]['content'], $share['string']);
                            $topic_list[$k]['recd_title'] = cut_str($topic_list[$k]['recd_title'], $share['string']);
                        }
                    }
                }
				
			}
			$rets = $topic_list;
		}

		$output = jget('output');
		if('json' == $output) {
			$rets = array_values($rets);
			$rets = array_iconv($this->Config['charset'], 'utf-8', $rets);
			$rets_json = json_encode($rets);
			$jsoncallback = jget('jsoncallback') ? jget('jsoncallback') : jget('callback');
			if($jsoncallback) {
				echo $jsoncallback . '(' . $rets_json . ')';
			} else {
				echo $rets_json;
			}
		} else {
			ob_start();

			include  template('share/sharetemp_'.$ids);

			$content = ob_get_contents();
			ob_clean();

						$content = str_replace(array("\r\n", "\n", "\r"), "", $content);
			$content = str_replace("'","\'",$content);
			$content = str_replace("/http", "http", $content);
			$content = str_replace("index.php?",$this->Config['site_url'].'/index.php?',$content);
			$content = str_replace($this->Config['site_url']."/".$this->Config['site_url'],$this->Config['site_url'],$content);
			#开启伪静态以后加网址
			$content = str_replace('href="/','href="'.$this->Config['site_url'].'/',$content);

			$content = str_replace('target="_blank"',"",$content);
			$content = str_replace('<a ', '<a target="_blank" ',$content);

			$content = preg_replace(array("/[\r\n]+/s","/\>\s+\</","/\>\s+/","/\s+\</"),array("","><",">","<"),$content);

						if(strtoupper($this->Config['charset']) != strtoupper($share['topic_charset'])) {
		        				@header('Content-Type: text/html; charset=' . $share['topic_charset']);

				$content  = array_iconv($this->Config['charset'],$share['topic_charset'],$content);
			}

			if('iframe' == $output) {
				echo $content;
			} else {
				echo "document.write('{$content}');";
			}
		}
		exit;
	}

    
    public function upload(){
        $item_id = jget('item_id');
        $item = jget('item');
        include  template('share/upload');

    }

}
?>
