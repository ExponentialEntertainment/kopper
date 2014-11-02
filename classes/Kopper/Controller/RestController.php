<?php

namespace Kopper\Controller;

use Kopper\Exception\NotImplementedException;

abstract class RestController extends Controller {

  abstract public function getAction();

  abstract public function postAction();

  abstract public function putAction();

  abstract public function deleteAction();
  
  protected function notImplemented(){
    throw new NotImplementedException();
  }
}
