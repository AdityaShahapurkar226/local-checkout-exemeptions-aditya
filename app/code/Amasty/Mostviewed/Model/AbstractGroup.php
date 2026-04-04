<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model;

use Amasty\Mostviewed\Api\Data\GroupInterface;
use Amasty\Mostviewed\Model\OptionSource\SourceType;
use Amasty\Mostviewed\Model\ResourceModel\RuleIndex;
use Magento\CatalogRule\Model\Rule;
use Magento\Store\Model\StoreManagerInterface;

class AbstractGroup extends Rule implements GroupInterface
{
    /**
     * @inheritdoc
     */
    public function getGroupId()
    {
        return $this->_getData(GroupInterface::GROUP_ID);
    }

    /**
     * @inheritdoc
     */
    public function setGroupId($groupId)
    {
        $this->setData(GroupInterface::GROUP_ID, $groupId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->_getData(GroupInterface::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        $this->setData(GroupInterface::STATUS, $status);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPriority()
    {
        return $this->_getData(GroupInterface::PRIORITY);
    }

    /**
     * @inheritdoc
     */
    public function setPriority($priority)
    {
        $this->setData(GroupInterface::PRIORITY, $priority);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->_getData(self::GROUP_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->setData(self::GROUP_NAME, $name);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBlockPosition()
    {
        return $this->_getData(GroupInterface::BLOCK_POSITION);
    }

    /**
     * @inheritdoc
     */
    public function setBlockPosition($blockPosition)
    {
        $this->setData(GroupInterface::BLOCK_POSITION, $blockPosition);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStores()
    {
        return $this->_getData(GroupInterface::STORES);
    }

    /**
     * @inheritdoc
     */
    public function setStores($stores)
    {
        $this->setData(GroupInterface::STORES, $stores);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroupIds()
    {
        return $this->_getData(GroupInterface::CUSTOMER_GROUP_IDS);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerGroupIds($customerGroupIds)
    {
        $this->setData(GroupInterface::CUSTOMER_GROUP_IDS, $customerGroupIds);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getWhereConditionsSerialized()
    {
        return $this->_getData(GroupInterface::WHERE_CONDITIONS);
    }

    /**
     * @inheritdoc
     */
    public function setWhereConditionsSerialized($whereConditions)
    {
        $this->setData(GroupInterface::WHERE_CONDITIONS, $whereConditions);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDisplayMode()
    {
        return $this->_getData(GroupInterface::DISPLAY_MODE);
    }

    /**
     * @inheritdoc
     */
    public function setDisplayMode($displayMode)
    {
        $this->setData(GroupInterface::DISPLAY_MODE, $displayMode);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSameAs()
    {
        return $this->_getData(GroupInterface::SAME_AS);
    }

    /**
     * @inheritdoc
     */
    public function setSameAs($sameAs)
    {
        $this->setData(GroupInterface::SAME_AS, $sameAs);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getIsCurrentCategoryOnly()
    {
        return $this->_getData(GroupInterface::CURRENT_CATEGORY);
    }

    /**
     * @inheritdoc
     */
    public function setIsCurrentCategoryOnly($value)
    {
        $this->setData(GroupInterface::CURRENT_CATEGORY, $value);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getConditionsSerialized()
    {
        return $this->_getData(GroupInterface::CONDITIONS);
    }

    /**
     * @inheritdoc
     */
    public function setConditionsSerialized($conditions)
    {
        $this->setData(GroupInterface::CONDITIONS, $conditions);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSameAsConditionsSerialized()
    {
        return $this->_getData(GroupInterface::SAME_AS_CONDITIONS);
    }

    /**
     * @inheritdoc
     */
    public function setSameAsConditionsSerialized($conditions)
    {
        $this->setData(GroupInterface::SAME_AS_CONDITIONS, $conditions);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBlockTitle()
    {
        return $this->_getData(GroupInterface::BLOCK_TITLE);
    }

    /**
     * @inheritdoc
     */
    public function setBlockTitle($blockTitle)
    {
        $this->setData(GroupInterface::BLOCK_TITLE, $blockTitle);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBlockLayout()
    {
        return $this->_getData(GroupInterface::BLOCK_LAYOUT);
    }

    /**
     * @inheritdoc
     */
    public function setBlockLayout($blockLayout)
    {
        $this->setData(GroupInterface::BLOCK_LAYOUT, $blockLayout);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSourceType()
    {
        return $this->_getData(GroupInterface::SOURCE_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setSourceType($sourceType)
    {
        $this->setData(GroupInterface::SOURCE_TYPE, $sourceType);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getReplaceType()
    {
        return $this->_getData(GroupInterface::REPLACE_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setReplaceType($replaceType)
    {
        $this->setData(GroupInterface::REPLACE_TYPE, $replaceType);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAddToCart()
    {
        return $this->_getData(GroupInterface::ADD_TO_CART);
    }

    /**
     * @inheritdoc
     */
    public function setAddToCart($addToCart)
    {
        $this->setData(GroupInterface::ADD_TO_CART, $addToCart);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMaxProducts()
    {
        return $this->_getData(GroupInterface::MAX_PRODUCTS);
    }

    /**
     * @inheritdoc
     */
    public function setMaxProducts($maxProducts)
    {
        $this->setData(GroupInterface::MAX_PRODUCTS, $maxProducts);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSorting()
    {
        return $this->_getData(GroupInterface::SORTING);
    }

    /**
     * @inheritdoc
     */
    public function setSorting($sorting)
    {
        $this->setData(GroupInterface::SORTING, $sorting);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getShowOutOfStock()
    {
        return $this->_getData(GroupInterface::SHOW_OUT_OF_STOCK);
    }

    /**
     * @inheritdoc
     */
    public function setShowOutOfStock($showOutOfStock)
    {
        $this->setData(GroupInterface::SHOW_OUT_OF_STOCK, $showOutOfStock);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getShowForOutOfStock()
    {
        return $this->_getData(GroupInterface::SHOW_FOR_OUT_OF_STOCK);
    }

    /**
     * @inheritdoc
     */
    public function setShowForOutOfStock($showForOutOfStock)
    {
        $this->setData(GroupInterface::SHOW_FOR_OUT_OF_STOCK, $showForOutOfStock);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLayoutUpdateId()
    {
        return $this->_getData(GroupInterface::LAYOUT_UPDATE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setLayoutUpdateId($layoutUpdateId)
    {
        $this->setData(GroupInterface::LAYOUT_UPDATE_ID, $layoutUpdateId);

        return $this;
    }

    public function getDisplayWishlistButton(): bool
    {
        return (bool)$this->_getData(GroupInterface::DISPLAY_WISHLIST_BUTTON);
    }

    public function setDisplayWishlistButton(bool $displayWishlist): void
    {
        $this->setData(GroupInterface::DISPLAY_WISHLIST_BUTTON, $displayWishlist);
    }

    public function getDisplayCompareButton(): bool
    {
        return (bool)$this->_getData(GroupInterface::DISPLAY_COMPARE_BUTTON);
    }

    public function setDisplayCompareButton(bool $displayCompare): void
    {
        $this->setData(GroupInterface::DISPLAY_COMPARE_BUTTON, $displayCompare);
    }
}
