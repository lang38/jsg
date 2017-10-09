<?php

/**
 * 缓存管理
 *
 * @author 狐狸<foxis@qq.com>
 * @package JishiGou
 * @version $Id: cache.mod.php 5302 2013-12-20 07:30:24Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
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
		switch($this->Code) {
			case 'do_clean':
				$this->DoClean();

			default:
				$this->Code = '';
				$this->Main();
				break;
		}
		$body = ob_get_clean();

		$this->ShowBody($body);
	}

	function Main() {

		$this->_fix_tb_data();
		$this->_free_login_ip();


		include template('admin/cache_index');
	}

	function DoClean() {
		$type = get_param('type');
		if(!$type) {
			$this->Messager("请先选择要清理的缓存对象");
		}

				$this->_removeTopicAttach();
		$this->_removeTopicLongtext();
		$this->_removeVoteImage();

		if(in_array('data', $type)) {
						cache_db('clear');
						jtable('failedlogins')->truncate();
						DB::query("update " . TABLE_PREFIX . "members set `username`=`uid` WHERE `username`!=`uid` AND `username` REGEXP '^[0-9]*$'");
		}

		if(in_array('tpl', $type)) {
						cache_clear();

						jconf::set('validate_category', array());
	
						jlogic('credits')->rule_conf(true);
		}

		if(in_array('channel', $type)) {
						jlogic('channel')->update_data();
		}
		if(in_array('album', $type)) {
						jlogic('image')->update_data();
		}

		$this->Messager("已清空所有缓存");
	}

	private function _removeTopicImage() {
					}
	private function _removeTopicAttach() {
				jlogic('attach')->clear_invalid(300);
	}
	private function _removeTopicLongtext() {
		jlogic('longtext')->clear_invalid();
	}
	private function _removeVoteImage() {
				jlogic('image')->clear_vote_invalid(300);
	}

	private function _free_login_ip() {
		global $_J;

		DB::query("DELETE FROM ".TABLE_PREFIX.'failedlogins'." WHERE ip='{$_J['client_ip']}'");
		DB::query("DELETE FROM ".TABLE_PREFIX.'failedlogins'." WHERE lastupdate<'".($_J['timestamp']-901)."'");
	}
	
	private function _fix_tb_data() {
		DB::query("update " . TABLE_PREFIX . "members set `username`=`uid` where `username`=''");
		DB::query("update " . TABLE_PREFIX . "members set `nickname`=`username` where `nickname`=''");
		DB::query("update " . TABLE_PREFIX . "members set `credits`=0, `extcredits1`=0, `extcredits2`=0, `extcredits3`=0, `extcredits4`=0, `extcredits5`=0, `extcredits6`=0, `extcredits7`=0, `extcredits8`=0 where `credits`>=2147480000");

		DB::query("delete from " . TABLE_PREFIX . "schedule where `uid`='0'");
	}
	
}
?>