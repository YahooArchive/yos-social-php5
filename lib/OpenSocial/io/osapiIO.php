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

require_once "osapiBatch.php";
require_once "osapiRequest.php";
require_once "osapiRestIO.php";
require_once "osapiRpcIO.php";
require_once "osapiHttpProvider.php";

/**
 * Base IO class, the REST and RPC implementations inherit from this class.
 *
 * @author Chris Chabot
 */
abstract class osapiIO {
  const USER_AGENT = 'osapi 1.0';

protected static function convertArray(osapiRequest $request, $val, $strictMode) {
    $converted = null;
    $service = $request->getService($request->method);
    $method = substr($request->method,stripos($request->method,'.')+1);
    
    // don't converArray on responses that do not need to be placed into 
    // their respective models. (supportedFields, delete, create, update)
    if($method == 'get'){
        switch ($service) {
          case 'people':
            $converted = osapiPeople::convertArray($val, $strictMode);
            break;
          case 'activities':
            $converted = osapiActivities::convertArray($val, $strictMode);
            break;
          case 'appdata':
            $converted = osapiAppData::convertArray($val, $strictMode);
            break;
          case 'messages':
            $converted = osapiMessages::convertArray($val, $strictMode);
            break;
          case 'mediaItems':
            $converted = osapiMediaItems::convertArray($val, $strictMode);
            break;
          case 'albums':
            $converted = osapiAlbums::convertArray($val, $strictMode);
            break;
          case 'statusmood':
            $converted = osapiStatusMood::convertArray($val, $strictMode);
            break;
          case 'notifications':
            $converted = osapiNotifications::convertArray($val, $strictMode);
            break;
          case 'groups':
            $converted = osapiGroups::convertArray($val, $strictMode);
            break;
        }
    }
    return $converted ? $converted : $val;
  }

  /**
   * Converts a collection response array into a collection object.
   *
   * @param array $entry
   * @return osapiCollection
   */
  protected static function listToCollection($entry, $strictMode) {
    // Result is a data collection, return as a osapiCollection
    $offset = isset($entry['startIndex']) ? $entry['startIndex'] : 0;
    $totalSize = isset($entry['totalResults']) ? $entry['totalResults'] : 0;
    $collection = new osapiCollection($entry['list'], $offset, $totalSize);
    if (isset($entry['itemsPerPage'])) {
      $collection->setItemsPerPage($entry['itemsPerPage']);
    }
    if (isset($entry['sorted'])) {
      $sorted = $entry['sorted'];
      $sorted = ($sorted == 1 || $sorted == 'true' || $sorted == true) ? true : false;
      $collection->setSorted($sorted);
    }
    if (isset($entry['filtered'])) {
      $filtered = $entry['filtered'];
      $filtered = ($filtered == 1 || $filtered == 'true' || $filtered == true) ? true : false;
      $collection->setFiltered($filtered);
    }
    if (isset($entry['updatedSince'])) {
      $updatedSince = $entry['updatedSince'];
      $updatedSince = ($updatedSince == 1 || $updatedSince == 'true' || $updatedSince == true) ? true : false;
      $collection->setUpdatedSince($updatedSince);
    }
    return $collection;
  }

  /**
   * This function sends the request batch, implemented in the sub-classes
   * (RPC and REST IO classes), for some reason PHP doesn't allow
   * abstract public static functions, hence the empty declaration.
   *
   * @param array $requests
   * @param osapiProvider $provider
   * @param osapiAuth $signer
   */
  public static function sendBatch(Array $requests, osapiProvider $provider, osapiAuth $signer, $strictMode = false) {
    throw new osapiException("osapiIO Should not be used directly, use osapiRpcIO or osapiRestIO instead");
  }

  /**
   * Function that performs the nitty-gritty work to send the request.
   *
   * @param string $url URL to request
   * @param string $method method to use (GET, POST, PUT, DELETE)
   * @param osapiHttpProvider the HTTP provider to use (such as local or curl)
   * @param array $headers optional: Headers to include in the request
   * @param string $postBody optional: postBody to post
   * @return array('http_code' => HTTP response code (200, 404, 401, etc), 'data' => the html document, 'headers' => parsed response headers)
   */
  public static function send($url, $method, $httpProvider, $headers = false, $postBody = false) {
    return $httpProvider->send($url, $method, $postBody, $headers);
  }
}
