<?php
/**
 * 普通用户校验（For DX）
 * 请保证传参所用字符集和论坛字符集一致，否则请先自行转换再传参
 * 返回值有两个array( 0 => UID, 1 => ADMINID )
 * 第一个数组下标（$return[0]）若大于0，则表示验证成功的登录uid。否则为错误信息：
 *  -1:UC用户不存在，或者被删除
 *  -2:密码错
 *  -3:安全提问错
 *  -4:用户没有在dx注册
 * 第二个数组下标（$return[1]）若大于等于0，则表示验证成功的adminid；
 * 否则为-1，表示验证失败
 * @author yaoying
 * @version $Id: siteUserVerifier.class.php 3699 2013-05-27 07:26:39Z wuliyong $
 */
class siteUserVerifier{

	var $db;

	function siteUserVerifier(){
		$this->db = XWB_plugin::getDB();
	}

	/**
	 * 进行身份验证
	 * 请保证传参所用字符集和论坛字符集一致，否则请先自行转换再传参
	 * @param string $username
	 * @param string $password
	 * @param int $questionid
	 * @param string $answer
	 * @param boolen $isuid 使用UID验证么？
	 * @return array
	 *    第一个数组下标（$return[0]）若大于0，则表示验证成功的登录uid。否则为错误信息：
	 *   	 -1:UC用户不存在，或者被删除
	 *    	 -2:密码错
	 *   	 -3:安全提问错
	 *   	 -4:用户没有在dz注册
	 *    第二个数组下标（$return[1]）若大于等于0，则表示验证成功的adminid；
	 *   	 否则为-1，表示验证失败
	 */
	function verify( $username, $password, $questionid = '', $answer = '',$isuid = 0 )
	{

		$return = array( 0 => -1, 1 => -1);


		$ip = XWB_plugin::getIP();


		/**
		 * 校验用户输入错误密码的次数
		 */
		$failedlogins = $this->db->fetch_first("select * from ".XWB_S_TBPRE."failedlogins where `ip`='{$ip}'");
		if($failedlogins && $failedlogins['count'] >= 5)
		{
			$return[0] = -5;

			return $return;
		}


		/**
		 * 校验用户输入的用户名和密码是否正确
		 */
		if (true===UCENTER)
		{
			//加载Ucenter客户端文件
			include_once(ROOT_PATH . './api/uc_client/client.php');

			$uc_result = uc_user_login($username, $password, $isuid, 0, $questionid, $answer);
			$ucuid = $uc_result[0];
			if ($ucuid < 1)
			{
				$return[0] = $ucuid;

				return $return;
			}
		}


		$member = $this->db->fetch_first("SELECT `uid`, `password`, `nickname`, `username`, `role_type`, `salt` FROM ". XWB_S_TBPRE. "members WHERE `nickname`='{$username}'");

		if ($member)
		{
			/**
			 * 在记事狗系统中比对用户输入的密码
			 */
			if($member['password']==jsg_member_password($password, $member['salt']))
			{
				$return[0] = (int)$member['uid'];
				$return[1] = ('admin'==$member['role_type'] ? 1 : 0);
			}
			else
			{
				$return[0] = -2;

				/**
				 * 更新密码输入错误的次数
				 */
				if($failedlogins)
				{
					$this->db->query("update ".XWB_S_TBPRE."failedlogins set `count`='".(max(1,(int) $failedlogins['count']) + 1)."', `lastupdate`='".time()."' where `ip`='{$ip}'");
				}
				else
				{
					$this->db->query("insert into ".XWB_S_TBPRE."failedlogins (`ip`,`count`,`lastupdate`) values ('{$ip}','1','".time()."')");
				}
			}
		}


		return $return;

	}

}