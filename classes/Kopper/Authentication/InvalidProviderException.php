<?php

namespace Kopper\Authentication;

use Exception;

class InvalidProviderException extends Exception{
  
  public function __construct($provider, $code = null, $previous = null) {
    parent::__construct("invalid provider ($provider)", $code, $previous);
  }
  
}