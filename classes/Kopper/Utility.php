<?php

namespace Kopper;

use DateTime;
use DateTimeZone;
use Kopper\Aws\Email;
use Kopper\Cache\GlobalCache;
use Kopper\Config;
use Kopper\Environment;
use ErrorException;
use Exception;
use stdClass;

class Utility {

  public static function utcDateTime($date = null) {
    return new DateTime($date, new DateTimeZone('utc'));
  }

  public static function getUserAgent() {
    if (empty($_SERVER['HTTP_USER_AGENT']) == false) {
      return $_SERVER['HTTP_USER_AGENT'];
    }
  }

  public static function inString($needle, $haystack) {
    $pos = strpos($haystack, $needle);

    if ($pos === false) {
      return false;
    } else {
      return true;
    }
  }

  public static function exactTime() {
    list($microSeconds, $seconds) = explode(' ', microtime());

    $microSeconds = substr($microSeconds, 2);
    return $seconds . '_' . $microSeconds;
  }

  public static function onlyRunIn(array $environments) {
    if (in_array(APPLICATION_ENV, $environments) == false) {
      exit();
    }
  }

  public static function getRequestHeader($name) {
    $headers = getallheaders();

    if (empty($headers[$name]) == false) {
      return $headers[$name];
    }
  }

  public static function getLocalIp() {
    if (empty($_SERVER['SERVER_ADDR']) == false) {
      return $_SERVER['SERVER_ADDR'];
    } else if (self::isCLI() == true) {
      return getHostName();
    } else {
      return null;
    }
  }

  public static function getRemoteIp() {
    if (empty($_SERVER["HTTP_X_FORWARDED_FOR"]) == false) {
      return $_SERVER["HTTP_X_FORWARDED_FOR"];
    } else if (empty($_SERVER["REMOTE_ADDR"]) == false) {
      return $_SERVER["REMOTE_ADDR"];
    } else {
      return null;
    }
  }

  public static function getProtocol() {
    if (empty($_SERVER['HTTP_X_FORWARDED_PROTO']) == false) {
      return $_SERVER['HTTP_X_FORWARDED_PROTO'];
    } else if (empty($_SERVER['HTTPS']) == false) {
      return 'https';
    } else {
      return 'http';
    }
  }

  public static function base64UrlDecode($input) {
    return base64_decode(strtr($input, '-_', '+/'));
  }

  public static function errorToException($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
      return false;
    }

    throw new ErrorException($errstr, $errno, $errno, $errfile, $errline);
  }

  public static function exceptionToInfo(Exception $e, $stack = false) {
    $exceptionInfo = array(
      'exception' => get_class($e),
      'code' => $e->getCode(),
      'file' => $e->getFile(),
      'line' => $e->getLine(),
      'uri' => empty($_SERVER['REQUEST_URI']) ? $_SERVER['SCRIPT_FILENAME'] : ($_SERVER['REQUEST_URI'] . (empty($_SERVER['QUERY_STRING']) ? ('?' . http_build_query($_POST)) : null)),
      'message' => $e->getMessage(),
      'server' => self::getLocalIp(),
      'remote' => self::getRemoteIp()
    );

    if (APPLICATION_ENV == Environment::LOCAL || $stack == true) {
      $exceptionInfo['stack'] = $e->getTrace();
    }

    return $exceptionInfo;
  }

  public static function isCLI() {
    return php_sapi_name() === 'cli';
  }

  public static function sendEmail($to, $subject, $body, $from = null) {
    if (empty($from) === true) {
      $from = Config::get('email.system');
    }

    $email = new Email();
    $email->send($from, $to, $subject, $body);
  }

  public static function unsetKeys(array &$array, $keys) {
    foreach ($keys as $key) {
      unset($array[$key]);
    }
  }

  public static function objectToArray($data) {
    if (is_array($data) || is_object($data)) {
      $result = array();

      foreach ($data as $key => $value) {
        $result[$key] = self::objectToArray($value);
      }

      return $result;
    }

    return $data;
  }

  public static function arrayToObject($data) {
    if (is_array($data) || is_object($data)) {
      $result = new stdClass();

      foreach ($data as $key => $value) {
        $result->$key = self::arrayToObject($value);
      }

      return $result;
    }

    return $data;
  }

  public static function isJson($string) {
    return !empty($string) && is_string($string) && is_array(json_decode($string, true)) && json_last_error() === JSON_ERROR_NONE;
  }

  public static function processLock() {
    if (APPLICATION_ENV != Environment::LOCAL) {
      $processName = md5(basename($_SERVER['SCRIPT_FILENAME']));
      $serverName = php_uname('n');

      $key = 'process_lock_' . $processName;
      GlobalCache::set($key, $serverName);

      sleep(10);

      $winner = GlobalCache::get($key);

      if ($winner !== $serverName) {
        exit();
      }
    }
  }
  
  public static function generateRandomString($length = 16) {
    return substr(hash('sha256', time() . mt_rand()), 0, $length);
  }

}
