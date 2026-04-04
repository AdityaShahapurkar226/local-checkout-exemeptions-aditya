<?php
// File: app/code/Ahy/EstateApiIntegration/ViewModel/ZipModalData.php
namespace Ahy\EstateApiIntegration\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Registry;

class ZipModalData implements ArgumentInterface
{
    protected $customerSession;
    protected $checkoutSession;
    protected $customerRepository;
    protected $addressRepository;
    protected $registry;

    public function __construct(
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        CustomerRepositoryInterface $customerRepository,
        AddressRepositoryInterface $addressRepository,
        Registry $registry
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
        $this->registry = $registry;
    }

    public function isLoggedIn(): bool
    {
        return $this->customerSession->isLoggedIn();
    }

    public function getCurrentProductSku(): string
    {
        $product = $this->registry->registry('current_product');
        return $product ? (string)$product->getSku() : '';
    }

    public function getCustomerZip(): string
    {
        $quote = $this->checkoutSession->getQuote();

        if ($quote->getShippingAddress() && $quote->getShippingAddress()->getPostcode()) {
            return $quote->getShippingAddress()->getPostcode();
        }

        if ($quote->getBillingAddress() && $quote->getBillingAddress()->getPostcode()) {
            return $quote->getBillingAddress()->getPostcode();
        }

        if ($this->customerSession->isLoggedIn()) {
            try {
                $customerId = $this->customerSession->getCustomerId();
                $customer = $this->customerRepository->getById($customerId);
                $defaultShippingId = $customer->getDefaultShipping();

                if ($defaultShippingId) {
                    $address = $this->addressRepository->getById($defaultShippingId);
                    return $address->getPostcode();
                }
            } catch (\Exception $e) {
                // Optionally log it
            }
        }

        return '';
    }
}