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
 * Assigned Data Class
 */
class Data extends \Magento\Framework\Model\AbstractModel implements
    \Magento\Framework\DataObject\IdentityInterface,
    \Webkul\MpAssignProduct\Api\Data\DataInterface
{

    public const NOROUTE_ENTITY_ID = 'no-route';

    public const CACHE_TAG = 'webkul_mpassignproduct_data';

    /**
     *
     * @var string
     */
    protected $_cacheTag = 'webkul_mpassignproduct_data';

    /**
     *
     * @var string
     */
    protected $_eventPrefix = 'webkul_mpassignproduct_data';

    /**
     * Set resource model
     */
    public function _construct()
    {
        $this->_init(\Webkul\MpAssignProduct\Model\ResourceModel\Data::class);
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
     * @return Webkul\MpAssignProduct\Model\DataInterface
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
     * Set Type
     *
     * @param int $type
     * @return Webkul\MpAssignProduct\Model\DataInterface
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * Get Type
     *
     * @return int
     */
    public function getType()
    {
        return parent::getData(self::TYPE);
    }

    /**
     * Set AssignId
     *
     * @param int $assignId
     * @return Webkul\MpAssignProduct\Model\DataInterface
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
     * Set Value
     *
     * @param string $value
     * @return Webkul\MpAssignProduct\Model\DataInterface
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }

    /**
     * Get Value
     *
     * @return string
     */
    public function getValue()
    {
        return parent::getData(self::VALUE);
    }

    /**
     * Set Date
     *
     * @param string $date
     * @return Webkul\MpAssignProduct\Model\DataInterface
     */
    public function setDate($date)
    {
        return $this->setData(self::DATE, $date);
    }

    /**
     * Get Date
     *
     * @return string
     */
    public function getDate()
    {
        return parent::getData(self::DATE);
    }

    /**
     * Set IsDefault
     *
     * @param int $isDefault
     * @return Webkul\MpAssignProduct\Model\DataInterface
     */
    public function setIsDefault($isDefault)
    {
        return $this->setData(self::IS_DEFAULT, $isDefault);
    }

    /**
     * Get IsDefault
     *
     * @return int
     */
    public function getIsDefault()
    {
        return parent::getData(self::IS_DEFAULT);
    }

    /**
     * Set Status
     *
     * @param int $status
     * @return Webkul\MpAssignProduct\Model\DataInterface
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
     * Set StoreView
     *
     * @param int $storeView
     * @return Webkul\MpAssignProduct\Model\DataInterface
     */
    public function setStoreView($storeView)
    {
        return $this->setData(self::STORE_VIEW, $storeView);
    }

    /**
     * Get StoreView
     *
     * @return int
     */
    public function getStoreView()
    {
        return parent::getData(self::STORE_VIEW);
    }
}
