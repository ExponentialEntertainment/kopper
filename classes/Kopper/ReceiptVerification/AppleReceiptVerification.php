<?php

namespace Kopper\ReceiptVerification;

use Kopper\Environment;
use Kopper\Exception\NonFatalException;
use Kopper\URLRequest;

class AppleReceiptVerification extends ReceiptVerification {

  const STORE_NAME = 'apple';
  const PRODUCTION_API = 'https://buy.itunes.apple.com/verifyReceipt';
  const SANDBOX_API = 'https://sandbox.itunes.apple.com/verifyReceipt';

  public function verify($id, $receipt) {
    $data = $this->decodeReceipt($receipt);

    if (empty($data->receipt) === true) {
      throw new NonFatalException('missing receipt');
    }

    $url = APPLICATION_ENV === Environment::PRODUCTION ? self::PRODUCTION_API : self::SANDBOX_API;
    $request = new URLRequest($url);

    $response = $request->postJSON(array('receipt-data' => $data->receipt), false);

    if (isset($response->status) === false || $response->status != 0) {
      throw new NonFatalException('invalid receipt - ' . $response->status, 400);
    }

    if (empty($response->receipt->in_app) === false) {
      foreach ($response->receipt->in_app as $purchasedItem) {
        if ($purchasedItem->product_id === $id) {
          return true;
        }
      }
    }

    return false;
  }

}
