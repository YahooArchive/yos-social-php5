<?php
/**
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */

class osapiNotification extends osapiModel 
{
    var $templateId;
    var $recipientIds;
    var $templateParameters = array();
    var $mediaItems = array();
    
    public function setTemplateParameter($key, $value) {
        $this->templateParameters[] = array('key'=>$key, 'value'=>$value);
    }
    
    public function getTemplateParameter($key) {
        foreach($this->templateParameters as $value){
            if($key == $value['key']){
                return $value['value'];
            }
        }
        return null;
    }
}