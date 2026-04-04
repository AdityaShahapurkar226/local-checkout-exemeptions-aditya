<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Cart\Add;

use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Downloadable\Model\Product\Type as Downloadable;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

class IsProductHasRequiredOptions
{
    public function execute(ProductInterface $product): bool
    {
        switch ($product->getTypeId()) {
            case Type::TYPE_SIMPLE:
            case Type::TYPE_VIRTUAL:
                $result = $product->getTypeInstance()->hasRequiredOptions($product);
                break;
            case Configurable::TYPE_CODE:
            case Grouped::TYPE_CODE:
            case Bundle::TYPE_CODE:
            case Downloadable::TYPE_DOWNLOADABLE:
            case 'amgiftcard':
            default:
                $result = true;
        }

        return $result;
    }
}
