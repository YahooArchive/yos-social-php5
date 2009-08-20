<?php
/*
 * Copyright 2009 Google Inc.
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

require_once "osapiAppender.php";

/**
 * The osapiFileAppender writes logs to console.
 *
 * @author Anash P. Oommen
 */
class osapiFileAppender extends osapiAppender {
  protected $logFile = "";

  public function __construct($logFile = "") {
    $this->setlogFile($logFile);
  }

  public function setlogFile($logFile) {
    $this->logFile = $logFile;
  }

  public function getlogFile() {
    return $this->logFile;
  }

  public function appendMessage($level, $message) {
    if (!is_int($level)) {
      throw new osapiLoggerException($level . " is not a number.");
    }

    if($level > osapiLogger::NONE || $level < osapiLogger::ALL) {
      throw new osapiLoggerException("Level should be between ALL(0) " .
          "and NONE(6).");
    }

    if ($this->isLocked()) {
      // Some other process is writing to this file too, wait until it's
      // done to prevent hickups.
      $this->waitForLock();
    }
    $this->createLock();
    $fpt = fopen($this->logFile, "a+");
    if($fpt != NULL) {
      fputs($fpt, parent::prettyPrint($level, $message));
      fclose($fpt);
    }
    $this->removeLock();
  }

  private function isLocked() {
    // our lock file convention is simple: /the/file/path.lock
    return file_exists($this->logFile . '.lock');
  }

  private function createLock() {
    $logDir = dirname($this->logFile);
    if (! is_dir($logDir)) {
      if (! @mkdir($logDir, 0755, true)) {
        // make sure the failure isn't because of a concurency issue
        if (! is_dir($logDir)) {
          throw new osapiStorageException("Could not create storage " .
              "directory: $logDir");
        }
      }
    }
    @touch($logFile . '.lock');
  }

  private function removeLock() {
    // suppress all warnings, if some other process removed it that's ok too
    @unlink($logFile . '.lock');
  }

  private function waitForLock() {
    // 20 x 250 = 5 seconds
    $tries = 20;
    $cnt = 0;
    do {
      // Make sure PHP picks up on file changes. This is an expensive action
      // but really can't be avoided.
      clearstatcache();
      // 250 ms is a long time to sleep, but it does stop the server from
      // burning all resources on polling locks..
      usleep(250);
      $cnt ++;
    } while ($cnt <= $tries && $this->isLocked($logFile));
    if ($this->isLocked($logFile)) {
      // 5 seconds passed, assume the owning process died off and remove it.
      $this->removeLock($logFile);
    }
  }
}
