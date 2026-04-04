<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

class ApplyCondition implements OptionSourceInterface
{
    public const ANY_PRODUCTS = 0;
    public const ALL_PRODUCTS = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::ANY_PRODUCTS, 'label' => __('Any Bundle Product is Chosen')],
            ['value' => self::ALL_PRODUCTS, 'label' => __('All Bundle Products are Chosen')]
        ];
    }
}
