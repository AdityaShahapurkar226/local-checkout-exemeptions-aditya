<?php

namespace Ahy\EstateApiIntegration\Plugin;

use Magento\Customer\CustomerData\Customer;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Plugin to add the customer's ZIP code to the customer data section
 *
 * Injects the default shipping address postal code into the customer data array,
 * allowing frontend components to access the customer's ZIP code easily.
 */
class AddZipToCustomerData
{
    /**
     * @var HttpContext
     */
    protected HttpContext $httpContext;

    /**
     * @var CustomerRepositoryInterface
     */
    protected CustomerRepositoryInterface $customerRepository;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param HttpContext $httpContext
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        HttpContext $httpContext,
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger
    ) {
        $this->httpContext = $httpContext;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
    }

    /**
     * After plugin for CustomerData's getSectionData method
     *
     * Adds the customer's default shipping ZIP code to the returned data array.
     *
     * @param Customer $subject
     * @param array $result Original customer data
     * @return array Modified customer data including 'zip' if available
     */
    public function afterGetSectionData(Customer $subject, array $result): array
    {
        try {
            $isLoggedIn = $this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
            if (!$isLoggedIn) {
                return $result;
            }

            $customerId = $this->httpContext->getValue('customer_id');
            if (!$customerId) {
                return $result;
            }

            $customer = $this->customerRepository->getById($customerId);
            $defaultShippingId = $customer->getDefaultShipping();

            $addresses = $customer->getAddresses();
            if ($defaultShippingId) {
                foreach ($addresses as $address) {
                    if ($address->getId() == $defaultShippingId) {
                        $postcode = $address->getPostcode();
                        if ($postcode) {
                            $result['zip'] = $postcode;
                            return $result;
                        }
                    }
                }
            }
            foreach ($addresses as $address) {
                $postcode = $address->getPostcode();
                if ($postcode) {
                    $result['zip'] = $postcode;
                    break;
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('[Plugin] Error injecting ZIP into customerData: ' . $e->getMessage());
        }

        return $result;
    }
}
