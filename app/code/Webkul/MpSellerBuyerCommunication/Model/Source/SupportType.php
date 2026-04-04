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
 * Class SupportType for the query type on the customer view.
 */
class SupportType implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = [
            'label' => __('Presale'),
            'value' => 0,
        ];
        $options[] = [
            'label' => __('Support'),
            'value' => 1,
        ];
        $options[] = [
            'label' => __('Technical'),
            'value' => 2,
        ];
        $options[] = [
            'label' => __('Other'),
            'value' => 3,
        ];
        return $options;
    }
}
