<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMassUpload
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpMassUpload\Api\Data;

interface AttributeProfileInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    public const ENTITY_ID = 'entity_id';
    /**#@-*/

    public const SELLER_ID = 'seller_id';

    public const PROFILE_NAME = 'profile_name';

    public const ATTRIBUTE_SET_ID = 'attribute_set_id';

    public const CREATED_DATE = 'created_date';

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
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     */
    public function setId($id);

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
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     */
    public function setSellerId($sellerId);

    /**
     * Get Profile Name.
     *
     * @return string|null
     */
    public function getProfileName();

    /**
     * Set Profile Name.
     *
     * @param string $profileName
     *
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     */
    public function setProfileName($profileName);

    /**
     * Get Attribute Set Id.
     *
     * @return int|null
     */
    public function getAttributeSetId();

    /**
     * Set Attribute Set Id.
     *
     * @param int $attributeSetId
     *
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     */
    public function setAttributeSetId($attributeSetId);

    /**
     * Get Created Date.
     *
     * @return date|null
     */
    public function getCreatedDate();

    /**
     * Set Created Date.
     *
     * @param string $createdDate
     *
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     */
    public function setCreatedDate($createdDate);
}
