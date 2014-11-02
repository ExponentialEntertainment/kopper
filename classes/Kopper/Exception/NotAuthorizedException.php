<?php

namespace Kopper\Exception;

class NotAuthorizedException extends NonFatalException {

  public function __construct($message = 'not authorized', $code = 401, $previous = null) {
    parent::__construct($message, $code, $previous);
  }

}
