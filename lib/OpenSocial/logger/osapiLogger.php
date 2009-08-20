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

require_once("osapiAppender.php");

/**
 * The osapiLogger class provides a logger for osapi library. You can
 * initialize it as follows:
 *
 * osapiLogger::setLevel(osapiLogger::INFO);
 * osapiLogger::setAppender(new osapiFileAppender("/tmp/log/osapi.log"));
 *
 * To make logs, you can call
 *
 * osapiLogger::warn($yourMessage); or
 * osapiLogger::info($yourObject);
 *
 * and so on. Objects will be var_dump()ed.
 *
 * @author Anash P. Oommen
 */

class osapiLogger {
  const ALL = 0;
  const DEBUG = 1;
  const INFO = 2;
  const WARN = 3;
  const ERROR = 4;
  const FATAL = 5;
  const NONE = 6;

  static $level = self::NONE;
  static $logAppender = null;

  public static function getLevel() {
    return self::$level;
  }

  public static function setLevel($newLevel) {
    if (!is_int($newLevel)) {
      throw new osapiLoggerException($newLevel . " is not a number.");
    }

    if($newLevel > self::NONE || $newLevel < self::ALL) {
      throw new osapiLoggerException("Level should be between ALL(0) " .
          "and NONE(6).");
    }
    self::$level = $newLevel;
  }

  public static function getAppender() {
    return self::$logAppender;
  }

  public static function setAppender($logAppender) {
    self::$logAppender = $logAppender;
  }

  public static function debug($message) {
    self::log(self::DEBUG, $message);
  }

  public static function info($message) {
    self::log(self::INFO, $message);
  }

  public static function warn($message) {
    self::log(self::WARN, $message);
  }

  public static function error($message) {
    self::log(self::ERROR, $message);
  }

  public static function fatal($message) {
    self::log(self::FATAL, $message);
  }

  static function log($level, $message) {
    if (!is_int($level)) {
      throw new osapiLoggerException($level . " is not a number.");
    }

    if($level > self::NONE || $level < self::ALL) {
      throw new osapiLoggerException("Level should be between ALL(0) " .
          "and NONE(6).");
    }

    if (self::$level <= $level && self::$logAppender != null) {
      if (is_scalar($message)) {
        self::$logAppender->appendMessage($level, $message);
      } else {
        self::$logAppender->appendMessage($level,
            self::convertToString($message));
      }
    }
  }

  static function convertToString($obj) {
    ob_start();
    var_dump($obj);
    return "\n" . ob_get_clean();
  }
}
