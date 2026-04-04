<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Backend\Pack\Initialization\ConditionalDiscount;

use Amasty\Mostviewed\Model\Backend\Pack\Registry;
use Magento\Framework\Exception\LocalizedException;

class LessThanItemsCount implements ColumnValidatorInterface
{
    /**
     * @var Registry
     */
    private $packRegistry;

    public function __construct(Registry $packRegistry)
    {
        $this->packRegistry = $packRegistry;
    }

    public function validate(string $columnName, ?string $value): void
    {
        $currentPack = $this->packRegistry->get();
        $childIds = array_filter(explode(',', $currentPack->getProductIds()));
        $parentCount = $currentPack->hasParentIds() || $currentPack->hasParentProductIds()
            ? 1 // 1 - is parent count in bundle
            : 0;
        $possiblePackItemsCount = count($childIds) + $parentCount;
        $value = (int) $value;

        if ($value > $possiblePackItemsCount) {
            throw new LocalizedException(__(
                'Amount of possible bundle items is lower than Number of Individual Bundle Items.
                Maximum items in the bundle is %1.',
                $possiblePackItemsCount
            ));
        }
    }
}
