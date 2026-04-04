<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Model\Config\Source;

class Options
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $data = [
                    ['value' => '0', 'label' => __('With Minimum Price')],
                    ['value' => '1', 'label' => __('With Maximum Price')],
                    ['value' => '2', 'label' => __('With Minimum Quantity')],
                    ['value' => '3', 'label' => __('With Maximum Quantity')],
                ];

        return $data;
    }
}
