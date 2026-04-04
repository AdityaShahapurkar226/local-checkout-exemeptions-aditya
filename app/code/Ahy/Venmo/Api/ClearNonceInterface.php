<?php
declare(strict_types=1);

namespace Ahy\Venmo\Api;

interface ClearNonceInterface
{
    /**
     * Clear the Venmo nonce from the session
     *
     * @return bool
     */
    public function clearNonce(): bool;
}
