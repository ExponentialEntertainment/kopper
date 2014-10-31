<?php

namespace Kopper\Aws;

use Aws\Common\InstanceMetadata\InstanceMetadataClient;

/**
 * Description of Meta
 *
 * @author eric
 */
class Meta extends AwsClient {

  public function __construct($config = array()) {
    parent::__construct($config);
    
    $this->client = InstanceMetadataClient::factory($this->config);
  }

  public function getPublicHostname() {
    $request = $this->client->get('meta-data/public-hostname/');
    $result = $request->send()->getBody(true);

    return $result;
  }

}