<?php
declare(strict_types=1);

namespace Ahy\Venmo\Logger\Handler;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Venmo extends Base
{
    protected $loggerType = Logger::DEBUG;
    protected $fileName = '/var/log/venmo_payment.log';
}
