<?php

/**
 *
 * qmd 模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: qmd.mod.php 3700 2013-05-27 07:27:54Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class ModuleObject extends MasterObject {

	function ModuleObject($config) {
		$this->MasterObject($config);

		if($this->Code && method_exists($this, $this->Code)) {
			$this->{$this->Code}();
		} else {
			exit('qmd ' . $this->Code . ' page is not exits');
		}
	}
	
	function select() {
		$uid = MEMBER_ID;
		if($uid > 0) {
			$sql = "select `uid`,`qmd_img` from `".TABLE_PREFIX."members` where `uid`='{$uid}'";
			$query = $this->DatabaseHandler->Query($sql);
			$row = $query->GetRow();
	
						$qmd_bg_path = $this->Post['qmd_bg_path'] ? $this->Post['qmd_bg_path'] : $row['qmd_img'];
	
			Load::logic('other');
			$OtherLogic = new OtherLogic();
			$qmd_return = $OtherLogic->qmd_list($uid,$qmd_bg_path);
	
			$sql = "update `".TABLE_PREFIX."members` set  `qmd_img`='{$qmd_bg_path}'  where `uid` = '{$uid}' ";
			$this->DatabaseHandler->Query($sql);
		}			
	}
	
	function update() {
		$uid = MEMBER_ID;
		if($this->Config['is_qmd'] && $uid > 0) {
			$sql = "select `uid`,`qmd_img` from `".TABLE_PREFIX."members` where `uid`='{$uid}'";
			$query = $this->DatabaseHandler->Query($sql);
			$row = $query->GetRow();

						$qmd_bg_path = $row['qmd_img']? $row['qmd_img'] : $this->Post['qmd_bg_path'];

			Load::logic('other');
			$OtherLogic = new OtherLogic();
			$qmd_return = $OtherLogic->qmd_list($uid,$qmd_bg_path);
		}
	}
	
}

?>
