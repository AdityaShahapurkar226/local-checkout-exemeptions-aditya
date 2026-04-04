<?php

declare(strict_types=1);

namespace Ahy\Authorizenet\Magewire\Checkout\Payment;

use Magento\Framework\Exception\LocalizedException;
use Hyva\Checkout\Magewire\Checkout\Payment\MethodList as OriginCheckoutMethodList;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as SessionCheckout;
use Magento\Quote\Api\CartRepositoryInterface;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Psr\Log\LoggerInterface;
use Ahy\SavedCC\ViewModel\ConfigProvider as SavedCcConfig;

class MethodList extends OriginCheckoutMethodList
{
    private PaymentTokenManagementInterface $paymentTokenManagement;
    private CustomerSession $customerSession;
    private LoggerInterface $logger;
    private SavedCcConfig $savedCcConfig;

    public function __construct(
        SessionCheckout $sessionCheckout,
        CartRepositoryInterface $cartRepository,
        EvaluationResultFactory $evaluationResultFactory,
        PaymentTokenManagementInterface $paymentTokenManagement,
        CustomerSession $customerSession,
        LoggerInterface $logger,
        SavedCcConfig $savedCcConfig
    ) {
        parent::__construct($sessionCheckout, $cartRepository, $evaluationResultFactory);
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
        $this->savedCcConfig = $savedCcConfig;
    }

    public function mount(): void
    {
        try {
            $quote = $this->sessionCheckout->getQuote();
            $method = $quote->getPayment()->getMethod();

            $this->logger->info('[Authorizenet Checkout] Payment method in quote before default check: ' . var_export($method, true));

            $preferredMethod = $this->getDefaultPaymentMethod();

            if (empty($method) || $method !== $preferredMethod) {
                $this->logger->info("[Authorizenet Checkout] Overwriting payment method with default: {$preferredMethod}");
                $method = $preferredMethod;
            } else {
                $this->logger->info('[Authorizenet Checkout] Keeping existing preferred method: ' . $method);
            }
        } catch (LocalizedException $exception) {
            $this->logger->error('[Authorizenet Checkout] Error in mount(): ' . $exception->getMessage());
            $method = $this->getDefaultPaymentMethod();
        }

        $quote->getPayment()->setMethod($method);
        $this->cartRepository->save($quote);
        $this->method = $method;
    }

    private function getDefaultPaymentMethod(): string
    {
        $customerId = $this->customerSession->getCustomerId();

        if ($customerId) {
            $this->logger->info("[Authorizenet Checkout] Logged-in customer ID: {$customerId}");
            $tokens = $this->paymentTokenManagement->getListByCustomerId($customerId);

            // Filter active tokens only
            $activeTokens = array_filter($tokens, function ($token) {
                return (int)$token->getIsActive() === 1;
            });

            $tokensCount = count($activeTokens);
            $this->logger->info('[Authorizenet Checkout] Stored cards count: ' . $tokensCount);

            if ($this->savedCcConfig->isEnabled() && $tokensCount > 0) {
                $this->logger->info('[Authorizenet Checkout] Selecting method: customervaultcards (SavedCC enabled + tokens exist)');
                return 'customervaultcards';
            }
        } else {
            $this->logger->info('[Authorizenet Checkout] No logged-in customer, using default new card method.');
        }

        $this->logger->info('[Authorizenet Checkout] Selecting method: authnetahypayment');
        return 'authnetahypayment';
    }
}
