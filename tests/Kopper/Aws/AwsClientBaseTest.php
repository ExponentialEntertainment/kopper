<?php

namespace Tests\Kopper\Aws;

use PHPUnit_Framework_TestCase;

abstract class AwsClientBaseTest extends PHPUnit_Framework_TestCase {

  abstract public function createClient();

  public function testCreateClient() {
    $client = $this->createClient();
    $this->assertNotEmpty($client);
  }

}
