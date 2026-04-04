<?php

declare(strict_types=1);

namespace Ahy\HidePayPal\Observer\Payment;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Payment\Model\MethodList;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Ahy\ThemeCustomization\Helper\Data;
use Psr\Log\LoggerInterface;

class MethodIsActive implements ObserverInterface
{
    protected $methodList;
    protected $checkoutSession;
    protected $ahyHelper;
    protected $cookieManager;
    protected $logger;

    public function __construct(
        MethodList $methodList,
        CheckoutSession $checkoutSession,
        Data $ahyHelper,
        CookieManagerInterface $cookieManager,
        LoggerInterface $logger
    ) {
        $this->methodList = $methodList;
        $this->checkoutSession = $checkoutSession;
        $this->ahyHelper = $ahyHelper;
        $this->cookieManager = $cookieManager;
        $this->logger = $logger;
    }

    public function execute(Observer $observer): void
    {
        /** @var PaymentInterface $payment */
        $payment = $observer->getEvent()->getData('method_instance');
        $quote = $this->checkoutSession->getQuote();

        // Check for country in cookies
        $countryCode = $this->cookieManager->getCookie('country') ?? '';
        if (in_array(needle: $countryCode, haystack: ['FR', 'NL'], strict: true)) {
            // if ($payment->getCode() === 'paypal_express') {
                $result = $observer->getEvent()->getResult();
                $result->setData('is_available', false);
                return;
            // }
        }
        
        $isFflRequired = false;
        $isAgeVerificationRequired = false;

        foreach ($quote->getAllItems() as $item) {
            try {
                $product        = $item->getProduct();
                $productId      = $product->getId();
                $isFflRequired  = $this->ahyHelper->getProductAttribute(productId: $productId, attributeCode: 'ffl_selection_required');
                $isAgeVerificationRequired = $this->ahyHelper->getProductAttribute(productId: $productId, attributeCode: 'age_verification_required');

                if ($isAgeVerificationRequired || $isFflRequired) {
                    break;
                }
            } catch (\Exception $e) {
                $product = $item->getProduct();
                $productName = $product ? $product->getName() : 'Unknown Product';
                $this->logger->error("Error occurred for product: $productName. Exception: " . $e->getMessage());
            }
        }

        if (($isAgeVerificationRequired || $isFflRequired) && ($payment->getCode() === 'paypal_express' || $payment->getCode() === 'braintree_googlepay' || $payment->getCode() === 'braintree_venmo' || $payment->getCode() === 'braintree_applepay')) {
            $result = $observer->getEvent()->getResult();
            $result->setData('is_available', false);

            $quote->getPayment()->setMethod('authnetahypayment');
            $quote->save();
        }
    }
}