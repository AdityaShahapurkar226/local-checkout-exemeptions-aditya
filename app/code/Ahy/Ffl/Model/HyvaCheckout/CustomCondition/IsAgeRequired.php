<?php

namespace Ahy\Ffl\Model\HyvaCheckout\CustomCondition;

use Hyva\Checkout\Model\CustomConditionInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Model\ProductRepository;

/**
 * @api
 */
class IsAgeRequired implements CustomConditionInterface
{
    protected $checkoutSession;
    protected $productRepository;

    public function __construct(
        CheckoutSession $checkoutSession,
        ProductRepository $productRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->productRepository = $productRepository;
    }

    public function validate(): bool
    {
        return $this->isAgeVerificationRequired();
    }

    public function isAgeVerificationRequired(): bool
    {
        $quote = $this->checkoutSession->getQuote();
        $items = $quote->getAllVisibleItems();
        
        foreach ($items as $item) {
            $product = $this->productRepository->getById($item->getProductId());
            $ageVerificationRequired = $product->getData('age_verification_required');
            $fflSelectionRequired = $product->getData('ffl_selection_required');
            if ($ageVerificationRequired == 1 || $fflSelectionRequired == 1) {
                return true;
            }
        }
        return false;
    }

    public function alwaysFalse(): bool
    {
        return false;
    }
}
