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
 * This class represents an OpenSocial request which can be posted
 * to the server either through the REST or RPC service.
 *
 * @author Chris Chabot
 */
class osapiRequest {
  public $method;
  public $params;
  public $id;
  
  public function __construct($method, $params) {
    $this->method = $method;
    $this->params = $params;
  }

  /**
   * Creates a request to the specified service after performing some
   * simple error checking.
   *
   * @param string $method the service and operation to perform (eg. people.get)
   * @param array $params the parameters supplied for the request
   * @return osapiRequest the generated request
   */
  public static function createRequest($method, $params) {
      $availableServices = array('people', 'activities', 'appdata', 'messages', 'system', 'cache', 
        'albums', 'mediaItems', 'statusmood', 'notifications', 'groups');
      $availableMethods = array('get', 'update', 'create', 'delete', 'upload', 
        'getSupportedFields', 'getSupportedMood');
      
    // Verify the service name
    if (! in_array(self::getService($method), $availableServices)) {
      throw new osapiException("Invalid service: ".self::getService($method));
    }
    // Verify the method
    if ((self::getService($method) == 'cache' && self::getOperation($method) != 'invalidate') ||
        (self::getService($method) == 'system' && self::getOperation($method) != 'listMethods') ||
        (self::getService($method) != 'cache' && self::getService($method) != 'system' && 
            !in_array(self::getOperation($method), $availableMethods))) {
      throw new osapiException("Invalid method: ".self::getOperation($method));
    }
    if (self::getService($method) != 'cache' && self::getService($method) != 'system') {
	    // Verify base params
	    if (! isset($params['userId'])) {
	      throw new osapiException("Invalid or missing userId");
	    }
	    if (! is_array($params['userId'])) {
	      $params['userId'] = array($params['userId']);
	    }
	    if (isset($params['groupId']) && ! in_array($params['groupId'], array('@self', '@all', '@friends', '@supportedMood'))) {
	      throw new osapiException("Invalid groupId, allowed types are: @self, @all and @friends");
	    }
    }
    // Everything checks out, create the request object & return it
    return new osapiRequest($method, $params);
  }

  /**
   * Returns the service portion of a request method (eg. people from people.get).
   *
   * @param string $rpcMethod the request method
   * @return string the service name
   */
  public static function getService($rpcMethod) {
    return substr($rpcMethod, 0, strpos($rpcMethod, '.'));
  }

  /**
   * Returns the operation portion of a request method (eg. get from people.get).
   *
   * @param string $rpcMethod the request method
   * @return string the operation name
   */
  public static function getOperation($rpcMethod) {
    return substr($rpcMethod, strpos($rpcMethod, '.') + 1);
  }
}
