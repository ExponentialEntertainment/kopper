<?php

namespace Kopper;

class Config {

  private static $data;

  public static function init($config = null) {
    if (isset(self::$data) == false) {
      self::$data = array();
    }

    if (empty($config) == false) {
      if (isset($config['global']) == true) {
        self::setMultiple($config['global']);
      }

      if (isset($config[APPLICATION_ENV]) == true) {
        self::setMultiple($config[APPLICATION_ENV]);
      }
    }
  }

  public static function get($key) {
    if (isset(self::$data[$key]) == true) {
      return self::$data[$key];
    }
  }

  public static function set($key, $value) {
    self::init();
    self::$data[$key] = $value;
  }

  public static function setMultiple(array $values) {
    self::init();
    self::$data = array_merge(self::$data, $values);
  }

}
