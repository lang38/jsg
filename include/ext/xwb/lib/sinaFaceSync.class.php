<?php
/**
 * 新浪头像同步器
 * 本类主要是将当前授权的单个登录新浪用户头像同步到指定的DZ uid中
 *
 * @author yaoying <yaoying@staff.sina.com.cn>
 * @copyright [JishiGou] (C)2005 - 2099 Cenwor Inc.
 * @version $Id: sinaFaceSync.class.php 3740 2013-05-28 09:38:05Z wuliyong $
 *
 */
class sinaFaceSync{

	/**
	 * DZ用户uid。传参数后由_getFaceAndCreateTemp方法创建
	 * @var int
	 */
	var $uid = 0;

	/**
	 * 当前授权的单个登录新浪用户资料
	 * @var array
	 */
	var $sina_userinfo = array();

	/**
	 * fsockopenHttp单例
	 * @var fsockopenHttp
	 */
	var $http;

	/**
	 * 临时头像文件数组集合
	 * 数组索引：1为大头像，2为中头像，3为小头像
	 * @var array
	 */
	var $faceTempPath = array();

	/**
	 * 头像大小设置
	 * 数组索引：1为大头像，2为中头像，3为小头像
	 * 预设值：大头像为180，中等头像为120，小头像为48
	 * @var array
	 */
	var $faceSize = array(
				1 => array( 'h' => 180, 'w' => 180 ) ,
				2 => array( 'h' => 150, 'w' => 150 ) ,
				3 => array( 'h' => 54, 'w' => 54 ) ,
				);

	/**
	 * 构造函数
	 *
	 * @return sinaFaceSync
	 */
	function sinaFaceSync(){
		$this->_getSinaUserInfo();
		$this->http = XWB_plugin::O('fsockopenHttp');
	}


	/**
	 * 获取当前授权的单个登录新浪用户资料（保护方法）
	 */
	function _getSinaUserInfo(){
		$xwb_user = XWB_plugin::getUser();
		$sina_id = (int)($xwb_user->getInfo('sina_uid'));
		if( $sina_id > 0 ){
			$wb = XWB_plugin::getWB();
			$wb->is_exit_error = false;
			$this->sina_userinfo = (array)$wb->getUserShow($sina_id);
		}
	}


	/**
	 * 同步头像到指定的DZ uid（自动判断是否存在UC）。
	 * 如果你实在不知道该用syncToUC还是syncToNoUC方法进行同步头像，就使用此方法，让程序自动判断。
	 *
	 * @param integer $uid DZ uid
	 * @return integer 同步结果
	 */
	function sync( $uid ){
		if ( true===UCENTER  ){
			//加载Ucenter客户端文件
			include_once(ROOT_PATH . './api/uc_client/client.php');

			return $this->syncToUC($uid);
		}else{
			return $this->syncToNoUC($uid);
		}
	}

	/**
	 * 同步头像到指定的UC uid（同步到UC）。
	 * 适用于已经安装了UC（不排除部分站长将旧版本DZ自行修改加载了UC）、或者是DZ 6.1及以上版本（也会安装了UC）
	 *
	 * @param integer $uid DZ uid
	 * @return integer 同步结果。正常为0，否则：
	 *  -1到-10：此错误码预留给_getFaceAndCreateTemp方法，请自行参阅该方法的注释
	 *
	 * 	-10：本地编码失败（一般是无法生成3种头像文件所致）
	 * 	-11：与UC进行HTTP通讯出错
	 * 	-12：UC返回头像编码解码失败代码
	 * 	-13：UC返回头像上传失败代码
	 * 	-14：UC返回找寻传参uid失败代码
	 * 	-15：UC返回未知错误代码
	 */
	function syncToUC( $uid ){
		$step1result = $this->_getFaceAndCreateTemp( $uid );
		if( $step1result < 0 ){
			return $step1result;
		}

		$postdata = $this->_createUCAvatarPostdata();
		if( count($postdata) != 3 ){
			return -10;
		}

		$this->http->setUrl( $this->_createUCUrl() );
		$this->http->setData( $postdata );
		$this->_delTempFace();     //构造完UC所需数据后，就可以删除临时文件了。
		$response = $this->http->request('post');
		$code = (int)$this->http->getState();

		if (200 !== $code) {
			return -11;
		}

		if( preg_match('/type="error"[ ]+value="([\-\+0-9]+)"/', $response, $matchErr) ){
			$matchErr = isset($matchErr[1]) ? (int)$matchErr[1] : -99;
			switch ($matchErr){
				case '-1':
					return -14;
				case '-2':
					return -12;
				default:
					return -15;
			}
		}

		$match = array();
		if ( !preg_match('/success="([0-9]+)"/', $response, $match) ){
			return -15;
		}

		if( !isset($match[1]) || (int)$match[1] != 1 ){
			return -13;
		}else{
			return 0;
		}

	}

	/**
	 * 同步头像到指定的DZ uid（同步到DZ）。
	 * 适用于没有安装UC的DZ。普通站长一般是DZ6.0.0
	 *
	 * @param integer $uid DZ uid
	 * @return integer 同步结果。正常为0，否则：
	 *  -1到-10：此错误码预留给_getFaceAndCreateTemp方法，请自行参阅该方法的注释
	 *
	 * 	-20：要复制的中等头像不存在
	 * 	-21：论坛设置不允许该用户所在用户组上传头像
	 * 	-22：复制头像到指定论坛头像目录失败
	 */
	function syncToNoUC( $uid ){
		$step1result = $this->_getFaceAndCreateTemp( $uid );
		if( $step1result < 0 ){
			return $step1result;
		}

		$db = XWB_plugin::getDB();

		//由于中等头像经大头像创建，而大头像已经经过安全检测，因此在这里只需要检测中等头像是否存在即可。
		if( !file_exists($this->faceTempPath[3]) ){
			$this->_delTempFace();
			return -20;
		}

		$_destPrefix = './images/face/'. jsg_face_path($this->uid) . $this->uid;
		$image_file_small = $destPath = $_destPrefix . '_s.jpg';
        $image_file = $_destPrefix . '_b.jpg';
		$destRealPath = XWB_S_ROOT . $destPath;
		if (!is_dir(dirname($destPath)))
		{
			jmkdir(dirname($destPath));
		}
		copy($this->faceTempPath[2],XWB_S_ROOT . $image_file);
		$copyresult = copy( $this->faceTempPath[3], $destRealPath );
		$this->_delTempFace();

		if( false == $copyresult ){
			return -22;
		}else{


            $face_url = '';
            if($GLOBALS['_J']['config']['ftp_enable'])
            {
                $ftp_key = randgetftp();
				$get_ftps = jconf::get('ftp');
				$face_url = $get_ftps[$ftp_key]['attachurl'];

                $ftp_result = ftpcmd('upload',$image_file,'',$ftp_key);
                if($ftp_result > 0)
                {
                    ftpcmd('upload',$image_file_small,'',$ftp_key);

                    @unlink($image_file);
                    @unlink($image_file_small);
                }
            }



			$destPath = mysql_real_escape_string($destPath);
			$db->result_first("UPDATE " . XWB_S_TBPRE . "members SET `face_url`='$face_url', `face`= '". $destPath ."' WHERE uid = '" . $this->uid . "' LIMIT 1");

			return 0;
		}

	}


	/**
	 * 从新浪获取头像，然后生成指定的3种尺寸图像供后续指定的$uid使用。（保护方法）
	 * 该方法是该类进行主要操作时第一个必须要运行的方法，否则将因为无法初始化对应参数而出错。
	 *
	 * @param integer $uid DZ uid
	 * @return integer 成功则返回0，否则返回错误代码：
	 * 	-1：初始化失败（无法获取新浪用户信息）
	 * 	-2：传uid参数错误（小于1）
	 * 	-3：无法获取服务器上的头像
	 * 	-4：服务器返回错误数据（非头像数据或者给出来的头像太小）；或者临时目录权限问题导致无大头像文件
	 *  -5：GD库没有加载，无法进行头像同步操作
	 */
	function _getFaceAndCreateTemp( $uid ){

		if (! extension_loaded ( 'gd' )) {
			return -5;
		}

		if( empty($this->sina_userinfo) || !isset($this->sina_userinfo['id']) ){
			 return -1;
		}
		$this->uid = (int)$uid;
		if( $this->uid < 1 ){
			return -2;
		}

		//获取大头像
		$faceurl = str_replace($this->sina_userinfo['id'].'/50/', $this->sina_userinfo['id'].'/180/', $this->sina_userinfo['profile_image_url']);
		$body = $this->http->Get($faceurl);
		if( $this->http->getState() !== 200 || empty($body)  ){
			return -3;
		}

		//写入临时目录
		$this->faceTempPath[1] = XWB_P_DATA. '/temp/'. $this->uid. '_1_xwb_face_temp.jpg';
		file_put_contents( $this->faceTempPath[1], $body, LOCK_EX );

		//大头像安全性和有效性检查(服务器给出来的头像太小，也丢弃处理)
		$imageSize = getimagesize($this->faceTempPath[1]);
		if( false === $imageSize || $imageSize[0] < 30 || $imageSize[1] < 30 ){
			$this->_delTempFace();
			return -4;
		}

		//创建中小头像
		foreach ( $this->faceSize as $key => $size ){
			//大头像无需处理
			if( 1 === $key ){
				continue;
			}
			$imgProc = XWB_plugin::N('images');
			$imgProc->loadFile($this->faceTempPath[1]);    //载入大头像
			//$imgProc->crop(0,0,180,180);
			$imgProc->resize($size['w'], $size['h']);
			$this->faceTempPath[$key] = XWB_P_DATA. '/temp/'. $this->uid. '_'. $key. '_xwb_face_temp.jpg';
			$imgProc->save($this->faceTempPath[$key]);
			$imgProc = null;      //释放资源，让其自动调用__destruct
		}

		return 0;

	}



	/**
	 * 删除临时建立的头像（保护方法）
	 *
	 */
	function _delTempFace(){
		foreach($this->faceTempPath as $face){
			if( file_exists($face) ){
				@unlink($face);
			}
		}
	}


	/**
	 * 生成UC所需的头像编码POST数据。
	 * @return array 编码好的数据
	 */
	function _createUCAvatarPostdata(){
		$postdata = array();
		$imageEncoder = XWB_plugin::N('imageEncoder');
		foreach ( $this->faceTempPath as $key => $face ){
			$content = file_get_contents($face);
			if(empty($content)){
				break;
			}
			$postkey = 'avatar'. $key;
			$postdata[$postkey] = $imageEncoder->flashdata_encode($content);
		}
		$imageEncoder = null;

		return $postdata;
	}


	/**
	 * 生成要发送数据的UC地址。
	 *
	 * @return string
	 */
	function _createUCUrl(){

		$db = XWB_plugin::getDB();

		$ucuid = $db->result_first("select `ucuid` from ".XWB_S_TBPRE."members where `uid`='{$this->uid}' limit 1");

		//最关键的input！必须使用dz函数authcode，并且必须使用Discuz!和UC之间的通讯密钥！
		$ucinput = authcode( 'uid='. $ucuid
						. '&agent='. md5($_SERVER['HTTP_USER_AGENT'])
						. '&time='. time() ,
						'ENCODE', UC_KEY );

		//PHP4没有http_build_query，只好.......
		$posturl = UC_API.'/index.php?m=user'
					. '&a=rectavatar'
					. '&inajax=1'
					. '&appid='. UC_APPID
					. '&agent='. urlencode( md5($_SERVER['HTTP_USER_AGENT']) )
					. '&input='. urlencode($ucinput)
		;

		return $posturl;
	}

}


