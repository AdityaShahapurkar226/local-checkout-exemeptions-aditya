<?php
declare(strict_types=1);

namespace Ahy\GooglePay\Api;

interface FrontendLoggerInterface
{
    /**
     * Log a frontend message
     *
     * @param string $message
     * @param string $level (info, warning, error)
     * @return bool
     */
    public function log(string $message, string $level = 'info'): bool;
}
