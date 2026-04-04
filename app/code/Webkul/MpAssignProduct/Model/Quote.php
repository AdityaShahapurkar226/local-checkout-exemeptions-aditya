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
 * Assigned Quote Class
 */
class Quote extends \Magento\Framework\Model\AbstractModel implements
    \Magento\Framework\DataObject\IdentityInterface,
    \Webkul\MpAssignProduct\Api\Data\QuoteInterface
{

    public const NOROUTE_ENTITY_ID = 'no-route';

    public const CACHE_TAG = 'webkul_mpassignproduct_quote';

    /**
     *
     * @var string
     */
    protected $_cacheTag = 'webkul_mpassignproduct_quote';

    /**
     *
     * @var string
     */
    protected $_eventPrefix = 'webkul_mpassignproduct_quote';

    /**
     * Set resource model
     */
    public function _construct()
    {
        $this->_init(\Webkul\MpAssignProduct\Model\ResourceModel\Quote::class);
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
     * @return Webkul\MpAssignProduct\Model\QuoteInterface
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
     * Set ItemId
     *
     * @param int $itemId
     * @return Webkul\MpAssignProduct\Model\QuoteInterface
     */
    public function setItemId($itemId)
    {
        return $this->setData(self::ITEM_ID, $itemId);
    }

    /**
     * Get ItemId
     *
     * @return int
     */
    public function getItemId()
    {
        return parent::getData(self::ITEM_ID);
    }

    /**
     * Set OwnerId
     *
     * @param int $ownerId
     * @return Webkul\MpAssignProduct\Model\QuoteInterface
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
     * @return Webkul\MpAssignProduct\Model\QuoteInterface
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
     * @return Webkul\MpAssignProduct\Model\QuoteInterface
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
     * Set ProductId
     *
     * @param int $productId
     * @return Webkul\MpAssignProduct\Model\QuoteInterface
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
     * Set QuoteId
     *
     * @param int $quoteId
     * @return Webkul\MpAssignProduct\Model\QuoteInterface
     */
    public function setQuoteId($quoteId)
    {
        return $this->setData(self::QUOTE_ID, $quoteId);
    }

    /**
     * Get QuoteId
     *
     * @return int
     */
    public function getQuoteId()
    {
        return parent::getData(self::QUOTE_ID);
    }

    /**
     * Set AssignId
     *
     * @param int $assignId
     * @return Webkul\MpAssignProduct\Model\QuoteInterface
     */
    public function setAssignId($assignId)
    {
        return $this->setData(self::ASSIGN_ID, $assignId);
    }

    /**
     * Get AssignId
     *
     * @return int
     */
    public function getAssignId()
    {
        return parent::getData(self::ASSIGN_ID);
    }

    /**
     * Set ChildAssignId
     *
     * @param int $childAssignId
     * @return Webkul\MpAssignProduct\Model\QuoteInterface
     */
    public function setChildAssignId($childAssignId)
    {
        return $this->setData(self::CHILD_ASSIGN_ID, $childAssignId);
    }

    /**
     * Get ChildAssignId
     *
     * @return int
     */
    public function getChildAssignId()
    {
        return parent::getData(self::CHILD_ASSIGN_ID);
    }
}
