<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\ResourceModel\Pack\Sales;

use Amasty\Mostviewed\Model\ResourceModel\Pack\Analytic\Sales\PackHistoryTable;
use Magento\Framework\App\ResourceConnection;

class LoadPackData
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(int $orderId): array
    {
        $select = $this->resourceConnection->getConnection()->select()->from(
            $this->resourceConnection->getTableName(PackHistoryTable::TABLE_NAME),
            [PackHistoryTable::PACK_COLUMN, PackHistoryTable::QTY_COLUMN, PackHistoryTable::PACK_NAME_COLUMN]
        )->where(sprintf('%s = ?', PackHistoryTable::ORDER_COLUMN), $orderId);

        return $this->resourceConnection->getConnection()->fetchAssoc($select);
    }
}
