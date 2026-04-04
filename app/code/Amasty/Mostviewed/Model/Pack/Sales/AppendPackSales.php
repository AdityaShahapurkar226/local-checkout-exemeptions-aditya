<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack\Sales;

use Amasty\Mostviewed\Model\ResourceModel\Pack\Sales\InsertMultiple;
use Psr\Log\LoggerInterface;
use Zend_Db_Exception;

class AppendPackSales
{
    /**
     * @var InsertMultiple
     */
    private $insertMultiple;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        InsertMultiple $insertMultiple,
        LoggerInterface $logger
    ) {
        $this->insertMultiple = $insertMultiple;
        $this->logger = $logger;
    }

    /**
     * @param array $data
     * @return void
     */
    public function execute(array $data): void
    {
        if (!$data) {
            return;
        }

        try {
            $this->insertMultiple->execute($data);
        } catch (Zend_Db_Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
