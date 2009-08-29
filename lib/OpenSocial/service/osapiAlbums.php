<?php
/*
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
 * OpenSocial API class for Albums requests
 *
 * @author Jesse Edwards
 */
class osapiAlbums extends osapiService {
	
  /**
   * Gets a list of fields supported by this service
   *
   * @return osapiRequest the request
   */
  public function getSupportedFields() {
  	return osapiRequest::createRequest('albums.getSupportedFields', array('userId' => '@supportedFields'));
  }
  
  /**
   * Gets albums
   *
   * @param array $params the parameters defining which albums to retrieve
   * @return osapiRequest the request
   */
  public function get($params) {
    return osapiRequest::createRequest('albums.get', $params);
  }
  
  /**
   * Updates an album
   *
   * @param array $params the parameters defining the album data to update
   * @return osapiRequest the request
   */
  public function update($params){
    if (!isset($params['album'])) throw new osapiException("Missing album in osapiAlbums->update()");
    if (!$params['album'] instanceof osapiAlbum) throw new osapiException("The params['album'] should be a osapiAlbum in osapiAlbums->update()");
    //TODO: check album.field restrictions
    return osapiRequest::createRequest('albums.update', $params);
  }
  
  /**
   * Deletes an album
   *
   * @param array $params the parameters defining the album to delete
   * @return osapiRequest the request
   */
  public function delete($params){
  	throw new osapiException("Deleting albums is not supported");
  }
  
  /**
   * Creates an album
   *
   * @param array $params the parameters defining the album to create
   * @return osapiRequest the request
   */
  public function create($params){
  	if (!isset($params['album'])) throw new osapiException("Missing album in osapiAlbums->create()");
    if (!$params['album'] instanceof osapiAlbum) throw new osapiException("The params['album'] should be a osapiAlbum in osapiAlbums->create()");
    //TODO: check album.field restrictions
    return osapiRequest::createRequest('albums.create', $params);
  }

  /**
   * Converts a response into a native data type.
   *
   * @param array $array the raw data
   * @param boolean $strictMode whether to throw spec errors
   * @return osapiPerson
   */
  static public function convertArray($array, $strictMode = true) {
 	$instance = new osapiAlbum();
 	$defaults = get_class_vars('osapiAlbum');
 	
 	if ($strictMode && sizeof($defaults != sizeof($array))) {
      throw new osapiException("Unexpected fields in people response". print_r($array, true));
    }
    
  	foreach($array as $key=>$value){
  		$instance->setField($key, $value);
  	}
    return self::trimResponse($instance);
  }
}
