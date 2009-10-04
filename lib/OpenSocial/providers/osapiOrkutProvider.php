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
 * Pre-defined provider class for Orkut (www.orkut.com)
 * Note: Orkut currently only supports the SecurityToken and
 * 2-legged OAuth auth methods, and it doesn't support the
 * activities end-point.
 * @author Chris Chabot
 */
class osapiOrkutProvider extends osapiProvider {

  /**
   * Specifies the appropriate data for an orkut request.
   * @param osapiHttpProvider httpProvider The HTTP request provider to use.
   */
  public function __construct(osapiHttpProvider $httpProvider = null) {
    parent::__construct(null, null, null, 'http://sandbox.orkut.com/social/rest/', 'http://sandbox.orkut.com/social/rpc', "Orkut", true, $httpProvider);
  }

  /**
   * Adjusts a request prior to being sent in order to fix orkut-specific bugs.
   * @param mixed $request The osapiRequest object being processed, or an array
   *     of osapiRequest objects.
   * @param string $method The HTTP method used for this request.
   * @param string $url The url being fetched for this request.
   * @param array $headers The headers being sent in this request.
   * @param osapiAuth $signer The signing mechanism used for this request.
   */
  public function preRequestProcess(&$request, &$method, &$url, &$headers, osapiAuth &$signer) {
    $this->useBodyHash($signer);

    if (is_array($request)) {
      foreach ($request as $req) {
        $this->fixRequest($req, $method, $url, $headers, $signer);
      }
    } else {
      $this->fixRequest($request, $method, $url, $headers, $signer);
    }
  }

  /**
   * Attempts to correct an atomic orkut request.
   * @param osapiRequest $request The request to fix.
   * @param string $method The HTTP method used for this request.
   * @param string $url The url being fetched for this request.
   * @param array $headers The headers being sent in this request.
   * @param osapiAuth $signer The signing mechanism used for this request.
   */
  private function fixRequest(osapiRequest &$request, &$method, &$url, &$headers, osapiAuth &$signer) {
    $this->fixViewer($request, $url);
    $this->fixFields($request);
  }

  /**
   * Opts to use the body hash signing mechanism instead of adding the entire
   * post body to the signed parameters list.
   * TODO: Eventually this should become default for all containers.
   * @param array $signer The (2 or 3 legged) OAuth or security token signer.
   */
  private function useBodyHash(&$signer) {
    if (method_exists($signer, 'setUseBodyHash')) {
      $signer->setUseBodyHash(true);
    }
  }

  /**
   * Fixes the "non partial app updates are not implemented yet error.
   * @param osapiRequest $request The request to adjust.
   */
  public function fixFields(osapiRequest &$request) {
    if ($request->method == 'appdata.create' ||
        $request->method == 'appdata.update') {
      if (array_key_exists('data', $request->params)) {
        $request->params['fields'] = array_keys($request->params['data']);
      }
    }
  }

  /**
   * Fixes the "forbidden: Only app data for the viewer can be modified" error.
   * @param osapiRequest $request The request to adjust.
   */
  public function fixViewer(osapiRequest &$request, &$url) {
    if ($request->method == 'appdata.create' ||
        $request->method == 'appdata.update' ||
        $request->method == 'activities.create') {
      if (array_key_exists('userId', $request->params)) {
        foreach ($request->params['userId'] as $key => $value) {
          if ($value === "@me") {
            $request->params['userId'][$key] = "@viewer";
          }
        }
      }
    }
  }
}
