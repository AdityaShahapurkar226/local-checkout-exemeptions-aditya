<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack\Analytic;

use Amasty\Mostviewed\Model\ResourceModel\Pack\Analytic\Sales\InsertMultiple;
use Amasty\Mostviewed\Model\ResourceModel\Pack\Analytic\Sales\PackHistoryTable;
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

    public function execute(int $orderId, array $packsData): void
    {
        if (!$packsData) {
            return;
        }

        $data = [];
        foreach ($packsData as $packId => $packData) {
            $data[] = [
                PackHistoryTable::ORDER_COLUMN => $orderId,
                PackHistoryTable::PACK_COLUMN => $packId,
                PackHistoryTable::QTY_COLUMN => $packData['qty'],
                PackHistoryTable::PACK_NAME_COLUMN => $packData['name']
            ];
        }

        try {
            $this->insertMultiple->execute($data);
        } catch (Zend_Db_Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
