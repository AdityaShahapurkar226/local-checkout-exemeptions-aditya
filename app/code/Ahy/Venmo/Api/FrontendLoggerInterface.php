<?php
declare(strict_types=1);

namespace Ahy\Venmo\Api;

interface FrontendLoggerInterface
{
    /**
     * @param string $message
     * @param string $level
     * @return bool
     */
    public function log(string $message, string $level = 'info'): bool;
}
