<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Marketplace\Model\Config\Source;

/**
 * CommissionWithDiscount class to calculate commission if discount amount.
 */
class CommissionWithDiscount
{
    /**
     * Options getter.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $data = [
            [
                'value' => '0',
                'label' => __(
                    'Both Seller Total Amount and Admin Commission Amount'
                )
            ],
            ['value' => '1', 'label' => __('Seller Total Amount')],
            ['value' => '2', 'label' => __('Admin Commission Amount')]
        ];
        return $data;
    }
}
