<?php

namespace Kopper\Aws;

use Kopper\URLRequest;

class Meta extends AwsClient {

  const ADDRESS = 'http://169.254.169.254/latest';

  public function __construct($config = array()) {
    parent::__construct($config);

    $this->client = new URLRequest();
  }

  public function get($api) {
    $this->client->setURL(self::ADDRESS . $api);
    $result = $this->client->get();

    return $result;
  }

  public function getPublicHostname() {
    return $this->get('/meta-data/public-hostname');
  }

}
