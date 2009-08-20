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

/**
 * see
 * http://code.google.com/apis/opensocial/docs/0.8/reference/#opensocial.Collection
 */
class osapiCollection {
  public $list;
  public $startIndex;
  public $totalResults;
  public $itemsPerPage;

  // boolean flags to indicate whether the requested operations were performed or declined
  public $filtered;
  public $sorted;
  public $updatedSince;

  public function __construct($list, $startIndex = false, $totalResults = false) {
    $this->list = $list;
    $this->startIndex = $startIndex;
    $this->totalResults = $totalResults;
  }

  public function getList() {
    return $this->list;
  }

  public function setList($list) {
    $this->list = $list;
  }

  public function getStartIndex() {
    return $this->startIndex;
  }

  public function setStartIndex($startIndex) {
    $this->startIndex = $startIndex;
  }

  public function getItemsPerPage() {
    return $this->itemsPerPage;
  }

  public function setItemsPerPage($itemsPerPage) {
    $this->itemsPerPage = $itemsPerPage;
  }

  public function getTotalResults() {
    return $this->totalResults;
  }

  public function setTotalResults($totalResults) {
    $this->totalResults = $totalResults;
  }

  public function getFiltered($filtered) {
    $this->filtered = $filtered;
  }

  public function setFiltered($filtered) {
    $this->filtered = $filtered;
  }

  public function getSorted($sorted) {
    $this->sorted = $sorted;
  }

  public function setSorted($sorted) {
    $this->sorted = $sorted;
  }

  public function getUpdatedSince($updatedSince) {
    $this->updatedSince = $updatedSince;
  }

  public function setUpdatedSince($updatedSince) {
    $this->updatedSince = $updatedSince;
  }
}
