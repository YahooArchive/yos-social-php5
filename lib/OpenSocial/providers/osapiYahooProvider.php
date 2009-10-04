<?php
/**
 * @package OpenSocial
 * @license Apache License
 *
 * Copyright 2008 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the 'License');
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an 'AS IS' BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Pre-defined provider class for Yahoo! (http://developer.yahoo.com/opensocial)
 */
class osapiYahooProvider extends osapiProvider {
  public function __construct(osapiHttpProvider $httpProvider = null) {
    parent::__construct('https://api.login.yahoo.com/oauth/v2/get_request_token', 'https://api.login.yahoo.com/oauth/v2/request_auth', 'https://api.login.yahoo.com/oauth/v2/get_token', 'http://appstore.apps.yahooapis.com/social/rest', null, 'Yahoo!', true, $httpProvider);
  }

  /**
   * Set's the signer's useBodyHash to true
   * @param mixed $request The osapiRequest object being processed, or an array
   *     of osapiRequest objects.
   * @param string $method The HTTP method used for this request.
   * @param string $url The url being fetched for this request.
   * @param array $headers The headers being sent in this request.
   * @param osapiAuth $signer The signing mechanism used for this request.
   */
  public function preRequestProcess(&$request, &$method, &$url, &$headers, osapiAuth &$signer) {
    if (method_exists($signer, 'setUseBodyHash')) {
      $signer->setUseBodyHash(false);
    }
  }
}
