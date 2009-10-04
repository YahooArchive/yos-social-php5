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
 * osapiAppDataModel
 * @author Jesse Edwards
 *
 */
class osapiAppDataModel extends osapiModel
{
	// TODO: this may need to be handeled differently.
	// we might have to create new dataentries then add them to app data objects.
	var $appData = Array();
	
	public function setField($key, $value) {
		if($this->_isValidKey($key))
			$this->appData[] = array('key'=>$key, 'value'=>$value);
		else
			throw new osapiException("Invalid key specified: $key");
	}
	
	public function getField($field) {
		foreach($this->appData as $value){
			if($field == $value['key']){
				return $value['value'];
			}
		}
		return null;
	}
	
	public function getdata() {
		return $this->appData;
	}
	
	public function validateKeys() {
		foreach($this->appData as $value) {
			if(!$this->_isValidKey($value['key'])) {
				throw new osapiException("Invalid key specified: $value[key]");
			}else{
				return true;
			}
		}
	}
	
	private function _isValidKey($key) {
      if (empty($key)) {
        return false;
      }
      if ($key == '*') {
        return true;
      }
      for ($i = 0; $i < strlen($key); ++ $i) {
        $c = substr($key, $i, 1);
        if (($c >= 'a' && $c <= 'z') || ($c >= 'A' && $c <= 'Z') || ($c >= '0' && $c <= '9') || ($c == '-') || ($c == '_') || ($c == '.')) {
          continue;
        }
        return false;
      }
      return true;
	}
	
	
}
?>