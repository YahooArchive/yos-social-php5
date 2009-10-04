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
 * OpenSocial API class for Application Data requests
 * Supported methods are get, create, update and delete
 *
 * @author Chris Chabot
 */
class osapiAppData extends osapiService {

  /**
   * Gets a set of appdata.
   *
   * @param array $params the parameters defining which appdata to retrieve
   * @return osapiRequest the request
   */
  public function get($params) {
    if (!isset($params['userId'])) throw new osapiException("Invalid or no userId specified for osapiAppData->get");
    if (!isset($params['groupId'])) throw new osapiException("Invalid or no groupId specified for osapiAppData->get");
    if (!isset($params['appId'])) throw new osapiException("Invalid or no appId specified for osapiAppData->get");
      if (isset($params['fields'])) {
      if (!is_array($params['fields'])) throw new osapiException("Optional param 'fields' should be an array in osapiAppData->get");
      foreach ($params['fields'] as $key) {
        if (!self::isValidKey($key)) {
          throw new osapiException("Invalid key specified in osapiAppData->get: $key");
        }
      }
    }
    return osapiRequest::createRequest('appdata.get', $params);
  }

  /**
   * Creates a set of appata.
   *
   * @param array $params the parameters defining which appdata to create
   * @return osapiRequest the request
   */
  public function create($params) {
    if (!isset($params['userId'])) throw new osapiException("Invalid or no userId specified for osapiAppData->create");
    if (!isset($params['groupId'])) throw new osapiException("Invalid or no groupId specified for osapiAppData->create");
    if (!isset($params['appId'])) throw new osapiException("Invalid or no appId specified for osapiAppData->create");
    if (!isset($params['data'])) throw new osapiException("Invalid or no data array specified for osapiAppData->create");
    if (!is_array($params['data'])) throw new osapiException("Invalid data specified, should be an array for osapiAppData->create");
    if (isset($params['fields']) && !is_array($params['fields'])) throw new osapiException("Optional param 'fields' should be an array in osapiAppData->create");
    foreach (array_keys($params['data']) as $key) {
      if (!self::isValidKey($key)) {
        throw new osapiException("Invalid key specified: $key");
      }
    }
    return osapiRequest::createRequest('appdata.create', $params);
  }

  /**
   * Updates a set of appdata.
   *
   * @param array $params the parameters defining which appdata to update
   * @return osapiRequest the request
   */
  public function update($params) {
    if (!isset($params['userId'])) throw new osapiException("Invalid or no userId specified for osapiAppData->update");
    if (!isset($params['groupId'])) throw new osapiException("Invalid or no groupId specified for osapiAppData->update");
    if (!isset($params['appId'])) throw new osapiException("Invalid or no appId specified for osapiAppData->update");
    if (isset($params['fields']) && !is_array($params['fields'])) throw new osapiException("Optional param 'fields' should be an array in osapiAppData->update");
    foreach (array_keys($params['data']) as $key) {
      if (!self::isValidKey($key)) {
        throw new osapiException("Invalid key specified: $key");
      }
    }
    return osapiRequest::createRequest('appdata.update', $params);
  }

  /**
   * Deletes a set of appdata.
   *
   * @param array $params the parameters defining which appdata to delete
   * @return osapiRequest the request
   */
  public function delete($params) {
    if (!isset($params['userId'])) throw new osapiException("Invalid or no userId specified for osapiAppData->delete");
    if (!isset($params['groupId'])) throw new osapiException("Invalid or no groupId specified for osapiAppData->delete");
    if (!isset($params['appId'])) throw new osapiException("Invalid or no appId specified for osapiAppData->delete");
    if (isset($params['fields'])) {
      if (!is_array($params['fields'])) throw new osapiException("Optional param 'fields' should be an array in osapiAppData->delete");
      foreach ($params['fields'] as $key) {
        if (!self::isValidKey($key)) {
          throw new osapiException("Invalid key specified in osapiAppData->delete: $key");
        }
      }
    }
    return osapiRequest::createRequest('appdata.delete', $params);
  }

  static public function convertarray($array, $strictMode = true) {
    return $array;
  }

  /**
   * Determines whether an appdata key is valid, or if it uses restricted characters.
   *
   * @param string $key the appdata key
   * @return boolean whether the key is valid
   */
  public static function isValidKey($key) {
    if (empty($key)) {
      return false;
    }
    if ($key == '*') {
      return true;
    }
    for ($i = 0; $i < strlen($key); ++ $i) {
      $c = substr($key, $i, 1);
      if (($c >= 'a' && $c <= 'z') || ($c >= 'A' && $c <= 'Z') || ($c >= '0' && $c <= '9') || ($c == '-') || ($c == '_') || ($c == '.')) {
        continue;
      }
      return false;
    }
    return true;
  }
}
