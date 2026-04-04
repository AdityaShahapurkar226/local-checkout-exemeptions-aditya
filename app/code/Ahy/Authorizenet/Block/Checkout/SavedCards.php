<?php
namespace Ahy\Authorizenet\Block\Checkout;

use Magento\Framework\View\Element\Template;
use Magento\Vault\Model\PaymentTokenManagement;
use Magento\Customer\Model\Session as CustomerSession;

class SavedCards extends Template
{
    protected PaymentTokenManagement $paymentTokenManagement;
    protected CustomerSession $customerSession;

    public function __construct(
        Template\Context $context,
        PaymentTokenManagement $paymentTokenManagement,
        CustomerSession $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->customerSession = $customerSession;
    }

    public function getCustomerSavedCards(): array
    {
        $customerId = $this->customerSession->getCustomerId();

        if (!$customerId) {
            return [];
        }

        $tokens = $this->paymentTokenManagement->getVisibleAvailableTokens($customerId);
        $cards = [];

        foreach ($tokens as $token) {
            if ($token->getType() === 'card') {
                $details = json_decode($token->getTokenDetails() ?? '{}', true);

                $cards[] = [
                    'public_hash' => $token->getPublicHash(),
                    'payment_method_code' => $token->getPaymentMethodCode(),
                    'last4' => $details['maskedCC'] ?? '',
                    'expiry' => $details['expirationDate'] ?? '',
                    'type' => $details['type'] ?? '',
                ];
            }
        }

        return $cards;
    }
}
