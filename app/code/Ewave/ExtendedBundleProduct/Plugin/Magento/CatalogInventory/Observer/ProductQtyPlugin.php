<?php
namespace Ewave\ExtendedBundleProduct\Plugin\Magento\CatalogInventory\Observer;

use Magento\CatalogInventory\Observer\ProductQty as Subject;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Bundle\Model\Product\Type as Bundle;

/**
 * Class ProductQtyPlugin
 */
class ProductQtyPlugin
{
    /**
     * @param Subject $subject
     * @param array $items
     * @param \Magento\Quote\Model\Quote\Item[] $relatedItems
     * @return array
     */
    public function afterGetProductQty(
        Subject $subject,
        $items,
        $relatedItems
    ) {
        foreach ($relatedItems as $relatedItem) {
            if ($relatedItem->getProductType() != Bundle::TYPE_CODE) {
                continue;
            }

            if ($children = $relatedItem->getChildren()) {
                foreach ($children as $childItem) {
                    /** @var \Magento\Quote\Model\Quote\Item $childItem */
                    if ($childItem->getProductType() == Configurable::TYPE_CODE) {
                        if ($simpleProduct = $childItem->getOptionByCode('simple_product')) {
                            $items[$simpleProduct->getValue()] = $childItem->getTotalQty();
                        }
                    }
                }
            }
        }
        return $items;
    }
}
