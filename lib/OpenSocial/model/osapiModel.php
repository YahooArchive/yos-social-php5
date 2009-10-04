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
 * Meta file that includes all the OpenSocial model definitions.
 * The model files them selves have been adopted from the php
 * version of shindig: http://incubator.apache.org/shindig and
 * are also licenced under the Apache License, version 2.0
 *
 * @author Chris Chabot
 * @author Jesse Edwards
 */

require_once "osapiCollection.php";
require_once "osapiComplexField.php";
require_once "osapiListField.php";
require_once "osapiEnum.php";
require_once "osapiAccount.php";
require_once "osapiActivity.php";
require_once "osapiAddress.php";
require_once "osapiBodyType.php";
require_once "osapiEmail.php";
require_once "osapiIdSpec.php";
require_once "osapiIm.php";
require_once "osapiMessage.php";
require_once "osapiName.php";
require_once "osapiOrganization.php";
require_once "osapiPerson.php";
require_once "osapiPhone.php";
require_once "osapiPhoto.php";
require_once "osapiUrl.php";
require_once "osapiError.php";
require_once "osapiAlbum.php";
require_once "osapiMediaItem.php";
require_once "osapiStatusMoodModel.php";
require_once "osapiNotification.php";
require_once "osapiGroup.php";
require_once "osapiAppDataModel.php";

class osapiModel
{
  /**
   * Standardized method for getting fields from osapiModels
   * @param string $field
   * @return mixed
   */
  public function getField($field)
  {
    return !!$this->{$field} ? $this->{$field} : null;	
  }
	
  /**
   * Standardized method for setting fields for osapiModels
   * @param string $field
   * @param mixed $value
   * @return none
   */
  public function setField($field, $value)
  {
  	$this->{$field} = $value;
  }
}

