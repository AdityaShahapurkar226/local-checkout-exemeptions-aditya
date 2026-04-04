<?php
namespace Ewave\ExtendedBundleProduct\Plugin\Magento\Checkout\CustomerData;

use Ewave\ExtendedBundleProduct\Helper\Bundle;
use Magento\Checkout\CustomerData\Cart;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote;

/**
 * Class CartPlugin
 * @package Ewave\ExtendedBundleProduct\Plugin\Magento\Checkout\CustomerData
 */
class CartPlugin
{
    /**
     * @var Bundle
     */
    private $helper;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var Quote
     */
    private $quote;

    /**
     * CartPlugin constructor.
     * @param Session $checkoutSession
     * @param Bundle $helper
     */
    public function __construct(
        Session $checkoutSession,
        Bundle $helper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
    }

    /**
     * @param Cart $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSectionData(Cart $subject, array $result)
    {
        $quote = $this->getQuote();
        if ($quote && $quote->getId() && ($qty = $this->helper->recalculateQtyWithBundleSeparateCount($quote))) {
            $result['summary_count'] = $qty;
        }
        return $result;
    }

    /**
     * @return Quote
     */
    protected function getQuote()
    {
        if ($this->quote === null) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }
}
