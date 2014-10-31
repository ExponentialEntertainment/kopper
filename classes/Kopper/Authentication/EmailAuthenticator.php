<?php

namespace Kopper\Authentication;

use Kopper\Config;

class EmailAuthenticator extends Authenticator {

  const PROVIDER_NAME = 'email';

  public function getProfile($token) {
    if (empty($this->profile) === true) {
      $emailProvider = Config::get('email.provider');

      if (empty($emailProvider) === false) {
        $profile = $emailProvider->get($token);
        
        $this->profile = array(
          'provider' => self::PROVIDER_NAME,
          'id' => $profile['id'],
          'email' => $profile['email'],
          'password' => $profile['password'],
          'firstName' => $profile['firstName'],
          'lastName' => $profile['lastName']
        );
      }
    }

    return $this->profile;
  }

}
