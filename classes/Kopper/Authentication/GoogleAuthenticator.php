<?php

namespace Kopper\Authentication;

use Google_Client;
use Google_Service_Oauth2;
use Kopper\Config;
use Kopper\Exception\NonFatalException;

class GoogleAuthenticator extends Authenticator {

  const PROVIDER_NAME = 'google';

  public function getProfile($token) {
    if (empty($this->profile) === true) {
      $client = new Google_Client();
      $client->setClientId(Config::get('google.api.client.id'));
      $client->setClientSecret(Config::get('google.api.client.secret'));

      $client->addScope('https://www.googleapis.com/auth/userinfo.profile');
      $client->addScope('https://www.googleapis.com/auth/userinfo.email');

      $profile = null;

      if (is_array($token) === true) {
        if (empty($token['idToken']) === false) {
          //this is a signed id token
          $ticket = $client->verifyIdToken($token['idToken'])->getAttributes()['payload'];
          if ($ticket['email'] === $token['email']) {
            $profile = $token;
            $profile['id'] = $ticket['sub'];
          } else {
            throw new NonFatalException('invalid token, emails dont match');
          }
        } else if (empty($token['access_token']) === false) {
          //regular access token
          $client->setAccessToken(json_encode($token));
        } else {
          throw new NonFatalException('invalid token, no accpetable attribute');
        }
      } else if (is_string($token) === true) {
        //token is a CODE to exchange for access token
        $client->authenticate($token);
      } else {
        throw new NonFatalException('invalid token, invalid type');
      }

      if (empty($profile) === true) {
        $oauth = new Google_Service_Oauth2($client);
        $profile = $oauth->userinfo->get();
      }

      $this->profile = array(
        'provider' => self::PROVIDER_NAME,
        'id' => $profile['id'],
        'email' => $profile['email'],
        'firstName' => isset($profile['givenName']) ? $profile['givenName'] : null,
        'lastName' => isset($profile['familyName']) ? $profile['familyName'] : null
      );
    }

    return $this->profile;
  }

}
