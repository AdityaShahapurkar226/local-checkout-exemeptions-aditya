<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Rule\Condition;

class ProductFactory extends \Magento\CatalogRule\Model\Rule\Condition\ProductFactory
{
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Amasty\Mostviewed\Model\Rule\Condition\Product::class
    ) {
        parent::__construct($objectManager, $instanceName);
        null;//fix coding standard. owerride to change instance
    }
}
