<?php
namespace Ahy\ApplePay\Logger\Handler;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;

class ApplePay extends Base
{
    protected $fileName = '/var/log/applepay_payment.log';  // relative path only!
    protected $loggerType = Logger::DEBUG;
}
