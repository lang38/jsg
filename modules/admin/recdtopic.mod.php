<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename recdtopic.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013-11-11 173066454 5298 $
 */




if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $TopicLogic;
	var $TopicRecommendLogic;

	function ModuleObject($config)
	{
		$this->MasterObject($config);
		Load::logic('topic_recommend');
		$this->TopicRecommendLogic = new  TopicRecommendLogic();

		$this->TopicLogic = jlogic('topic');

		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
			case 'delete':
				$this->delete();
				break;
			case 'edit':
				$this->edit();
				break;
		  	case 'doedit':
				$this->doedit();
				break;
		  	case 'onekey':
		  		$this->onekey();
		  		break;
			default:
				$this->Code = 'recdtopic_manage';
				$this->index();
				break;
		}
		$body = ob_get_clean();

		$this->ShowBody($body);

	}

	function index()
	{
		$per_page_num = min(500, max(20, (int) (isset($_GET['pn']) ? $_GET['pn'] : $_GET['per_page_num'])));
		$gets = array(
			'mod' => 'recdtopic',
			'pn' => $this->Get['pn'],
			'per_page_num' => $this->Get['per_page_num'],
			'keyword' => $this->Get['keyword'],
			'nickname' => $this->Get['nickname'],
		);
		$page_url = 'admin.php?'.url_implode($gets);
		$where_sql = ' 1 AND tr.tid>0 ';

				$keyword = trim($this->Get['keyword']);
		if ($keyword) {
			$_GET['highlight'] = $keyword;
			$where_sql .= " AND ".build_like_query('t.content,t.content2',$keyword)." ";
		}

				$nickname = trim($this->Get['nickname']);
		if ($nickname) {

			$sql = "select `username`,`nickname` from `".TABLE_PREFIX."members` where `nickname`='{$nickname}' limit 0,1";
			$query = $this->DatabaseHandler->Query($sql);
			$members=$query->GetRow();
			$where_sql .= " AND `username`='{$members['username']}' ";
		}

		$count = DB::result_first("SELECT COUNT(*)
								   FROM ".DB::table('topic')." AS t
								   LEFT JOIN ".DB::table('topic_recommend')." AS tr
								   ON t.tid=tr.tid
								   WHERE {$where_sql}");

		$topic_list = array();
		if ($count) {
			$page_arr = page($count,$per_page_num,$page_url,array('return'=>'array'));
			$query = DB::query("SELECT t.*,tr.dateline AS recd_time,tr.expiration,tr.r_nickname
								FROM  ".DB::table('topic')." AS t
								LEFT JOIN ".DB::table('topic_recommend')." AS tr
								ON t.tid=tr.tid
								WHERE {$where_sql}
								ORDER BY tr.dateline DESC
								{$page_arr['limit']} ");
			while ($value = DB::fetch($query)) {
				$value['recd_time'] = my_date_format2($value['recd_time']);
				$topic_list[] = $value;
			}
			$topic_list = $this->TopicLogic->MakeAll($topic_list);
		}


		include template('admin/recdtopic');
	}

	function delete()
	{
		$ids = (array) ($this->Post['ids'] ? $this->Post['ids'] : $this->Get['ids']);
		if(!$ids) {
			$this->Messager("请指定要删除的对象");
		}
		$this->TopicRecommendLogic->delete($ids);
		$this->Messager("操作成功了");
	}

		function edit()
	{
		$tid = intval($this->Get['tid']);
		$topic_recd = $this->TopicRecommendLogic->get_info($tid);
		$channels = array();
		if($this->Config['channel_enable']){
			$channels = $this->TopicRecommendLogic->recd_channels();
		}

		if (!empty($topic_recd)) {
			$topic_recd['expiration'] = empty($topic_recd['expiration']) ? '' : my_date_format($topic_recd['expiration'], 'Y-m-d H:i');
			$recd_html = $this->jishigou_form->Radio('recd[]',array(array("name"=>"重点推荐","value"=>"4"),array("name"=>"普通推荐","value"=>"2"),array("name"=>"取消推荐","value"=>"0")),$topic_recd['recd']);
		}else{
			$recd_html = $this->jishigou_form->Radio('recd[]',array(array("name"=>"重点推荐","value"=>"4"),array("name"=>"普通推荐","value"=>"2")),2);
		}
		include template('admin/recdtopic_edit');
	}

		function doedit()
	{
		$tid = intval($this->Post['tid']);
		$recd = intval($this->Post['recd'][0]);
		$r_title = strip_tags(trim($this->Post['r_title']));
		if(empty($r_title)){
			$this->Messager("推荐标题为空或内容不合法");
		}

				if ($recd>4 || $recd < 0) {
			$this->Messager("推荐等级错误");
		}

		$expiration = jstrtotime(trim($this->Post['expiration']));

		$data = array(
			'recd' => $recd,
			'expiration' => $expiration,
			'r_title' => $r_title,
		);
		if($this->Post['item_id']){
			$data['item'] = 'channel';
			$data['item_id'] = intval($this->Post['item_id']);
		}
		$this->TopicRecommendLogic->modify($data, array('tid'=>$tid));
		$this->Messager("操作成功了", 'admin.php?mod=recdtopic');
	}

	function onekey()
	{
		$time = time();
				$ids = array();
		$query = DB::query("SELECT tid FROM ".DB::table('topic_recommend')." WHERE expiration>0 AND expiration<=$time");
		while ($value = DB::fetch($query)){
			$ids[] = $value['tid'];
		}
		if($ids){
			$this->TopicRecommendLogic->delete($ids);
		}
		$this->Messager("操作成功了", 'admin.php?mod=recdtopic');
	}
}



?>