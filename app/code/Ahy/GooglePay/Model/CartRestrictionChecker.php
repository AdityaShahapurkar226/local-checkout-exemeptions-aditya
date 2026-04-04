<?php
declare(strict_types=1);

namespace Ahy\GooglePay\Model;

use Magento\Quote\Model\Quote;
use Ahy\ThemeCustomization\Helper\Data as AhyHelper;

class CartRestrictionChecker
{
    private AhyHelper $ahyHelper;

    public function __construct(AhyHelper $ahyHelper)
    {
        $this->ahyHelper = $ahyHelper;
    }

    public function cartContainsRestrictedProduct(Quote $quote): bool
    {
        foreach ($quote->getAllItems() as $item) {
            $product = $item->getProduct();
            if (!$product || !$product->getId()) {
                continue;
            }

            $productId = (int) $product->getId();

            if (
                $this->ahyHelper->getProductAttribute($productId, 'ffl_selection_required')
                || $this->ahyHelper->getProductAttribute($productId, 'age_verification_required')
            ) {
                return true;
            }
        }

        return false;
    }
}
