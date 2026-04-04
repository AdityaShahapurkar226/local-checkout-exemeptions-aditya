<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Api\Data;

use Amasty\Mostviewed\Api\Data\PackExtensionInterface;
use Magento\Framework\Api\ExtensibleDataInterface;

interface PackInterface extends ExtensibleDataInterface
{
    public const PACK_ID = 'pack_id';
    public const STORE_ID = 'store_id';
    public const STATUS = 'status';
    public const PRIORITY = 'priority';
    public const NAME = 'name';
    public const CUSTOMER_GROUP_IDS = 'customer_group_ids';
    public const PRODUCT_IDS = 'product_ids';
    public const BLOCK_TITLE = 'block_title';
    public const DISCOUNT_TYPE = 'discount_type';
    public const APPLY_FOR_PARENT = 'apply_for_parent';
    public const APPLY_CONDITION = 'apply_condition';
    public const DISCOUNT_AMOUNT = 'discount_amount';
    public const CREATED_AT = 'created_at';
    public const CART_MESSAGE = 'cart_message';
    public const DATE_FROM = 'date_from';
    public const DATE_TO = 'date_to';
    public const PRODUCTS_INFO = 'products_info';

    /**
     * @return int
     */
    public function getPackId();

    /**
     * @param int $packId
     *
     * @return \Amasty\Mostviewed\Api\Data\PackInterface
     */
    public function setPackId($packId);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param int $status
     *
     * @return \Amasty\Mostviewed\Api\Data\PackInterface
     */
    public function setStatus($status);

    /**
     * @return int
     */
    public function getPriority();

    /**
     * @param int $priority
     *
     * @return \Amasty\Mostviewed\Api\Data\PackInterface
     */
    public function setPriority($priority);

    /**
     * @return string|null
     */
    public function getName();

    /**
     * @param string|null $name
     *
     * @return \Amasty\Mostviewed\Api\Data\PackInterface
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getCustomerGroupIds();

    /**
     * @param string $customerGroupIds
     *
     * @return \Amasty\Mostviewed\Api\Data\PackInterface
     */
    public function setCustomerGroupIds($customerGroupIds);

    /**
     * @return string
     */
    public function getProductIds();

    /**
     * @return array
     */
    public function getParentIds(): ?array;

    /**
     * @param string $productIds
     *
     * @return \Amasty\Mostviewed\Api\Data\PackInterface
     */
    public function setProductIds($productIds);

    /**
     * @return string|null
     */
    public function getBlockTitle();

    /**
     * @param string|null $blockTitle
     *
     * @return \Amasty\Mostviewed\Api\Data\PackInterface
     */
    public function setBlockTitle($blockTitle);

    /**
     * @return int
     */
    public function getDiscountType(): int;

    /**
     * @param int $discountType
     *
     * @return \Amasty\Mostviewed\Api\Data\PackInterface
     */
    public function setDiscountType($discountType);

    /**
     * @return int
     */
    public function getApplyForParent();

    /**
     * @param int $applyForParent
     *
     * @return \Amasty\Mostviewed\Api\Data\PackInterface
     */
    public function setApplyForParent($applyForParent);

    public function getApplyCondition(): int;

    public function setApplyCondition(int $applyCondition): void;

    /**
     * @return string|null
     */
    public function getDiscountAmount();

    /**
     * @param string|null $discountAmount
     *
     * @return \Amasty\Mostviewed\Api\Data\PackInterface
     */
    public function setDiscountAmount($discountAmount);

    /**
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * @param string|null $createdAt
     *
     * @return \Amasty\Mostviewed\Api\Data\PackInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string|null
     */
    public function getDateFrom();

    /**
     * @param string|null $dateFrom
     *
     * @return \Amasty\Mostviewed\Api\Data\PackInterface
     */
    public function setDateFrom($dateFrom);

    /**
     * @return string|null
     */
    public function getDateTo();

    /**
     * @param string|null $dateTo
     *
     * @return \Amasty\Mostviewed\Api\Data\PackInterface
     */
    public function setDateTo($dateTo);

    /**
     * @return string|null
     */
    public function getCartMessage();

    /**
     * @param string|null $cartMessage
     *
     * @return \Amasty\Mostviewed\Api\Data\PackInterface
     */
    public function setCartMessage($cartMessage);

    /**
     * @param int $productId
     * @return int
     */
    public function getChildProductQty(int $productId): int;

    /**
     * @param int $productId
     * @return float
     */
    public function getChildProductDiscount(int $productId): ?float;

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Amasty\Mostviewed\Api\Data\PackExtensionInterface|null
     */
    public function getExtensionAttributes(): ?PackExtensionInterface;

    /**
     * Set an extension attributes object.
     *
     * @param \Amasty\Mostviewed\Api\Data\PackExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(PackExtensionInterface $extensionAttributes): void;
}
