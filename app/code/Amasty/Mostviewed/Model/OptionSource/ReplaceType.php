<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

class ReplaceType implements OptionSourceInterface
{
    public const REPLACE = '0';

    public const ADD = '1';

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::REPLACE, 'label' => __('Replace Manually Added Products')],
            ['value' => self::ADD, 'label' => __('Append to Manually Added Products')]
        ];
    }
}
