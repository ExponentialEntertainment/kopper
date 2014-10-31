<?php

namespace Kopper\Authentication;

use Exception;
use Kopper\Cache\GlobalCache;
use Kopper\Exception\NonFatalException;

abstract class EmailProvider {

  private $loginAttempts = 0;

  public function get($token) {
    $token = json_decode(base64_decode($token));

    $this->validate($token);

    return array(
      'id' => $this->getEmail($token),
      'email' => $this->getEmail($token),
      'password' => $token->password,
      'firstName' => empty($token->firstName) ? null : $token->firstName,
      'lastName' => empty($token->lastName) ? null : $token->lastName
    );
  }

  public function validate($token) {
    if (empty($token) === true || empty($token->id) === true || empty($token->password)) {
      throw new Exception('invalid email token');
    }

    if (filter_var($this->getEmail($token), FILTER_VALIDATE_EMAIL) === false) {
      throw new NonFatalException('invalid email');
    }
  }

  abstract public function getUser($id);

  protected function getEmail($token) {
    return strtolower($token->id);
  }

  protected function attemptLimitKey($userId) {
    return "email_{$userId}_login_attempts";
  }

  protected function checkAttemptLimit($userId) {
    $this->loginAttempts = GlobalCache::get($this->attemptLimitKey($userId));

    if ($this->loginAttempts > 3) {
      throw new Exception('to many attempts', 403);
    }
  }

  protected function addAttempt($userId) {
    $this->loginAttempts = empty($this->loginAttempts) ? 1 : ($this->loginAttempts + 1);
    GlobalCache::set($this->attemptLimitKey($userId), $this->loginAttempts, 300);
  }

}
