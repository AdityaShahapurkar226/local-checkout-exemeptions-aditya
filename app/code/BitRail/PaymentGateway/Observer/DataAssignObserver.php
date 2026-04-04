<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace BitRail\PaymentGateway\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

use BitRail\PaymentGateway\Gateway\Http\Client\BitrailClient;
use BitRail\PaymentGateway\Gateway\Http\Client\BitrailOrderTokenizer;

class DataAssignObserver extends AbstractDataAssignObserver
{
    /**
     * @var Session
     */
    protected $checkoutSession;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(Session $checkoutSession, ScopeConfigInterface $scopeConfig)
    {
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);
        if ($data->getDataByKey('method') === 'bitrail_gateway' && $data->hasData('orderVerificatioToken')) {
            $environment = $this->scopeConfig->getValue(
                'payment/bitrail_gateway/environment',
                ScopeInterface::SCOPE_STORE
            );
            $bitrailClient = new BitrailClient($environment);
            $orderVerificationToken = $bitrailClient->verifyTransaction($data->getDataByKey('orderVerificatioToken'));
            if (!BitrailOrderTokenizer::tokenIsValid($this->checkoutSession->getQuoteId(), $orderVerificationToken)) {
                throw new \Exception(__('Gateway rejected the transaction.')); // Maybe need other text
            }
        }
    }
}
