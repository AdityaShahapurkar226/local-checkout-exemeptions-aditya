<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\OptionSource;

class BlockPositionInput extends BlockPosition
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => " ", 'label' => __('Please select an option')],
            [
                'label' => __('Product Page'),
                'value' => [
                    ['value' => self::PRODUCT_BEFORE_RELATED, 'label' => __('Before Native Related Block')],
                    ['value' => self::PRODUCT_AFTER_RELATED, 'label' => __('After Native Related Block')],
                    ['value' => self::PRODUCT_INTO_RELATED, 'label' => __('Add into Native Related Block')],
                    ['value' => self::PRODUCT_BEFORE_UPSELL, 'label' => __('Before Native Up-sells Block')],
                    ['value' => self::PRODUCT_AFTER_UPSELL, 'label' => __('After Native Up-sells Block')],
                    ['value' => self::PRODUCT_INTO_UPSELL, 'label' => __('Add into Native Up-sells Block')],
                    ['value' => self::PRODUCT_CONTENT_TAB, 'label' => __('Into Native Tab Block')],
                    ['value' => self::PRODUCT_BEFORE_TAB, 'label' => __('Before Native Tab Block')],
                    ['value' => self::PRODUCT_CONTENT_TOP, 'label' => __('Content Top')],
                    ['value' => self::PRODUCT_CONTENT_BOTTOM, 'label' => __('Content Bottom')],
                    ['value' => self::PRODUCT_SIDEBAR_TOP, 'label' => __('Sidebar Top')],
                    ['value' => self::PRODUCT_SIDEBAR_BOTTOM, 'label' => __('Sidebar Bottom')],
                ]
            ],
            [
                'label' => __('Shopping Cart Page'),
                'value' => [
                    ['value' => self::CART_BEFORE_CROSSSEL, 'label' => __('Before Native Cross-sells Block')],
                    ['value' => self::CART_AFTER_CROSSSEL, 'label' => __('After Native Cross-sells Block')],
                    ['value' => self::CART_INTO_CROSSSEL, 'label' => __('Add into Native Cross-sells Block')],
                    ['value' => self::CART_CONTENT_TOP, 'label' => __('Content Top')],
                    ['value' => self::CART_CONTENT_BOTTOM, 'label' => __('Content Bottom')],
                ]
            ],
            [
                'label' => __('Category Page'),
                'value' => [
                    ['value' => self::CATEGORY_CONTENT_TOP, 'label' => __('Content Top')],
                    ['value' => self::CATEGORY_CONTENT_BOTTOM, 'label' => __('Content Bottom')],
                    ['value' => self::CATEGORY_SIDEBAR_TOP, 'label' => __('Sidebar Top')],
                    ['value' => self::CATEGORY_SIDEBAR_BOTTOM, 'label' => __('Sidebar Bottom')],
                ]
            ],
            ['value' => self::CUSTOM, 'label' => __('Custom Position')]
        ];
    }
}
