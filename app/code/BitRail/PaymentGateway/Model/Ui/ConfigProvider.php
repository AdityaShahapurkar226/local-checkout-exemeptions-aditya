<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace BitRail\PaymentGateway\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Model\Config\Source\Allmethods;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\App\ObjectManager;
use Magento\Customer\Model\Session;
use Magento\Store\Model\ScopeInterface;

use BitRail\PaymentGateway\Gateway\Http\Client\BitrailClient;
use BitRail\PaymentGateway\Gateway\Http\Client\ClientMock;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'bitrail_gateway';

    /**
     * @var Data
     */
    private $data;
    /**
     * @var Escaper
     */
    private $escaper;
    /**
     * @var UrlInterface
     */
    private $urlInterface;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var allPaymentMethod
     */
    protected $allPaymentMethod;
    /**
     * @var Repository
     */
    protected $assetRepository;
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * ConfigProvider constructor.
     *
     * @param Data                 $data
     * @param Escaper              $escaper
     * @param Allmethods           $allPaymentMethod
     * @param UrlInterface         $urlInterface
     * @param ScopeConfigInterface $scopeConfig
     * @param Repository           $assetRepository
     * @param Session              $customerSession
     */
    public function __construct(
        Data $data,
        Escaper $escaper,
        Allmethods $allPaymentMethod,
        UrlInterface $urlInterface,
        ScopeConfigInterface $scopeConfig,
        Repository $assetRepository,
        Session $customerSession
    ) {
        $this->data             = $data;
        $this->escaper          = $escaper;
        $this->urlInterface     = $urlInterface;
        $this->scopeConfig      = $scopeConfig;
        $this->allPaymentMethod = $allPaymentMethod;
        $this->assetRepository  = $assetRepository;
        $this->customerSession  = $customerSession;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $objectManager = ObjectManager::getInstance();
        $storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);

        $environment = $this->scopeConfig->getValue(
            'payment/bitrail_gateway/environment',
            ScopeInterface::SCOPE_STORE,
            $storeManager->getStore()->getStoreId()
        );

        $client = new BitrailClient($environment);
        $apiUrl = $client->getApiUrl();

        return [
            'payment' => [
                self::CODE => [
                    'apiUrl' => $apiUrl,
                    'nonceCode' => ClientMock::getNonceCode(),
                ]
            ],
        ];
    }
}