<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

class PackPosition implements OptionSourceInterface
{
    public const PRODUCT_INFO = 'below';

    public const TAB = 'tab';

    public const CUSTOM_POSITION = 'custom';

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::PRODUCT_INFO, 'label' => __('Below Product Info')],
            ['value' => self::TAB, 'label' => __('Product Tab')],
            ['value' => self::CUSTOM_POSITION, 'label' => __('Custom Position')]
        ];
    }
}
