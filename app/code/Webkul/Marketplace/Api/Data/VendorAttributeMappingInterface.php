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

namespace Webkul\Marketplace\Api\Data;

interface VendorAttributeMappingInterface
{
    public const ENTITY_ID  = 'entity_id';

    public const SELLER_ID  = 'seller_id';

    public const ATTRIBUTE_ID     = 'attribute_id';

    public const UPDATED_AT      = 'updated_at';

    public const CREATED_AT = 'created_at';

    /**
     * Gets the entity ID.
     *
     * @return int Entity ID.
     */
    public function getEntityId();

    /**
     * Sets entity ID.
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * Gets the Seller ID.
     *
     * @return int Seller ID.
     */
    public function getSellerId();

    /**
     * Sets Seller ID.
     *
     * @param int $sellerId
     * @return $this
     */
    public function setSellerId($sellerId);

    /**
     * Gets the Attribute Id.
     *
     * @return string Reason
     */
    public function getAttributeId();

    /**
     * Sets the attribute id.
     *
     * @param string $attributeId
     * @return $this
     */
    public function setAttributeId($attributeId);

    /**
     * Gets creation timestamp.
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Sets updated timestamp.
     *
     * @param string $timestamp
     * @return $this
     */
    public function setCreatedAt($timestamp);
    /**
     * Gets creation timestamp.
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Sets updated timestamp.
     *
     * @param string $timestamp
     * @return $this
     */
    public function setUpdatedAt($timestamp);
}
