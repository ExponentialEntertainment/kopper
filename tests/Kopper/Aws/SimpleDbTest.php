<?php

namespace Tests\Kopper\Aws;

use Kopper\Aws\SimpleDb;

class SimpleDbTest extends AwsClientBaseTest {

  public function createClient() {
    return new SimpleDb();
  }

}
