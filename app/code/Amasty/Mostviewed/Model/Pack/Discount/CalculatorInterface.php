<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack\Discount;

use Amasty\Mostviewed\Model\Pack\Finder\Result\SimplePack;
use Magento\Quote\Model\Quote\Item\AbstractItem;

interface CalculatorInterface
{
    /**
     * Return array with amount and base amount discount.
     *
     * @param AbstractItem $item
     * @param SimplePack $simplePack
     * @return array
     */
    public function execute(AbstractItem $item, SimplePack $simplePack): array;
}
