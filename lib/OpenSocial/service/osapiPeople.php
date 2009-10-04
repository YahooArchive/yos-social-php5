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
 * OpenSocial API class for People requests
 * Only the get method is supported in the OpenSocial spec.
 *
 * @author Chris Chabot
 */
class osapiPeople extends osapiService {

  /**
   * Gets a set of people.
   *
   * @param array $params the parameters defining which people to retrieve
   * @return osapiRequest the request
   */
  public function get($params) {
    return osapiRequest::createRequest('people.get', $params);
  }
  
  /**
   * Gets a list of fields supported by this service
   *
   * @return osapiRequest the request
   */
  public function getSupportedFields() {
    return osapiRequest::createRequest('people.getSupportedFields', array('userId' => '@supportedFields'));
  }
  
  /**
   * Gets the application's viewer.
   *
   * @param array $params the parameters defining which people to retrieve
   * @return osapiRequest the request
   */
  public function getViewer($params) {
    return osapiRequest::createRequest('people.get', array_merge(array('userId' => '@viewer'), $params));
  }

  public function getOwner($params) {
    throw new Exception("The OpenSocial RESTful API doesn't have an owner");
  }

  public function update($params)
  {
    throw new osapiException("Updating people is not supported");
  }

  public function delete($params)
  {
    throw new osapiException("Deleting people is not supported");
  }

  public function create($params)
  {
    throw new osapiException("Creating people is not supported");
  }

  /**
   * Converts a response into a native data type.
   *
   * @param array $array the raw data
   * @param boolean $strictMode whether to throw spec errors
   * @return osapiPerson
   */
  static public function convertarray($array, $strictMode = true) {
    //TODO parse out the list entities into typed objects (see osapi/src/models for the type models)
    $id = isset($array['id']) ? $array['id'] : null;
    $name = null;
    if (isset($array['displayName'])) {
      $name = $array['displayName'];
    } elseif (isset($array['name']) && is_array($array['name'])) {
      $name = implode(', ', $array['name']);
    }
    $person = new osapiPerson($id, $name);
    $personVars = get_object_vars($person);
    foreach ($array as $key => $val) {
      if (array_key_exists($key, $personVars)) {
        $person->$key = $val;
        // remove the assigned field, so we can detect unexpected fields later
        unset($array[$key]);
      }
    }
    if ($strictMode && count($array)) {
      throw new osapiException("Unexpected fields in people response". print_r($array, true));
    }
    return self::trimResponse($person);
  }
}
