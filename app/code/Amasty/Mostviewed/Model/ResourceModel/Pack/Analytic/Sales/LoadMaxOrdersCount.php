<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\ResourceModel\Pack\Analytic\Sales;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

class LoadMaxOrdersCount
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var GetAggregatedTable
     */
    private $getAggregatedTable;

    public function __construct(
        ResourceConnection  $resourceConnection,
        GetAggregatedTable $getAggregatedTable
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->getAggregatedTable = $getAggregatedTable;
    }

    public function execute(): int
    {
        $select = $this->resourceConnection->getConnection()->select()->from(
            $this->getAggregatedTable->execute(),
            [GetAggregatedTable::COUNT_COLUMN]
        )->order(sprintf('%s %s', GetAggregatedTable::COUNT_COLUMN, Select::SQL_DESC));

        return (int) $this->resourceConnection->getConnection()->fetchOne($select);
    }
}
