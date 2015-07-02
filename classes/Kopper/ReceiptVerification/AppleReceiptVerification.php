<?php

namespace Kopper\ReceiptVerification;

use Exception;
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

    $response = $this->check($data->receipt);

    if (empty($response->receipt->in_app) === false) {
      foreach ($response->receipt->in_app as $purchasedItem) {
        if ($purchasedItem->product_id === $id) {
          return true;
        }
      }
    } else if (empty($response->receipt->product_id) === false) {
      if ($response->receipt->product_id === $id) {
        return true;
      }
    }

    Logger::getInstance()->log(new Exception('bad receipt - ' . json_encode($response)));

    return false;
  }

  protected function check($receipt, $url = null) {
    $url = empty($url) ? self::PRODUCTION_API : $url;

    $request = new URLRequest($url);
    $response = $request->postJSON(array('receipt-data' => $receipt), false);

    if (isset($response->status) === false) {
      throw new NonFatalException('invalid receipt', 400);
    } else if ($response->status != 0) {
      if ($response->status === 21007) {
        $response = $this->check($receipt, self::SANDBOX_API);
      } else {
        throw new NonFatalException('invalid receipt - ' . $response->status, 400);
      }
    }

    return $response;
  }

}
