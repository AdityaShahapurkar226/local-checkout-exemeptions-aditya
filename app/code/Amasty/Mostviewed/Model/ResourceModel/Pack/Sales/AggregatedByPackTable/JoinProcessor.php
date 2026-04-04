<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\ResourceModel\Pack\Sales\AggregatedByPackTable;

use Amasty\Mostviewed\Model\ResourceModel\Pack\Analytic\Sales\PackHistoryTable;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;

class JoinProcessor
{
    public const JOINED_PACK_DATA_FLAG = 'pack_data_joined';

    public function execute(Collection $orderCollection): void
    {
        if ($orderCollection->isLoaded() || $orderCollection->hasFlag(self::JOINED_PACK_DATA_FLAG)) {
            return;
        }

        $orderCollection->setFlag(self::JOINED_PACK_DATA_FLAG, true);

        $orderCollection->getSelect()->joinLeft(
            [PackHistoryTable::TABLE_NAME => $orderCollection->getResource()->getTable(PackHistoryTable::TABLE_NAME)],
            sprintf(
                '%s.%s = main_table.entity_id',
                PackHistoryTable::TABLE_NAME,
                PackHistoryTable::ORDER_COLUMN
            ),
            [
                'mostviewed_bundles' => sprintf(
                    'group_concat(`%s` separator \', \')',
                    PackHistoryTable::PACK_NAME_COLUMN
                ),
                'mostviewed_includes_bundles' => $orderCollection->getConnection()->getCheckSql(
                    sprintf(
                        '%s.%s IS NOT NULL',
                        PackHistoryTable::TABLE_NAME,
                        PackHistoryTable::ORDER_COLUMN
                    ),
                    1,
                    0
                )
            ]
        )->group('main_table.entity_id');
    }
}
