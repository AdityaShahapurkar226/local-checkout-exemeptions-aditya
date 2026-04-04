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

/**
 * Interface AttributeInterface
 * @api
 * @since 100.0.2
 */
interface AttributeInterface
{
    public const ID             = 'id';

    public const PROFILE_NAME   = 'profile_name';

    public const ATTRIBUTE_SET  = 'attribute_set';

    public const EDIT_ID        = 'edit_id';

    public const ERROR_STATUS   = 'error_status';

    public const MESSAGE        = 'message';

    public const MAGE_ATTRIBUTE = 'mage_attribute';

    public const FILE_ATTRIBUTE = 'file_attribute';

    /**
     * Get attribute Profile Name
     *
     * @return string
     */
    public function getProfileName();

    /**
     * Set attribute profile Name
     *
     * @param string $profileName
     * @return Webkul\MpMassUpload\Api\Data\AttributeInterface
     */
    public function setProfileName($profileName);

     /**
      * Get attribute Set
      *
      * @return int
      */
    public function getAttributeSet();

    /**
     * Set attribute set
     *
     * @param int $attributeSet
     * @return Webkul\MpMassUpload\Api\Data\AttributeInterface
     */
    public function setAttributeSet($attributeSet);

    /**
     * Get attribute profile Edit Id
     *
     * @return int
     */
    public function getEditId();

    /**
     * Set attribute profile Edit Id
     *
     * @param int|null $editId
     * @return Webkul\MpMassUpload\Api\Data\AttributeInterface
     */
    public function setEditId($editId);

    /**
     * Get Error Status
     *
     * @return int
     */
    public function getErrorStatus();

    /**
     * Set Error Status
     *
     * @param int|null $errorStatus
     * @return Webkul\MpMassUpload\Api\Data\AttributeInterface
     */
    public function setErrorStatus($errorStatus);

    /**
     * Get Profiler Message
     *
     * @return string
     */
    public function getMessage();

    /**
     * Set Profiler Message
     *
     * @param string|null $message
     * @return Webkul\MpMassUpload\Api\Data\AttributeInterface
     */
    public function setMessage($message);

    /**
     * Get attribute profile Id
     *
     * @return int
     */
    public function getId();

    /**
     * Set attribute profile Id
     *
     * @param int|null $id
     * @return Webkul\MpMassUpload\Api\Data\AttributeInterface
     */
    public function setId($id);

    /**
     * Get Mage Attribue
     *
     * @return string
     */
    public function getMageAttribute();

    /**
     * Set Mage Attribue
     *
     * @param string|null $mageAttribute
     * @return Webkul\MpMassUpload\Api\Data\AttributeInterface
     */
    public function setMageAttribute($mageAttribute);
    
    /**
     * Get File Attribute
     *
     * @return string
     */
    public function getFileAttribute();

    /**
     * Set File Attribute
     *
     * @param string|null $fileAttribute
     * @return Webkul\MpMassUpload\Api\Data\AttributeInterface
     */
    public function setFileAttribute($fileAttribute);
}
