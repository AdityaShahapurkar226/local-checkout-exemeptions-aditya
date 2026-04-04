<?php

namespace Ahy\EstateApiIntegration\Block;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session as CustomerSession;

class CustomerZip extends Template
{
    protected $customerSession;

    public function __construct(
        Template\Context $context,
        CustomerSession $customerSession,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    public function isCustomerLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    public function getCustomerZip()
    {
        if (!$this->isCustomerLoggedIn()) {
            return '';
        }

        $customer = $this->customerSession->getCustomer();
        $defaultShipping = $customer->getDefaultShippingAddress();
        if ($defaultShipping) {
            return $defaultShipping->getPostcode();
        }
        return '';
    }
}
