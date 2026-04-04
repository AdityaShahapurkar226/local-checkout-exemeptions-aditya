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
 * Assiggned  Associates Class
 */
class Associates extends \Magento\Framework\Model\AbstractModel implements
    \Magento\Framework\DataObject\IdentityInterface,
    \Webkul\MpAssignProduct\Api\Data\AssociatesInterface
{

    public const NOROUTE_ENTITY_ID = 'no-route';

    public const CACHE_TAG = 'webkul_mpassignproduct_associates';

    /**
     *
     * @var string
     */
    protected $_cacheTag = 'webkul_mpassignproduct_associates';

    /**
     *
     * @var string
     */
    protected $_eventPrefix = 'webkul_mpassignproduct_associates';

    /**
     * Set resource model
     */
    public function _construct()
    {
        $this->_init(\Webkul\MpAssignProduct\Model\ResourceModel\Associates::class);
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
     * @return Webkul\MpAssignProduct\Model\AssociatesInterface
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
     * @return Webkul\MpAssignProduct\Model\AssociatesInterface
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
     * Set ParentId
     *
     * @param int $parentId
     * @return Webkul\MpAssignProduct\Model\AssociatesInterface
     */
    public function setParentId($parentId)
    {
        return $this->setData(self::PARENT_ID, $parentId);
    }

    /**
     * Get ParentId
     *
     * @return int
     */
    public function getParentId()
    {
        return parent::getData(self::PARENT_ID);
    }

    /**
     * Set ParentProductId
     *
     * @param int $parentProductId
     * @return Webkul\MpAssignProduct\Model\AssociatesInterface
     */
    public function setParentProductId($parentProductId)
    {
        return $this->setData(self::PARENT_PRODUCT_ID, $parentProductId);
    }

    /**
     * Get ParentProductId
     *
     * @return int
     */
    public function getParentProductId()
    {
        return parent::getData(self::PARENT_PRODUCT_ID);
    }

    /**
     * Set Qty
     *
     * @param int $qty
     * @return Webkul\MpAssignProduct\Model\AssociatesInterface
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
     * @return Webkul\MpAssignProduct\Model\AssociatesInterface
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
     * Set Options
     *
     * @param string $options
     * @return Webkul\MpAssignProduct\Model\AssociatesInterface
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
     * Set AssignProductId
     *
     * @param int $assignProductId
     * @return Webkul\MpAssignProduct\Model\AssociatesInterface
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
