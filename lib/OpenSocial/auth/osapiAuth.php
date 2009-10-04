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

require_once "osapiHttpBasic.php";
require_once "osapiOAuth2Legged.php";
require_once "osapiOAuth3Legged.php";
require_once "osapiOAuth3Legged_10a.php";
require_once "osapiSecurityToken.php";
require_once "osapiFCAuth.php";

/**
 * Authentication class that deals with 3-Legged OAuth
 * See http://sites.google.com/site/oauthgoog/2leggedoauth/2opensocialrestapi
 * for more information on the difference between 2 or 3 leggged oauth
 *
 * @author Chris Chabot
 */
abstract class osapiAuth {
  abstract public function sign($method, $url, $params = array(), $postBody = false, &$headers = array());
}
