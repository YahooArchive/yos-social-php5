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
 * OpenSocial API class for Activity requests
 * Supported methods are get and create
 *
 * @author Chris Chabot
 */
class osapiActivities extends osapiService {

  /**
   * Gets a list of fields supported by this service
   *
   * @return osapiRequest the request
   */
  public function getSupportedFields() {
    return osapiRequest::createRequest('activities.getSupportedFields', array('userId' => '@supportedFields'));
  }
  
  /**
   * Gets a list of activities.
   *
   * @param array $params the parameters defining which activities to retrieve
   * @return osapiRequest the request
   */
  public function get($params) {
    return osapiRequest::createRequest('activities.get', $params);
  }

  /**
   * Creates an activity.
   *
   * @param array $params the parameters defining the activity to create
   * @return osapiRequest the request
   */
  public function create($params) {
    // basic sanity checking of the request object
    if (!isset($params['userId'])) throw new osapiException("Missing 'userId' param for osapiActivities->create");
    if (!isset($params['groupId'])) throw new osapiException("Missing 'groupId' param for osapiActivities->create");
    if (!isset($params['activity'])) throw new osapiException("Missing 'activity' param for osapiActivities->create");
    if (!$params['activity'] instanceof osapiActivity) throw new osapiException("Activity param should be a osapiActivity in osapiActivities->create");
    // strip out the null values before we post the activity
    $params['activity'] = self::trimResponse($params['activity']);
    // add appId => @app if it is missing.
    if (!isset($params['appId'])) {
      $params['appId'] = '@app';
    }
    
    return osapiRequest::createRequest('activities.create', $params);
  }

  public function update($params) {
    throw new osapiException("Updating activities is not supported");
  }

  public function delete($params) {
    throw new osapiException("Deleting activities is not supported");
  }

  /**
   * Private function to parse out the media items into typed osapiMediaItems
   *
   * @param osapiActivity $activity
   * @param array $array
   * @param boolean $strictMode
   */
  static private function convertMediaItems(osapiActivity &$activity, Array &$array, $strictMode) {
    if (isset($array['mediaItems']) && count($array['mediaItems'])) {
      $mediaItems = array();
      foreach ($array['mediaItems'] as $mediaItem) {
        $mimeType = isset($mediaItem['mimeType']) ? $mediaItem['mimeType'] : null;
        $type = isset($mediaItem['type']) ? $mediaItem['type'] : null;
        $url = isset($mediaItem['url']) ? $mediaItem['url'] : null;
        if ($strictMode && ($mimeType == null || $type == null || $url == null)) {
          throw new osapiException("Invalid activity entry, missing fields (mimeType: $mimeType, type: $type, url: $url)");
        }
        $mediaItems[] = new osapiMediaItem($mimeType, $type, $url);
      }
      $activity->setMediaItems($mediaItems);
    }
    unset($array['mediaItems']);
  }

  /**
   * Converts a response into a native data type.
   *
   * @param array $array the raw data
   * @param boolean $strictMode whether to throw spec errors
   * @return osapiActivity
   */
  static public function convertarray($array, $strictMode = true) {
    $id = isset($array['id']) ? $array['id'] : null;
    $userId = isset($array['userId']) ? $array['userId'] : null;
    if ($strictMode && $userId == null) {
      //throw new osapiException("Missing user id in activity");
    }
    $activity = new osapiActivity($id, $userId);
    self::convertMediaItems($activity, $array, $strictMode);
    $activityVars = get_object_vars($activity);
    foreach ($array as $key => $val) {
      if (array_key_exists($key, $activityVars)) {
        $activity->$key = $val;
        // remove the assigned field, so we can detect unexpected fields later
        unset($array[$key]);
      }
    }
    if ($strictMode && count($array)) {
      throw new osapiException("Unexpected fields in activity response: " . implode(', ', $array));
    }
    return self::trimResponse($activity);
  }
}

