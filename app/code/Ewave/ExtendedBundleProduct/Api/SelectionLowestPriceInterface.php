<?php
namespace Ewave\ExtendedBundleProduct\Api;

use Magento\Framework\Pricing\SaleableInterface;

interface SelectionLowestPriceInterface
{
    /**
     * @param SaleableInterface $selection
     * @return float|false
     */
    public function getSelectionLowestPrice(SaleableInterface $selection);
}
