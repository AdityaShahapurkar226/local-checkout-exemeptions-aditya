<?php

namespace Ewave\ExtendedBundleProduct\Plugin\Magento\Sales\Model\AdminOrder;

use Magento\Sales\Model\AdminOrder\Create as Subject;
use Magento\Sales\Model\Order\Item;

/**
 * Class CreatePlugin
 * @package Ewave\ExtendedBundleProduct\Plugin\Magento\Sales\Model\AdminOrder
 */
class CreatePlugin
{
    /**
     * Work with buy request data bundle product
     *
     * @param Subject $subject
     * @param Item $orderItem
     * @param null $qty
     * @return array
     */
    public function beforeInitFromOrderItem(Subject $subject, Item $orderItem, $qty = null)
    {
        $mainItemId = $orderItem->getItemId();
        $collectionItems = $orderItem->getOrder()->getItemsCollection();
        $bundleSuperAttribute = [];
        foreach ($collectionItems as $collectionItem) {
            if ($collectionItem->getProductType() == 'configurable' && $collectionItem->getParentItemId() == $mainItemId) {
                $bundleSuperAttribute = $collectionItem->getBuyRequest()->getBundleSuperAttribute();
                break;
            }
        }
        if (count($bundleSuperAttribute)) {
            $productOptions = $orderItem->getProductOptions();
            if (isset($productOptions['info_buyRequest'])) {
                $productOptions['info_buyRequest']['bundle_super_attribute'] = $bundleSuperAttribute;
                $orderItem->setProductOptions($productOptions);
            }
        }

        return [$orderItem, $qty];
    }
}
