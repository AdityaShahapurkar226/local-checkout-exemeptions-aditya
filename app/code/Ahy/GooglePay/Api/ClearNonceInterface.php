<?php
declare(strict_types=1);

namespace Ahy\GooglePay\Api;

interface ClearNonceInterface
{
    /**
     * Clear the Google Pay nonce from the session
     *
     * @return bool
     */
    public function clearNonce(): bool;
}
