<?php

namespace BitRail\PaymentGateway\Controller\Order;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Checkout\Model\Session;
use Magento\Store\Model\ScopeInterface;

use BitRail\PaymentGateway\Gateway\Http\Client\BitrailClient;
use BitRail\PaymentGateway\Gateway\Http\Client\BitrailOrderTokenizer;
use BitRail\PaymentGateway\Gateway\Http\Client\ClientMock;

class GetInfo extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var Session
     */
    protected $checkoutSession;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ScopeConfigInterface $scopeConfig,
        Session $checkoutSession
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;

        return parent::__construct($context);
    }

    public function execute()
    {
        try {
            $reqParams = $this->getRequest()->getParams();
            if (!isset($reqParams['nonceCode']) || $reqParams['nonceCode'] !== ClientMock::getNonceCode()) {
                throw new \Exception('Payment was cancelled for security reasons');
            }

            $quote = $this->checkoutSession->getQuote();
            $quote->reserveOrderId()->save();
            $data = [
                'success' => true,
                'data' => [
                    'apiUrl' => $this->getApiUrl(),
                    'orderNumber' => $quote->getReservedOrderId(),
                    'paymentMethodTitle' => $this->getStoreConfigValue('title'),
                    'authToken' => $this->getBitrailAPIAuthToken(),
                    'orderToken' => BitrailOrderTokenizer::getOrderToken($this->checkoutSession->getQuoteId()),
                    'destinationVaultHandle' => $this->getStoreConfigValue(
                        'vault_handle_'.$this->getStoreConfigValue('environment')
                    ),
                    'description' => $this->getStoreConfigValue('order_description'),
                ],
            ];
        } catch (\Exception $e) {
            $data = ['success' => false, 'data' => ['message' => $e->getMessage()]];
        }
        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData($data);
    }

    private function getStoreConfigValue(string $configAttribute)
    {
        return $this->scopeConfig->getValue(
            'payment/bitrail_gateway/'.$configAttribute,
            ScopeInterface::SCOPE_STORE
        );
    }

    private function getBitrailAPIAuthToken(): string
    {
        $environment = $this->getStoreConfigValue('environment');
        $client = new BitrailClient($environment);

        return $client->oauth();
    }

    private function getApiUrl(): string
    {
        $environment = $this->getStoreConfigValue('environment');
        $client = new BitrailClient($environment);

        return $client->getApiUrl();
    }
}
