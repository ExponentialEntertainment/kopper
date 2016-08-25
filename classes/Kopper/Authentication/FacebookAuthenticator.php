<?php

namespace Kopper\Authentication;

use Facebook\Facebook;
use Kopper\Config;

class FacebookAuthenticator extends Authenticator {

	const PROVIDER_NAME = 'facebook';

	public function getProfile($token) {
		if (empty($this->profile) === true) {
			$fb = new Facebook([
				'app_id' => Config::get('facebook.app.id'),
				'app_secret' => Config::get('facebook.app.secret'),
				'default_access_token' => $token
			]);

			$profile = $fb->get('/me')->getGraphUser();

			$this->profile = array(
				'provider' => self::PROVIDER_NAME,
				'id' => $profile->getField('id'),
				'email' => $profile->getField('email'),
				'firstName' => $profile->getFirstName(),
				'lastName' => $profile->getLastName()
			);
		}

		return $this->profile;
	}

}
