<?php

namespace Kopper\Aws;

use Aws\Sqs\SqsClient;
use Kopper\Cache\LocalCache;

class Queue extends AwsClient {

  const MAX_RECIEVE = 10;
  const DEFAULT_TIMEOUT = 60;

  public function __construct($config = array()) {
    parent::__construct($config);

    $this->client = new SqsClient($this->config);
  }

  public function getQueueUrl($queueName) {
    $key = $this->getRealEnvName($queueName) . '_queue_url';

    $queueUrl = LocalCache::get($key);

    if ($queueUrl === false) {
      $result = $this->client->getQueueUrl(array(
        'QueueName' => $this->getRealEnvName($queueName)
      ));

      $queueUrl = $result['QueueUrl'];

      LocalCache::set($key, $queueUrl);
    }

    return $queueUrl;
  }

  public function send($queueName, $message) {
    $this->client->sendMessage(array(
      'QueueUrl' => $this->getQueueUrl($queueName),
      'MessageBody' => $message
    ));
  }

  private function getMessages($queueName, $max = self::MAX_RECIEVE, $timeout = self::DEFAULT_TIMEOUT) {
    $result = $this->client->receiveMessage(array(
      'QueueUrl' => $this->getQueueUrl($queueName),
      'MaxNumberOfMessages' => $max,
      'VisibilityTimeout' => $timeout
    ));

    return $result['Messages'];
  }

  public function recieve($queueName, $max = 100, $timeout = self::DEFAULT_TIMEOUT) {
    $loopCount = floor($max / self::MAX_RECIEVE);
    $remainder = $max % self::MAX_RECIEVE;

    $messages = array();

    $i = 0;
    $done = false;
    while ($i < $loopCount && $done == false) {
      $newMessages = $this->getMessages($queueName, self::MAX_RECIEVE, $timeout);

      if (count($newMessages) < self::MAX_RECIEVE) {
        $done = true;
      }

      if (is_array($newMessages) == true) {
        $messages = array_merge($messages, $newMessages);
      }

      $i++;
    }

    if ($done == false && $remainder > 0) {
      $newMessages = $this->getMessages($queueName, $remainder, $timeout);

      if (is_array($newMessages) == true) {
        $messages = array_merge($messages, $newMessages);
      }
    }

    return $messages;
  }

  public function delete($queueName, $id) {
    $this->client->deleteMessage(array(
      'QueueUrl' => $this->getQueueUrl($queueName),
      'ReceiptHandle' => $id
    ));
  }

}
