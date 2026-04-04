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
 * Class Status for the query grid on the admmin side.
 */
class Status implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = [
            'label' => __('Disapprove'),
            'value' => 0,
        ];
        $options[] = [
            'label' => __('Approved'),
            'value' => 1,
        ];
        return $options;
    }
}
