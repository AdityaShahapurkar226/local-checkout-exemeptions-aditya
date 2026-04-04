<?php
namespace Ahy\EstateApiIntegration\Block;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session;

class IsLoggedIn extends Template
{
    protected $customerSession;

    public function __construct(
        Template\Context $context,
        Session $customerSession,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    public function isCustomerLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }
}
