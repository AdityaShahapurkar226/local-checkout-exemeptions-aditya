<?php

namespace Ahy\EstateApiIntegration\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

class RestrictionConfig implements ArgumentInterface
{
    protected ScopeConfigInterface $scopeConfig;
    protected LoggerInterface $logger;

    // XML path for the admin toggle
    private const XML_PATH_ENABLE_RESTRICTION = 'ahy_estateapi/general/enable_restriction';

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * Check if the product restriction feature is enabled in admin
     *
     * @return bool
     */
    public function isRestrictionEnabled(): bool
    {
        try {
            return $this->scopeConfig->isSetFlag(
                self::XML_PATH_ENABLE_RESTRICTION,
                ScopeInterface::SCOPE_STORE
            );
        } catch (\Exception $e) {
            $this->logger->error('Error fetching restriction config: ' . $e->getMessage());
            return true; // fallback to enabled if config fails
        }
    }
}
