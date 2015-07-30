<?php

namespace Tests\Kopper\Aws;

use Kopper\Aws\Meta;

class MetaTest extends AwsClientBaseTest {

  public function createClient() {
    return new Meta();
  }

}
