<?php

namespace Ahy\EstateApiIntegration\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Customer\Model\Context;
use Magento\Customer\CustomerData\Customer;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Registry;
use Magento\Directory\Model\RegionFactory;
use Psr\Log\LoggerInterface;
use Ahy\EstateApiIntegration\Model\RestrictionValidator;

class CustomerInfo implements ArgumentInterface
{
    protected Customer $customerData;
    protected ScopeConfigInterface $scopeConfig;
    protected HttpContext $httpContext;
    protected LoggerInterface $logger;
    protected CustomerRepositoryInterface $customerRepository;
    protected Session $customerSession;
    protected Registry $registry;
    protected RegionFactory $regionFactory;
    protected RestrictionValidator $restrictionValidator;

    public function __construct(
        Customer $customerData,
        ScopeConfigInterface $scopeConfig,
        HttpContext $httpContext,
        LoggerInterface $logger,
        CustomerRepositoryInterface $customerRepository,
        Session $customerSession,
        Registry $registry,
        RegionFactory $regionFactory,
        RestrictionValidator $restrictionValidator
    ) {
        $this->customerData         = $customerData;
        $this->scopeConfig          = $scopeConfig;
        $this->httpContext          = $httpContext;
        $this->logger               = $logger;
        $this->customerRepository   = $customerRepository;
        $this->customerSession      = $customerSession;
        $this->registry             = $registry;
        $this->regionFactory        = $regionFactory;
        $this->restrictionValidator = $restrictionValidator;
    }

    // -------------------------------------------------------------------------
    //  Auth
    // -------------------------------------------------------------------------

    /**
     * FPC-safe login check via HTTP context.
     */
    public function isLoggedIn(): bool
    {
        return (bool) $this->httpContext->getValue(Context::CONTEXT_AUTH);
    }

    // -------------------------------------------------------------------------
    //  Customer address data
    // -------------------------------------------------------------------------

    /**
     * Returns the logged-in customer's state code (e.g. "TX", "CA").
     * Returns null for guests — they provide their state via the modal.
     */
    public function getCustomerState(): ?string
    {
        if (!$this->customerSession->isLoggedIn()) {
            return null;
        }

        try {
            $customer = $this->customerRepository->getById(
                $this->customerSession->getCustomerId()
            );

            // Priority 1: default shipping address
            if ($customer->getDefaultShipping()) {
                foreach ($customer->getAddresses() as $address) {
                    if ($address->getId() == $customer->getDefaultShipping()) {
                        $code = $address->getRegion()?->getRegionCode();
                        if ($code) return strtoupper($code);
                    }
                }
            }

            // Priority 2: default billing address
            if ($customer->getDefaultBilling()) {
                foreach ($customer->getAddresses() as $address) {
                    if ($address->getId() == $customer->getDefaultBilling()) {
                        $code = $address->getRegion()?->getRegionCode();
                        if ($code) return strtoupper($code);
                    }
                }
            }

            // Priority 3: any address
            foreach ($customer->getAddresses() as $address) {
                $code = $address->getRegion()?->getRegionCode();
                if ($code) return strtoupper($code);
            }

            return null;

        } catch (\Exception $e) {
            $this->logger->error('CustomerInfo: STATE fetch error', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Returns the logged-in customer's city.
     * Returns null for guests — they provide their city via the modal.
     */
    public function getCustomerCity(): ?string
    {
        if (!$this->customerSession->isLoggedIn()) {
            return null;
        }

        try {
            $customer = $this->customerSession->getCustomer();

            // Priority 1: default shipping address
            $city = $customer->getDefaultShippingAddress()?->getCity();
            if ($city) {
                $this->logger->info('CustomerInfo: City from shipping address', ['city' => $city]);
                return $city;
            }

            // Priority 2: default billing address
            $city = $customer->getDefaultBillingAddress()?->getCity();
            if ($city) {
                $this->logger->info('CustomerInfo: City from billing address', ['city' => $city]);
                return $city;
            }

            // Priority 3: any address
            foreach ($customer->getAddresses() as $address) {
                if ($address->getCity()) {
                    $this->logger->info('CustomerInfo: City from fallback address', ['city' => $address->getCity()]);
                    return $address->getCity();
                }
            }

            $this->logger->info('CustomerInfo: City not found');
            return null;

        } catch (\Exception $e) {
            $this->logger->error('CustomerInfo: CITY fetch error', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Returns the full state name (e.g. "Texas") from the state code.
     */
    public function getCustomerStateName(): ?string
    {
        $stateCode = $this->getCustomerState();

        if (!$stateCode) {
            return null;
        }

        try {
            $region = $this->regionFactory->create()->loadByCode($stateCode, 'US');
            return $region->getName() ?: $stateCode;
        } catch (\Exception $e) {
            $this->logger->error('CustomerInfo: STATE NAME fetch error', ['message' => $e->getMessage()]);
            return $stateCode;
        }
    }

    /**
     * Returns the customer's ZIP code from section data.
     */
    public function getCustomerZip(): ?string
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        try {
            $data = $this->customerData->getSectionData();
            return $data['zip'] ?? null;
        } catch (\Exception $e) {
            $this->logger->error('CustomerInfo: ZIP fetch error', ['message' => $e->getMessage()]);
            return null;
        }
    }

    // -------------------------------------------------------------------------
    //  Product restriction flags  (used by restriction-alpine.phtml)
    // -------------------------------------------------------------------------

    /**
     * Returns true when the current product's product_type attribute
     * is "regulated-weapon".
     */
    public function shouldShowRegulatedWeaponModal(): bool
    {
        $product = $this->registry->registry('current_product');

        if (!$product) {
            return false;
        }

        return $this->restrictionValidator
            ->getProductRegulatedType((int) $product->getId()) !== null;
    }

    /**
     * Returns true when the current product's product_type attribute
     * is "magazine".
     */
    public function isMagazineProduct(): bool
    {
        $product = $this->registry->registry('current_product');

        if (!$product) {
            return false;
        }

        return $this->restrictionValidator
            ->isMagazineProduct((int) $product->getId());
    }

    // -------------------------------------------------------------------------
    //  Config
    // -------------------------------------------------------------------------

    public function getZipRetryLimit(): int
    {
        $value = $this->scopeConfig->getValue(
            'ahy_estateapi/general/zip_retry_limit',
            ScopeInterface::SCOPE_STORE
        );

        return is_numeric($value) ? (int) $value : 0;
    }
}
