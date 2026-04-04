<?php

namespace Ahy\Authorizenet\Block\Checkout\Payment;

use Magento\Framework\View\Element\Template;

class EncryptCardDetails extends Template
{
    public function encrypt($dataString, $key)
    {
        $method = 'aes-256-cbc';
        $key = substr(hash('sha256', $key), 0, 32);
        $iv = random_bytes(16);
        $encryptedData = openssl_encrypt($dataString, $method, $key, OPENSSL_RAW_DATA, $iv);
        $encryptedString = base64_encode($iv . $encryptedData);
        return $encryptedString;
    }
    
}
