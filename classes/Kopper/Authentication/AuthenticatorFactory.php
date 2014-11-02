<?php

namespace Kopper\Authentication;

use Kopper\Exception\InvalidProviderException;

class AuthenticatorFactory {

  public static function create($provider) {
    switch ($provider) {
      case FacebookAuthenticator::PROVIDER_NAME:
        return new FacebookAuthenticator();
      case GoogleAuthenticator::PROVIDER_NAME:
        return new GoogleAuthenticator();
      case EmailAuthenticator::PROVIDER_NAME:
        return new EmailAuthenticator();
      default:
        throw new InvalidProviderException($provider);
    }
  }

}
