<?php

namespace Kopper;

class Environment {

  const PRODUCTION = 'prod';
  const TEST = 'test';
  const DEVELOPMENT = 'dev';
  const LOCAL = 'local';
  const DEBUG = 'debug';

  public static function prefix($name = '') {
    $seperator = '_';
    $prefix = Config::get('env.prefix');

    $pattern = '/^' . $prefix . $seperator . '(' . self::DEVELOPMENT . '|' . self::TEST . '|' . self::PRODUCTION . ')' . $seperator . '/';

    if (preg_match($pattern, $name) == true) {
      return $name;
    } else {
      $env = APPLICATION_ENV;
      if ($env == self::LOCAL) {
        $env = self::DEVELOPMENT;
      }

      return $prefix . $seperator . $env . $seperator . $name;
    }
  }

}
