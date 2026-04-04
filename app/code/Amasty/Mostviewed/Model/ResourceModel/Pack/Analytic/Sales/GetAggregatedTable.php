<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\ResourceModel\Pack\Analytic\Sales;

use Amasty\Mostviewed\Model\ConfigProvider;
use Amasty\Mostviewed\Model\ResourceModel\Pack\Sales\GetAggregatedByPackTable;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

class GetAggregatedTable
{
    public const VIEW_NAME = 'amasty_mostviewed_pack_sales_aggregated';
    public const PACK_COLUMN = 'pack_id';
    public const COUNT_COLUMN = 'orders_count';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var GetAggregatedByPackTable
     */
    private $getAggregatedByPackTable;

    public function __construct(
        GetAggregatedByPackTable $getAggregatedByPackTable,
        ResourceConnection $resourceConnection,
        ConfigProvider $configProvider
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->configProvider = $configProvider;
        $this->getAggregatedByPackTable = $getAggregatedByPackTable;
    }

    public function execute(): Select
    {
        $orderTable = $this->resourceConnection->getTableName('sales_order');

        $table = $this->getAggregatedByPackTable->execute()->join(
            ['sales_order' => $orderTable],
            sprintf(
                'pack_sales.%s = sales_order.entity_id AND sales_order.status IN (%s)',
                PackHistoryTable::ORDER_COLUMN,
                $this->resourceConnection->getConnection()->quoteInto(
                    '?',
                    $this->configProvider->getPackAnalyticOrderStatuses()
                )
            ),
            []
        )->group('pack_id');

        return $table;
    }
}
