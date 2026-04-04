<?php
declare(strict_types=1);

namespace Ahy\EstateApiIntegration\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Handler extends Base
{
    /**
     * Log file name
     *
     * @var string
     */
    protected $fileName = '/var/log/estateintegration.log';

    /**
     * Logging level
     *
     * @var int
     */
    protected $loggerType = Logger::DEBUG;
}
