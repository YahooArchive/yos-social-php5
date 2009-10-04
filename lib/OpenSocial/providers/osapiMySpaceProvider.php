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
 * Pre-defined provider class for MySpace (www.myspace.com)
 * @author Chris Chabot
 */
class osapiMySpaceProvider extends osapiProvider {
  public function __construct(osapiHttpProvider $httpProvider = null) {
    parent::__construct("http://api.myspace.com/request_token", 
    	"http://api.myspace.com/authorize", 
    	"http://api.myspace.com/access_token", 
    	"http://opensocial.myspace.com/roa/09", null, "MySpace", true, $httpProvider);
  }
  
  /**
   * Adjusts a request prior to being sent in order to fix container-specific
   * bugs.
   * @param mixed $request The osapiRequest object being processed, or an array
   *     of osapiRequest objects.
   * @param string $method The HTTP method used for this request.
   * @param string $url The url being fetched for this request.
   * @param array $headers The headers being sent in this request.
   * @param osapiAuth $signer The signing mechanism used for this request.
   */
  public function preRequestProcess(&$request, &$method, &$url, &$headers, osapiAuth &$signer) {
    // Using the full myspace ID in the xoauth_requestor_id doesn't work
    if ($signer instanceof osapiOAuth2Legged) {
      $signer->setUserId(str_replace('myspace.com.person.', '', $signer->getUserId()));
    }
    
    if($request->method == 'appdata.update' || $request->method == 'appdata.create') {
      $this->formatAppDataOut($request);
    }
  }

  private function formatAppDataOut(osapiRequest &$request) {
    $data = new osapiAppDataModel();
    
    foreach($request->params['data'] as $key=>$value) {
      $data->setField($key, $value);
    }
    
    $request->params['data'] = $data;
  }
  
  /**
   * Attempts to correct an atomic request.
   * @param osapiRequest $request The request to fix.
   * @param string $method The HTTP method used for this request.
   * @param string $url The url being fetched for this request.
   * @param array $headers The headers being sent in this request.
   * @param osapiAuth $signer The signing mechanism used for this request.
   */
  private function fixRequest(osapiRequest &$request, &$method, &$url, &$headers, osapiAuth &$signer) {
	
  }

  /**
   * Attempts to correct a response to address per-container bugs.
   * @param osapiRequest $request
   * @param array $response
   */
  public function postRequestProcess(osapiRequest &$request, &$response) {
    $this->fixNastyResponses($response);
    $this->fixStatusLink($request,$response);
    
    if($request->method == 'appdata.get') {
      $this->formatAppDataIn($request, $response);
    }else{
      $this->fixModelContainer($request,$response);
    }
  }

  /**
   * Attempts to correct the response containing a statusLink when it should return an ID.
   * @param osapiRequest $request
   * @param array $response
   */
  private function fixStatusLink(osapiRequest &$request, &$response) {
  	
    if( stripos($request->method, 'update') !== false || 
	    stristr($request->method, 'create') !== false || 
	    stristr($request->method, 'upload') !== false ) {
	        
		$data = json_decode($response['data']);
	    
      if (!empty($data->statusLink)) {
          $data->id = substr($data->statusLink, strrpos($data->statusLink,"/")+1);
          unset($data->statusLink);
          $response['data'] = json_encode($data);
      }
    }
  }
  
  private function formatAppDataIn(osapiRequest &$request, &$response) {
      $msdata = json_decode($response['data']);
      $data = array();
      
      foreach($msdata->entry as $entry) {
        $personId = $entry->userAppData->personId;
        $data[$personId] = array();
        
        foreach($entry->userAppData->appData as $value) {
          $data[$personId][$value->key] = $value->value;
        }
      }
      $msdata->entry = $data;
      
      $response['data'] = json_encode($msdata);
  }
        
  
 /**
   * Correct issue where data objects are nested inside a 'modelname' node.
   * @param osapiRequest $request
   * @param array $response
   */
  private function fixModelContainer(osapiRequest &$request, &$response) {
    $plural_rules = array(
      'groups'=>'group', 
      'people'=>'person', 
      'albums'=>'album', 
      'mediaItems'=>'mediaItem', 
      'activities'=>'activity', 
      'appdata'=>'appData',
      'statusmood'=>'statusmood',
      'notifications'=>'notification');
    
    $data = json_decode($response['data']);
    $service = $request->getService($request->method);
    $model = $plural_rules[$service];
    
    if (isset($data->entry)) {
        foreach($data->entry as $key=>$value) {
            if($model == 'appData') {
                $data->entry[$key] = $value->{'userAppData'};
            } else {
                $data->entry[$key] = $value->{$model};
            }
        }
    } else if(isset($data->{$model})) {
        $data = $data->{$model};
    }
    
    $response['data'] = json_encode($data);
  }
  
  /**
   * Parses MySpace's gross HTML-ified error response.
   * @param array $response
   */
  private function fixNastyResponses(&$response) {
    if ($response['http_code'] == 403 || 
        $response['http_code'] == 401 || 
        $response['http_code'] == 404) {
      $matches = array();
      if (preg_match("/<h2>(.*?)<\/h2>/s", $response['data'], $matches )) {
        $response['data'] = $matches[1];
      }
    }
  }
}