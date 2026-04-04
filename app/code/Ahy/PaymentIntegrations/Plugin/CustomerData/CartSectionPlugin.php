<?php
declare(strict_types=1);

namespace Ahy\PaymentIntegrations\Plugin\CustomerData;

use Magento\Checkout\CustomerData\Cart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Ahy\PaymentIntegrations\Model\CartRestrictionChecker;

class CartSectionPlugin
{
    private CheckoutSession $checkoutSession;
    private CartRestrictionChecker $checker;

    public function __construct(
        CheckoutSession $checkoutSession,
        CartRestrictionChecker $checker
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->checker = $checker;
    }

    public function afterGetSectionData(Cart $subject, array $result): array
    {
        $quote = $this->checkoutSession->getQuote();

        $result['cartContainsRestrictedProduct'] =
            $quote && $quote->getId()
                ? $this->checker->cartContainsRestrictedProduct($quote)
                : false;

        return $result;
    }
}

