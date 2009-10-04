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
 * An OpenSocial RESTful client who's API is roughly inspired by the new lightweight JS API:
 * http://wiki.opensocial.org/index.php?title=Lightweight_JS_APIs
 *
 * This library supports both the RPC and REST API of OpenSocial, and supports
 * both 2 legged and 3 legged OAuth.
 */

require_once "external/XrdsSimpleParser.php";
require_once "providers/osapiProvider.php";
require_once "storage/osapiStorage.php";
require_once "io/osapiIO.php";
require_once "io/osapiHttpProvider.php";
require_once "service/osapiService.php";
require_once "auth/osapiAuth.php";
require_once "model/osapiModel.php";
require_once "logger/osapiLogger.php";

/* Basic exception classes */
class osapiException extends Exception {}
class osapiAuthError extends Exception {}
class osapiStorageException extends Exception {}
class osapiLoggerException extends Exception {}

/**
 * The osapi (OpenSocial API) class can be used to work with social
 * information on a remote server that supports the OpenSocial (or
 * PortableContacts) RESTful API and OAuth.
 *
 * See the the samples in the examples directory for example usage
 *
 * @author Chris Chabot
 */
class osapi {
  private $userId;
  private $config;
  private $strictMode = false;
  private $availableServices = array(
    'people' => 'osapiPeople',
    'activities' => 'osapiActivities',
    'appdata' => 'osapiAppData',
    'messages' => 'osapiMessages',
    'albums' => 'osapiAlbums',
    'mediaitems' => 'osapiMediaItems',
    'system' => 'osapiSystem',
    'statusmood'=>'osapiStatusMood',
    'notifications'=>'osapiNotifications',
    'groups'=>'osapiGroups'
  );

  /**
   * Constructs the osapi class based on the provided provider and signer
   * and initiates the basic people/activities/appdata and messages classes
   * and makes them available through $osapi->people, activities, appdata and messages
   * and thus mimicing the LightWeight JS API
   *
   * @param osapiProvider $provider provider to use (myspace, partuza, plaxo, xrds, etc)
   * @param osapiAuth $signer signer to use (security token, 2 legged oauth or 3 legged oauth)
   */
  public function __construct(osapiProvider $provider, osapiAuth $signer) {
    $this->provider = $provider;
    $this->signer = $signer;
  }

  public function __get($var) {
    $service = strtolower($var);
    if (array_key_exists($service, $this->availableServices)) {
      $class = $this->availableServices[$service];
      $this->$service = new $class;
      $this->{$service}->setStrictMode($this->strictMode);
      return $this->$service;
    }
  }

  /**
   * If set to true, osapi will raise exceptions on anything
   * that isn't quite spec compliant. Mostly useful for testing
   * and not for production work
   *
   * @param boolean $strictMode
   */
  public function setStrictMode($strictMode) {
    $this->strictMode = $strictMode;
  }

  /**
   * Returns true if osapi is set for strict spec checking
   *
   * @return boolean
   */
  public function getStrictMode() {
    return $this->strictMode;
  }

  /**
   * Creates a new batch request for this provider
   *
   * @return osapiBatch
   */
  public function newBatch() {
    return new osapiBatch($this->provider, $this->signer, $this->strictMode);
  }

  /**
   * People might thing this class is identical to the JavaScript counterpart
   * and that they could do osapi->makeRequest(). This friendly function
   * raises an exception to remind them it's not quite the same
   */
  public function makeRequest() {
    throw new osapiException("makeRequest is not supported");
  }

  /**
   * Misc function that returns TRUE if the endpoint is an OpenSocial
   * container, and FALSE if it's a PortableContacts endpoint.
   *
   * PortableContacts endpoints only support the people service, while
   * OpenSocial endpoints can also support activities, appdata and messages
   * (but it depends on the container which of those services they support)
   *
   * @return unknown
   */
  public function isOpenSocial() {
    return $this->provider->isOpenSocial;
  }
}
