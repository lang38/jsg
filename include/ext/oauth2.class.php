<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename oauth2.class.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2014-04-25 14:36:59 901210630 2096006508 56025 $
 */







define("OAUTH2_DEFAULT_ACCESS_TOKEN_LIFETIME", 3600);


define("OAUTH2_DEFAULT_AUTH_CODE_LIFETIME", 30);


define("OAUTH2_DEFAULT_REFRESH_TOKEN_LIFETIME", 1209600);





define("OAUTH2_CLIENT_ID_REGEXP", "/^[a-z0-9-_]{3,32}$/i");







define("OAUTH2_AUTH_RESPONSE_TYPE_ACCESS_TOKEN", "token");


define("OAUTH2_AUTH_RESPONSE_TYPE_AUTH_CODE", "code");


define("OAUTH2_AUTH_RESPONSE_TYPE_CODE_AND_TOKEN", "code-and-token");


define("OAUTH2_AUTH_RESPONSE_TYPE_REGEXP", "/^(token|code|code-and-token)$/");







define("OAUTH2_GRANT_TYPE_AUTH_CODE", "authorization_code");


define("OAUTH2_GRANT_TYPE_USER_CREDENTIALS", "password");


define("OAUTH2_GRANT_TYPE_ASSERTION", "assertion");


define("OAUTH2_GRANT_TYPE_REFRESH_TOKEN", "refresh_token");


define("OAUTH2_GRANT_TYPE_NONE", "none");


define("OAUTH2_GRANT_TYPE_REGEXP", "/^(authorization_code|password|assertion|refresh_token|none)$/");







define("OAUTH2_TOKEN_PARAM_NAME", "oauth_token");







define("OAUTH2_HTTP_FOUND", "302 Found");


define("OAUTH2_HTTP_BAD_REQUEST", "400 Bad Request");


define("OAUTH2_HTTP_UNAUTHORIZED", "401 Unauthorized");


define("OAUTH2_HTTP_FORBIDDEN", "403 Forbidden");







define("OAUTH2_ERROR_INVALID_REQUEST", "invalid_request");


define("OAUTH2_ERROR_INVALID_CLIENT", "invalid_client");


define("OAUTH2_ERROR_UNAUTHORIZED_CLIENT", "unauthorized_client");


define("OAUTH2_ERROR_REDIRECT_URI_MISMATCH", "redirect_uri_mismatch");


define("OAUTH2_ERROR_USER_DENIED", "access_denied");


define("OAUTH2_ERROR_UNSUPPORTED_RESPONSE_TYPE", "unsupported_response_type");


define("OAUTH2_ERROR_INVALID_SCOPE", "invalid_scope");


define("OAUTH2_ERROR_INVALID_GRANT", "invalid_grant");


define("OAUTH2_ERROR_UNSUPPORTED_GRANT_TYPE", "unsupported_grant_type");


define("OAUTH2_ERROR_INVALID_TOKEN", "invalid_token");


define("OAUTH2_ERROR_EXPIRED_TOKEN", "expired_token");


define("OAUTH2_ERROR_INSUFFICIENT_SCOPE", "insufficient_scope");




abstract class OAuth2 {

  
  protected $conf = array();

  
  public function getVariable($name, $default = NULL) {
    return isset($this->conf[$name]) ? $this->conf[$name] : $default;
  }

  
  public function setVariable($name, $value) {
    $this->conf[$name] = $value;
    return $this;
  }

  
  
  abstract protected function checkClientCredentials($client_id, $client_secret = NULL);

  
  abstract protected function getRedirectUri($client_id);

  
  abstract protected function getAccessToken($oauth_token);

  
  abstract protected function setAccessToken($oauth_token, $client_id, $expires, $scope = NULL);

            
  
  protected function getSupportedGrantTypes() {
    return array();
  }

  
  protected function getSupportedAuthResponseTypes() {
    return array(
      OAUTH2_AUTH_RESPONSE_TYPE_AUTH_CODE,
      OAUTH2_AUTH_RESPONSE_TYPE_ACCESS_TOKEN,
      OAUTH2_AUTH_RESPONSE_TYPE_CODE_AND_TOKEN
    );
  }

  
  protected function getSupportedScopes() {
    return array();
  }

  
  protected function checkRestrictedAuthResponseType($client_id, $response_type) {
    return TRUE;
  }

  
  protected function checkRestrictedGrantType($client_id, $grant_type) {
    return TRUE;
  }

  
  
  protected function getAuthCode($code) {
    return NULL;
  }

  
  protected function setAuthCode($code, $client_id, $redirect_uri, $expires, $scope = NULL) {
  }

  
  protected function checkUserCredentials($client_id, $username, $password) {
    return FALSE;
  }

  
  protected function checkAssertion($client_id, $assertion_type, $assertion) {
    return FALSE;
  }

  
  protected function getRefreshToken($refresh_token) {
    return NULL;
  }

  
  protected function setRefreshToken($refresh_token, $client_id, $expires, $scope = NULL) {
    return;
  }

  
  protected function unsetRefreshToken($refresh_token) {
    return;
  }

  
  protected function checkNoneAccess($client_id) {
    return FALSE;
  }

  
  protected function getDefaultAuthenticationRealm() {
    return "Service";
  }

  
  
  public function __construct($config = array()) {
    foreach ($config as $name => $value) {
      $this->setVariable($name, $value);
    }
  }

  
  
  public function verifyAccessToken($scope = NULL, $exit_not_present = TRUE, $exit_invalid = TRUE, $exit_expired = TRUE, $exit_scope = TRUE, $realm = NULL) {
    $token_param = $this->getAccessTokenParams();
    if ($token_param === FALSE)       return $exit_not_present ? $this->errorWWWAuthenticateResponseHeader(OAUTH2_HTTP_BAD_REQUEST, $realm, OAUTH2_ERROR_INVALID_REQUEST, 'The request is missing a required parameter, includes an unsupported parameter or parameter value, repeats the same parameter, uses more than one method for including an access token, or is otherwise malformed.', NULL, $scope) : FALSE;
        $token = $this->getAccessToken($token_param);
    if ($token === NULL)
      return $exit_invalid ? $this->errorWWWAuthenticateResponseHeader(OAUTH2_HTTP_UNAUTHORIZED, $realm, OAUTH2_ERROR_INVALID_TOKEN, 'The access token provided is invalid.', NULL, $scope) : FALSE;

        if (isset($token["expires"]) && time() > $token["expires"])
      return $exit_expired ? $this->errorWWWAuthenticateResponseHeader(OAUTH2_HTTP_UNAUTHORIZED, $realm, OAUTH2_ERROR_EXPIRED_TOKEN, 'The access token provided has expired.', NULL, $scope) : FALSE;

            if ($scope && (!isset($token["scope"]) || !$token["scope"] || !$this->checkScope($scope, $token["scope"])))
      return $exit_scope ? $this->errorWWWAuthenticateResponseHeader(OAUTH2_HTTP_FORBIDDEN, $realm, OAUTH2_ERROR_INSUFFICIENT_SCOPE, 'The request requires higher privileges than provided by the access token.', NULL, $scope) : FALSE;

    return TRUE;
  }

  
  private function checkScope($required_scope, $available_scope) {
        if (!is_array($required_scope))
      $required_scope = explode(" ", $required_scope);

    if (!is_array($available_scope))
      $available_scope = explode(" ", $available_scope);

    return (count(array_diff($required_scope, $available_scope)) == 0);
  }

  
  private function getAccessTokenParams() {
    $auth_header = $this->getAuthorizationHeader();

    if ($auth_header !== FALSE) {
            if (isset($_GET[OAUTH2_TOKEN_PARAM_NAME]) || isset($_POST[OAUTH2_TOKEN_PARAM_NAME]))
        $this->errorJsonResponse(OAUTH2_HTTP_BAD_REQUEST, OAUTH2_ERROR_INVALID_REQUEST, 'Auth token found in GET or POST when token present in header');

      $auth_header = trim($auth_header);

            if (strcmp(substr($auth_header, 0, 5), "OAuth ") !== 0)
        $this->errorJsonResponse(OAUTH2_HTTP_BAD_REQUEST, OAUTH2_ERROR_INVALID_REQUEST, 'Auth header found that doesn\'t start with "OAuth"');

            if (preg_match('/\s*OAuth\s*="(.+)"/', substr($auth_header, 5), $matches) == 0 || count($matches) < 2)
        $this->errorJsonResponse(OAUTH2_HTTP_BAD_REQUEST, OAUTH2_ERROR_INVALID_REQUEST, 'Malformed auth header');

      return $matches[1];
    }

    if (isset($_GET[OAUTH2_TOKEN_PARAM_NAME])) {
      if (isset($_POST[OAUTH2_TOKEN_PARAM_NAME]))         $this->errorJsonResponse(OAUTH2_HTTP_BAD_REQUEST, OAUTH2_ERROR_INVALID_REQUEST, 'Only send the token in GET or POST, not both');

      return $_GET[OAUTH2_TOKEN_PARAM_NAME];
    }

    if (isset($_POST[OAUTH2_TOKEN_PARAM_NAME]))
      return $_POST[OAUTH2_TOKEN_PARAM_NAME];

    return FALSE;
  }

  
  
  public function grantAccessToken() {
    $filters = array(
      "grant_type" => array("filter" => FILTER_VALIDATE_REGEXP, "options" => array("regexp" => OAUTH2_GRANT_TYPE_REGEXP), "flags" => FILTER_REQUIRE_SCALAR),
      "scope" => array("flags" => FILTER_REQUIRE_SCALAR),
      "code" => array("flags" => FILTER_REQUIRE_SCALAR),
      "redirect_uri" => array("filter" => FILTER_SANITIZE_URL),
      "username" => array("flags" => FILTER_REQUIRE_SCALAR),
      "password" => array("flags" => FILTER_REQUIRE_SCALAR),
      "assertion_type" => array("flags" => FILTER_REQUIRE_SCALAR),
      "assertion" => array("flags" => FILTER_REQUIRE_SCALAR),
      "refresh_token" => array("flags" => FILTER_REQUIRE_SCALAR),
    );

    $input = filter_input_array(INPUT_POST, $filters);

        if (!$input["grant_type"])
      $this->errorJsonResponse(OAUTH2_HTTP_BAD_REQUEST, OAUTH2_ERROR_INVALID_REQUEST, 'Invalid grant_type parameter or parameter missing');

        if (!in_array($input["grant_type"], $this->getSupportedGrantTypes()))
      $this->errorJsonResponse(OAUTH2_HTTP_BAD_REQUEST, OAUTH2_ERROR_UNSUPPORTED_GRANT_TYPE);

        $client = $this->getClientCredentials();

    if ($this->checkClientCredentials($client[0], $client[1]) === FALSE)
      $this->errorJsonResponse(OAUTH2_HTTP_BAD_REQUEST, OAUTH2_ERROR_INVALID_CLIENT);

    if (!$this->checkRestrictedGrantType($client[0], $input["grant_type"]))
      $this->errorJsonResponse(OAUTH2_HTTP_BAD_REQUEST, OAUTH2_ERROR_UNAUTHORIZED_CLIENT);

        switch ($input["grant_type"]) {
      case OAUTH2_GRANT_TYPE_AUTH_CODE:
        if (!$input["code"] || !$input["redirect_uri"])
          $this->errorJsonResponse(OAUTH2_HTTP_BAD_REQUEST, OAUTH2_ERROR_INVALID_REQUEST);

        $stored = $this->getAuthCode($input["code"]);

                if ($stored === NULL || (strcasecmp(substr($input["redirect_uri"], 0, strlen($stored["redirect_uri"])), $stored["redirect_uri"]) !== 0) || $client[0] != $stored["client_id"])
          $this->errorJsonResponse(OAUTH2_HTTP_BAD_REQUEST, OAUTH2_ERROR_INVALID_GRANT);

        if ($stored["expires"] < time())
          $this->errorJsonResponse(OAUTH2_HTTP_BAD_REQUEST, OAUTH2_ERROR_EXPIRED_TOKEN);

        break;
      case OAUTH2_GRANT_TYPE_USER_CREDENTIALS:
        if (!$input["username"] || !$input["password"])
          $this->errorJsonResponse(OAUTH2_HTTP_BAD_REQUEST, OAUTH2_ERROR_INVALID_REQUEST, 'Missing parameters. "username" and "password" required');

        $stored = $this->checkUserCredentials($client[0], $input["username"], $input["password"]);

        if ($stored === FALSE)
          $this->errorJsonResponse(OAUTH2_HTTP_BAD_REQUEST, OAUTH2_ERROR_INVALID_GRANT);

        break;
      case OAUTH2_GRANT_TYPE_ASSERTION:
        if (!$input["assertion_type"] || !$input["assertion"])
          $this->errorJsonResponse(OAUTH2_HTTP_BAD_REQUEST, OAUTH2_ERROR_INVALID_REQUEST);

        $stored = $this->checkAssertion($client[0], $input["assertion_type"], $input["assertion"]);

        if ($stored === FALSE)
          $this->errorJsonResponse(OAUTH2_HTTP_BAD_REQUEST, OAUTH2_ERROR_INVALID_GRANT);

        break;
      case OAUTH2_GRANT_TYPE_REFRESH_TOKEN:
        if (!$input["refresh_token"])
          $this->errorJsonResponse(OAUTH2_HTTP_BAD_REQUEST, OAUTH2_ERROR_INVALID_REQUEST, 'No "refresh_token" parameter found');

        $stored = $this->getRefreshToken($input["refresh_token"]);

        if ($stored === NULL || $client[0] != $stored["client_id"])
          $this->errorJsonResponse(OAUTH2_HTTP_BAD_REQUEST, OAUTH2_ERROR_INVALID_GRANT);

        if ($stored["expires"] < time())
          $this->errorJsonResponse(OAUTH2_HTTP_BAD_REQUEST, OAUTH2_ERROR_EXPIRED_TOKEN);

                $this->setVariable('_old_refresh_token', $stored["token"]);

        break;
      case OAUTH2_GRANT_TYPE_NONE:
        $stored = $this->checkNoneAccess($client[0]);

        if ($stored === FALSE)
          $this->errorJsonResponse(OAUTH2_HTTP_BAD_REQUEST, OAUTH2_ERROR_INVALID_REQUEST);
    }

        if ($input["scope"] && (!is_array($stored) || !isset($stored["scope"]) || !$this->checkScope($input["scope"], $stored["scope"])))
      $this->errorJsonResponse(OAUTH2_HTTP_BAD_REQUEST, OAUTH2_ERROR_INVALID_SCOPE);

    if (!$input["scope"])
      $input["scope"] = NULL;

    $token = $this->createAccessToken($client[0], $input["scope"]);

    $this->sendJsonHeaders();
    echo json_encode($token);
  }

  
  protected function getClientCredentials() {
    if (isset($_SERVER["PHP_AUTH_USER"]) && $_POST && isset($_POST["client_id"]))
      $this->errorJsonResponse(OAUTH2_HTTP_BAD_REQUEST, OAUTH2_ERROR_INVALID_CLIENT);

        if (isset($_SERVER["PHP_AUTH_USER"]))
      return array($_SERVER["PHP_AUTH_USER"], $_SERVER["PHP_AUTH_PW"]);

        if ($_POST && isset($_POST["client_id"])) {
      if (isset($_POST["client_secret"]))
        return array($_POST["client_id"], $_POST["client_secret"]);

      return array($_POST["client_id"], NULL);
    }

        $this->errorJsonResponse(OAUTH2_HTTP_BAD_REQUEST, OAUTH2_ERROR_INVALID_CLIENT);
  }

  
  
  public function getAuthorizeParams() {
    $filters = array(
      "client_id" => array("filter" => FILTER_VALIDATE_REGEXP, "options" => array("regexp" => OAUTH2_CLIENT_ID_REGEXP), "flags" => FILTER_REQUIRE_SCALAR),
      "response_type" => array("filter" => FILTER_VALIDATE_REGEXP, "options" => array("regexp" => OAUTH2_AUTH_RESPONSE_TYPE_REGEXP), "flags" => FILTER_REQUIRE_SCALAR),
      "redirect_uri" => array("filter" => FILTER_SANITIZE_URL),
      "state" => array("flags" => FILTER_REQUIRE_SCALAR),
      "scope" => array("flags" => FILTER_REQUIRE_SCALAR),
    );

    $input = filter_input_array(INPUT_GET, $filters);

        if (!$input["client_id"]) {
      if ($input["redirect_uri"])
        $this->errorDoRedirectUriCallback($input["redirect_uri"], OAUTH2_ERROR_INVALID_CLIENT, NULL, NULL, $input["state"]);

      $this->errorJsonResponse(OAUTH2_HTTP_FOUND, OAUTH2_ERROR_INVALID_CLIENT);     }

            $redirect_uri = $this->getRedirectUri($input["client_id"]);

        if (!$redirect_uri && !$input["redirect_uri"])
      $this->errorJsonResponse(OAUTH2_HTTP_FOUND, OAUTH2_ERROR_INVALID_REQUEST);

            if ($redirect_uri === FALSE)
      $this->errorDoRedirectUriCallback($input["redirect_uri"], OAUTH2_ERROR_INVALID_CLIENT, NULL, NULL, $input["state"]);

        if ($redirect_uri && $input["redirect_uri"]) {
            if (strcasecmp(substr($input["redirect_uri"], 0, strlen($redirect_uri)), $redirect_uri) !== 0)
        $this->errorDoRedirectUriCallback($input["redirect_uri"], OAUTH2_ERROR_REDIRECT_URI_MISMATCH, NULL, NULL, $input["state"]);
    }
    elseif ($redirect_uri) {       $input["redirect_uri"] = $redirect_uri;
    }

        if (!$input["response_type"])
      $this->errorDoRedirectUriCallback($input["redirect_uri"], OAUTH2_ERROR_INVALID_REQUEST, 'Invalid response type.', NULL, $input["state"]);

        if (array_search($input["response_type"], $this->getSupportedAuthResponseTypes()) === FALSE)
      $this->errorDoRedirectUriCallback($input["redirect_uri"], OAUTH2_ERROR_UNSUPPORTED_RESPONSE_TYPE, NULL, NULL, $input["state"]);

        if ($this->checkRestrictedAuthResponseType($input["client_id"], $input["response_type"]) === FALSE)
      $this->errorDoRedirectUriCallback($input["redirect_uri"], OAUTH2_ERROR_UNAUTHORIZED_CLIENT, NULL, NULL, $input["state"]);

        if ($input["scope"] && !$this->checkScope($input["scope"], $this->getSupportedScopes()))
      $this->errorDoRedirectUriCallback($input["redirect_uri"], OAUTH2_ERROR_INVALID_SCOPE, NULL, NULL, $input["state"]);

    return $input;
  }

  
  public function finishClientAuthorization($is_authorized, $params = array()) {
    $params += array(
      'scope' => NULL,
      'state' => NULL,
    );
    extract($params);

    if ($state !== NULL)
      $result["query"]["state"] = $state;

    if ($is_authorized === FALSE) {
      $result["query"]["error"] = OAUTH2_ERROR_USER_DENIED;
    }
    else {
      if ($response_type == OAUTH2_AUTH_RESPONSE_TYPE_AUTH_CODE || $response_type == OAUTH2_AUTH_RESPONSE_TYPE_CODE_AND_TOKEN)
        $result["query"]["code"] = $this->createAuthCode($client_id, $redirect_uri, $scope);

      if ($response_type == OAUTH2_AUTH_RESPONSE_TYPE_ACCESS_TOKEN || $response_type == OAUTH2_AUTH_RESPONSE_TYPE_CODE_AND_TOKEN) {
      	$result["fragment"] = $this->createAccessToken($client_id, $scope);
      	      	if('STATE_JAPI' == $state) {
      		$this->doRedirectOpenerCallback($result['fragment']['access_token']);
      	}
      	      }
    }

    $this->doRedirectUriCallback($redirect_uri, $result);
  }
    private function doRedirectOpenerCallback($access_token) {
  	header("HTTP/1.1 ". OAUTH2_HTTP_FOUND);
  	if($access_token) {
  		echo '<script language="javascript">
if( window.opener && !window.opener.closed ) {
	window.opener.JAPI.c.access_token = "'.$access_token.'";
	window.opener.JAPI.login_ready();	
	if( window.opener.progressWindow ) {
		window.opener.progressWindow.close();
	}
}
window.close();
</script>';
  	} else {
  		echo '$access_token is empty';
  	}
  	exit;
  }
  
  
  
  private function doRedirectUriCallback($redirect_uri, $params) {
    header("HTTP/1.1 ". OAUTH2_HTTP_FOUND);
    header("Location: " . $this->buildUri($redirect_uri, $params));
    exit;
  }

  
  private function buildUri($uri, $params) {
    $parse_url = parse_url($uri);

        foreach ($params as $k => $v) {
      if (isset($parse_url[$k]))
        $parse_url[$k] .= "&" . http_build_query($v);
      else
        $parse_url[$k] = http_build_query($v);
    }

        return
      ((isset($parse_url["scheme"])) ? $parse_url["scheme"] . ":/"."/" : "")
      . ((isset($parse_url["user"])) ? $parse_url["user"] . ((isset($parse_url["pass"])) ? ":" . $parse_url["pass"] : "") . "@" : "")
      . ((isset($parse_url["host"])) ? $parse_url["host"] : "")
      . ((isset($parse_url["port"])) ? ":" . $parse_url["port"] : "")
      . ((isset($parse_url["path"])) ? $parse_url["path"] : "")
      . ((isset($parse_url["query"])) ? "?" . $parse_url["query"] : "")
      . ((isset($parse_url["fragment"])) ? "#" . $parse_url["fragment"] : "");
  }

  
  protected function createAccessToken($client_id, $scope = NULL) {
    $token = array(
      "access_token" => $this->genAccessToken(),
      "expires_in" => $this->getVariable('access_token_lifetime', OAUTH2_DEFAULT_ACCESS_TOKEN_LIFETIME),
      "scope" => $scope
    );

    $this->setAccessToken($token["access_token"], $client_id, time() + $this->getVariable('access_token_lifetime', OAUTH2_DEFAULT_ACCESS_TOKEN_LIFETIME), $scope);

        if (in_array(OAUTH2_GRANT_TYPE_REFRESH_TOKEN, $this->getSupportedGrantTypes())) {
      $token["refresh_token"] = $this->genAccessToken();
      $this->setRefreshToken($token["refresh_token"], $client_id, time() + $this->getVariable('refresh_token_lifetime', OAUTH2_DEFAULT_REFRESH_TOKEN_LIFETIME), $scope);
            if ($this->getVariable('_old_refresh_token'))
        $this->unsetRefreshToken($this->getVariable('_old_refresh_token'));
    }

    return $token;
  }

  
  private function createAuthCode($client_id, $redirect_uri, $scope = NULL) {
    $code = $this->genAuthCode();
    $this->setAuthCode($code, $client_id, $redirect_uri, time() + $this->getVariable('auth_code_lifetime', OAUTH2_DEFAULT_AUTH_CODE_LIFETIME), $scope);
    return $code;
  }

  
  protected function genAccessToken() {
    return md5(base64_encode(pack('N6', mt_rand(), mt_rand(), mt_rand(), mt_rand(), mt_rand(), uniqid())));
  }

  
  protected function genAuthCode() {
    return md5(base64_encode(pack('N6', mt_rand(), mt_rand(), mt_rand(), mt_rand(), mt_rand(), uniqid())));
  }

  
  private function getAuthorizationHeader() {
    if (array_key_exists("HTTP_AUTHORIZATION", $_SERVER))
      return $_SERVER["HTTP_AUTHORIZATION"];

    if (function_exists("apache_request_headers")) {
      $headers = apache_request_headers();

      if (array_key_exists("Authorization", $headers))
        return $headers["Authorization"];
    }

    return FALSE;
  }

  
  private function sendJsonHeaders() {
    header("Content-Type: application/json");
    header("Cache-Control: no-store");
  }

  
  private function errorDoRedirectUriCallback($redirect_uri, $error, $error_description = NULL, $error_uri = NULL, $state = NULL) {
    $result["query"]["error"] = $error;

    if ($state)
      $result["query"]["state"] = $state;

    if ($this->getVariable('display_error') && $error_description)
      $result["query"]["error_description"] = $error_description;

    if ($this->getVariable('display_error') && $error_uri)
      $result["query"]["error_uri"] = $error_uri;

    $this->doRedirectUriCallback($redirect_uri, $result);
  }

  
  private function errorJsonResponse($http_status_code, $error, $error_description = NULL, $error_uri = NULL) {
    $result['error'] = $error;

    if ($this->getVariable('display_error') && $error_description)
      $result["error_description"] = $error_description;

    if ($this->getVariable('display_error') && $error_uri)
      $result["error_uri"] = $error_uri;

    header("HTTP/1.1 " . $http_status_code);
    $this->sendJsonHeaders();
    echo json_encode($result);

    exit;
  }

  
  private function errorWWWAuthenticateResponseHeader($http_status_code, $realm, $error, $error_description = NULL, $error_uri = NULL, $scope = NULL) {
    $realm = $realm === NULL ? $this->getDefaultAuthenticationRealm() : $realm;

    $result = "WWW-Authenticate: OAuth realm='" . $realm . "'";

    if ($error)
      $result .= ", error='" . $error . "'";

    if ($this->getVariable('display_error') && $error_description)
      $result .= ", error_description='" . $error_description . "'";

    if ($this->getVariable('display_error') && $error_uri)
      $result .= ", error_uri='" . $error_uri . "'";

    if ($scope)
      $result .= ", scope='" . $scope . "'";

    header("HTTP/1.1 ". $http_status_code);
    header($result);

    exit;
  }
}

?>