<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubAccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SellerSubAccount\Model;

use Magento\Framework\Model\AbstractModel;
use Webkul\SellerSubAccount\Api\Data\SubAccountInterface;
use Magento\Framework\DataObject\IdentityInterface;

class SubAccount extends AbstractModel implements SubAccountInterface, IdentityInterface
{
    /**
     * No route page id.
     */
    public const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * SellerSubAccount SubAccount cache tag.
     */
    public const CACHE_TAG = 'marketplace_sub_accounts';

    /**
     * @var string
     */
    public $_cacheTag = 'marketplace_sub_accounts';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    public $_eventPrefix = 'marketplace_sub_accounts';

    /**
     * Initialize resource model.
     */
    public function _construct()
    {
        $this->_init(\Webkul\SellerSubAccount\Model\ResourceModel\SubAccount::class);
    }

    /**
     * Load object data.
     *
     * @param int|null $id
     * @param string   $field
     *
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteSubAccount();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route SubAccount.
     *
     * @return \Webkul\SellerSubAccount\Model\SubAccount
     */
    public function noRouteSubAccount()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Available event marketplace_seller_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getStatuses()
    {
        return [
            0 => __('No'),
            1 => __('Yes')
        ];
    }

    /**
     * Get identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\SellerSubAccount\Api\Data\SubAccountInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get Customer Id.
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return parent::getData(self::CUSTOMER_ID);
    }

    /**
     * Set Customer Id.
     *
     * @param int $customerId
     *
     * @return \Webkul\SellerSubAccount\Api\Data\SubAccountInterface
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Get Seller Id.
     *
     * @return int|null
     */
    public function getSellerId()
    {
        return parent::getData(self::SELLER_ID);
    }

    /**
     * Set Seller Id.
     *
     * @param int $sellerId
     *
     * @return \Webkul\SellerSubAccount\Api\Data\SubAccountInterface
     */
    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }

    /**
     * Get Permission Type.
     *
     * @return string|null
     */
    public function getPermissionType()
    {
        return parent::getData(self::PERMISSION_TYPE);
    }

    /**
     * Set Permission Type.
     *
     * @param string $permissionType
     *
     * @return \Webkul\SellerSubAccount\Api\Data\SubAccountInterface
     */
    public function setPermissionType($permissionType)
    {
        return $this->setData(self::PERMISSION_TYPE, $permissionType);
    }

    /**
     * Get Status.
     *
     * @return string|null
     */
    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }

    /**
     * Set Status.
     *
     * @param string $status
     *
     * @return \Webkul\SellerSubAccount\Api\Data\SubAccountInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get Created Date.
     *
     * @return date|null
     */
    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_AT);
    }

    /**
     * Set Created Date.
     *
     * @param string $createdAt
     *
     * @return \Webkul\SellerSubAccount\Api\Data\SubAccountInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get Updated Date.
     *
     * @return date|null
     */
    public function getUpdatedAt()
    {
        return parent::getData(self::UPDATED_AT);
    }

    /**
     * Set Updated Date.
     *
     * @param string $updatedAt
     *
     * @return \Webkul\SellerSubAccount\Api\Data\SubAccountInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
