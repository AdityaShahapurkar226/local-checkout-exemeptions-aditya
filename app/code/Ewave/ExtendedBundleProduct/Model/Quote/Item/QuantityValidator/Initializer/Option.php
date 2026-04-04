<?php
namespace Ewave\ExtendedBundleProduct\Model\Quote\Item\QuantityValidator\Initializer;

use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

/**
 * Quote item option initializer.
 */
class Option extends \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option
{
    /**
     * Init stock item
     *
     * @param \Magento\Quote\Model\Quote\Item\Option $option
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     *
     * @return \Magento\CatalogInventory\Model\Stock\Item|\Magento\CatalogInventory\Api\Data\StockItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStockItem(
        \Magento\Quote\Model\Quote\Item\Option $option,
        \Magento\Quote\Model\Quote\Item $quoteItem
    ) {
        if ($simpleId = $this->getSimpleProductId($option, $quoteItem)) {
            $stockItem = $this->stockRegistry->getStockItem(
                $simpleId,
                $quoteItem->getStore()->getWebsiteId()
            );

            if (!$stockItem->getItemId()) {
                throw new LocalizedException(
                    __('The stock item for Product in option is not valid.')
                );
            }

            $stockItem->setIsChildItem(true);
            return $stockItem;
        }

        return parent::getStockItem($option, $quoteItem);
    }

    /**
     * Initialize item option
     *
     * @param \Magento\Quote\Model\Quote\Item\Option $option
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param int $qty
     *
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function initialize(
        \Magento\Quote\Model\Quote\Item\Option $option,
        \Magento\Quote\Model\Quote\Item $quoteItem,
        $qty
    ) {
        try {
            if ($simpleProductId = $this->getSimpleProductId($option, $quoteItem)) {
                $result = $this->initializeOptionItem($option, $quoteItem, $qty, $simpleProductId);
            } else {
                $result = parent::initialize($option, $quoteItem, $qty);
            }
        } catch (LocalizedException $e) {
            $result = new DataObject();
            $option->setStockStateResult($result);
        }
        return $result;
    }

    /**
     * Initialize item option
     *
     * @param \Magento\Quote\Model\Quote\Item\Option $option
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param int $qty
     * @param int $optionProductId
     *
     * @return \Magento\Framework\DataObject|int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function initializeOptionItem(
        \Magento\Quote\Model\Quote\Item\Option $option,
        \Magento\Quote\Model\Quote\Item $quoteItem,
        $qty,
        $optionProductId
    ) {
        $optionValue = $option->getValue();
        $optionQty = $qty * $optionValue;
        $increaseOptionQty = ($quoteItem->getQtyToAdd() ? $quoteItem->getQtyToAdd() : $qty) * $optionValue;
        $qtyForCheck = $this->quoteItemQtyList->getQty(
            $optionProductId,
            $quoteItem->getId(),
            $quoteItem->getQuoteId(),
            $increaseOptionQty
        );

        $stockItem = $this->getStockItem($option, $quoteItem);
        $stockItem->setProductName($option->getProduct()->getName());
        $result = $this->stockState->checkQuoteItemQty(
            $optionProductId,
            $optionQty,
            $qtyForCheck,
            $optionValue,
            $option->getProduct()->getStore()->getWebsiteId()
        );

        if ($result->getItemIsQtyDecimal() !== null) {
            $option->setIsQtyDecimal($result->getItemIsQtyDecimal());
        }

        if ($result->getHasQtyOptionUpdate()) {
            $option->setHasQtyOptionUpdate(true);
            $quoteItem->updateQtyOption($option, $result->getOrigQty());
            $option->setValue($result->getOrigQty());
            /**
             * if option's qty was updates we also need to update quote item qty
             */
            $quoteItem->setData('qty', intval($qty));
        }

        if ($result->getMessage() !== null) {
            $option->setMessage($result->getMessage());
            $quoteItem->setMessage($result->getMessage());
        }

        if ($result->getItemBackorders() !== null) {
            $option->setBackorders($result->getItemBackorders());
        }

        $stockItem->unsIsChildItem();
        $option->setStockStateResult($result);

        return $result;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\Option $option
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @return int|false
     *
     * @throws LocalizedException
     */
    public function getSimpleProductId(
        \Magento\Quote\Model\Quote\Item\Option $option,
        \Magento\Quote\Model\Quote\Item $quoteItem
    ) {
        if ($quoteItem->getProduct()->getTypeId() == Bundle::TYPE_CODE
            && $option->getProduct()->getTypeId() == Configurable::TYPE_CODE
        ) {
            /** @var \Magento\Catalog\Model\Product\Configuration\Item\Option $simpleProduct */
            if ($simpleProduct = $option->getProduct()->getCustomOption('simple_product')) {
                return $simpleProduct->getValue();
            }

            throw new LocalizedException(__('The stock validation should be skipped.'));
        }

        $parentItem = $quoteItem->getParentItem();
        if ($parentItem && $parentItem->getProductType() == Bundle::TYPE_CODE
            && $quoteItem->getProductType() == Configurable::TYPE_CODE
        ) {
            return $quoteItem->getOptionByCode('simple_product')->getValue();
        }

        return false;
    }
}
