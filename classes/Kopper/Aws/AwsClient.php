<?php

namespace Kopper\Aws;

use Kopper\Config;
use Kopper\Environment;

class AwsClient {

  protected $config;
  protected $client;

  public function __construct($config = array()) {
    $this->config = array_merge(self::defaultConfig(), $config);
  }

  public static function defaultConfig() {
    $config = array(
      'version' => 'latest'
    );

    if (APPLICATION_ENV == Environment::LOCAL) {
      $config['credentials'] = array();

      if (empty($config['key']) === true) {
        $config['credentials']['key'] = Config::get('aws.key');
      }

      if (empty($config['secret']) === true) {
        $config['credentials']['secret'] = Config::get('aws.secret');
      }
    }

    $region = Config::get('aws.region');
    $config['region'] = empty($region) ? 'us-east-1' : $region;

    return $config;
  }

  public static function getInstance($config = array()) {
    return new static($config);
  }

  public function getRealEnvName($name) {
    return Environment::prefix($name);
  }

}
