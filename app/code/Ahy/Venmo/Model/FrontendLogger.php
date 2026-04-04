<?php
declare(strict_types=1);

namespace Ahy\Venmo\Model;

use Ahy\Venmo\Api\FrontendLoggerInterface;
use Ahy\Venmo\Logger\VenmoLogger;
use Psr\Log\LogLevel;

class FrontendLogger implements FrontendLoggerInterface
{
    private VenmoLogger $logger;

    public function __construct(VenmoLogger $logger)
    {
        $this->logger = $logger;
    }

    public function log(string $message, string $level = LogLevel::INFO): bool
    {
        switch (strtolower($level)) {
            case 'debug':
                $this->logger->debug($message);
                break;
            case 'notice':
                $this->logger->notice($message);
                break;
            case 'warn':
            case 'warning':
                $this->logger->warning($message);
                break;
            case 'error':
                $this->logger->error($message);
                break;
            default:
                $this->logger->info($message);
        }
        return true;
    }
}
