<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

class SourceType implements OptionSourceInterface
{
    public const NONE = 0;

    public const SOURCE_BOUGHT = 1;

    public const SOURCE_VIEWED = 2;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::NONE, 'label' => __('Only Product Conditions')],
            ['value' => self::SOURCE_VIEWED, 'label' => __('Viewed Together')],
            ['value' => self::SOURCE_BOUGHT, 'label' => __('Bought Together')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::SOURCE_VIEWED => __('Viewed Together'),
            self::SOURCE_BOUGHT => __('Bought Together'),
            self::NONE          => __('Only Product Conditions')
        ];
    }
}
