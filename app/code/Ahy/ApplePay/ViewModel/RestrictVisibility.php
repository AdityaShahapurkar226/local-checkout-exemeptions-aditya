<?php
declare(strict_types=1);

namespace Ahy\ApplePay\ViewModel;

use Magento\Checkout\Model\Session as CheckoutSession;
use Ahy\ThemeCustomization\Helper\Data as AhyHelper;
use Magento\Framework\View\Element\Block\ArgumentInterface;

use Magento\Customer\Model\Session as CustomerSession;

class RestrictVisibility implements ArgumentInterface
{
    protected $checkoutSession;
    protected $ahyHelper;

    private $customerSession;

    public function __construct(
        CheckoutSession $checkoutSession,
        AhyHelper $ahyHelper,
        CustomerSession $customerSession
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->ahyHelper = $ahyHelper;
         $this->customerSession = $customerSession;
    }

    public function shouldShowButton(): bool
    {
        $quote = $this->checkoutSession->getQuote();

        foreach ($quote->getAllItems() as $item) {
            $product = $item->getProduct();
            $productId = $product->getId();
            $isFflRequired = $this->ahyHelper->getProductAttribute($productId, 'ffl_selection_required');
            $isAgeVerificationRequired = $this->ahyHelper->getProductAttribute($productId, 'age_verification_required');

            if ($isFflRequired || $isAgeVerificationRequired) {
                return false;
            }
        }

        return true;
    }

    public function getCurrentQuoteAmount(): float
    {
        $quote = $this->checkoutSession->getQuote();
        return $quote ? (float) $quote->getGrandTotal() : 0.0;
    }

    public function isCustomerLoggedIn(): bool
    {
        return $this->customerSession->isLoggedIn();
    }
}
