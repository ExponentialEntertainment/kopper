<?php

namespace Kopper\Controller;

class RestFrontController extends FrontController {

  protected static $instance;

  public function getActionName($action = null) {
    return parent::getActionName($this->app->request()->getMethod());
  }

  public function route($stack = null, array $params = array()) {
    if (empty($stack) === true) {
      $stack = array(
        self::DEFAULT_CONTROLLER,
        self::DEFAULT_ACTION
      );
    }

    if (count($stack) > 0) {
      $controllerName = array_shift($stack);
    } else {
      $controllerName = self::DEFAULT_CONTROLLER;
    }
    
    $controllerName = $this->unDash($controllerName);
    
    $this->dispatchRoute($controllerName, null, $params);
  }

}
