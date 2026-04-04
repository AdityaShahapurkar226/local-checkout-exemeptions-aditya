<?php

namespace Ahy\Authorizenet\ViewModel;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Psr\Log\LoggerInterface;

class CustomerAddressHelper implements ArgumentInterface
{
    protected CustomerSession $customerSession;
    protected AddressRepositoryInterface $addressRepository;
    protected CustomerRepositoryInterface $customerRepository;
    protected LoggerInterface $logger;

    public function __construct(
        CustomerSession $customerSession,
        AddressRepositoryInterface $addressRepository,
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger
    ) {
        $this->customerSession = $customerSession;
        $this->addressRepository = $addressRepository;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
    }

    /**
     * Returns all addresses for the logged-in customer
     *
     * @return \Magento\Customer\Api\Data\AddressInterface[]
     */
    public function getCustomerAddresses(): array
    {
        try {
            if (!$this->customerSession->isLoggedIn()) {
                $this->logger->info('[CustomerAddressHelper] Customer not logged in. Returning empty address list.');
                return [];
            }

            $customerId = (int)$this->customerSession->getCustomerId();
            $customer = $this->customerRepository->getById($customerId);
            $addresses = $customer->getAddresses();

            $this->logger->info('[CustomerAddressHelper] Retrieved ' . count($addresses) . ' addresses for customer ID ' . $customerId);
            return $addresses;
        } catch (\Exception $e) {
            $this->logger->error('[CustomerAddressHelper] Error fetching customer addresses: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Returns true if the customer has at least one billing address
     *
     * @return bool
     */
    public function hasBillingAddress(): bool
    {
        $addresses = $this->getCustomerAddresses();

        foreach ($addresses as $address) {
            if ($address->isDefaultBilling() || $address->getFirstname()) {
                $this->logger->info('[CustomerAddressHelper] Found at least one billing address.');
                return true;
            }
        }

        $this->logger->info('[CustomerAddressHelper] No billing address found.');
        return false;
    }
}
