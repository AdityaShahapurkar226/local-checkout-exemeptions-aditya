<?php

namespace BitRail\PaymentGateway\Gateway\Http\Client;

class BitrailOrderTokenizer
{
    private const ORDER_TOKEN_SALT_PRE    = 'aoAlNPvOyI1LvMdm';
    private const ORDER_TOKEN_SALT_POST   = 's2WFNTuP90KeB7jz';

    public static function getOrderToken(string $quoteId): string
    {
        return md5(
            self::ORDER_TOKEN_SALT_PRE . $quoteId . self::ORDER_TOKEN_SALT_POST
        );
    }

    /**
     * This token is different from self::getOrderToken(). Need improve this function.
     */
    public static function tokenIsValid(string $quoteId, string $token): bool
    {
        return self::getOrderToken($quoteId) === $token;
    }
}