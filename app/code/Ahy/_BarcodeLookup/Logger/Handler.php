<?php

namespace Ahy\BarcodeLookup\Logger;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;
use Magento\Framework\Filesystem\DriverInterface;

class Handler extends Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * File name
     * @var string
     */
    protected $fileName;

    public function __construct(DriverInterface $driver, $loggerType = null)
    {
        $this->fileName = "/var/log/BarcodeLookup/" . date('Y-m-d') . "/barcode_lookup.log";
        parent::__construct($driver, $loggerType);
    }
}
