<?php

namespace Kopper\Controller;

class ActionFrontController extends FrontController {

  protected static $instance;

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

    if (count($stack) > 0) {
      $action = array_shift($stack);
    } else {
      $action = self::DEFAULT_ACTION;
    }

    $controllerName = $this->unDash($controllerName);
    $action = $this->unDash($action);
    
    $this->dispatchRoute($controllerName, $action, array_merge($params, $this->paraseParams($stack)));
  }

  protected function paraseParams(array $stack) {
    $params = array();

    $length = count($stack);
    for ($i = 0; $i < $length; $i+=2) {
      if (empty($stack[$i]) === false) {
        $key = $stack[$i];
        $value = empty($stack[$i + 1]) ? null : $stack[$i + 1];

        $params[$key] = $value;
      }
    }

    return $params;
  }

}
