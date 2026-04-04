<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubAccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SellerSubAccount\Model\SubAccount\Source;

use Magento\Framework\Option\ArrayInterface;

class Gender implements ArrayInterface
{
    /**
     * Get array for Gender field
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            1 => [
                'label' => 'Male',
                'value' => '1'
            ],
            2 => [
                'label' => 'Female',
                'value' => '2'
            ],
            3 => [
                'label' => 'Not Specified',
                'value' => '3'
            ],
        ];

        return $options;
    }
}
