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
 * The osapiLocalHttpProvider class is used to define a mock HTTP layer that
 * returns a pre-defined response.
 *
 * @author Dan Holevoet
 */
class osapiLocalHttpProvider extends osapiHttpProvider {
  protected $responses;
  protected $request;
  
  public function __construct() {
    $this->responses = array();
    $this->request = null;
  }
  
  /**
   * Sends a request using the supplied parameters.
   *
   * @param string $url the requested URL
   * @param string $method the HTTP verb to use
   * @param string $postBody the optional POST body to send in the request
   * @param boolean $headers whether or not to return header information
   * @param string $ua the user agent to send in the request
   * @return array the returned data, parsed response headers, and status code
   */
  public function send($url, $method, $postBody = false, $headers = false, $ua = self::USER_AGENT) {
    $this->request = array(
      'url' => $url,
      'method' => $method,
      'body' => $postBody,
      'headers' => $headers
    );
    
    osapiLogger::info("HTTP Request");
    osapiLogger::info($this->request);
    
    $response = array_shift($this->responses);
    
    osapiLogger::info("HTTP Response");
    osapiLogger::info($response);

    return $response;
  }
  
  /**
   * Adds a fake response to the queue of responses.
   * @param string $data The data to return.
   * @param int $status The http status code to return.
   * @param array $headers optional The array of parsed headers to return.
   */
  public function addResponse($data, $status = 200, $headers = array()) {
    $this->responses[] = array(
      'http_code' => $status,
      'data' => $data,
      'headers' => $headers
    );
  }

  /**
   * Gets the last request which was executed through this object.
   * @return array The last request made with this instance.
   */
  public function getLastRequest() {
    return $this->request;
  }
}
