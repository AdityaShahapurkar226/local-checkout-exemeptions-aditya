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
namespace Webkul\MpMultiShipping\Model\Cart;

use Magento\Quote\Api\Data\ShippingMethodInterface;

/**
 * Quote shipping method data.
 */
class ShippingMethod extends \Magento\Quote\Model\Cart\ShippingMethod
{

    const CUSTOM_DATA = 'custom_data';

    /**
     * Returns the shipping carrier code.
     *
     * @return string Shipping carrier code.
     */
    public function getCustomData()
    {
        return $this->_get(self::CUSTOM_DATA);
    }

    /**
     * Sets the shipping carrier code.
     *
     * @param string $carrierCode
     * @return $this
     */
    public function setCustomData($carrierCode)
    {
        return $this->setData(self::CUSTOM_DATA, $carrierCode);
    }
}
