<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

class RuleType implements OptionSourceInterface
{
    public const PRODUCT = 'product';

    public const CART = 'cart';

    public const CATEGORY = 'category';

    public const CUSTOM = 'custom';

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::PRODUCT, 'label' => __('Product Page')],
            ['value' => self::CART, 'label' => __('Shopping Cart Page')],
            ['value' => self::CATEGORY, 'label' => __('Category Page')],
            ['value' => self::CUSTOM, 'label' => __('Custom')]
        ];
    }

    /**
     * @param $value
     *
     * @return array
     */
    public function getNameByValue($value)
    {
        $result = '';
        foreach ($this->toOptionArray() as $item) {
            if ($item['value'] == $value) {
                $result = $item;
                break;
            }
        }

        return $result;
    }
}
