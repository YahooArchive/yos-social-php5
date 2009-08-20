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
 * The osapiConsoleAppender writes logs to console.
 *
 * @author Anash P. Oommen
 */
class osapiConsoleAppender extends osapiAppender {
  public function appendMessage($level, $message) {
    if (!is_int($level)) {
      throw new osapiLoggerException($level . " is not a number.");
    }

    if($level > osapiLogger::NONE || $level < osapiLogger::ALL) {
      throw new osapiLoggerException("Level should be between ALL(0) " .
          "and NONE(6).");
    }

    echo(parent::prettyPrint($level, $message));
  }
}
