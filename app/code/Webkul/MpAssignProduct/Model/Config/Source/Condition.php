<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Condition implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'label' => __('New'),
                'value' => 1,
            ],
            [
                'label' => __('Used'),
                'value' => 2,
            ]
        ];
        return $options;
    }
}
