<?php
namespace Ahy\Authorizenet\ViewModel\Checkout;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Customer\Model\Session;

class CustomerSession implements ArgumentInterface
{
    protected $customerSession;

    public function __construct(
        Session $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    public function isLoggedIn(): bool
    {
        return $this->customerSession->isLoggedIn();
    }

    public function getCustomer()
    {
        return $this->customerSession->getCustomer();
    }

    public function getCustomerId()
    {
        return $this->customerSession->getCustomerId();
    }
}
