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
 * OpenSocial API class for MediaItem requests
 *
 * @author Jesse Edwards
 */
class osapiMediaItems extends osapiService {

  /**
   * Gets a list of fields supported by this service
   *
   * @return osapiRequest the request
   */
  public function getSupportedFields() {
  	return osapiRequest::createRequest('mediaItems.getSupportedFields', array('userId' => '@supportedFields'));
  }
  
  /**
   * Get mediaItem(s)
   *
   * @param array $params the parameters defining the mediaItem(s) to get
   * @return osapiRequest the request
   */
  public function get($params) {
    return osapiRequest::createRequest('mediaItems.get', $params);
  }
  
  /**
   * Updates a mediaItem
   *
   * @param array $params the parameters defining the mediaItem data to update
   * @return osapiRequest the request
   */
  public function update($params){
    //TODO: check field restrictions
    return osapiRequest::createRequest('mediaItems.update', $params);
  }
  
  /**
   * Deletes a mediaItem
   *
   * @param array $params the parameters defining the mediaItem to delete
   * @return osapiRequest the request
   */
  public function delete($params){
    return osapiRequest::createRequest('mediaItems.delete', $params);
  }
  
  /**
   * Creates an mediaItem
   *
   * @param array $params the parameters defining the mediaItem to create
   * @return osapiRequest the request
   */
  public function create($params){
    //TODO: check field restrictions
    return osapiRequest::createRequest('mediaItems.create', $params);
  }
  
  /**
   * Upload mediaItem to an album
   *
   * @param array $params the parameters defining the album and mediaItem data to upload
   * @return osapiRequest the request
   */
  public function uploadContent($params){
  	return osapiRequest::createRequest('mediaItems.upload', $params);
  }
  
  /**
   * Converts a response into a native data type.
   *
   * @param array $array the raw data
   * @param boolean $strictMode whether to throw spec errors
   * @return osapiPerson
   */
  static public function convertarray($array, $strictMode = true) {
  	$instance = new osapiMediaItem();
 	$defaults = get_class_vars('osapiMediaItem');
 	
 	if ($strictMode && sizeof($defaults > sizeof($array))) {
      throw new osapiException("Unexpected fields in mediaItem response". print_r($array, true));
    }
    
  	foreach($array as $key=>$value){
  		$instance->setField($key, $value);
  	}
    return self::trimResponse($instance);
  }
}
