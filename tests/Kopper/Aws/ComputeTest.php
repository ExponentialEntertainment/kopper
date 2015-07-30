<?php

namespace Tests\Kopper\Aws;

use Kopper\Aws\Compute;

class ComputeTest extends AwsClientBaseTest {

  public function createClient() {
    return new Compute();
  }

}
