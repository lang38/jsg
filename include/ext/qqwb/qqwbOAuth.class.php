<?php
/**
 * 腾讯微博OAuth授权及API接口类
 *
 * @author 狐狸 <foxis@qq.com>
 * @version $Id$
 */

class qqwbOAuth
{
    public static $client_id = '';
    public static $client_secret = '';

    private static $accessTokenURL = 'https://open.t.qq.com/cgi-bin/oauth2/access_token';
    private static $authorizeURL = 'https://open.t.qq.com/cgi-bin/oauth2/authorize';

    public static $openid = '';
    public static $openkey = '';
    public static $access_token = '';
    private static $api_host = 'https://open.t.qq.com/api/';
    private static $debug = false;

    /**
     * 初始化
     * @param $client_id 即 appid
     * @param $client_secret 即 appkey
     * @return
     */
    public static function init($client_id, $client_secret, $access_token = '', $openid = '', $openkey = '')
    {
        if (!$client_id || !$client_secret) exit('client_id or client_secret is null');
        self::$client_id = $client_id;
        self::$client_secret = $client_secret;
        self::$access_token = $access_token;
        self::$openid = $openid;
        self::$openkey = $openkey;
    }

    /**
     * 获取授权URL
     * @param $redirect_uri 授权成功后的回调地址，即第三方应用的url
     * @param $response_type 授权类型，为code
     * @param $wap 用于指定手机授权页的版本，默认PC，值为1时跳到wap1.0的授权页，为2时同理
     * @return string
     */
    public static function getAuthorizeURL($redirect_uri, $response_type = 'code', $wap = false)
    {
        $params = array(
            'client_id' => self::$client_id,
            'redirect_uri' => $redirect_uri,
            'response_type' => $response_type,
            'wap' => $type
        );
        return self::$authorizeURL.'?'.http_build_query($params);
    }

    /**
     * 获取请求token的url
     * @param $code 调用authorize时返回的code
     * @param $redirect_uri 回调地址，必须和请求code时的redirect_uri一致
     * @return string
     */
    public static function getAccessToken($code, $redirect_uri)
    {
        $params = array(
            'client_id' => self::$client_id,
            'client_secret' => self::$client_secret,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirect_uri
        );
        $url = self::$accessTokenURL.'?'.http_build_query($params);
        $r = self::request($url);
        parse_str($r, $out);
        return $out;
    }

    /**
     * 发起一个腾讯API请求
     * @param $command 接口名称 如：t/add
     * @param $params 接口参数  array('content'=>'test');
     * @param $method 请求方式 POST|GET
     * @param $multi 图片信息
     * @return string
     */
    public static function api($command, $params = array(), $method = 'GET', $multi = false)
    {    	
		//OAuth 2.0 鉴权默认参数
		$params_def = array();
		$params_def['format'] = 'json';
		$params_def['access_token'] = self::$access_token;
		$params_def['oauth_consumer_key'] = self::$client_id;
		$params_def['openid'] = self::$openid;
		$params_def['oauth_version'] = '2.a';
		$params_def['clientip'] = $GLOBALS['_J']['client_ip'];
		$params_def['scope'] = 'all';
		$params_def['appfrom'] = 'JishiGou OAuth2 Client v0.2';
		$params_def['seqid'] = time();
		$params_def['serverip'] = $_SERVER['SERVER_ADDR'];
		
		settype($params, 'array');
		$params = array_merge($params_def, $params);
		
		if(empty($params['access_token']) || empty($params['openid'])) {
			exit('openid or access_token is empty');
		}
		
		$url = self::$api_host.trim($command, '/');


        //请求接口
        $r = self::request($url, $params, $method, $multi);
        $r = preg_replace('/[^\x20-\xff]*/', "", $r); //清除不可见字符
        $r = iconv("utf-8", "utf-8//ignore", $r); //UTF-8转码
        //调试信息
        if (self::$debug) {
            echo '<pre>';
            echo '接口：'.$url;
            echo '<br>请求参数：<br>';
            print_r($params);
            echo '返回结果：'.$r;
            echo '</pre>';
        }
        $r = json_decode($r, true);
        $r = array_iconv('utf-8', $GLOBALS['_J']['config']['charset'], $r);
        return $r['data'];
    }
    
    /**
     * 发起一个HTTP/HTTPS的请求
     * @param $url 接口的URL
     * @param $params 接口参数   array('content'=>'test', 'format'=>'json');
     * @param $method 请求类型    GET|POST
     * @param $multi 图片信息
     * @param $extheaders 扩展的包头信息
     * @return string
     */
    public static function request( $url , $params = array(), $method = 'GET' , $multi = false, $extheaders = array())
    {
        if(!function_exists('curl_init')) {
        	exit('Need to open the curl extension');
        }
        $method = strtoupper($method);
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_USERAGENT, $params['appfrom']);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 3);
        $timeout = $multi?30:3;
        curl_setopt($ci, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ci, CURLOPT_HEADER, false);
        $headers = (array)$extheaders;
        switch ($method)
        {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($params))
                {
                    if($multi)
                    {
                        foreach($multi as $key => $file)
                        {
                            $params[$key] = '@' . $file;
                        }
                        curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
                        $headers[] = 'Expect: ';
                    }
                    else
                    {
                        curl_setopt($ci, CURLOPT_POSTFIELDS, http_build_query($params));
                    }
                }
                break;
            case 'DELETE':
            case 'GET':
                $method == 'DELETE' && curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($params))
                {
                    $url = $url . (strpos($url, '?') ? '&' : '?')
                        . (is_array($params) ? http_build_query($params) : $params);
                }
                break;
        }
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );
        curl_setopt($ci, CURLOPT_URL, $url);
        if($headers)
        {
            curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
        }

        $response = curl_exec($ci);
        curl_close ($ci);
        return $response;
    }
    
	function tAdd($content = '')
	{
		$params = array();
		$params['content'] = $content;
		$params['clientip'] = $GLOBALS['_J']['client_ip'];

		return self::api('t/add', $params, 'POST');
	}

	function tAddPic($content = '',$pic=array())
	{
		$params = array();
		$params['content'] = $content;
		$params['clientip'] = $GLOBALS['_J']['client_ip'];
		$params['pic'] = $pic;
		
		return self::api('t/add_pic', $params, 'POST', true);
	}

	function tReply($reid,$content)
	{
		$params = array(
            'reid' => $reid,
            'content' => $content,
            'clientip' => $GLOBALS['_J']['client_ip'],
		);

		return self::api('t/reply', $params, 'POST');
	}
}