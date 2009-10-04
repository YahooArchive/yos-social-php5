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
 * OpenSocial API class for System.listMethods
 * See http://opensocial-resources.googlecode.com/svn/spec/draft/RPC-Protocol.xml#listMethods for details
 * (notice: this is a JSON-RPC only feature, so containers that only support REST (like myspace) won't
 * support this
 * @author Chris Chabot
 */
class osapiSystem extends osapiService {

  /**
   * Calls the system.listMethods method that returns an array
   * of supported RPC methods, ie something like:
   * array('people.get', 'activities.get', 'activities.create', etc)
   *
   * @return osapiRequest
   */
    
  public function listMethods() {
    return osapiRequest::createRequest('system.listMethods', array());
  }

  public function get($params) {
    throw new osapiException("osapiCache only supports the invalidate method");
  }

  public function update($params)
  {
    throw new osapiException("osapiCache only supports the invalidate method");
  }

  public function delete($params)
  {
    throw new osapiException("osapiCache only supports the invalidate method");
  }

  public function create($params)
  {
    throw new osapiException("osapiCache only supports the invalidate method");
  }
  
}
