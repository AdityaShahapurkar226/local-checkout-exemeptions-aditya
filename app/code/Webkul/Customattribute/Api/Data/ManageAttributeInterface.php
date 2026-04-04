<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Customattribute
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Customattribute\Api\Data;

/**
 * Custom Attribute interface.
 * @api
 */
interface ManageAttributeInterface
{

    public const ENTITY_ID  = 'entity_id';

    public const ATTRIBUTE_ID  = 'attribute_id';

    public const STATUS  = 'status';

    /**
     * Get entity id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set entity id
     *
     * @param int $id
     * @return \Webkul\Marketplace\Api\Data\SellerInterface
     */
    public function setId($id);

    /**
     * Get AttributeId
     *
     * @return int|null
     */
    public function getAttributeId();

    /**
     * Set AttributeId
     *
     * @param int $attributeId
     * @return \Webkul\Marketplace\Api\Data\SellerInterface
     */
    public function setAttributeId($attributeId);

    /**
     * Get Status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Set Status
     *
     * @param int $status
     * @return \Webkul\Marketplace\Api\Data\SellerInterface
     */
    public function setStatus($status);
}
