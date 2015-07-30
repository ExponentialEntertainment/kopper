<?php

namespace Tests\Kopper\Aws;

use Kopper\Aws\AwsClient;
use Kopper\Config;

class AwsClientTest extends AwsClientBaseTest {

  public function createClient() {
    return new AwsClient();
  }
  
  public function testDefaultConfig() {
    $config = AwsClient::defaultConfig();

    $this->assertNotEmpty($config);
    $this->assertArrayHasKey('region', $config);
    $this->assertEquals($config['region'], Config::get('aws.region'));
  }

  public function testRealEnvName() {
    $name = 'test';
    
    $client = $this->createClient();
    $realEnvName = $client->getRealEnvName($name);

    $this->assertEquals('kopper_dev_' . $name, $realEnvName);
  }

}
