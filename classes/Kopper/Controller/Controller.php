<?php

namespace Kopper\Controller;

use DOMDocument;
use Propel\PropelConnection;
use Slim\Slim;

abstract class Controller {

  const PARAM_ID = 'id';
  const PARAM_REQUEST_BODY = 'requestBody';
  
  /**
   *
   * @var \Slim\Slim
   */
  private $app;

  /**
   *
   * @var \Slim\Http\Request
   */
  private $request;

  /**
   *
   * @var type \Slim\Http\Response
   */
  private $response;
  private $params;

  public function __construct(Slim $app, array $params = array()) {
    $this->app = $app;
    $this->request = $app->request();
    $this->response = $app->response();
    $this->params = $this->buildParams($params);
  }

  private function buildParams(array $params = array()) {
    $body = $this->request->getBody();

    if (is_array($body) === false) {
      $body = array();
    }

    if (array_key_exists(0, $body) === true) {
      $body = array(
        self::PARAM_REQUEST_BODY => $body
      );
    }

    return array_replace($this->request->params(), $body, $params);
  }

  protected function params() {
    return $this->params;
  }

  protected function param($key, $default = null) {    
    return (isset($this->params[$key]) ? $this->params[$key] : $default);
  }

  protected function setStatus($code) {
    if ($code < 200 || $code > 599) {
      $code = 500;
    }

    $this->response->setStatus($code);
  }

  protected function setContentType($type) {
    $this->app->contentType($type);
  }

  protected function setHeader($name, $value) {
    $this->response->header($name, $value);
  }

  protected function outputJson($response, $code = 200) {
    $this->setContentType('application/json');
    $this->setStatus($code);

    echo(json_encode($response));
    
    return $response;
  }

  protected function outputXml(DOMDocument $doc, $code = 200) {
    $this->setContentType('text/xml');
    $this->setStatus($code);

    echo($doc->saveXML());
    
    return $doc;
  }

  protected function render($page, $params = null, $code = 200) {
    $this->setContentType('text/html');
    $this->setStatus($code);

    require_once(APPLICATION_DIR . '/views/' . $page . '.phtml');
  }

  protected function notFound() {
    $this->app->notFound();
  }
  
  protected function getMethod(){
    return $this->request->getMethod();
  }
  
  protected function isGet(){
    return $this->request->isGet();
  }
  
  protected function isPost(){
    return $this->request->isPost();
  }
  
  protected function isPut(){
    return $this->request->isPut();
  }
  
  protected function isDelete(){
    return $this->request->isDelete();
  }
  
  protected function redirect($url, $status = 302){
    $this->app->redirect($url, $status);
  }
  
  protected function connectToDatabase(){
    PropelConnection::connect();
  }

}
