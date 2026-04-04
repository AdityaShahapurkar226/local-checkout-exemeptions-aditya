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
use Magento\Framework\DB\Select;

class GetAggregatedByPackTable
{
    public const VIEW_NAME = 'amasty_mostviewed_pack_sales_aggregated';
    public const PACK_COLUMN = 'pack_id';
    public const COUNT_COLUMN = 'orders_count';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(): Select
    {
        $packHistoryTable = $this->resourceConnection->getTableName(PackHistoryTable::TABLE_NAME);

        $table = $this->resourceConnection->getConnection()->select()->from(['pack_sales' => $packHistoryTable], [
            self::PACK_COLUMN => PackHistoryTable::PACK_COLUMN,
            self::COUNT_COLUMN => sprintf('SUM(%s)', PackHistoryTable::QTY_COLUMN)
        ])->group('pack_id');

        return $table;
    }
}
