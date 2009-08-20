<?php
/*
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
 * Pre-defined provider class for MySpace (www.myspace.com)
 * @author Chris Chabot
 */
class osapiMySpaceProvider extends osapiProvider {
  public function __construct(osapiHttpProvider $httpProvider = null) {
    parent::__construct("http://api.myspace.com/request_token", "http://api.myspace.com/authorize", "http://api.myspace.com/access_token", "http://api.myspace.com/v2", null, "MySpace", true, $httpProvider);
  }

  /**
   * Adjusts a request prior to being sent in order to fix container-specific
   * bugs.
   * @param mixed $request The osapiRequest object being processed, or an array
   *     of osapiRequest objects.
   * @param string $method The HTTP method used for this request.
   * @param string $url The url being fetched for this request.
   * @param array $headers The headers being sent in this request.
   * @param osapiAuth $signer The signing mechanism used for this request.
   */
   public function preRequestProcess(&$request, &$method, &$url, &$headers, osapiAuth &$signer) {
    if (is_array($request)) {
      foreach ($request as $req) {
        $this->fixRequest($req, $method, $url, $headers, $signer);
      }
    } else {
      $this->fixRequest($request, $method, $url, $headers, $signer);
    }
  }

  /**
   * Attempts to correct an atomic request.
   * @param osapiRequest $request The request to fix.
   * @param string $method The HTTP method used for this request.
   * @param string $url The url being fetched for this request.
   * @param array $headers The headers being sent in this request.
   * @param osapiAuth $signer The signing mechanism used for this request.
   */
  private function fixRequest(osapiRequest &$request, &$method, &$url, &$headers, osapiAuth &$signer) {
    $this->fixAtMe($request, $url, $signer);
  }


  /**
   * MySpace returns "oauth_problem=permission_denied" if you request @me/@self.
   * @param osapiRequest $request
   * @param string $url
   * @param osapiAuth $signer
   */
  private function fixAtMe(osapiRequest &$request, &$url, osapiAuth &$signer) {
    if (method_exists($signer, 'getUserId')) {
      $userId = $signer->getUserId();
      if ($userId) {
        if (array_key_exists('userId', $request->params)) {
          if (is_array($request->params['userId'])) {
            foreach($request->params['userId'] as $key => $value) {
              if ($value == '@me') {
                $request->params['userId'][$key] = $userId;
              }
            }
          } else if ($request->params['userId'] == '@me') {
            $request->params['userId'] = $userId;
          }
        }
      }
    }
  }

  /**
   * Attempts to correct a response to address per-container bugs.
   * @param osapiRequest $request
   * @param array $response
   */
  public function postRequestProcess(osapiRequest &$request, &$response) {
    $this->fixNastyResponses($response);
  }

  /**
   * Parses MySpace's gross HTML-ified error response.
   * @param array $response
   */
  private function fixNastyResponses(&$response) {
    if ($response['http_code'] == 403) {
      $matches = array();
      if (preg_match("/<h2>(.*?)<\/h2>/s", $response['data'], $matches )) {
        $response['data'] = $matches[1];
      }
    }
  }
}