<?php
/**
 * 前台公告显示用模块
 *
 * @package www.jishigou.com
 * @author 狐狸<foxis@qq.com>
 * @copyright 2011
 * @version $Id: qun_announcement.mod.php 3785 2013-06-03 09:22:56Z yupengfei $
 * @access public
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class ModuleObject extends MasterObject {

	function ModuleObject($config) {
		$this->MasterObject($config);

		ob_start();
		$this->qun_announcement_index();
		$this->ShowBody(ob_get_clean());
	}

	function qun_announcement_index() {
		$this->Title = $this->Config['changeword']['weiqun'] . '公告';
		$qid = jget('qid', 'int');
		$id = jget('id', 'int');
		if($id < 1) {
			$id = jget('ids', 'int');
			if($id < 1) {
				$id = jget('code', 'int');
			}
		}
		if($id > 0) {			
			$qun_announcement_info = jtable('qun_announcement')->info($id);
			$author_member = jsg_member_info($qun_announcement_info['author_id']);
			$this->Title .= ' - ' . cutstr(trim(strip_tags($qun_announcement_info['message'])), 30); 
		}
		
		
		
		include template('qun/qun_announcement_index');		
	}

}


?>
