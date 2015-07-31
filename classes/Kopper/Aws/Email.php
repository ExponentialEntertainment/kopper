<?php

namespace Kopper\Aws;

use Aws\Ses\SesClient;

class Email extends AwsClient {

  public function __construct($config = array()) {
    parent::__construct($config);

    $this->client = new SesClient($this->config);
  }

  public function getLimits() {
    return $this->client->getSendQuota();
  }

  public function send($from, $to, $subject, array $body) {
    $message = array(
        'Source' => $from,
        'Destination' => array(
            'ToAddresses' => array($to)
        ),
        'Message' => array(
            'Subject' => array(
                'Data' => $subject
            ),
            'Body' => $body
        )
    );

    $this->client->sendEmail($message);
  }

}