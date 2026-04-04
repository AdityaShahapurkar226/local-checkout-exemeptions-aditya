<?php

namespace BitRail\PaymentGateway\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Data\ProcessorInterface;
use Magento\Store\Model\ScopeInterface;

class OrderDescription extends Value implements ProcessorInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function processValue($value)
    {
        if (empty($value)) {
            $storeName = $this->scopeConfig->getValue(
                'general/store_information/name',
                ScopeInterface::SCOPE_STORE
            );
            $value = 'Purchase in'.($storeName ? ' '.$storeName : '').' e-shop';
            $this->setValue($value);
        }

        return $value;
    }
}
