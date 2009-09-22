<?php

/**
 * Yahoo! PHP5 SDK
 *
 *  * Yahoo! Query Language
 *  * Yahoo! Social API
 *
 * Find documentation and support on Yahoo! Developer Network: http://developer.yahoo.com
 *
 * Hosted on GitHub: http://github.com/yahoo/yos-social-php5/tree/master
 *
 * @package    yos-social-php5
 * @subpackage yahoo
 *
 * @author     Dustin Whittle <dustin@yahoo-inc.com>
 * @copyright  Copyrights for code authored by Yahoo! Inc. is licensed under the following terms:
 * @license    BSD Open Source License
 *
 *   Permission is hereby granted, free of charge, to any person obtaining a copy
 *   of this software and associated documentation files (the "Software"), to deal
 *   in the Software without restriction, including without limitation the rights
 *   to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *   copies of the Software, and to permit persons to whom the Software is
 *   furnished to do so, subject to the following conditions:
 *
 *   The above copyright notice and this permission notice shall be included in
 *   all copies or substantial portions of the Software.
 *
 *   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *   FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *   AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *   LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *   OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *   THE SOFTWARE.
 **/

require_once 'YahooCurl.class.php';
require_once 'YahooYQLQuery.class.php';
require_once 'YahooOAuthApplicationException.class.php';
require_once 'YahooOAuthAccessToken.class.php';
require_once 'YahooOAuthRequestToken.class.php';
require_once 'YahooOAuthClient.class.php';


class YahooOAuthApplication
{
  public function __construct($consumer_key, $consumer_secret, $application_id, $callback_url = null, $token = null, $options = array(), $client = null)
  {
    $this->client = is_null($client) ? new YahooOAuthClient() : $client;

    $this->consumer_key               = $consumer_key;
    $this->consumer_secret            = $consumer_secret;
    $this->application_id             = $application_id;
    $this->callback_url               = $callback_url;
    $this->token                      = $token;
    $this->options                    = $options;

    $this->consumer                   = new OAuthConsumer($this->consumer_key, $this->consumer_secret);
    $this->signature_method_plaintext = new OAuthSignatureMethod_PLAINTEXT();
    $this->signature_method_hmac_sha1 = new OAuthSignatureMethod_HMAC_SHA1();
  }

  public function getOpenIDUrl($return_to = false, $lang = 'en')
  {
    $openid_request = array(
      'openid.ns'                => 'http://specs.openid.net/auth/2.0',
      'openid.claimed_id'        => 'http://specs.openid.net/auth/2.0/identifier_select',
      'openid.identity'          => 'http://specs.openid.net/auth/2.0/identifier_select',
      'openid.realm'             =>  $this->callback_url,
      'openid.ui.mode'           => 'popup',
      'openid.return_to'         =>  $return_to,
      'openid.mode'              => 'checkid_setup',
      'openid.assoc_handle'      => session_id(),
      'openid.ns.ui'             => 'http://specs.openid.net/extensions/ui/1.0',
      'openid.ui.icon'           => 'true',
      'openid.ui.language'       =>  $lang,
      'openid.ns.ext1'           => 'http://openid.net/srv/ax/1.0',
      'openid.ext1.mode'         => 'fetch_request',
      'openid.ext1.type.email'   => 'http://axschema.org/contact/email',
      'openid.ext1.type.first'   => 'http://axschema.org/namePerson/first',
      'openid.ext1.type.last'    => 'http://axschema.org/namePerson/last',
      'openid.ext1.type.country' => 'http://axschema.org/contact/country/home',
      'openid.ext1.type.lang'    => 'http://axschema.org/pref/language',
      'openid.ext1.required'     => 'email,first,last,country,lang',
      'openid.ns.oauth'          => 'http://specs.openid.net/extensions/oauth/1.0',
      'openid.oauth.consumer'    => $this->consumer_key,
      'openid.oauth.scope'       => '',
      'xopenid_lang_pref'        => $lang,
   );

    return 'https://open.login.yahooapis.com/openid/op/auth?'.http_build_query($openid_request);
  }


  public function validateOpenID()
  {

  }

  # oauth standard apis
  public function getRequestToken()
  {
    # $this->options['lang']
    $parameters = array('xoauth_lang_pref' => 'en');
    $oauth_request = OAuthRequest::from_consumer_and_token($this->consumer, null, 'GET', YahooOAuthClient::REQUEST_TOKEN_API_URL, $parameters);
    $oauth_request->sign_request($this->signature_method_hmac_sha1, $this->consumer, null);
    return $this->client->fetch_request_token($oauth_request);
  }

  public function getAuthorizationUrl($oauth_request_token, $callback = null)
  {
    $oauth_request = OAuthRequest::from_consumer_and_token($this->consumer, $oauth_request_token, 'GET', YahooOAuthClient::AUTHORIZATION_API_URL, array('oauth_callback' => $callback));
    $oauth_request->sign_request($this->signature_method_hmac_sha1, $this->consumer, $oauth_request_token);

    return $oauth_request->to_url();
  }

  public function getAccessToken($oauth_request_token, $verifier = null)
  {
    if ($verifier == null)
    {
      $parameters = array();
    }
    else
    {
      $parameters = array('oauth_verifier' => $verifier);
    }

    $oauth_request = OAuthRequest::from_consumer_and_token($this->consumer, $oauth_request_token, 'GET', YahooOAuthClient::ACCESS_TOKEN_API_URL, $parameters);
    $oauth_request->sign_request($this->signature_method_hmac_sha1, $this->consumer, $oauth_request_token);
    $this->token = $this->client->fetch_access_token($oauth_request);

    return $this->token;
  }

  public function refreshAccessToken($oauth_access_token)
  {
    $parameters = array('oauth_session_handle' => $oauth_access_token->session_handle);
    $oauth_request = OAuthRequest::from_consumer_and_token($this->consumer, $oauth_access_token, 'GET', YahooOAuthClient::REQUEST_TOKEN_API_URL, $parameters);
    $oauth_request->sign_request($this->signature_method_hmac_sha1, $this->consumer, $oauth_access_token);
    $this->token = $this->client->fetch_access_token($oauth_request);

    return $this->token;
  }

  public function getProfile($guid = null)
  {
    if($guid == null && !is_null($this->token))
    {
      $guid = $this->token->yahoo_guid;
    }
    $url = sprintf(YahooOAuthClient::SOCIAL_API_URL.'/user/%s/profile', $guid);
    $parameters = array('format' => 'json');
    $oauth_request = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, 'GET', $url, $parameters);
    $oauth_request->sign_request($this->signature_method_hmac_sha1, $this->consumer, $this->token);

    $data = json_decode($this->client->access_resource($oauth_request));

    return ($data) ? $data->profile : false;
  }

  public function getStatus($guid = null)
  {
    if($guid == null && !is_null($this->token))
    {
      $guid = $this->token->yahoo_guid;
    }

    $url = sprintf(YahooOAuthClient::SOCIAL_API_URL.'/user/%s/profile/status', $guid);
    $parameters = array('format' => 'json');
    $oauth_request = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, 'GET', $url, $parameters);
    $oauth_request->sign_request($this->signature_method_hmac_sha1, $this->consumer, $this->token);

    return json_decode($this->client->access_resource($oauth_request));
  }

  public function setStatus($guid = null, $status)
  {
    if($guid == null && !is_null($this->token))
    {
      $guid = $this->token->yahoo_guid;
    }

    $body = '{"status":{"message":"'.$status.'"}}';

    $url = sprintf(YahooOAuthClient::SOCIAL_API_URL.'/user/%s/profile/status', $guid);
    $parameters = array('format' => 'json');

    $oauth_request = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, 'PUT', $url, $parameters);
    $oauth_request->sign_request($this->signature_method_hmac_sha1, $this->consumer, $this->token);

    $http = YahooCurl::fetch($oauth_request->to_url(), array(), array('Content-Type: application/x-www-form-urlencoded', 'Accept: *'), $oauth_request->get_normalized_http_method(), $body);

    return $http['response_body'];
  }

  public function getConnections($guid = null, $offset = 0, $limit = 10)
  {
    if($guid == null && !is_null($this->token))
    {
      $guid = $this->token->yahoo_guid;
    }

    $url = sprintf(YahooOAuthClient::SOCIAL_API_URL.'/user/%s/connections', $guid);
    $parameters = array('format' => 'json', 'view' => 'usercard', 'start' => $offset, 'count' => $limit);
    $oauth_request = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, 'GET', $url, $parameters);
    $oauth_request->sign_request($this->signature_method_hmac_sha1, $this->consumer, $this->token);

    $data = json_decode($this->client->access_resource($oauth_request));

    return ($data) ? $data->connections->connection : false;
  }

  public function getContacts($guid = null, $offset = 0, $limit = 10)
  {
    if($guid == null && !is_null($this->token))
    {
      $guid = $this->token->yahoo_guid;
    }

    $url = sprintf(YahooOAuthClient::SOCIAL_API_URL.'/user/%s/contacts', $guid);
    $parameters = array('format' => 'json', 'view' => 'tinyusercard', 'start' => $offset, 'count' => $limit);
    $oauth_request = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, 'GET', $url, $parameters);
    $oauth_request->sign_request($this->signature_method_hmac_sha1, $this->consumer, $this->token);

    $data = json_decode($this->client->access_resource($oauth_request));

    return ($data) ? $data->contacts->contact : false;
  }

  public function getUpdates($guid = null, $offset = 0, $limit = 10, $transform = null)
  {
    if($guid == null && !is_null($this->token))
    {
      $guid = $this->token->yahoo_guid;
    }

    $url = sprintf(YahooOAuthClient::SOCIAL_API_URL.'/user/%s/updates', $guid);
    $parameters = array('format' => 'json', 'start' => $offset, 'count' => $limit, 'transform' => ($transform) ? $transform : '( sort "pubDate" numeric descending (all) )');
    $oauth_request = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, 'GET', $url, $parameters);
    $oauth_request->sign_request($this->signature_method_hmac_sha1, $this->consumer, $this->token);

    $data = json_decode($this->client->access_resource($oauth_request));

    return ($data) ? $data->updates : false;
  }

  public function insertUpdate($guid = null, $description, $title, $link)
  {
    if($guid == null && !is_null($this->token))
    {
      $guid = $this->token->yahoo_guid;
    }

    $source = 'APP.'.$this->application_id;
    $suid = 'ugc'.rand(0, 1000);
    $body = sprintf('
    { "updates": [ {
                "class": "app",
                "collectionType": "guid",
                "description": "%s",
                "suid": "%s",
                "link": "%s",
                "source": "%s",
                "pubDate": "%s",
                "title": "%s",
                "type": "appActivity",
                "collectionID": "%s"
            } ] }', $description, $suid, $link, $source, time(), $title, $guid);

    $url = sprintf('%s/user/%s/updates/%s/%s', YahooOAuthClient::SOCIAL_API_URL, $guid, $source, $suid);
    $parameters = array('format' => 'json');

    $oauth_request = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, 'PUT', $url, $parameters);
    $oauth_request->sign_request($this->signature_method_hmac_sha1, $this->consumer, $this->token);

    $http = YahooCurl::fetch($oauth_request->to_url(), array(), array('Content-Type: application/x-www-form-urlencoded', 'Accept: *'), $oauth_request->get_normalized_http_method(), $body);

    return $http['response_body'];
  }

  public function getSocialGraph($offset = 0, $limit = 10)
  {
    $data = $this->yql('select * from social.profile ('.$offset.', '.$limit.') where guid in (select guid from social.connections ('.$offset.', '.$limit.') where owner_guid=me)');

    return isset($data->query->results) ? $data->query->results : false;
  }

  public function getProfileLocation($guid = null)
  {
    if($guid == null && !is_null($this->token))
    {
      $guid = $this->token->yahoo_guid;
    }

    $data = $this->yql(sprintf('select * from geo.places where text in (select location from social.profile where guid="%s")', $guid));

    return isset($data->query->results) ? $data->query->results : false;
  }

  public function getGeoPlaces($location)
  {
    $data = $this->yql(sprintf('select * from geo.places where text="%s"', $location));

    return isset($data->query->results) ? $data->query->results : false;
  }

  public function yql($query, $parameters = array())
  {
    if(is_array($query))
    {
      // handle multi queries
      $query = sprintf('select * from query.multi where queries="%s"', implode(';', str_replace('"', "'", $query)));
    }

    $parameters = array_merge(array('q' => $query, 'format' => 'json', 'env' => YahooYQLQuery::DATATABLES_URL), $parameters);

    $oauth_request = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, 'GET', YahooYQLQuery::OAUTH_API_URL, $parameters);
    $oauth_request->sign_request($this->signature_method_hmac_sha1, $this->consumer, $this->token);

    return json_decode($this->client->access_resource($oauth_request));
  }

}
