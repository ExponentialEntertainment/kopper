<?php

namespace Tests\Kopper\Aws;

use Kopper\Aws\CloudFront;

class CloudFrontTest extends AwsClientBaseTest {

  public function createClient() {
    return new CloudFront();
  }

}
