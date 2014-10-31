<?php

namespace Kopper\Aws;

use Aws\Ec2\Ec2Client;

class Compute extends AwsClient {

	public function __construct($config = array()) {
    parent::__construct($config);

		$this->client = Ec2Client::factory($this->config);
	}

	public function getInstances($name) {
		$iterator = $this->client->getDescribeInstancesIterator(array(
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
		$instances = $this->getInstances($name);

		$domains = array();

		foreach ($instances as $instance) {
			array_push($domains, $instance['PublicDnsName']);
		}

		return $domains;
	}

}