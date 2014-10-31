<?php

namespace Kopper\Aws;

use Aws\CloudFront\CloudFrontClient;
use Kopper\Config;

class CloudFront extends AwsClient {

  public function __construct($config = array()) {
    parent::__construct($config);

    $this->client = CloudFrontClient::factory($this->config);
  }

  public function invalidate($distributionId, $keys) {
    $this->client->createInvalidation(array(
      'DistributionId' => $distributionId,
      'Paths' => array(
        'Quantity' => count($keys),
        'Items' => $keys
      ),
      'CallerReference' => Config::get('env.prefix') . '-' . time()
    ));
  }

  public function getSignedUrl($path, $privateKeyFileName, $keyPairId, $expires) {
    return $this->client->getSignedUrl(array(
        'url' => $path,
        'expires' => $expires,
        'private_key' => $privateKeyFileName,
        'key_pair_id' => $keyPairId
    ));
  }

}
