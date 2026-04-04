<?php
declare(strict_types=1);

namespace Ahy\GooglePay\Model;

use Ahy\GooglePay\Api\FrontendLoggerInterface;
use Ahy\GooglePay\Logger\GooglePayLogger;

class FrontendLogger implements FrontendLoggerInterface
{
    protected $logger;

    public function __construct(GooglePayLogger $logger)
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
