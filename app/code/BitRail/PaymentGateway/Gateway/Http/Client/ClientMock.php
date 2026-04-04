<?php

namespace BitRail\PaymentGateway\Gateway\Http\Client;

class ClientMock
{
    private const NONCE_CODE = 'R3wzqma6LdkbQiQg';

    public static function getNonceCode(): string
    {
        return md5(self::NONCE_CODE);
    }
}