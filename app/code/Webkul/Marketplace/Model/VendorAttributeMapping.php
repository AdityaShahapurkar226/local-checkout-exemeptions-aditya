<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Marketplace
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Model;

use Webkul\Marketplace\Api\Data\VendorAttributeMappingInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;

class VendorAttributeMapping extends \Magento\Framework\Model\AbstractModel implements VendorAttributeMappingInterface
{
    /**
     * Marketplace VendorAttributeMapping cache tag.
     */
    public const CACHE_TAG = 'wk_mp_attr_mapping';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_mp_attr_mapping';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_mp_attr_mapping';

    /**
     * Construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Marketplace\Model\ResourceModel\VendorAttributeMapping::class);
    }

    /**
     * Get Entity Id
     *
     * @return string
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Set Entity Id
     *
     * @param string $entityId
     * @return \Webkul\Marketplace\Api\Data\VendorAttributeMappingInterface
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Get Seller Id
     *
     * @return string
     */
    public function getSellerId()
    {
        return $this->getData(self::SELLER_ID);
    }

    /**
     * Set Seller Id
     *
     * @param string $sellerId
     * @return \Webkul\Marketplace\Api\Data\VendorAttributeMappingInterface
     */
    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }

    /**
     * Get attribute id
     *
     * @return string
     */
    public function getAttributeId()
    {
        return $this->getData(self::ATTRIBUTE_ID);
    }

    /**
     * Set attribute id
     *
     * @param string $attributeId
     * @return \Webkul\Marketplace\Api\Data\VendorAttributeMappingInterface
     */
    public function setAttributeId($attributeId)
    {
        return $this->setData(self::ATTRIBUTE_ID, $attributeId);
    }

    /**
     * Get CreatedAt
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set CreatedAt
     *
     * @param string $timestamp
     * @return \Webkul\Marketplace\Api\Data\VendorAttributeMappingInterface
     */
    public function setCreatedAt($timestamp)
    {
        return $this->setData(self::CREATED_AT, $timestamp);
    }

    /**
     * Get UpdatedAt
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Set UpdatedAt
     *
     * @param string $timestamp
     * @return \Webkul\Marketplace\Api\Data\VendorAttributeMappingInterface
     */
    public function setUpdatedAt($timestamp)
    {
        return $this->setData(self::UPDATED_AT, $timestamp);
    }
}
