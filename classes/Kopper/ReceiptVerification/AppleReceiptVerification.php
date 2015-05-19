<?php

namespace Kopper\ReceiptVerification;

use Exception;
use Kopper\Config;
use Kopper\Environment;
use Kopper\Exception\NonFatalException;
use Kopper\Logger\Logger;
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

    if(Config::get('receiptVerification.sandbox') === true){
      $url = self::SANDBOX_API;
    }else{
      $url = Environment::is(Environment::PRODUCTION) ? self::PRODUCTION_API : self::SANDBOX_API;
    }
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

    Logger::getInstance()->log(new Exception('bad receipt - ' . json_encode($response)));

    return false;
  }

}
