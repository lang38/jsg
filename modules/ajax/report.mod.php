<?php

/**
 *
 * report 模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: report.mod.php 5000 2013-11-13 01:43:24Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class ModuleObject extends MasterObject {

	function ModuleObject($config) {
		$this->MasterObject($config);

		$this->Execute();
	}

	function Execute() {
		switch($this->Code) {
			case 'topic':
				$this->Topic();
				break;

			default : {
					$this->Main();
					break;
				}
		}
	}

	function Main() {
		response_text('page is not exits');
	}

	
	function Topic() {
		if(MEMBER_ID < 1)
		{
			response_text('您是游客，没有权限举报');
		}

		$tid =  jget('totid','int','P');
		$report_reason = $this->Post['report_reason'];
		$report_content = $this->Post['report_content'];

		
		$data = array(
				'uid' => MEMBER_ID,
				'username' => MEMBER_NICKNAME,
				'ip' => $GLOBALS['_J']['client_ip'],
				'reason' => (int) $report_reason,
				'content' => strip_tags($report_content),
				'tid' => (int) $tid,
				'dateline' => time(),
		);

		$result = jtable('report')->insert($data);

		if($notice_to_admin = $this->Config['notice_to_admin']){
			$message = "用户".MEMBER_NICKNAME."举报了微博ID：$tid(".$data['content'].")，<a href='admin.php?mod=report&code=report_manage' target='_blank'>点击</a>进入管理。";
			$pm_post = array(
				'message' => $message,
				'to_user' => str_replace('|',',',$notice_to_admin),
			);
						$admin_info = DB::fetch_first('select `uid`,`username`,`nickname` from `'.TABLE_PREFIX.'members` where `uid` = 1');
			load::logic('pm');
			$PmLogic = new PmLogic();
			$PmLogic->pmSend($pm_post,$admin_info['uid'],$admin_info['username'],$admin_info['nickname']);
		}

		response_text('举报成功');
	}

}

?>
