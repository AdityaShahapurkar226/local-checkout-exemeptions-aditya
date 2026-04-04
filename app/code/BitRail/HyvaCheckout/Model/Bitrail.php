<?php

namespace Bitrail\HyvaCheckout\Model;

use \Magento\Framework\Exception\LocalizedException;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Checkout\Model\Session;
use \Magento\Framework\Model\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\Api\ExtensionAttributesFactory;
use \Magento\Framework\Api\AttributeValueFactory;
use \Magento\Payment\Helper\Data;
use \Magento\Payment\Model\Method\Logger;
use Bitrail\HyvaCheckout\Gateway\Http\Client\BitrailClient;
use BitRail\PaymentGateway\Gateway\Http\Client\BitrailOrderTokenizer;
use \Magento\Payment\Model\Method\AbstractMethod;
use Bitrail\HyvaCheckout\Model\Config\ConfigProvider;

class Bitrail extends AbstractMethod
{
    protected $_code = 'bitrail';

    protected $checkoutSession;
    protected $configProvider;
    protected $bitrailClient;

    public function __construct(
        Session $checkoutSession,
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        ConfigProvider $configProvider,
        BitrailClient $bitrailClient
    ) {
        $this->configProvider = $configProvider;
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->bitrailClient = $bitrailClient;

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
        );
    }

    public function getTitle()
    {
        return __('');
    }



    public function validate()
    {
        parent::validate();

        $quote = $this->checkoutSession->getQuote();

        if ($quote) {
            $orderNumber = $quote->getReservedOrderId();
            $verificationToken = $this->checkoutSession->getData($orderNumber);

            if (!$verificationToken) {
                throw new LocalizedException(__('Unable to verify payment: Invalid state.'));
            }

            try {
                $transactionCode = $this->bitrailClient->verifyTransaction($verificationToken);
                if (!BitrailOrderTokenizer::tokenIsValid($quote->getId(), $transactionCode)) {
                    throw new LocalizedException(__('Unable to verify payment: gateway rejected the transaction.'));
                }

                return $this;

            } catch (\Exception $e) {
                throw new LocalizedException(__('Unable to verify payment: ' . $e->getMessage()));
            }

        } else {
            throw new LocalizedException(__('Unable to verify payment: Quote is no longer available.'));
        }

    }

}


