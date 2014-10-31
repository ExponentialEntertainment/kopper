<?php

namespace Kopper\Authentication;

use Facebook\FacebookRequest;
use Facebook\FacebookSession;
use Facebook\GraphUser;

class FacebookAuthenticator extends Authenticator {

  const PROVIDER_NAME = 'facebook';

  public function getProfile($token) {
    if (empty($this->profile) === true) {
      $session = new FacebookSession($token);
      $session->validate();

      $request = new FacebookRequest($session, 'GET', '/me');
      $profile = $request->execute()->getGraphObject(GraphUser::className());

      $this->profile = array(
        'provider' => self::PROVIDER_NAME,
        'id' => $profile->getProperty('id'),
        'email' => $profile->getProperty('email'),
        'firstName' => $profile->getFirstName(),
        'lastName' => $profile->getLastName()
      );
    }
    
    return $this->profile;
  }

}
