<?php
namespace Ahy\EfflApiIntegration\Helper;

use Magento\Customer\Model\Session;
use Magento\Customer\Api\AddressRepositoryInterface;

class CustomerZip
{
    protected $customerSession;
    protected $addressRepository;

    public function __construct(
        Session $customerSession,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->customerSession = $customerSession;
        $this->addressRepository = $addressRepository;
    }

    public function getCustomerZip()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return null;
        }

        $customer = $this->customerSession->getCustomer();
        $defaultShippingId = $customer->getDefaultShipping();

        if ($defaultShippingId) {
            $address = $this->addressRepository->getById($defaultShippingId);
            return $address->getPostcode();
        }

        return null;
    }
}
