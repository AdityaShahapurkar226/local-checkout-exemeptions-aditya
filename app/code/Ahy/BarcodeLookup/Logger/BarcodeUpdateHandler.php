<?php

namespace Ahy\BarcodeLookup\Logger;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;
use Magento\Framework\Filesystem\DriverInterface;

class BarcodeUpdateHandler extends Base
{
    protected $loggerType = Logger::INFO;

    protected $fileName;

    public function __construct(DriverInterface $driver, $loggerType = null)
    {
        $this->fileName = '/var/log/BarcodeLookup/' . date('Y-m-d') . '/barcode_update.log';
        parent::__construct($driver, $loggerType);
    }
}
