<?php

namespace Tests\Kopper\Aws;

use Kopper\Aws\Email;

class EmailTest extends AwsClientBaseTest {

  public function createClient() {
    return new Email();
  }

}
