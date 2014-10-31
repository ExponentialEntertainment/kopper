<?php

namespace Kopper\Authentication;

abstract class Authenticator {
  
  protected $profile;

  abstract public function getProfile($token);

  public function authenticate($providerId, $token) {
    $profile = $this->getProfile($token);

    if(empty($profile['id']) === false){
      return $providerId === $profile['id'];
    }
    
    return false;
  }
}
