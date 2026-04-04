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

class GetAggregatedByOrderTable
{
    public const VIEW_NAME = 'amasty_mostviewed_pack_sales_aggregated_order';
    public const ORDER_COLUMN = 'order_id';
    public const PACK_NAMES_COLUMN = 'pack_names';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(?int $packId = null): Select
    {
        $packHistoryTable = $this->resourceConnection->getTableName(PackHistoryTable::TABLE_NAME);

        $table = $this->resourceConnection->getConnection()->select()->from(['pack_sales' => $packHistoryTable], [
            self::ORDER_COLUMN => PackHistoryTable::ORDER_COLUMN,
            self::PACK_NAMES_COLUMN => sprintf(
                'group_concat(`%s` separator \', \')',
                PackHistoryTable::PACK_NAME_COLUMN
            )
        ])->group(PackHistoryTable::ORDER_COLUMN);

        if ($packId) {
            $table->where(
                sprintf('%s = ?', PackHistoryTable::PACK_COLUMN),
                $packId
            );
        }

        return $table;
    }
}
