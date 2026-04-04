<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\ResourceModel\Pack\Analytic\Sales\OrderFilters;

use Amasty\Mostviewed\Model\ResourceModel\Pack\Analytic\Sales\PackHistoryTable;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;

class OrderByPackFilter implements OrderFilterInterface
{
    public function execute(Collection $collection, string $value): void
    {
        $collection->getSelect()->where(sprintf(
            '%s.%s = ?',
            PackHistoryTable::TABLE_NAME,
            PackHistoryTable::PACK_COLUMN
        ), $value);
    }
}
