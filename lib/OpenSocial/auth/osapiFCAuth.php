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

require_once "osapiAuth.php";

/**
 * Authentication class that uses the fcauth token to authenticate
 * requests. Singing in this case means simply adding ?fcauth=<token> to
 * the url
 *
 * @author Arne Roomann-Kurrik
 */
class osapiFCAuth extends osapiSecurityToken {
  /**
   * Constructs an osapiFCAuth for simple authentication.
   *
   * @param string $securityToken the supplied fcauth token
   */
  public function __construct($securityToken) {
    parent::__construct($securityToken);
    $this->tokenParameter = "fcauth";
  }
}
