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

namespace Webkul\MpSellerBuyerCommunication\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class QueryStatus for the reply form on the admin side.
 */
class QueryStatus implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = [
            'label' => __('Pending'),
            'value' => 0,
        ];
        $options[] = [
            'label' => __('Resolved'),
            'value' => 1,
        ];
        $options[] = [
            'label' => __('Closed'),
            'value' => 2,
        ];
        return $options;
    }
}
