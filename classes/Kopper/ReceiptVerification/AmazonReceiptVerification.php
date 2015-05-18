<?php

namespace Kopper\ReceiptVerification;

use Kopper\Config;
use Kopper\Environment;
use Kopper\Exception\NonFatalException;
use Kopper\URLRequest;
use Exception;

class AmazonReceiptVerification extends ReceiptVerification {

  const STORE_NAME = 'amazon';
  const PRODUCTION_API = 'https://appstore-sdk.amazon.com';
  //const SANDBOX_API = 'http://127.0.0.1:8080/RVSSandbox';
  const SANDBOX_API = 'https://appstore-sdk.amazon.com';

  public function verify($id, $receipt) {
    $data = $this->decodeReceipt($receipt);
    
    if(empty($data->receipt) === true || empty($data->userId) === true ){
      throw new NonFatalException('missing receipt or userId');
    }
    
    $secret = Config::get('amazon.iap.secret');

    $suffix = "/version/2.0/verify/developer/$secret/user/{$data->userId}/purchaseToken/{$data->receipt}";
    $url = (Environment::is(Environment::PRODUCTION) ? self::PRODUCTION_API : self::SANDBOX_API) . $suffix;

    $request = new URLRequest($url);
    $response = $request->getJSON(false);

    if (empty($response) === true) {
      throw new Exception('invalid receipt', 400);
    }

    if (empty($response->message) === false) {
      throw new NonFatalException($response->message, $request->lastResponseCode);
    }

    $productId = empty($response->sku) ? null : $response->sku;
    
    return $id === $productId;
  }

}
