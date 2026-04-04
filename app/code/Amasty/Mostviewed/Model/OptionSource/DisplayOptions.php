<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

class DisplayOptions implements OptionSourceInterface
{
    public const ONLY_REQUIRED = 0;
    public const ALL_OPTIONS = 1;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::ONLY_REQUIRED, 'label' => __('Only if Product has Required Options')],
            ['value' => self::ALL_OPTIONS, 'label' => __('Always')]
        ];
    }
}
