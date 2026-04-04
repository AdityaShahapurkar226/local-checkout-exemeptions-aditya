<?php
namespace Ewave\ExtendedBundleProduct\Helper;

use Ewave\ExtendedBundleProduct\Model\Config\Source\Bundle\CountSeparatelyOption;
use Magento\Quote\Model\Quote;

/**
 * Class Bundle
 * @package Ewave\ExtendedBundleProduct\Helper
 */
class Bundle
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var array
     */
    protected $bundleQtyRecalculated = [];

    /**
     * @param Data $dataHelper
     */
    public function __construct(Data $dataHelper)
    {
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param Quote $quote
     * @return float|int|mixed
     */
    public function recalculateQtyWithBundleSeparateCount(Quote $quote)
    {
        $quoteId = $quote->getId();
        if (!empty($this->bundleQtyRecalculated[$quoteId])) {
            return $this->bundleQtyRecalculated[$quoteId];
        }

        $items = $quote->getAllVisibleItems();
        $qty = 0;

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            if ($item->getProductType() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE ||
                !$this->isCalculateBundleSeparately($item)
            ) {
                $qty += $item->getQty();
                continue;
            }

            $bundleItemQty = $item->getQty();
            $children = $item->getChildren();

            foreach ($children as $child) {
                $qty += ($child->getQty() * $bundleItemQty );
            }
        }

        $this->bundleQtyRecalculated[$quoteId] = $qty;
        return $qty;
    }

    /**
     * @param Quote\Item $item
     * @return bool
     */
    public function isCalculateBundleSeparately(\Magento\Quote\Model\Quote\Item $item)
    {
        $productAttribute = $item->getProduct()->getData(Data::CODE_ATTRIBUTE_BUNDLE_IS_COUNT_ITEMS_SEPARATE);
        if (CountSeparatelyOption::VALUE_USE_CONFIG == $productAttribute || null === $productAttribute) {
            return $this->dataHelper->isCountBundleItemsSeparately();
        }
        return (bool) $productAttribute;
    }
}
