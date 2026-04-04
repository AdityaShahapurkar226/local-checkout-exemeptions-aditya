<?php
namespace Ahy\ThemeCustomization\Helper;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Helper\AbstractHelper;

class Session extends AbstractHelper{
    protected $customerSession;
    protected $_checkoutSession;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Helper\Context $context,
        CheckoutSession $checkoutSession
    ) {
        $this->customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context);
    }
    
    public function isLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }
    
    public function getCustomer()
    {
        return $this->customerSession->getCustomer();
    }

    public function getCheckoutSession(){
        return $this->_checkoutSession;
    }

    public function getCustomerZipCode(): ?string
    {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $address = $this->customerSession->getCustomer()->getDefaultShippingAddress();
        if ($address) {
            return $address->getPostcode();
        }

        return null;
    }

}

?>
