<?php
/**
 * 文件名：reminded.mod.php
 * @version $Id: reminded.mod.php 5159 2013-12-04 01:25:19Z wuliyong $
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 新消息提醒模块
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

		$this->Execute();
	}

	function Execute()
	{
        ob_start();

		switch($this->Code)
		{
            case 'show':
                $this->ShowReminded();
                break;
			 case 'recommend':
                $this->ShowRecommend();
                break;
			case 'modacttime':
                $this->modacttime();
                break;

			default:
				$this->Main();
				break;
		}

        response_text(ob_get_clean());
	}

	function Main()
    {
        response_text('正在建设中……');
    }

    	function ShowReminded()
	{
        if((int) $this->Config['ajax_topic_time'] < 1) {
            exit;
        }

    	$uid = max(0, (int) $this->Post['uid']);
        if($uid < 1) {
            exit;
        }
        
                $fcode = jpost('fcode');
        $all_topic_notice = ('topicnew' == $fcode);

		$__my = jsg_member_info($uid);
        if(!$__my) {
            exit;
        }

		$time = TIMESTAMP;

				$is_uptime = $this->Post['is_uptime'];
		if($is_uptime == 1) {
			DB::query("update `".TABLE_PREFIX."members` set `lastactivity`='{$time}' where `uid`='$uid'");

			cache_db('rm', "{$uid}-topic-%", 1);

            echo '<success></success>';
			echo "<script language='Javascript'>";
			            echo "listTopic(0,0);";
			echo "</script>";
			exit;
		}

        $total_record = jlogic('buddy')->check_new_topic($uid, 0, 0, $all_topic_notice);
		jsg_setcookie('topnotice','block');

		include(template('ajax_reminded'));
	}

	function modacttime()
	{

		$uid = MEMBER_ID;
		$time = TIMESTAMP;
		DB::query("update `".TABLE_PREFIX."members` set `close_recd_time`='{$time}' where `uid`='$uid'");
					}

	function ShowRecommend()
	{
				if (jstrpos($_SERVER['HTTP_REFERER'],'at') > 0) {
			return;
		}
				$uid = max(0, (int) $this->Post['uid']);
		$tid = max(0, (int) $this->Post['tid']);
        if($uid < 1) {
           exit;
        }
		$return_tid = jlogic('buddy')->check_new_recd_topic($uid,$tid);
		if($return_tid){
			$tids = array($return_tid);
			$TopicListLogic = jlogic('topic_list');
			$options = array('tid'=>$tids,'count'=>'1');
			$info = $TopicListLogic->get_data($options);
			$topic_list = $info['list'];
			if($topic_list){
				$TopicLogic = jlogic('topic');
				$parent_list = $TopicLogic->GetParentTopic($topic_list,1);
								$relate_list = $TopicLogic->GetRelateTopic($topic_list);

				foreach($topic_list as $key => $val){
					if ($val['longtextid'] > 0) {
						$topic_list[$key]['content'] = $val['content'].'...';
					}
				}
				include(template('ajax_recommend'));
			}
		}
	}
}

?>
