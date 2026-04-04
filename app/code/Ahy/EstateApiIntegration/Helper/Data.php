<?php
namespace Ahy\EstateApiIntegration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Helper class for Estate API Integration
 *
 * Provides utility methods for retrieving configuration values and
 * customer-related data such as zip codes.
 */
class Data extends AbstractHelper
{
    /**
     * XML path for zip retry limit configuration
     */
    const XML_PATH_ZIP_RETRY_LIMIT = 'ahy_estateapi/general/zip_retry_limit';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param LoggerInterface $logger
     * @param CustomerSession $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        LoggerInterface $logger,
        CustomerSession $customerSession
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->customerSession = $customerSession;
    }

    /**
     * Retrieve the zip retry limit value from configuration
     *
     * @return int
     */
    public function getZipRetryLimit()
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_ZIP_RETRY_LIMIT,
            ScopeInterface::SCOPE_STORE
        );
        return (int) $value;
    }

    /**
     * Retrieve the currently logged-in customer's default shipping zip code
     *
     * @return string|null Returns zip code as string or null if customer is not logged in or no shipping address set
     */
    public function getCustomerZipCode(): ?string
    {
        if (!$this->customerSession->isLoggedIn()) {
            return null;
        }

        $customer = $this->customerSession->getCustomer();
        $address = $customer->getDefaultShippingAddress();
        $zip = $address ? $address->getPostcode() : null;
        return $zip;
    }
}
