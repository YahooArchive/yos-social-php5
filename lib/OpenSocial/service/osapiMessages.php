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
 * OpenSocial API class for Messages requests
 *
 * @author Chris Chabot
 */
class osapiMessages extends osapiService {

  public function get($params) {
    throw new osapiException("Retrieving messages is not supported");
  }

  public function create($params) {
    if (!isset($params['message'])) throw new osapiException("Missing message in osapiMessages->create()");
    if (!$params['message'] instanceof osapiMessage) throw new osapiException("The params['message'] should be a osapiMessage in osapiMessages->create()");
    return osapiRequest::createRequest('messages.create', $params);
  }

  public function delete($params)
  {
    throw new osapiException("Deleting messages is not supported");
  }

  public function update($params)
  {
    throw new osapiException("Updating messages is not supported");
  }

  static public function convertarray($array, $strictMode = true) {

  }
}
