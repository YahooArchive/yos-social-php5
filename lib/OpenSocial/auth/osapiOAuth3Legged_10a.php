<?php
/**
 * @package OpenSocial
 * @license Apache License
 *
 * Copyright 2008 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

require_once "osapiOAuth2Legged.php";

/**
 * Authentication class that deals with 3-Legged OAuth 1.0a
 * See http://sites.google.com/site/oauthgoog/2leggedoauth/2opensocialrestapi
 * for more information on the difference between 2 or 3 leggged oauth.
 *
 * This class uses the new and improved OAuth 1.0a spec which has a slightly
 * different work flow in how callback urls, request & access tokens are dealt with
 * to prevent a possible man in the middle attack
 *
 * @author Chris Chabot
 */
class osapiOAuth3Legged_10a extends osapiOAuth2Legged {
  protected $userId;
  protected $consumerToken;
  protected $accessToken;
  protected $provider;
  private $localUserId;
  private $storage;
  public $storageKey;

  /**
   * Instantiates the class, but does not initiate the login flow, leaving it
   * to the discretion of the caller.
   *
   * @param string $consumerKey
   * @param string $consumerSecret
   * @param osapiStorage $storage storage class to use (file,apc,memcache,mysql)
   * @param osapiProvider $provider the provider configuration (required to get the oauth endpoints)
   * @param any $localUser the *local* user ID (this is not the user's ID on the social network site, but the user id on YOUR site, this is used to link the oauth access token to a local login)
   * @param any $userId the *remote* user ID, you can supply this user id if known but it's completely optional. If set it will be included in the oauth requests in the xoauth_requestor_id field)
   */
  public function __construct($consumerKey, $consumerSecret, osapiStorage $storage, osapiProvider $provider, $localUserId = null, $userId = null) {
    $this->provider = $provider;
    $this->localUserId = $localUserId;
    $this->userId = $userId;
    $this->consumerToken = new OAuthConsumer($consumerKey, $consumerSecret, NULL);
    $this->signatureMethod = new OAuthSignatureMethod_HMAC_SHA1();
    $this->storage = $storage;
    $this->storageKey = 'OAuth:' . $consumerKey . ':' . $userId . ':' . $localUserId; // Scope data to the local user as well, or else multiple local users will share the same OAuth credentials.
    if (($token = $storage->get($this->storageKey)) !== false) {
      $this->accessToken = $token;
    }
  }

  /**
   * The 3 legged oauth class needs a way to store the access key and token
   * it uses the osapiStorage class to do so.
   *
   * Constructing this class will initiate the 3 legged oauth work flow, including redirecting
   * to the OAuth provider's site if required(!)
   *
   * @param string $consumerKey
   * @param string $consumerSecret
   * @param osapiStorage $storage storage class to use (file,apc,memcache,mysql)
   * @param osapiProvider $provider the provider configuration (required to get the oauth endpoints)
   * @param any $localUser the *local* user ID (this is not the user's ID on the social network site, but the user id on YOUR site, this is used to link the oauth access token to a local login)
   * @param any $userId the *remote* user ID, you can supply this user id if known but it's completely optional. If set it will be included in the oauth requests in the xoauth_requestor_id field)
   * @return osapiOAuth3Legged the logged-in provider instance
   */
  public static function performOAuthLogin($consumerKey, $consumerSecret, osapiStorage $storage, osapiProvider $provider, $localUserId = null, $userId = null) {
    $auth = new osapiOAuth3Legged($consumerKey, $consumerSecret, $storage, $provider, $localUserId, $userId);
    if (($token = $storage->get($auth->storageKey)) !== false) {
      $auth->accessToken = $token;
    } else {
      if (isset($_GET['oauth_verifier']) && isset($_GET['oauth_token'])  && isset($_GET['uid'])) {
        $uid = $_GET['uid'];
        $secret = $auth->storage->get($auth->storageKey.":nonce" . $uid);
        $auth->storage->delete($auth->storageKey.":nonce" . $uid);
        $token = $auth->upgradeRequestToken($_GET['oauth_token'], $secret, $_GET['oauth_verifier']);
        $auth->redirectToOriginal();
      } else {
        // Initialize the OAuth dance, first request a request token, then kick the client to the authorize URL
        // First we store the current URL in our storage, so that when the oauth dance is completed we can return there
        $callbackUrl = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $uid = uniqid();
        $token = $auth->obtainRequestToken($callbackUrl, $uid);
        $auth->storage->set($auth->storageKey.":nonce" . $uid,  $token->secret);
        $auth->redirectToAuthorization($token);
      }
    }

    return $auth;
  }

  /**
   * Upgrades an existing request token to an access token.
   *
   * @param osapiStorage $storage storage class to use (file,apc,memcache,mysql)
   * @param osapiProvider $provider the provider configuration (required to get the oauth endpoints)
   * @param oauthVerifier
   */
  public function upgradeRequestToken($requestToken, $requestTokenSecret, $oauthVerifier) {
    $ret = $this->requestAccessToken($requestToken, $requestTokenSecret, $oauthVerifier);
    if ($ret['http_code'] == '200') {
      $matches = array();
      @parse_str($ret['data'], $matches);
      if (!isset($matches['oauth_token']) || !isset($matches['oauth_token_secret'])) {
        throw new osapiException("Error authorizing access key (result was: {$ret['data']})");
      }
      // The token was upgraded to an access token, we can now continue to use it.
      $this->accessToken = new OAuthConsumer(urldecode($matches['oauth_token']), urldecode($matches['oauth_token_secret']));
      $this->storage->set($this->storageKey, $this->accessToken);
      return $this->accessToken;
    } else {
      throw new osapiException("Error requesting oauth access token, code " . $ret['http_code'] . ", message: " . $ret['data']);
    }
  }

  /**
   * Sends the actual request to exchange an existing request token for an access token.
   *
   * @param string $requestToken the existing request token
   * @param string $requestTokenSecret the request token secret
   * @param osapiProvider $provider the provider configuration (required to get the oauth endpoints)
   * @return array('http_code' => HTTP response code (200, 404, 401, etc), 'data' => the html document)
   */
  protected function requestAccessToken($requestToken, $requestTokenSecret, $oauthVerifier) {
    $accessToken = new OAuthConsumer($requestToken, $requestTokenSecret);
    $accessRequest = OAuthRequest::from_consumer_and_token($this->consumerToken, $accessToken, "GET", $this->provider->accessTokenUrl, array('oauth_verifier' => $oauthVerifier));
    $accessRequest->sign_request($this->signatureMethod, $this->consumerToken, $accessToken);
    return osapiIO::send($accessRequest, 'GET', $this->provider->httpProvider);
  }

  /**
   * Redirects the page to the original url, prior to OAuth initialization. This removes the extraneous
   * parameters from the URL, adding latency, but increasing user-friendliness.
   *
   * @param osapiStorage $storage storage class to use (file,apc,memcache,mysql)
   */
  public function redirectToOriginal() {
    $originalUrl = $this->storage->get($this->storageKey.":originalUrl");
    if ($originalUrl && !empty($originalUrl)) {
      // The url was retrieve successfully, remove the temporary original url from storage, and redirect
      $this->storage->delete($this->storageKey.":originalUrl");
      header("Location: $originalUrl");
    }
  }

  /**
   * Obtains a request token from the specified provider.
   *
   * @param osapiStorage $storage storage class to use (file,apc,memcache,mysql)
   * @param osapiProvider $provider the provider configuration (required to get the oauth endpoints)
   */
  public function obtainRequestToken($callbackUrl, $uid) {
    $this->storage->set($this->storageKey.":originalUrl", $callbackUrl);
    $callbackParams = (strpos($_SERVER['REQUEST_URI'], '?') !== false ? '&' : '?') . 'uid=' . urlencode($uid);
    $ret = $this->requestRequestToken($callbackUrl . $callbackParams);
    if ($ret['http_code'] == '200') {
      $matches = array();
      preg_match('/oauth_token=(.*)&oauth_token_secret=(.*)/', $ret['data'], $matches);
      if (!is_array($matches) || count($matches) != 3) {
        throw new osapiException("Error retrieving request key ({$ret['data']})");
      }
      return new OAuthToken(urldecode($matches[1]), urldecode($matches[2]));
    } else {
      throw new osapiException("Error requesting oauth request token, code " . $ret['http_code'] . ", message: " . $ret['data']);
    }
  }

  /**
   * Sends the actual request to obtain a request token.
   *
   * @param osapiProvider $provider the provider configuration (required to get the oauth endpoints)
   * @return array('http_code' => HTTP response code (200, 404, 401, etc), 'data' => the html document)
   */
  protected function requestRequestToken($callbackUrl) {
    $requestTokenRequest = OAuthRequest::from_consumer_and_token($this->consumerToken, NULL, "GET", $this->provider->requestTokenUrl, array());
    if(is_array($this->provider->requestTokenParams)){
      foreach($this->provider->requestTokenParams as $key => $value) {
        $requestTokenRequest->set_parameter($key, $value);
      }
    }
    $requestTokenRequest->set_parameter('oauth_callback', $callbackUrl);
    $requestTokenRequest->sign_request($this->signatureMethod, $this->consumerToken, NULL);
    return osapiIO::send($requestTokenRequest, 'GET', $this->provider->httpProvider);
  }

  /**
   * Redirect the uset to the (provider's) authorize page, if approved it should kick the user back to the call back URL
   * which hopefully means we'll end up in the constructor of this class again, but with oauth_continue=1 set
   *
   * @param OAuthToken $token the request token
   * @param string $callbackUrl the URL to return to post-authorization (passed to login site)
   */
  public function redirectToAuthorization($token) {
    $authorizeRedirect = $this->provider->authorizeUrl . "?oauth_token={$token->key}";
    header("Location: $authorizeRedirect");
  }
}
