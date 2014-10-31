<?php

namespace Kopper\ReceiptVerification;

use Kopper\Config;
use Kopper\Exception\NonFatalException;

class GoogleReceiptVerification extends ReceiptVerification {

  const STORE_NAME = 'google';

  public function verify($id, $receipt) {
    $data = $this->decodeReceipt($receipt);
    
    if(empty($data->receipt) === true || empty($data->signature) === true){
      throw new NonFatalException('missing receipt or signature');
    }

    $decodedReceipt = base64_decode($data->receipt);
    $decodedSignature = base64_decode(str_replace(' ', '+', $data->signature));

    $publicKey = Config::get('google.iab.key');

    $key = '-----BEGIN PUBLIC KEY-----' . PHP_EOL . chunk_split($publicKey, 64, PHP_EOL) . '-----END PUBLIC KEY-----';
    $key = openssl_get_publickey($key);

    $verified = openssl_verify($decodedReceipt, $decodedSignature, $key);

    //openssl_verify return 0 on invalid and -1 on error
    if ($verified < 1) {
      throw new NonFatalException('invalid receipt or signature');
    }
    
    $purchaseInfo = json_decode($decodedReceipt);
    $productId = empty($purchaseInfo->productId) ? null : $purchaseInfo->productId;
    
    return $id === $productId;
  }

}
