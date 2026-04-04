<?php declare(strict_types=1);
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpFixedRateshipping
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpFixedRateshipping\Model\Config;

/**
 * Webkul Mppercountryperproductshipping Config Model
 *
 * @author      Webkul Software
 */
class Basedon implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 'vendor', 'label' => __('Vendor')], ['value' => 'product', 'label' => __('Product')]];
    }
}
