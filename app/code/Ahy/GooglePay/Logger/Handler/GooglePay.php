<?php
namespace Ahy\GooglePay\Logger\Handler;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;

class GooglePay extends Base
{
    protected $fileName = '/var/log/googlepay_payment.log'; // log file location
    protected $loggerType = Logger::DEBUG;          // log level (DEBUG, INFO, etc.)
}
