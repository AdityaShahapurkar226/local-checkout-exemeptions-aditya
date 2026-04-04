<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model;

use Amasty\Mostviewed\Api\Data\PackExtensionInterface;
use Amasty\Mostviewed\Api\Data\PackInterface;
use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class Pack extends AbstractExtensibleModel implements PackInterface, IdentityInterface
{
    public const PERSISTENT_NAME = 'amasty_mostviewed_pack';

    public const CACHE_TAG = 'mostviewed_pack';

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    /**
     * @var null|array
     */
    private $childProductsInfo = null;

    protected function _construct()
    {
        $this->_init(\Amasty\Mostviewed\Model\ResourceModel\Pack::class);
        $this->setIdFieldName('pack_id');
        $this->serializer = $this->_data['amasty_serializer'] ?? null;
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getPackId()];
    }

    /**
     * @inheritdoc
     */
    public function getPackId()
    {
        return $this->_getData(PackInterface::PACK_ID);
    }

    /**
     * @inheritdoc
     */
    public function setPackId($packId)
    {
        $this->setData(PackInterface::PACK_ID, $packId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->_getData(PackInterface::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        $this->setData(PackInterface::STATUS, $status);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPriority()
    {
        return $this->_getData(PackInterface::PRIORITY);
    }

    /**
     * @inheritdoc
     */
    public function setPriority($priority)
    {
        $this->setData(PackInterface::PRIORITY, $priority);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->_getData(PackInterface::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->setData(PackInterface::NAME, $name);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroupIds()
    {
        return $this->_getData(PackInterface::CUSTOMER_GROUP_IDS);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerGroupIds($customerGroupIds)
    {
        $this->setData(PackInterface::CUSTOMER_GROUP_IDS, $customerGroupIds);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProductIds()
    {
        return $this->_getData(PackInterface::PRODUCT_IDS);
    }

    public function getParentIds(): ?array
    {
        return $this->_getData('parent_ids');
    }

    /**
     * @inheritdoc
     */
    public function setProductIds($productIds)
    {
        $this->setData(PackInterface::PRODUCT_IDS, $productIds);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBlockTitle()
    {
        return $this->_getData(PackInterface::BLOCK_TITLE);
    }

    /**
     * @inheritdoc
     */
    public function setBlockTitle($blockTitle)
    {
        $this->setData(PackInterface::BLOCK_TITLE, $blockTitle);

        return $this;
    }

    public function getDiscountType(): int
    {
        return (int) $this->_getData(PackInterface::DISCOUNT_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setDiscountType($discountType)
    {
        $this->setData(PackInterface::DISCOUNT_TYPE, $discountType);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getApplyForParent()
    {
        return $this->_getData(PackInterface::APPLY_FOR_PARENT);
    }

    /**
     * @inheritdoc
     */
    public function setApplyForParent($applyForParent)
    {
        $this->setData(PackInterface::APPLY_FOR_PARENT, $applyForParent);

        return $this;
    }

    public function getApplyCondition(): int
    {
        return (int) $this->_getData(PackInterface::APPLY_CONDITION);
    }

    public function setApplyCondition(int $applyCondition): void
    {
        $this->setData(PackInterface::APPLY_CONDITION, $applyCondition);
    }

    /**
     * @inheritdoc
     */
    public function getDiscountAmount()
    {
        return $this->_getData(PackInterface::DISCOUNT_AMOUNT);
    }

    /**
     * @inheritdoc
     */
    public function setDiscountAmount($discountAmount)
    {
        $this->setData(PackInterface::DISCOUNT_AMOUNT, $discountAmount);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->_getData(PackInterface::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(PackInterface::CREATED_AT, $createdAt);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDateFrom()
    {
        return $this->_getData(PackInterface::DATE_FROM);
    }

    /**
     * @inheritdoc
     */
    public function setDateFrom($dateFrom)
    {
        $this->setData(PackInterface::DATE_FROM, $dateFrom);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDateTo()
    {
        return $this->_getData(PackInterface::DATE_TO);
    }

    /**
     * @inheritdoc
     */
    public function setDateTo($dateTo)
    {
        $this->setData(PackInterface::DATE_TO, $dateTo);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCartMessage()
    {
        return $this->_getData(PackInterface::CART_MESSAGE);
    }

    /**
     * @inheritdoc
     */
    public function setCartMessage($cartMessage)
    {
        $this->setData(PackInterface::CART_MESSAGE, $cartMessage);

        return $this;
    }

    public function getChildProductQty(int $productId): int
    {
        if ($this->childProductsInfo === null) {
            $this->initChildProductsInfo();
        }

        return (int) ($this->childProductsInfo[$productId]['quantity'] ?? 1);
    }

    public function getChildProductDiscount(int $productId): ?float
    {
        if ($this->childProductsInfo === null) {
            $this->initChildProductsInfo();
        }
        if (isset($this->childProductsInfo[$productId]['discount_amount'])) {
            $discountAmount = $this->childProductsInfo[$productId]['discount_amount'];
            $discountAmount = is_numeric($discountAmount) ? (float) $discountAmount : null;
        } else {
            $discountAmount = null;
        }

        return $discountAmount;
    }

    private function initChildProductsInfo(): void
    {
        $this->childProductsInfo = $this->serializer->unserialize(
            $this->getData(PackInterface::PRODUCTS_INFO)
        );
    }

    private function initExtensionAttributes(): void
    {
        $extensionAttributes = $this->extensionAttributesFactory->create(Pack::class, []);
        $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * @return ExtensionAttributesInterface|PackExtensionInterface
     */
    public function getExtensionAttributes(): ?PackExtensionInterface
    {
        if (!$this->hasData(self::EXTENSION_ATTRIBUTES_KEY)) {
            $this->initExtensionAttributes();
        }

        return $this->_getExtensionAttributes();
    }

    public function setExtensionAttributes(PackExtensionInterface $extensionAttributes): void
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
