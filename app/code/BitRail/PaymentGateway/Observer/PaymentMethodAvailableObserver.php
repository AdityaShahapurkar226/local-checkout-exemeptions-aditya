<?php

namespace BitRail\PaymentGateway\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Store\Model\ScopeInterface;

class PaymentMethodAvailableObserver extends AbstractDataAssignObserver
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function execute(Observer $observer)
    {
        $objectManager = ObjectManager::getInstance();
        $storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);

        $vaultHandle = 'vault_handle_' . $this->scopeConfig->getValue(
            'payment/bitrail_gateway/environment',
            ScopeInterface::SCOPE_STORE
        );
        if (
            $observer->getMethodInstance()->getCode() === 'bitrail_gateway' &&
            (
                $storeManager->getStore()->getCurrentCurrency()->getCode() !== 'USD' ||
                empty($this->scopeConfig->getValue(
                    'payment/bitrail_gateway/' . $vaultHandle,
                    ScopeInterface::SCOPE_STORE
                ))
            )
        ) {
            $checkResult = $observer->getEvent()->getResult();
            $checkResult->setData('is_available', false);
        }
    }
}
