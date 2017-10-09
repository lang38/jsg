<?php
/**
 * 文件名：report.mod.php
 * @version $Id: report.mod.php 5666 2014-04-25 01:53:16Z wuliyong $
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 举报模块
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
		switch($this->Code)
		{
			case 'do':
				$this->DoReport();
				break;

			default:
				$this->Main();
				break;
		}
	}

	function Main()
	{
		$url = urlencode(get_safe_code(urldecode($this->Get['url'])));

		$report_config = jconf::get('report');


		include(template('report.inc'));
	}

	function DoReport()
	{
		$url = get_param('url');
		$report_url = get_param('report_url');

		$url = get_safe_code(urldecode(urldecode(($url ? $url : $report_url))));

		$data = array(
			'uid' => MEMBER_ID,
			'username' => MEMBER_NAME,
			'ip' => $GLOBALS['_J']['client_ip'],
			'reason' => (int) get_param('report_reason'),
			'content' => strip_tags(get_param('report_content')),
			'url' => strip_tags(urldecode($url)),
			'dateline' => time(),
		);
		$result = jtable('report')->insert($data);


		$this->Messager("举报成功");
	}

}

?>
