<?php declare(strict_types=1);
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMultiShipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpMultiShipping\Model\Config\Source;

class Model implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * returns modes for MultiShipping
     *
     * @return array
     */
    public function toOptionArray()
    {
        return
        [
            ['value' => 1, 'label' => __('Seller Wise')],
            ['value' => 2, 'label' => __('Product Wise')]
        ];
    }
}
