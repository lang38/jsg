<?php
/**
 * 前台公告显示用模块
 *
 * @package www.jishigou.com
 * @author 狐狸<foxis@qq.com>
 * @copyright 2011
 * @version $Id: notice.mod.php 3700 2013-05-27 07:27:54Z wuliyong $
 * @access public
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class ModuleObject extends MasterObject {

	function ModuleObject($config) {
		$this->MasterObject($config);

		ob_start();
		$this->notice_index();
		$this->ShowBody(ob_get_clean());
	}

	function notice_index() {
		$this->Title = '网站公告';
		$id = jget('id', 'int');
		if($id < 1) {
			$id = jget('ids', 'int');
			if($id < 1) {
				$id = jget('code', 'int');
			}
		}
		if($id > 0) {			
			$notice_info = jtable('notice')->info($id);
			$this->Title .= ' - ' . $notice_info['title']; 
		}
		
		
		
		$notice_data = jtable('notice')->get_data();
		
		
		include template('notice_index');		
	}

}


?>
