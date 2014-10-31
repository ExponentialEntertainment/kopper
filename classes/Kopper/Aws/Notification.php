<?php

namespace Kopper\Aws;

use Aws\Sns\SnsClient;

class Notification extends AwsClient {

	public function __construct($config = array()) {
    parent::__construct($config);

		$this->client = SnsClient::factory($this->config);
	}

	public function publish($topic, $subject, $message) {
		$this->client->publish(array(
				'TopicArn' => $topic,
				'Subject' => $subject,
				'Message' => $message
		));
	}

}