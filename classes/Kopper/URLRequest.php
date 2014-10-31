<?php

namespace Kopper;

use \Exception;

class URLRequest {

  const GET = "get";
  const POST = "post";

  private $handle;
  public $lastResponseCode;

  public function __construct($url = null) {
    $this->handle = curl_init($url);

    curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);

    if (APPLICATION_ENV == Environment::LOCAL) {
      curl_setopt($this->handle, CURLOPT_SSL_VERIFYPEER, false);
    }
  }

  public function __destruct() {
    curl_close($this->handle);
  }

  protected function send() {
    $data = curl_exec($this->handle);

    $this->lastResponseCode = curl_getinfo($this->handle, CURLINFO_HTTP_CODE);

    if ($data === false) {
      throw new Exception(curl_error($this->handle), curl_errno($this->handle));
    } else {
      return $data;
    }
  }

  public function setURL($url) {
    curl_setopt($this->handle, CURLOPT_URL, $url);
  }

  public function getJSON($assoc = true, $depth = 512, $options = 0) {
    return json_decode($this->get(), $assoc, $depth, $options);
  }
  
  public function postJSON($data, $assoc = true, $depth = 512, $options = 0) {
    return json_decode($this->post(json_encode($data)), $assoc, $depth, $options);
  }

  public function get() {
    curl_setopt($this->handle, CURLOPT_HTTPGET, true);

    return $this->send();
  }

  public function post($data) {
    curl_setopt($this->handle, CURLOPT_POST, true);
    curl_setopt($this->handle, CURLOPT_POSTFIELDS, $data);

    return $this->send();
  }

  public function setHeaders(array $headers) {
    curl_setopt($this->handle, CURLOPT_HTTPHEADER, $headers);
  }

  public function authentication($username, $password) {
    curl_setopt($this->handle, CURLOPT_USERPWD, $username . ":" . $password);
  }

  public function isSuccessful() {
    return $this->lastResponseCode < 400;
  }

}
