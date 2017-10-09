<?php
/*
 * Code based on:
 * Abraham Williams (abraham@abrah.am) http://abrah.am
 * Load OAuth lib. You can find it at http://oauth.net
 * @ignore
 * @version $Id: oauth.class.php 4051 2013-07-30 02:32:24Z wuliyong $
 */

include_once ROOT_PATH . 'include/ext/xwb/lib/compat.inc.php';

$GLOBALS['__CLASS']['OAuthRequest']['__STATIC'] = array(
'OAuthRequest_version'=>'1.0a',
'boundary'=>''
);

if (! function_exists ( '___throwException' )) {
	function ___throwException($str) {
		trigger_error ( $str, 256 );
	}
}

/*
class OAuthException extends Exception {
    // pass
}
*/

/**
 * @ignore
 */
class OAuthConsumer {
    var $key;
    var $secret;

    function __construct($key, $secret) {
        $this->OAuthConsumer($key, $secret);
    }

    function OAuthConsumer($key, $secret) {
        $this->key = $key;
        $this->secret = $secret;
    }

    function __toString() {
        return "OAuthConsumer[key=$this->key,secret=$this->secret]";
    }
}

/**
 * @ignore
 */
class OAuthToken {
    // access tokens and request tokens
    var $key;
    var $secret;

    /**
     * key = the token
     * secret = the token secret
     */
    function __construct($key, $secret) {
        $this->OAuthToken($key, $secret);
    }

    function OAuthToken($key, $secret) {
        $this->key = $key;
        $this->secret = $secret;
    }

    /**
     * generates the basic string serialization of a token that a server
     * would respond to request_token and access_token calls with
     */
    function to_string() {
        return "oauth_token=" .
            OAuthUtil::urlencode_rfc3986($this->key) .
            "&oauth_token_secret=" .
            OAuthUtil::urlencode_rfc3986($this->secret);
    }

    function __toString() {
        return $this->to_string();
    }
}

/**
 * @ignore
 */
class OAuthSignatureMethod {
    function check_signature(&$request, $consumer, $token, $signature) {
        $built = $this->build_signature($request, $consumer, $token);
        return $built == $signature;
    }
}

/**
 * @ignore
 */
class OAuthSignatureMethod_HMAC_SHA1 extends OAuthSignatureMethod {
    function get_name() {
        return "HMAC-SHA1";
    }

    function build_signature(&$request, $consumer, $token) {
        $base_string = $request->get_signature_base_string();

		//print_r( $base_string );
        $request->base_string = $base_string;

        $key_parts = array(
            $consumer->secret,
            ($token) ? $token->secret : ""
        );

        //print_r( $key_parts );
		$key_parts = OAuthUtil::urlencode_rfc3986($key_parts);


		$key = implode('&', $key_parts);
		$request->key_string = $key;

		return base64_encode(_xwb_hash_hmac('sha1', $base_string, $key, true));
		//return base64_encode(hash_hmac('sha1', $base_string, $key, true));

    }
}

/**
 * @ignore
 */
class OAuthSignatureMethod_PLAINTEXT extends OAuthSignatureMethod {
    function get_name() {
        return "PLAINTEXT";
    }

    function build_signature(&$request, $consumer, $token) {
        $sig = array(
            OAuthUtil::urlencode_rfc3986($consumer->secret)
        );

        if ($token) {
            array_push($sig, OAuthUtil::urlencode_rfc3986($token->secret));
        } else {
            array_push($sig, '');
        }

        $raw = implode("&", $sig);
        // for debug purposes
        $request->base_string = $raw;

        return OAuthUtil::urlencode_rfc3986($raw);
    }
}

/**
 * @ignore
 */
class OAuthSignatureMethod_RSA_SHA1 extends OAuthSignatureMethod {
    function get_name() {
        return "RSA-SHA1";
    }

    function fetch_public_cert(&$request) {
        // not implemented yet, ideas are:
        // (1) do a lookup in a table of trusted certs keyed off of consumer
        // (2) fetch via http using a url provided by the requester
        // (3) some sort of specific discovery code based on request
        //
        // either way should return a string representation of the certificate
        ___throwException("fetch_public_cert not implemented");
    }

    function fetch_private_cert(&$request) {
        // not implemented yet, ideas are:
        // (1) do a lookup in a table of trusted certs keyed off of consumer
        //
        // either way should return a string representation of the certificate
        ___throwException("fetch_private_cert not implemented");
    }

    function build_signature(&$request, $consumer, $token) {
        $base_string = $request->get_signature_base_string();
        $request->base_string = $base_string;

        // Fetch the private key cert based on the request
        $cert = $this->fetch_private_cert($request);

        // Pull the private key ID from the certificate
        $privatekeyid = openssl_get_privatekey($cert);

        // Sign using the key
        $ok = openssl_sign($base_string, $signature, $privatekeyid);

        // Release the key resource
        openssl_free_key($privatekeyid);

        return base64_encode($signature);
    }

    function check_signature(&$request, $consumer, $token, $signature) {
        $decoded_sig = base64_decode($signature);

        $base_string = $request->get_signature_base_string();

        // Fetch the public key cert based on the request
        $cert = $this->fetch_public_cert($request);

        // Pull the public key ID from the certificate
        $publickeyid = openssl_get_publickey($cert);

        // Check the computed signature against the one passed in the query
        $ok = openssl_verify($base_string, $decoded_sig, $publickeyid);

        // Release the key resource
        openssl_free_key($publickeyid);

        return $ok == 1;
    }
}

/**
 * @ignore
 */
class OAuthRequest {
    var $parameters;
    var $http_method;
    var $http_url;
    // for debug purposes
    var $base_string;
    var $key_string;
    //public static $version = '1.0a';

    function __construct($http_method, $http_url, $parameters=NULL) {
        $this->OAuthRequest($http_method, $http_url, $parameters);
    }

    function OAuthRequest($http_method, $http_url, $parameters=NULL) {
        @$parameters or $parameters = array();
        $this->parameters = $parameters;
        $this->http_method = $http_method;
        $this->http_url = $http_url;
    }


    /**
     * attempt to build up a request from what was passed to the server
     */
    function from_request($http_method=NULL, $http_url=NULL, $parameters=NULL) {
        $scheme = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on")
            ? 'http'
            : 'https';
        @$http_url or $http_url = $scheme .
            ':/'.'/' . $_SERVER['HTTP_HOST'] .
            ':' .
            $_SERVER['SERVER_PORT'] .
            $_SERVER['REQUEST_URI'];
        @$http_method or $http_method = $_SERVER['REQUEST_METHOD'];

        // We weren't handed any parameters, so let's find the ones relevant to
        // this request.
        // If you run XML-RPC or similar you should use this to provide your own
        // parsed parameter-list
        if (!$parameters) {
            // Find request headers
            $request_headers = OAuthUtil::get_headers();

            // Parse the query-string to find GET parameters
            $parameters = OAuthUtil::parse_parameters($_SERVER['QUERY_STRING']);

            // It's a POST request of the proper content-type, so parse POST
            // parameters and add those overriding any duplicates from GET
            if ($http_method == "POST"
                && @strstr($request_headers["Content-Type"],
                    "application/x-www-form-urlencoded")
            ) {
                $post_data = OAuthUtil::parse_parameters(
                    file_get_contents('php:/'.'/input')
                );
                $parameters = array_merge($parameters, $post_data);
            }

            // We have a Authorization-header with OAuth data. Parse the header
            // and add those overriding any duplicates from GET or POST
            if (@substr($request_headers['Authorization'], 0, 6) == "OAuth ") {
                $header_parameters = OAuthUtil::split_header(
                    $request_headers['Authorization']
                );
                $parameters = array_merge($parameters, $header_parameters);
            }

        }

        return new OAuthRequest($http_method, $http_url, $parameters);
    }

    /**
     * pretty much a helper function to set up the request
     */
    function from_consumer_and_token($consumer, $token, $http_method, $http_url, $parameters=NULL) {
        @$parameters or $parameters = array();
        $defaults = array("source" => $consumer->key,
			"oauth_version" => $GLOBALS['__CLASS']['OAuthRequest']['__STATIC']['OAuthRequest_version'],
            "oauth_nonce" => OAuthRequest::generate_nonce(),
            "oauth_timestamp" => OAuthRequest::generate_timestamp(),
            "oauth_consumer_key" => $consumer->key);
        if ($token)
            $defaults['oauth_token'] = $token->key;

        $parameters = array_merge($defaults, $parameters);

        return new OAuthRequest($http_method, $http_url, $parameters);
    }

    function set_parameter($name, $value, $allow_duplicates = true) {
        if ($allow_duplicates && isset($this->parameters[$name])) {
            // We have already added parameter(s) with this name, so add to the list
            if (is_scalar($this->parameters[$name])) {
                // This is the first duplicate, so transform scalar (string)
                // into an array so we can add the duplicates
                $this->parameters[$name] = array($this->parameters[$name]);
            }

            $this->parameters[$name][] = $value;
        } else {
            $this->parameters[$name] = $value;
        }
    }

    function get_parameter($name) {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
    }

    function get_parameters() {
        return $this->parameters;
    }

    function unset_parameter($name) {
        unset($this->parameters[$name]);
    }

    /**
     * The request parameters, sorted and concatenated into a normalized string.
     * @return string
     */
    function get_signable_parameters() {
        // Grab all parameters
        $params = $this->parameters;

        // remove pic
        if (isset($params['pic'])) {
            unset($params['pic']);
        }

        if (isset($params['image'])) {
            unset($params['image']);
        }

        // Remove oauth_signature if present
        // Ref: Spec: 9.1.1 ("The oauth_signature parameter MUST be excluded.")
        if (isset($params['oauth_signature'])) {
            unset($params['oauth_signature']);
        }

        return OAuthUtil::build_http_query($params);
    }

    /**
     * Returns the base string of this request
     *
     * The base string defined as the method, the url
     * and the parameters (normalized), each urlencoded
     * and the concated with &.
     */
    function get_signature_base_string() {
        $parts = array(
            $this->get_normalized_http_method(),
            $this->get_normalized_http_url(),
            $this->get_signable_parameters()
        );

        //print_r( $parts );

        $parts = OAuthUtil::urlencode_rfc3986($parts);

        return implode('&', $parts);
    }

    /**
     * just uppercases the http method
     */
    function get_normalized_http_method() {
        return strtoupper($this->http_method);
    }

    /**
     * parses the url and rebuilds it to be
     * scheme://host/path
     */
    function get_normalized_http_url() {
        $parts = parse_url($this->http_url);

        $port = @$parts['port'];
        $scheme = $parts['scheme'];
        $host = $parts['host'];
        $path = @$parts['path'];

        $port or $port = ($scheme == 'https') ? '443' : '80';

        if (($scheme == 'https' && $port != '443')
            || ($scheme == 'http' && $port != '80')) {
                $host = "$host:$port";
            }
        return "$scheme:/"."/$host$path";
    }

    /**
     * builds a url usable for a GET request
     */
    function to_url() {
        $post_data = $this->to_postdata();
        $out = $this->get_normalized_http_url();
        if ($post_data) {
            $out .= '?'.$post_data;
        }
        return $out;
    }

    /**
     * builds the data one would send in a POST request
     */
    function to_postdata( $multi = false ) {
    //echo "multi=" . $multi . '`';
    if( $multi )
    	return OAuthUtil::build_http_query_multi($this->parameters);
    else
        return OAuthUtil::build_http_query($this->parameters);
    }

    /**
     * builds the Authorization: header
     */
    function to_header() {
        $out ='Authorization: OAuth realm=""';
        $total = array();
        foreach ($this->parameters as $k => $v) {
            if (substr($k, 0, 5) != "oauth") continue;
            if (is_array($v)) {
                ___throwException('Arrays not supported in headers');
            }
            $out .= ',' .
                OAuthUtil::urlencode_rfc3986($k) .
                '="' .
                OAuthUtil::urlencode_rfc3986($v) .
                '"';
        }
        return $out;
    }

    function __toString() {
        return $this->to_url();
    }


    function sign_request($signature_method, $consumer, $token) {
        $this->set_parameter(
            "oauth_signature_method",
            $signature_method->get_name(),
            false
        );
		$signature = $this->build_signature($signature_method, $consumer, $token);
        //echo "sign=" . $signature;
		$this->set_parameter("oauth_signature", $signature, false);
    }

    function build_signature($signature_method, $consumer, $token) {
        $signature = $signature_method->build_signature($this, $consumer, $token);
        return $signature;
    }

    /**
     * util function: current timestamp
     */
    function generate_timestamp() {
        //return 1273566716;
		return time();
    }

    /**
     * util function: current nonce
     */
    function generate_nonce() {
        //return '462d316f6f40c40a9e0eef1b009f37fa';
		$mt = microtime();
        $rand = mt_rand();

        return md5($mt . $rand); // md5s look nicer than numbers
    }
}

/**
 * @ignore
 */
class OAuthUtil {

	public static $boundary = '';

    public static function urlencode_rfc3986($input) {
        if (is_array($input)) {
            return array_map(array('OAuthUtil', 'urlencode_rfc3986'), $input);
        } else if (is_scalar($input)) {
            return str_replace(
                '+',
                ' ',
                str_replace('%7E', '~', rawurlencode($input))
            );
        } else {
            return '';
        }
    }


    // This decode function isn't taking into consideration the above
    // modifications to the encoding process. However, this method doesn't
    // seem to be used anywhere so leaving it as is.
    public static function urldecode_rfc3986($string) {
        return urldecode($string);
    }

    // Utility function for turning the Authorization: header into
    // parameters, has to do some unescaping
    // Can filter out any non-oauth parameters if needed (default behaviour)
    public static function split_header($header, $only_allow_oauth_parameters = true) {
        $pattern = '/(([-_a-z]*)=("([^"]*)"|([^,]*)),?)/';
        $offset = 0;
        $params = array();
        while (preg_match($pattern, $header, $matches, PREG_OFFSET_CAPTURE, $offset) > 0) {
            $match = $matches[0];
            $header_name = $matches[2][0];
            $header_content = (isset($matches[5])) ? $matches[5][0] : $matches[4][0];
            if (preg_match('/^oauth_/', $header_name) || !$only_allow_oauth_parameters) {
                $params[$header_name] = OAuthUtil::urldecode_rfc3986($header_content);
            }
            $offset = $match[1] + strlen($match[0]);
        }

        if (isset($params['realm'])) {
            unset($params['realm']);
        }

        return $params;
    }

    // helper to try to sort out headers for people who aren't running apache
    public static function get_headers() {
        if (function_exists('apache_request_headers')) {
            // we need this to get the actual Authorization: header
            // because apache tends to tell us it doesn't exist
            return apache_request_headers();
        }
        // otherwise we don't have apache and are just going to have to hope
        // that $_SERVER actually contains what we need
        $out = array();
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) == "HTTP_") {
                // this is chaos, basically it is just there to capitalize the first
                // letter of every word that is not an initial HTTP and strip HTTP
                // code from przemek
                $key = str_replace(
                    " ",
                    "-",
                    ucwords(strtolower(str_replace("_", " ", substr($key, 5))))
                );
                $out[$key] = $value;
            }
        }
        return $out;
    }

    // This function takes a input like a=b&a=c&d=e and returns the parsed
    // parameters like this
    // array('a' => array('b','c'), 'd' => 'e')
    public static function parse_parameters( $input ) {
        if (!isset($input) || !$input) return array();

        $pairs = explode('&', $input);

        $parsed_parameters = array();
        foreach ($pairs as $pair) {
            $split = explode('=', $pair, 2);
            $parameter = OAuthUtil::urldecode_rfc3986($split[0]);
            $value = isset($split[1]) ? OAuthUtil::urldecode_rfc3986($split[1]) : '';

            if (isset($parsed_parameters[$parameter])) {
                // We have already recieved parameter(s) with this name, so add to the list
                // of parameters with this name

                if (is_scalar($parsed_parameters[$parameter])) {
                    // This is the first duplicate, so transform scalar (string) into an array
                    // so we can add the duplicates
                    $parsed_parameters[$parameter] = array($parsed_parameters[$parameter]);
                }

                $parsed_parameters[$parameter][] = $value;
            } else {
                $parsed_parameters[$parameter] = $value;
            }
        }
        return $parsed_parameters;
    }

    public static function build_http_query_multi($params) {
        if (!$params) return '';

		//print_r( $params );
		//return null;

        // Urlencode both keys and values
        $keys = array_keys($params);
        $values = array_values($params);
        //$keys = OAuthUtil::urlencode_rfc3986(array_keys($params));
        //$values = OAuthUtil::urlencode_rfc3986(array_values($params));
        $params = array_combine($keys, $values);

        // Parameters are sorted by name, using lexicographical byte value ordering.
        // Ref: Spec: 9.1.1 (1)
        uksort($params, 'strcmp');

        $pairs = array();

        self::$boundary = $GLOBALS['__CLASS']['OAuthRequest']['__STATIC']['boundary'] = $boundary = uniqid('------------------');
		$MPboundary = '--'.$boundary;
		$endMPboundary = $MPboundary. '--';
		$multipartbody = '';

        foreach ($params as $parameter => $value) {
			if( ($parameter == 'pic' || $parameter == 'image') ){
				if(is_string($value) && $value{0} == '@')
				{
					$url = ltrim( $value , '@' );

					//锟斤拷时锟斤拷锟斤拷
					$ctx_userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; .NET CLR 3.5.30729)';
					if( version_compare(PHP_VERSION, '5.0.0', '>=') ){
						$ctx_header = "Accept: *"."/"."*\r\nAccept-Language: zh-cn\r\nUser-Agent: {$ctx_userAgent}\r\n";
						$ctx = stream_context_create(array('http'=>array('timeout'=>8,'method'=>'GET','header'=>$ctx_header)));
						$content = file_get_contents( $url, 0, $ctx);
					}else{
						@ini_set('user_agent', $ctx_userAgent);
						$content = file_get_contents( $url );
					}

					$filename = reset( explode( '?' , basename( $url ) ));
					$mime = OAuthUtil::get_image_mime($url);
				}
				elseif(is_array($value))
				{
					$content = $value[2];//file_get_contents( $url );
					$filename = $value[1];//reset( explode( '?' , basename( $url ) ));
					$mime = $value[0];//self::get_image_mime($url);
				}

				$multipartbody .= $MPboundary . "\r\n";
				$multipartbody .= 'Content-Disposition: form-data; name="'.$parameter.'"; filename="' . $filename . '"'. "\r\n";
				$multipartbody .= 'Content-Type: '. $mime . "\r\n\r\n";
				$multipartbody .= $content. "\r\n";
			} else {
				$multipartbody .= $MPboundary . "\r\n";
				$multipartbody .= 'Content-Disposition: form-data; name="'.$parameter."\"\r\n\r\n";
				$multipartbody .= $value."\r\n";

			}
        }

        $multipartbody .=  "$endMPboundary\r\n";
        // For each parameter, the name is separated from the corresponding value by an '=' character (ASCII code 61)
        // Each name-value pair is separated by an '&' character (ASCII code 38)
        // echo $multipartbody;
        return $multipartbody;
    }

    public static function build_http_query($params) {
        if (!$params) return '';

        // Urlencode both keys and values
        $keys = OAuthUtil::urlencode_rfc3986(array_keys($params));
        $values = OAuthUtil::urlencode_rfc3986(array_values($params));
        $params = array_combine($keys, $values);

        // Parameters are sorted by name, using lexicographical byte value ordering.
        // Ref: Spec: 9.1.1 (1)
        uksort($params, 'strcmp');

        $pairs = array();
        foreach ($params as $parameter => $value) {
            if (is_array($value)) {
                // If two or more parameters share the same name, they are sorted by their value
                // Ref: Spec: 9.1.1 (1)
                natsort($value);
                foreach ($value as $duplicate_value) {
                    $pairs[] = $parameter . '=' . $duplicate_value;
                }
            } else {
                $pairs[] = $parameter . '=' . $value;
            }
        }
        // For each parameter, the name is separated from the corresponding value by an '=' character (ASCII code 61)
        // Each name-value pair is separated by an '&' character (ASCII code 38)
        return implode('&', $pairs);
    }

    public static function get_image_mime( $file )
    {
    	$ext = strtolower(pathinfo( $file , PATHINFO_EXTENSION ));
    	switch( $ext )
    	{
    		case 'jpg':
    		case 'jpeg':
    			$mime = 'image/jpg';
    			break;

    		case 'png':
    			$mime = 'image/png';
    			break;

    		case 'gif':
    		default:
    			$mime = 'image/gif';
    			break;
    	}
    	return $mime;
    }
}
