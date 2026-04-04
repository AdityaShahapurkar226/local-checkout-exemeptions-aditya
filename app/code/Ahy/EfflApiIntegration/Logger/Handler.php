<?php
namespace Ahy\EfflApiIntegration\Logger;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;

class Handler extends Base
{
    protected $fileName = '/var/log/efflintegration.log';
    protected $loggerType = Logger::DEBUG;
}
