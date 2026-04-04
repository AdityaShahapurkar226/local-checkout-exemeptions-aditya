<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MpAssignProduct
 * @author Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */


namespace Webkul\MpAssignProduct\Model;

/**
 * Assgined Items Class
 */
class Items extends \Magento\Framework\Model\AbstractModel implements
    \Magento\Framework\DataObject\IdentityInterface,
    \Webkul\MpAssignProduct\Api\Data\ItemsInterface
{
    /**
     * TABLE_NAME table name
     */
    public const TABLE_NAME = 'marketplace_assignproduct_items';

    public const NOROUTE_ENTITY_ID = 'no-route';

    public const CACHE_TAG = 'webkul_mpassignproduct_items';
    /**
     * product's Statuses
     */
    public const STATUS_ENABLED = 1;
    public const STATUS_DISABLED = 0;

    /**
     *
     * @var string
     */
    protected $_cacheTag = 'webkul_mpassignproduct_items';

    /**
     *
     * @var string
     */
    protected $_eventPrefix = 'webkul_mpassignproduct_items';

    /**
     * Set resource model
     */
    public function _construct()
    {
        $this->_init(\Webkul\MpAssignProduct\Model\ResourceModel\Items::class);
    }

    /**
     * Load No-Route Indexer.
     *
     * @return $this
     */
    public function noRouteReasons()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Get identities.
     *
     * @return []
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Set Id
     *
     * @param int $id
     * @return Webkul\MpAssignProduct\Model\ItemsInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Get Id
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ID);
    }

    /**
     * Set ProductId
     *
     * @param int $productId
     * @return Webkul\MpAssignProduct\Model\ItemsInterface
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * Get ProductId
     *
     * @return int
     */
    public function getProductId()
    {
        return parent::getData(self::PRODUCT_ID);
    }

    /**
     * Set OwnerId
     *
     * @param int $ownerId
     * @return Webkul\MpAssignProduct\Model\ItemsInterface
     */
    public function setOwnerId($ownerId)
    {
        return $this->setData(self::OWNER_ID, $ownerId);
    }

    /**
     * Get OwnerId
     *
     * @return int
     */
    public function getOwnerId()
    {
        return parent::getData(self::OWNER_ID);
    }

    /**
     * Set SellerId
     *
     * @param int $sellerId
     * @return Webkul\MpAssignProduct\Model\ItemsInterface
     */
    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }

    /**
     * Get SellerId
     *
     * @return int
     */
    public function getSellerId()
    {
        return parent::getData(self::SELLER_ID);
    }

    /**
     * Set Qty
     *
     * @param int $qty
     * @return Webkul\MpAssignProduct\Model\ItemsInterface
     */
    public function setQty($qty)
    {
        return $this->setData(self::QTY, $qty);
    }

    /**
     * Get Qty
     *
     * @return int
     */
    public function getQty()
    {
        return parent::getData(self::QTY);
    }

    /**
     * Set Price
     *
     * @param float $price
     * @return Webkul\MpAssignProduct\Model\ItemsInterface
     */
    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    /**
     * Get Price
     *
     * @return float
     */
    public function getPrice()
    {
        return parent::getData(self::PRICE);
    }

    /**
     * Set Description
     *
     * @param string $description
     * @return Webkul\MpAssignProduct\Model\ItemsInterface
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Get Description
     *
     * @return string
     */
    public function getDescription()
    {
        return parent::getData(self::DESCRIPTION);
    }

    /**
     * Set Options
     *
     * @param string $options
     * @return Webkul\MpAssignProduct\Model\ItemsInterface
     */
    public function setOptions($options)
    {
        return $this->setData(self::OPTIONS, $options);
    }

    /**
     * Get Options
     *
     * @return string
     */
    public function getOptions()
    {
        return parent::getData(self::OPTIONS);
    }

    /**
     * Set Image
     *
     * @param string $image
     * @return Webkul\MpAssignProduct\Model\ItemsInterface
     */
    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
    }

    /**
     * Get Image
     *
     * @return string
     */
    public function getImage()
    {
        return parent::getData(self::IMAGE);
    }

    /**
     * Set Condition
     *
     * @param int $condition
     * @return Webkul\MpAssignProduct\Model\ItemsInterface
     */
    public function setCondition($condition)
    {
        return $this->setData(self::CONDITION, $condition);
    }

    /**
     * Get Condition
     *
     * @return int
     */
    public function getCondition()
    {
        return parent::getData(self::CONDITION);
    }

    /**
     * Set TaxClass
     *
     * @param int $taxClass
     * @return Webkul\MpAssignProduct\Model\ItemsInterface
     */
    public function setTaxClass($taxClass)
    {
        return $this->setData(self::TAX_CLASS, $taxClass);
    }

    /**
     * Get TaxClass
     *
     * @return int
     */
    public function getTaxClass()
    {
        return parent::getData(self::TAX_CLASS);
    }

    /**
     * Set Type
     *
     * @param string $type
     * @return Webkul\MpAssignProduct\Model\ItemsInterface
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * Get Type
     *
     * @return string
     */
    public function getType()
    {
        return parent::getData(self::TYPE);
    }

    /**
     * Set CreatedAt
     *
     * @param string $createdAt
     * @return Webkul\MpAssignProduct\Model\ItemsInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get CreatedAt
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_AT);
    }

    /**
     * Set Status
     *
     * @param int $status
     * @return Webkul\MpAssignProduct\Model\ItemsInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get Status
     *
     * @return int
     */
    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }

    /**
     * Set ShippingCountryCharge
     *
     * @param string $shippingCountryCharge
     * @return Webkul\MpAssignProduct\Model\ItemsInterface
     */
    public function setShippingCountryCharge($shippingCountryCharge)
    {
        return $this->setData(self::SHIPPING_COUNTRY_CHARGE, $shippingCountryCharge);
    }

    /**
     * Get ShippingCountryCharge
     *
     * @return string
     */
    public function getShippingCountryCharge()
    {
        return parent::getData(self::SHIPPING_COUNTRY_CHARGE);
    }

    /**
     * Set AssignProductId
     *
     * @param int $assignProductId
     * @return Webkul\MpAssignProduct\Model\ItemsInterface
     */
    public function setAssignProductId($assignProductId)
    {
        return $this->setData(self::ASSIGN_PRODUCT_ID, $assignProductId);
    }

    /**
     * Get AssignProductId
     *
     * @return int
     */
    public function getAssignProductId()
    {
        return parent::getData(self::ASSIGN_PRODUCT_ID);
    }
}
