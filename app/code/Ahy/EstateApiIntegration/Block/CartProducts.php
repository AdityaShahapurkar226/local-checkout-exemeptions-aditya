<?php

namespace Ahy\EstateApiIntegration\Block;

use Magento\Framework\View\Element\Template;
use Magento\Checkout\Model\Session;

class CartProducts extends Template
{
    protected $checkoutSession;

    public function __construct(
        Template\Context $context,
        Session $checkoutSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
    }

    public function getCartItems()
    {
        $quote = $this->checkoutSession->getQuote();
        return $quote ? $quote->getAllVisibleItems() : [];
    }
}
