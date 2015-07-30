<?php

namespace Tests\Kopper\Aws;

use Kopper\Aws\Queue;

class QueueTest extends AwsClientBaseTest {

  public function createClient() {
    return new Queue();
  }

}
