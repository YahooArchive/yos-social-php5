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
 * Pre-defined provider class for Plaxo (www.plaxo.com)
 * @author Chris Chabot
 */
class osapiPlaxoProvider extends osapiProvider {
  public function __construct(osapiHttpProvider $httpProvider = null) {
    parent::__construct("http://www.plaxo.com/oauth/request", "http://www.plaxo.com/oauth/authorize", "http://www.plaxo.com/oauth/activate", "http://www.plaxo.com/pdata/contacts", null, "Plaxo", false, $httpProvider);
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
    $this->fixFetchPersonById($request, $signer);
  }

  /**
   * Plaxo doesn't allow for fetching a user directly by ID.
   * TODO: Implement a fix to allow requesting users directly by ID in plaxo (/@me/@all/[id])
   * @param osapiRequest $request
   */
  private function fixFetchPersonById(&$request, &$signer) {
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
   * Parses Plaxo's gross HTML-ified error response.
   * @param array $response
   */
  private function fixNastyResponses(&$response) {
    if ($response['http_code'] == 400 ||
        $response['http_code'] == 401) {
      $matches = array();
      if (preg_match("/<p class=\"error\" style=\"font-family: monospace\">(.*?)<\/p>/", $response['data'], $matches )) {
        $response['data'] = $matches[1];
      }
    }
  }

  /**
   * Attempts to correct the parsed response to fix per-container bugs.
   * @param osapiRequest $request 
   * @param array $response The response object
   */
  public function postParseResponseProcess(osapiRequest &$request, &$response) {
    foreach ($response as $resp) {
      $this->fixPersonIds($resp);
    }
  }

  /**
   * Adds an ID parameter to a returned Plaxo person object since Plaxo doesn't
   * return one where we expect it to be.
   * @param array $response
   */
  public function fixPersonIds(&$response) {
    if ($response instanceof osapiPerson) {
      $accounts = $response->getAccounts();
      foreach ($accounts as $account) {
        if ($account['domain'] === 'plaxo.com') {
          $response->setId($account['userid']);
          break;
        }
      }
    }
  }
}
