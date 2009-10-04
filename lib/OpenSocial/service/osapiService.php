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

require_once "osapiActivities.php";
require_once "osapiAppData.php";
require_once "osapiMessages.php";
require_once "osapiPeople.php";
require_once "osapiSystem.php";

require_once "osapiAlbums.php";
require_once "osapiMediaItems.php";
require_once "osapiStatusMood.php";
require_once "osapiNotifications.php";
require_once "osapiGroups.php";

/**
 * Abstract base class for the service definitions
 *
 * @author Chris Chabot
 */
abstract class osapiService {
  protected $strictMode = false;

  /**
   * Set strict mode for this service. If set to true
   * osapi will raise an exception on anything that is not
   * spec compliant.
   *
   * @param boolean $strictMode
   */
  public function setStrictMode($strictMode) {
    $this->strictMode = $strictMode;
  }

  /**
   * Returns the correct strict mode
   *
   * @return boolean
   */
  public function getStrictMode() {
    return $this->strictCode;
  }

  /**
   * Trims all the un-used fields from an object or array recursively
   * Every type definition contains all possible OpenSocial fields
   * while a container supports only a specific subset of them, so
   * trimming the response saves a lot of memory and also makes debugging
   * and development a lot easier
   *
   * @param any $object
   * @return any
   */
  protected static function trimResponse(&$object) {
    if (is_array($object)) {
      foreach ($object as $key => $val) {
        // binary compare, otherwise false == 0 == null too
        if ($val === null) {
          unset($object[$key]);
        } elseif (is_array($val) || is_object($val)) {
          $object[$key] = self::trimResponse($val);
        }
      }
    } elseif (is_object($object)) {
      $vars = get_object_vars($object);
      foreach ($vars as $key => $val) {
        if ($val === null) {
          unset($object->$key);
        } elseif (is_array($val) || is_object($val)) {
          $object->$key = self::trimResponse($val);
        }
      }
    }
    return $object;
  }
 
  // Will add this once I check in other Updates.
  //abstract public function getSupportedFields();

  abstract public function get($params);

  abstract public function create($params);

  abstract public function update($params);

  abstract public function delete($params);

  static public function convertarray($array, $strictMode = true) {}
}