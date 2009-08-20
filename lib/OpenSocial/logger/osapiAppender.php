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

include_once("osapiDummyAppender.php");
include_once("osapiConsoleAppender.php");
include_once("osapiFileAppender.php");

/**
 * The osapiAppender is the base class for all the appenders to be used
 * with osapiLogger.
 *
 * @author Anash P. Oommen
 */
abstract class osapiAppender {
  protected static $logLevels = array("NONE", "DEBUG", "INFO", "WARN",
      "ERROR", "FATAL", "ALL");
  public abstract function appendMessage($level, $message);

  protected function prettyPrint($level, $message) {
    return ("[" . self::$logLevels[$level] . "]" . "[" . date(DATE_RFC822)
        . "]" . " - " . $message . "\n");
  }
}
