<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Api\Data;

interface GroupInterface
{
    public const TABLE_NAME = 'amasty_mostviewed_group';

    /**#@+
     * Constants defined for keys of data array
     */
    public const GROUP_ID = 'group_id';

    public const STATUS = 'status';

    public const GROUP_NAME = 'name';

    public const PRIORITY = 'priority';

    public const BLOCK_POSITION = 'block_position';

    public const SOURCE_TYPE = 'source_type';

    public const STORES = 'stores';

    public const CUSTOMER_GROUP_IDS = 'customer_group_ids';

    public const WHERE_CONDITIONS = 'where_conditions_serialized';

    public const DISPLAY_MODE = 'display_mode';

    public const CONDITIONS = 'conditions_serialized';

    public const BLOCK_TITLE = 'block_title';

    public const BLOCK_LAYOUT = 'block_layout';

    public const REPLACE_TYPE = 'replace_type';

    public const ADD_TO_CART = 'add_to_cart';

    public const MAX_PRODUCTS = 'max_products';

    public const SORTING = 'sorting';

    public const SHOW_OUT_OF_STOCK = 'show_out_of_stock';

    public const SHOW_FOR_OUT_OF_STOCK = 'for_out_of_stock';

    public const LAYOUT_UPDATE_ID = 'layout_update_id';

    public const SAME_AS = 'same_as';

    public const SAME_AS_CONDITIONS = 'same_as_conditions_serialized';

    public const CURRENT_CATEGORY = 'current_category';

    public const DISPLAY_WISHLIST_BUTTON = 'display_wishlist_button';

    public const DISPLAY_COMPARE_BUTTON = 'display_compare_button';

    /**#@-*/

    /**
     * @return int
     */
    public function getGroupId();

    /**
     * @param int $groupId
     *
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface
     */
    public function setGroupId($groupId);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param int $status
     *
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface
     */
    public function setStatus($status);

    /**
     * @return int
     */
    public function getPriority();

    /**
     * @param int $priority
     *
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface
     */
    public function setPriority($priority);

    /**
     * @return string|null
     */
    public function getName();

    /**
     * @param string|null $name
     *
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface
     */
    public function setName($name);

    /**
     * @return string|null
     */
    public function getBlockPosition();

    /**
     * @param string|null $blockPosition
     *
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface
     */
    public function setBlockPosition($blockPosition);

    /**
     * @return string
     */
    public function getStores();

    /**
     * @param string $stores
     *
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface
     */
    public function setStores($stores);

    /**
     * @return string
     */
    public function getCustomerGroupIds();

    /**
     * @param string $customerGroupIds
     *
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface
     */
    public function setCustomerGroupIds($customerGroupIds);

    /**
     * @return string|null
     */
    public function getWhereConditionsSerialized();

    /**
     * @param string|null $whereConditions
     *
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface
     */
    public function setWhereConditionsSerialized($whereConditions);

    /**
     * @return string|null
     */
    public function getSameAsConditionsSerialized();

    /**
     * @param string|null $conditions
     *
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface
     */
    public function setSameAsConditionsSerialized($conditions);

    /**
     * @return int
     */
    public function getSourceType();

    /**
     * @param int $sourceType
     *
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface
     */
    public function setSourceType($sourceType);

    /**
     * @return int
     */
    public function getDisplayMode();

    /**
     * @param int $displayMode
     *
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface
     */
    public function setDisplayMode($displayMode);

    /**
     * @return string|null
     */
    public function getConditionsSerialized();

    /**
     * @param string|null $conditions
     *
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface
     */
    public function setConditionsSerialized($conditions);

    /**
     * @return string|null
     */
    public function getBlockTitle();

    /**
     * @param string|null $blockTitle
     *
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface
     */
    public function setBlockTitle($blockTitle);

    /**
     * @return int
     */
    public function getBlockLayout();

    /**
     * @param int $blockLayout
     *
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface
     */
    public function setBlockLayout($blockLayout);

    /**
     * @return int
     */
    public function getReplaceType();

    /**
     * @param int $replaceType
     *
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface
     */
    public function setReplaceType($replaceType);

    /**
     * @return int
     */
    public function getAddToCart();

    /**
     * @param int $addToCart
     *
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface
     */
    public function setAddToCart($addToCart);

    /**
     * @return int
     */
    public function getMaxProducts();

    /**
     * @param int $maxProducts
     *
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface
     */
    public function setMaxProducts($maxProducts);

    /**
     * @return string|null
     */
    public function getSorting();

    /**
     * @param string|null $sorting
     *
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface
     */
    public function setSorting($sorting);

    /**
     * @return bool
     */
    public function getSameAs();

    /**
     * @param bool $same
     *
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface
     */
    public function setSameAs($same);

    /**
     * @return bool
     */
    public function getIsCurrentCategoryOnly();

    /**
     * @param bool $value
     *
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface
     */
    public function setIsCurrentCategoryOnly($value);

    /**
     * @return int
     */
    public function getShowOutOfStock();

    /**
     * @param int $showOutOfStock
     *
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface
     */
    public function setShowOutOfStock($showOutOfStock);

    /**
     * @return int
     */
    public function getShowForOutOfStock();

    /**
     * @param int $showForOutOfStock
     *
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface
     */
    public function setShowForOutOfStock($showForOutOfStock);

    /**
     * @return int
     */
    public function getLayoutUpdateId();

    /**
     * @param int $layoutUpdateId
     *
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface
     */
    public function setLayoutUpdateId($layoutUpdateId);

    /**
     * @return bool
     */
    public function getDisplayWishlistButton(): bool;

    /**
     * @param bool $displayWishlist
     * @return void
     */
    public function setDisplayWishlistButton(bool $displayWishlist): void;

    /**
     * @return bool
     */
    public function getDisplayCompareButton(): bool;

    /**
     * @param bool $displayCompare
     * @return void
     */
    public function setDisplayCompareButton(bool $displayCompare): void;
}
