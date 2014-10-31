<?php

namespace Kopper\Exception;

class NotFoundException extends NonFatalException {
  
  public function __construct($message = 'not found', $code = 404, $previous = null) {
    parent::__construct($message, $code, $previous);
  }
  
}
