<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\ResourceModel\Pack\Analytic\PackSales;

class Table
{
    public const TABLE_NAME = 'amasty_mostviewed_pack_sales';

    public const ID_COLUMN = 'id';
    public const PACK_ID_COLUMN = 'pack_id';
    public const PACK_NAME_COLUMN = 'pack_name';
    public const PRODUCT_NAMES_COLUMN = 'product_names';
    public const ORDER_ID_COLUMN = 'order_id';
    public const BASE_SUBTOTAL_ID_COLUMN = 'base_subtotal';
    public const BASE_TOTAL_ID_COLUMN = 'base_grand_total';
    public const TOTAL_ID_COLUMN = 'grand_total';
}
