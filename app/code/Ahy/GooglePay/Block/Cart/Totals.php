<?php
namespace Ahy\GooglePay\Block\Cart;

use Magento\Checkout\Block\Cart\Totals as CoreTotals;

class Totals extends CoreTotals
{
    /**
     * Format price using current store currency
     *
     * @param float $price
     * @return string
     */
    public function formatPrice($price)
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->format($price, [], false);
    }
}
