<?php
declare(strict_types=1);

namespace Ahy\ApplePay\Model;

use Ahy\ApplePay\Api\FrontendLoggerInterface;
use Ahy\ApplePay\Logger\ApplePayLogger;

class FrontendLogger implements FrontendLoggerInterface
{
    protected $logger;

    public function __construct(ApplePayLogger $logger)
    {
        $this->logger = $logger;
    }

    public function log(string $message, string $level = 'info'): bool
    {
        switch (strtolower($level)) {
            case 'error':
                $this->logger->error('[Frontend] ' . $message);
                break;
            case 'warning':
                $this->logger->warning('[Frontend] ' . $message);
                break;
            default:
                $this->logger->info('[Frontend] ' . $message);
        }

        return true;
    }
}
