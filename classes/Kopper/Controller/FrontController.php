<?php

namespace Kopper\Controller;

use Slim\Slim;

abstract class FrontController {

  const DEFAULT_CONTROLLER = 'default';
  const DEFAULT_ACTION = 'default';
  const SEPERATOR = '/';

  /**
   *
   * @var \Slim\Slim
   */
  protected $app;
  protected $namesapce;

  protected function __construct(Slim $app, $namespace) {
    $this->app = $app;
    $this->namesapce = $namespace;
  }

  public static function getInstance(Slim $app, $namespace) {
    if (static::$instance == null) {
      static::$instance = new static($app, $namespace);
    }

    return static::$instance;
  }

  public function getControllerName($controllerName) {
    return $this->namesapce . '\\' . ucfirst($controllerName) . 'Controller';
  }

  public function getActionName($action) {
    return $action . 'Action';
  }

  abstract public function route($stack = null, array $params = array());
  
  protected function dispatchRoute($controllerName, $action, array $params = array()){
    $controllerName = $this->getControllerName($controllerName);
    $action = $this->getActionName($action);
    
    $this->checkRoute($controllerName, $action);

    $controller = new $controllerName($this->app, $params);
    call_user_func(array($controller, $action));
  }

  protected function checkRoute($controllerName, $action) {
    if (is_callable(array($controllerName, $action)) === false) {
      $this->app->notFound();
    }
  }
  
  protected function unDash($name){
    return str_replace(' ', '', ucwords(str_replace('-', ' ', $name)));
  }

}
