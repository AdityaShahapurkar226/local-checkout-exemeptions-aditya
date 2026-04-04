<?php
/**
 * Webkul Software
 *
 * @category    Webkul
 * @package     Webkul_MpSellerBuyerCommunication
 * @author      Webkul
 * @copyright   Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license     https://store.webkul.com/license.html
 */

namespace Webkul\MpSellerBuyerCommunication\Model\Sender\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Sender Type
 */
class Type implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = [
            'label' => __('Customer'),
            'value' => 0,
        ];
        $options[] = [
            'label' => __('Seller'),
            'value' => 1,
        ];
        return $options;
    }
}
