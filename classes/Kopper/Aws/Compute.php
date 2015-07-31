<?php

namespace Kopper\Aws;

use Aws\Ec2\Ec2Client;

class Compute extends AwsClient {

  public function __construct($config = array()) {
    parent::__construct($config);

    $this->client = new Ec2Client($this->config);
  }

  public function getInstances($name) {
    $iterator = $this->client->getDescribeInstancesPaginator(array(
      'DryRun' => false,
      'Filters' => array(
        array(
          'Name' => 'tag:Name',
          'Values' => array($name)
        )
      )
    ));

    return $iterator;
  }

  public function getInstanceDomains($name) {
    $results = $this->getInstances($name);

    $domains = array();

    foreach ($results as $result) {
      foreach ($result['Contents'] as $instance) {
        array_push($domains, $instance['PublicDnsName']);
      }
    }

    return $domains;
  }

}
