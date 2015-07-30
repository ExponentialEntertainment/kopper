<?php

namespace Tests\Kopper\Aws;

use Kopper\Aws\DynamoDb;

class DynamoDbTest extends AwsClientBaseTest {

  public function createClient() {
    return new DynamoDb();
  }

}
