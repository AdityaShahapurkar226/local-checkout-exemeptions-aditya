<?php

namespace Ewave\ExtendedBundleProduct\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * Catalog inventory module observer
 *
 * Class BeforeProductSaveObserver
 * @package Ewave\ExtendedBundleProduct\Observer
 */
class BeforeProductSaveObserver implements ObserverInterface
{
    /**
     * Type of option
     */
    const MULTI_TYPE = 'multi';

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * BeforeProductSaveObserver constructor.
     *
     * @param CollectionFactory $productCollectionFactory
     */
    public function __construct(
        CollectionFactory $productCollectionFactory
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Cancel order item
     *
     * @param   EventObserver $observer
     * @return  void
     */
    public function execute(EventObserver $observer)
    {
        /** @var Product $product */
        $product = $observer->getEvent()->getDataObject();
        if ($product->getTypeId() != \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
            return;
        }

        $multySelections = [];
        $bundleOptions = $product->getBundleOptionsData();
        if (is_array($bundleOptions)) {
            foreach ($bundleOptions as $bundleOption) {
                if (isset($bundleOption['type']) && $bundleOption['type'] == self::MULTI_TYPE
                    && !empty($bundleOption['bundle_button_proxy'])) {
                    foreach ($bundleOption['bundle_button_proxy'] as $optionSelection) {
                        $multySelections[] = $optionSelection['entity_id'];
                    }
                }
            }
        }

        if (!empty($multySelections)) {
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->getSelect()->where('entity_id in (?)', $multySelections)
                ->where('type_id = ?', \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE);

            if ($productCollection->count()) {
                throw new LocalizedException(__('Configurable products cannot be used as part of multiselect option'));
            }
        }
    }
}
