<?php

namespace Tests\Kopper\Aws;

use Kopper\Aws\Notification;

class NotificationTest extends AwsClientBaseTest {

  public function createClient() {
    return new Notification();
  }

}
