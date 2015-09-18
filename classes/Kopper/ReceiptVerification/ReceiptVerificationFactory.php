<?php

namespace Kopper\ReceiptVerification;

use Exception;

class ReceiptVerificationFactory {

  public static function create($store) {
    switch ($store) {
      case AppleReceiptVerification::STORE_NAME:
        return new AppleReceiptVerification();
      case GoogleReceiptVerification::STORE_NAME:
        return new GoogleReceiptVerification();
      case AmazonReceiptVerification::STORE_NAME:
        return new AmazonReceiptVerification();
      default:
        throw new Exception("invalid store ($store)");
    }
  }

}
