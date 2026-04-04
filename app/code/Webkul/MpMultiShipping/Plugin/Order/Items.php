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

namespace Webkul\MpMultiShipping\Plugin\Order;

class Items
{
    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * add extra filters and columns to the collection
     *
     * @param \Webkul\Marketplace\Block\Order\Items $items
     * @param  object $result
     * @return object
     */
    public function afterAddAdditionalFilters(
        \Webkul\Marketplace\Block\Order\Items $items,
        $result
    ) {
    
        $scopeStore = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $fieldId = "carriers/mpmultishipping/shipping_mode";
        $enabledProductWise = $this->scopeConfig->getValue($fieldId, $scopeStore);
        if ($enabledProductWise == 2) {
            $result->getSelect()
                    ->columns('msl.shipping_method AS shipping_method')
                    ->columns('msl.shipping_price AS shipping_price');
            return $result;
        } else {
            return $result;
        }
    }
}
