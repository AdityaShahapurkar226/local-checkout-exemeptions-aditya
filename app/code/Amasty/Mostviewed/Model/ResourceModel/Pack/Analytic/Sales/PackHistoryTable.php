<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\ResourceModel\Pack\Analytic\Sales;

class PackHistoryTable
{
    public const TABLE_NAME = 'amasty_mostviewed_pack_sales_history';

    public const PACK_COLUMN = 'pack_id';
    public const PACK_NAME_COLUMN = 'pack_name';
    public const ORDER_COLUMN = 'order_id';
    public const QTY_COLUMN = 'qty';
}
