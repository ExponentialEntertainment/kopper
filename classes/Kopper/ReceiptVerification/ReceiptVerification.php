<?php

namespace Kopper\ReceiptVerification;

use Kopper\Utility;

abstract class ReceiptVerification {

  abstract function verify($id, $receipt);

  public function decodeReceipt($receipt) {
    if (is_string($receipt) === true) {
      return json_decode(base64_decode($receipt));
    } else if (is_array($receipt) === true) {
      return Utility::arrayToObject($receipt);
    } else if (is_object($receipt) === true) {
      return $receipt;
    }
  }

}
