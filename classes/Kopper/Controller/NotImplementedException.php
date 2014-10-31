<?php

namespace Kopper\Controller;

use Exception;

class NotImplementedException extends Exception {
  
  public function __construct($message = 'not implemented', $code = 501, $previous = null) {
    parent::__construct($message, $code, $previous);
  }
  
}
