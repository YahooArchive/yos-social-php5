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
 * Pre-defined provider class for Netlog (http://www.netlog.com)
 * @author Chris Chabot
 */
class osapiNetlogProvider extends osapiProvider {
  public function __construct(osapiHttpProvider $httpProvider = null) {
    parent::__construct("http://en.netlog.com/oauth/request_token", "http://en.netlog.com/oauth/authorize", "http://en.netlog.com/oauth/access_token", "http://en.api.netlog.com/opensocial/social/rest", "http://en.api.netlog.com/opensocial/social/rpc", "Netlog", true, $httpProvider);
  }
}