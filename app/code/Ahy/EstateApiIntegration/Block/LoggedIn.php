<?php
namespace Ahy\EstateApiIntegration\Block;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class LoggedIn extends Template
{
    protected $customerSession;
    protected $customerRepository;
    protected $addressRepository;
    protected $logger;

    public function __construct(
        Template\Context $context,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AddressRepositoryInterface $addressRepository,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
        $this->logger = $logger;
        parent::__construct($context, $data);
    }

    public function isCustomerLoggedIn(): bool
    {
        return $this->customerSession->isLoggedIn();
    }

    public function getCustomerZip(): ?string
    {
        if (!$this->isCustomerLoggedIn()) {
            $this->logger->info('Customer not logged in.');
            return null;
        }

        try {
            $customerId = $this->customerSession->getCustomerId();
            $customer = $this->customerRepository->getById($customerId);

            $defaultBillingId = $customer->getDefaultBilling();
            if ($defaultBillingId) {
                $billingAddress = $this->addressRepository->getById($defaultBillingId);
                $zip = $billingAddress->getPostcode();
                $this->logger->info("Billing ZIP for customer ID {$customerId}: {$zip}");
                return $zip;
            }

            $defaultShippingId = $customer->getDefaultShipping();
            if ($defaultShippingId) {
                $shippingAddress = $this->addressRepository->getById($defaultShippingId);
                $zip = $shippingAddress->getPostcode();
                $this->logger->info("Shipping ZIP for customer ID {$customerId}: {$zip}");
                return $zip;
            }

            $this->logger->info("No default billing/shipping address found for customer ID {$customerId}");
        } catch (NoSuchEntityException $e) {
            $this->logger->error('Customer or address not found: ' . $e->getMessage());
        }

        return null;
    }
}