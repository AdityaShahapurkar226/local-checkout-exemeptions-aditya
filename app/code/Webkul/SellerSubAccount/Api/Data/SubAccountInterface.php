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
namespace Webkul\SellerSubAccount\Api\Data;

interface SubAccountInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    public const ENTITY_ID = 'entity_id';

    public const CUSTOMER_ID = 'customer_id';

    public const SELLER_ID = 'seller_id';

    public const PERMISSION_TYPE = 'permission_type';

    public const STATUS = 'status';

    public const CREATED_AT = 'created_at';

    public const UPDATED_AT = 'updated_date';

    /**
     * Get ID.
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\SellerSubAccount\Api\Data\SubAccountInterface
     */
    public function setId($id);

    /**
     * Get Customer Id.
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Set Customer Id.
     *
     * @param int $customerId
     *
     * @return \Webkul\SellerSubAccount\Api\Data\SubAccountInterface
     */
    public function setCustomerId($customerId);

    /**
     * Get Seller Id.
     *
     * @return int|null
     */
    public function getSellerId();

    /**
     * Set Seller Id.
     *
     * @param int $sellerId
     *
     * @return \Webkul\SellerSubAccount\Api\Data\SubAccountInterface
     */
    public function setSellerId($sellerId);

    /**
     * Get Permission Type.
     *
     * @return string|null
     */
    public function getPermissionType();

    /**
     * Set Permission Type.
     *
     * @param string $permissionType
     *
     * @return \Webkul\SellerSubAccount\Api\Data\SubAccountInterface
     */
    public function setPermissionType($permissionType);

    /**
     * Get Status.
     *
     * @return string|null
     */
    public function getStatus();

    /**
     * Set Status.
     *
     * @param string $status
     *
     * @return \Webkul\SellerSubAccount\Api\Data\SubAccountInterface
     */
    public function setStatus($status);

    /**
     * Get Created Date.
     *
     * @return date|null
     */
    public function getCreatedAt();

    /**
     * Set Created Date.
     *
     * @param string $createdAt
     *
     * @return \Webkul\SellerSubAccount\Api\Data\SubAccountInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get Updated Date.
     *
     * @return date|null
     */
    public function getUpdatedAt();

    /**
     * Set Updated Date.
     *
     * @param string $updatedAt
     *
     * @return \Webkul\SellerSubAccount\Api\Data\SubAccountInterface
     */
    public function setUpdatedAt($updatedAt);
}
