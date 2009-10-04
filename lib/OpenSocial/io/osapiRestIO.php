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

/**
 * The REST based implementation of the IO layer. This class
 * sends the batched requests one by one to the REST endpoint
 *
 * @author Chris Chabot
 */
class osapiRestIO extends osapiIO {
  
  // URL templates used to construct the REST requests
  private static $urlTemplates = array(
    'people' => 'people/{userId}/{groupId}/{personId}',
    'activities' => 'activities/{userId}/{groupId}/{appId}/{activityId}',
    'appdata' => 'appdata/{userId}/{groupId}/{appId}',
    'messages' => 'messages/{userId}/outbox/{msgId}', 
    'albums'=>'albums/{userId}/{groupId}/{albumId}',
    'mediaItems'=>'mediaItems/{userId}/{groupId}/{albumId}/{mediaItemId}',
    'groups'=>'groups/{userId}',
    // MySpace Specific
    'statusmood'=>'statusmood/{userId}/{groupId}/{moodId}',
    'notifications'=>'notifications/{userId}/{groupId}'
  );
  
  // Array used to resolve the method to the correct HTTP operation
  private static $methodAliases = array(
    'get' => 'GET',
    'create' => 'POST',
    'delete' => 'DELETE',
    'update' => 'PUT',
    'upload'=>'POST',
    'getSupportedFields'=>'GET',
    'getSupportedMood'=>'GET'
  );
  
  // Array used to define which field in the params array is supposed to be the post body
  private static $postAliases = array(
    'people' => 'person',
    'activities' => 'activity', 
    'appdata' => 'data',
    'messages' => 'message', 
    'albums'=>'album',
    'mediaItems'=>'mediaItem',
    'statusmood'=>'statusMood',
    'notifications'=>'notification'
  );

  /**
   * Sends the batched requests to the REST endpoint, the actual sending
   * is done in self::executeRestRequest()
   *
   * @param array $requests
   * @param osapiProvider $provider
   * @param osapiAuth $signer
   * @return array results
   */
  public static function sendBatch(Array $requests, osapiProvider $provider, osapiAuth $signer, $strictMode = false) {
    $ret = array();
    foreach ($requests as $request) {
      $entry = self::executeRestRequest($request, $provider, $signer);
      
      // flip 'entry' to 'list' so the result structure processing can be the same between the RPC and REST implementations
      if (!$entry instanceof osapiError) {
        if (isset($entry['data']['entry'])) {
          $entry['data']['list'] = $entry['data']['entry'];
          unset($entry['data']['entry']);
        }
        if (isset($entry['data']['list']) && count($entry['data']) != 1) {
          foreach ($entry['data']['list'] as $key => $val) {
            $entry['data']['list'][$key] = self::convertArray($request, $val, $strictMode);
          }
          $entry['data'] = self::listToCollection($entry['data'], $strictMode);
        } else {
          if (isset($entry['data']['list'])) {
            $entry['data'] = self::convertArray($request, $entry['data']['list'], $strictMode);
          }else{
          	$entry['data'] = self::convertArray($request, $entry['data'], $strictMode);
          }
        }
      }
      if (isset($request->id)) {
        $ret[$request->id] = is_array($entry) && isset($entry['data']) ? $entry['data'] : $entry;
      } else {
        $ret[] = $entry;
      }
    }

    if (method_exists($provider, 'postParseResponseProcess')) {
      $provider->postParseResponseProcess($request, $ret);
    }
    
    return $ret;
  }

  /**
   * Performs the actual REST request by rewriting
   * the method (people.get) to the proper REST endpoint
   * and converting the params into a properly formed
   * REST url
   *
   * @param osapiRequest $request
   * @return array decoded response body
   */
  private static function executeRestRequest(osapiRequest $request, osapiProvider $provider, osapiAuth $signer) {
    $service = $request->getService($request->method);
    $operation = $request->getOperation($request->method);

    if (! isset(self::$urlTemplates[$service])) {
      throw new osapiException("Invalid service: $service");
    }
    $urlTemplate = self::$urlTemplates[$service];

    if (! isset(self::$methodAliases[$operation])) {
      throw new osapiException("Invalid method: ($service) $operation");
    }
    $method = self::$methodAliases[$operation];
    $postBody = false;
    $headers = false;
    $hasPostBody = false;
    
    if ($method != 'GET') {
      if (isset(self::$postAliases[$service]) && isset($request->params[self::$postAliases[$service]])) {
      	$hasPostBody = true;
      	$headers = array("Content-Type: application/json");
      	
      	if($request->method == 'mediaItems.upload'){
          $postBody = $request->params[self::$postAliases[$service]];
          $headers = array("Content-Type: " . $request->params['contentType'], 'Expect:');
          unset($request->params['contentType']);
      	}
      }
    }
    
    $baseUrl = $provider->restEndpoint;
    if (substr($baseUrl, strlen($baseUrl) - 1, 1) == '/') {
      // Prevent double //'s in the url when concatinating
      $baseUrl = substr($baseUrl, 0, strlen($baseUrl) - 1);
    }

    if (method_exists($provider, 'preRequestProcess')) {
      // Note that we're passing baseUrl, not the complete service URL.
      // It should be easier to change service parameters by changing
      // the params array than modifying a string url.
      $provider->preRequestProcess($request, $method, $baseUrl, $headers, $signer);
    }

    if ($hasPostBody) {
        if($request->method == 'mediaItems.upload') {
            // If we are uploading a mediaItem don't try to json_encode it.
            $postBody = $request->params[self::$postAliases[$service]];
        }else {
            // Pull out the (possibly) modified post body parameter and
            // unset it from the request, so that it doesn't get signed.
            $postBody = json_encode($request->params[self::$postAliases[$service]]);
        }
        unset($request->params[self::$postAliases[$service]]);
    }

    $url = $baseUrl . self::constructUrl($urlTemplate, $request->params);
    if (! $provider->isOpenSocial) {
      // PortableContacts end points don't require the /people bit added
      $url = str_replace('/people', '', $url);
    }
    
    $signedUrl = $signer->sign($method, $url, $request->params, $postBody, $headers);
    $response = self::send($signedUrl, $method, $provider->httpProvider, $headers, $postBody);

    if (method_exists($provider, 'postRequestProcess')) {
      $provider->postRequestProcess($request, $response);
    }
    $ret = array();
    
    // Added 201 for create requests
    if (($response['http_code'] == '200' || $response['http_code'] == '201')  && !empty($response['data'])) {
      $ret['data'] = json_decode($response['data'], true);
      if ($ret['data'] == $response['data']) {
        // signals a failure in decoding the json
        throw new osapiException("Error decoding server response: '" . $response['data'] . "'");
      }
    } else {
      $ret = new osapiError($response['http_code'], isset($response['data']) ? $response['data'] : '');
    }
    
    return $ret;
  }

  /**
   * Fill in the URL based on the service template, anything that's not part of the URL is posted as query param
   * The url components are removed from the params so they are passed by reference
   * @param string $template the url template
   * @param $params Array parameters
   * @return string url
   */
  private static function constructUrl($urlTemplate, &$params) {
    $url = '';
    $urlParts = explode('/', $urlTemplate);
    foreach ($urlParts as $part) {
      if (substr($part, 0, 1) == '{' && substr($part, strlen($part) - 1, 1) == '}') {
        $tag = substr($part, 1, strlen($part) - 2);
        if (isset($params[$tag])) {
          $url .= '/' . (is_array($params[$tag]) ? implode(',', $params[$tag]) : $params[$tag]);
          unset($params[$tag]);
        }
      } else {
        $url .= '/' . $part;
      }
    }
    // Everything that was a part of the url template has been removed from the array, so what's left are the query params
    if (count($params)) {
      foreach ($params as $key => $val) {
        if (is_array($val)) {
          $val = implode(',', $val);
        }
        $url .= ((strpos($url, '?') === false) ? '?' : '&') . urlencode($key) . '=' . urlencode($val);
      }
    }
    return $url;
  }
}
