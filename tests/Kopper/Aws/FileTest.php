<?php

namespace Tests\Kopper\Aws;

use Kopper\Aws\File;

class FileTest extends AwsClientBaseTest {

  public function createClient() {
    return new File();
  }

}
