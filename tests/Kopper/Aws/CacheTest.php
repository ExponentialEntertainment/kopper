<?php

namespace Tests\Kopper\Aws;

use Kopper\Aws\Cache;

class CacheTest extends AwsClientBaseTest {

  public function createClient() {
    return new Cache();
  }

}
