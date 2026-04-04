<?php
namespace Ahy\ThemeCustomization\Block\Onepage;

class Success extends \Magento\Checkout\Block\Onepage\Success

{
    /**
     * Get grand total of the order
     *
     * @return float
     */
    public function getGrandTotal()
    {
        $order = $this->_checkoutSession->getLastRealOrder();
        return $order->getGrandTotal();
    }
}
