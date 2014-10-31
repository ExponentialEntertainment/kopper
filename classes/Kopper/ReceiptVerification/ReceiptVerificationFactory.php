<?php

namespace Kopper\ReceiptVerification;

use Exception;

class ReceiptVerificationFactory {

  public static function create($store) {
    switch ($store) {
      case AppleReceiptVerification::STORE_NAME:
        return new AppleReceiptVerification();
      default:
        throw new Exception("invalid store ($store)");
    }
  }

}
