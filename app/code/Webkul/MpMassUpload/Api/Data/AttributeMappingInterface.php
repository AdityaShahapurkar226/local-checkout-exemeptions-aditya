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

interface AttributeMappingInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    public const ENTITY_ID = 'entity_id';
    /**#@-*/

    public const PROFILE_ID = 'profile_id';

    public const FILE_ATTRIBUTE = 'file_attribute';

    public const MAGE_ATTRIBUTE = 'mage_attribute';

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
     * @return \Webkul\MpMassUpload\Api\Data\AttributeMappingInterface
     */
    public function setId($id);

    /**
     * Get Profile Id.
     *
     * @return int|null
     */
    public function getProfileId();

    /**
     * Set Profile Id.
     *
     * @param int $profileId
     *
     * @return \Webkul\MpMassUpload\Api\Data\AttributeMappingInterface
     */
    public function setProfileId($profileId);

    /**
     * Get File Attribute.
     *
     * @return string|null
     */
    public function getFileAttribute();

    /**
     * Set File Attribute.
     *
     * @param int $fileAttribute
     *
     * @return \Webkul\MpMassUpload\Api\Data\AttributeMappingInterface
     */
    public function setFileAttribute($fileAttribute);

    /**
     * Get Magento Attribute.
     *
     * @return string|null
     */
    public function getMageAttribute();

    /**
     * Set Magento Attribute.
     *
     * @param string $mageAttribute
     *
     * @return \Webkul\MpMassUpload\Api\Data\AttributeMappingInterface
     */
    public function setMageAttribute($mageAttribute);
}
