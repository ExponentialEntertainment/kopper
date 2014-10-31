<?php

namespace Kopper;

abstract class Singleton {
  
  protected function __construct() {
    //singleton
  }

  public static function getInstance() {
    if (static::$instance == null) {
      static::$instance = new static();
    }

    return static::$instance;
  }

}
