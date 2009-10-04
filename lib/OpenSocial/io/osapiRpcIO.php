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
 * The JSON-RPC based implementation of the IO layer. This class
 * sends the complete batch of requests in a single request which saves
 * a lot of connection overhead, thus is used by default if the RPC
 * endpoint is available
 *
 * @author Chris Chabot
 */
class osapiRpcIO extends osapiIO {
  /**
   * Sends the batched requests to the RPC endpoint
   *
   * @param array $requests
   * @param osapiProvider $provider
   * @param osapiAuth $signer
   * @param boolean $strictMode
   * @return array results
   */
  public static function sendBatch(Array $requests, osapiProvider $provider, osapiAuth $signer, $strictMode = false) {
    $method = 'POST';
    $url = $provider->rpcEndpoint;
    $params = array();
    $headers = array("Content-Type: application/json");

    if (method_exists($provider, 'preRequestProcess')) {
      $provider->preRequestProcess($requests, $method, $url, $headers, $signer);
    }

    $request = json_encode($requests);
    $signedUrl = $signer->sign($method, $url, $params, $request, $headers);
    $ret = self::send($signedUrl, $method, $provider->httpProvider, $headers, $request);
    if (method_exists($provider, 'postRequestProcess')) {
      $provider->postRequestProcess($requests, $response);
    }
    if ($ret['http_code'] == '200') {
      $result = json_decode($ret['data'], true);
      // the decode result and input string being the same indicates a decoding failure
      if ($result == $ret['data']) {
        throw new osapiException("Error decoding response body:\n" . $ret['data']);
      }
      if (isset($result['code']) && $result['code'] == '401') {
        throw new osapiAuthError("Authentication error: {$result['message']}");
      }
      if (is_array($result)) {
        // rewrite the result set into a $key => $response set
        $ret = array();
        foreach ($result as $entry) {
          $requestType = '';
          foreach ($requests as $request) {
            if (isset($entry['id']) && $request->id == $entry['id']) {
              $requestType = $request->getService($request->method);
              break;
            }
          }
          if (isset($entry['error'])) {
            $entry['data'] = new osapiError(isset($entry['error']['code']) ? $entry['error']['code'] : 500, isset($entry['error']['message']) ? $entry['error']['message'] : 'Unknown error occured');
            unset($entry['error']);
          } else {
            if (isset($entry['data']['entry'])) {
              if ($strictMode) {
                throw new osapiException("Invalid RPC response body, collection key should be a 'list' and not 'entry'");
              } else {
                $entry['data']['list'] = $entry['data']['entry'];
              }
            }
            if (isset($entry['data']['list'])) {
              // first see if we can convert each individual response entry into a typed object
              if (isset($entry['id'])) {
                // map back to the original request so we can determine the expected type
                foreach ($entry['data']['list'] as $key => $val) {
                  $entry['data']['list'][$key] = self::convertArray($request, $val, $strictMode);
                }
              }
              $entry['data'] = self::listToCollection($entry['data'], $strictMode);
            } else {
              // convert the response into a type implementation if no error occured
              if (isset($entry['data'])) {
                $entry['data'] = self::convertArray($request, $entry['data'], $strictMode);
              }
            }
          }
          if (isset($entry['id'])) {
            // remove the request id from the data array, and use that id to store it in the results array
            $id = $entry['id'];
            unset($entry['id']);
            $ret[$id] = isset($entry['data']) ? $entry['data'] : $entry;
          } else {
            // no request id specified, store it as-is
            $ret[] = $entry;
          }
        }
        if (method_exists($provider, 'postParseResponseProcess')) {
          $provider->postParseResponseProcess($requests, $ret);
        }
        return $ret;
      } else {
        if (method_exists($provider, 'postParseResponseProcess')) {
          $provider->postParseResponseProcess($request, $result);
        }
        return $result;
      }
    } else {
      throw new osapiException("Error sending RPC request: " . $ret['data']);
    }
  }
}
