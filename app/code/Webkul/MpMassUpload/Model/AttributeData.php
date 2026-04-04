<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MpMassUpload
 * @author Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */


namespace Webkul\MpMassUpload\Model;

use \Magento\Framework\DataObject\IdentityInterface;
use \Webkul\MpMassUpload\Api\Data\AttributeInterface;
use \Magento\Framework\Model\AbstractModel;

/**
 * ProfilerData Class Getter Setter
 */
class AttributeData extends AbstractModel implements IdentityInterface, AttributeInterface
{

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
     * Set Attribute Set
     *
     * @param int $id
     * @return Webkul\MpMassUpload\Api\Data\AttributeInterface
     */
    public function setAttributeSet($id)
    {
        return $this->setData(self::ATTRIBUTE_SET, $id);
    }

    /**
     * Get Attribute Set
     *
     * @return int
     */
    public function getAttributeSet()
    {
        return parent::getData(self::ATTRIBUTE_SET);
    }

    /**
     * Get Profiler Name
     *
     * @return string
     */
    public function getProfileName()
    {
        return parent::getData(self::PROFILE_NAME);
    }

    /**
     * Set Profiler Name
     *
     * @param string $profileName
     * @return \Webkul\MpMassUpload\Api\Data\AttributeInterface
     */
    public function setProfileName($profileName)
    {
        return $this->setData(self::PROFILE_NAME, $profileName);
    }

    /**
     * Get attribute profile Edit Id
     *
     * @return int
     */
    public function getEditId()
    {
        return parent::getData(self::EDIT_ID);
    }

    /**
     * Set attribute profile Edit Id
     *
     * @param int $editId
     * @return \Webkul\MpMassUpload\Api\Data\AttributeInterface
     */
    public function setEditId($editId)
    {
        return $this->setData(self::EDIT_ID, $editId);
    }

    /**
     * Get Error Status
     *
     * @return int
     */
    public function getErrorStatus()
    {
        return parent::getData(self::ERROR_STATUS);
    }

    /**
     * Set Error Status
     *
     * @param int|null $errorStatus
     * @return \Webkul\MpMassUpload\Api\Data\AttributeInterface
     */
    public function setErrorStatus($errorStatus)
    {
        return $this->setData(self::ERROR_STATUS, $errorStatus);
    }

    /**
     * Get Profiler Message
     *
     * @return string
     */
    public function getMessage()
    {
        return parent::getData(self::MESSAGE);
    }

    /**
     * Set Profiler Message
     *
     * @param string $message
     * @return \Webkul\MpMassUpload\Api\Data\AttributeInterface
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * Get attribute profile  Id
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ID);
    }

    /**
     * Set attribute profile Id
     *
     * @param int $id
     * @return \Webkul\MpMassUpload\Api\Data\AttributeInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Set Mage Attribute
     *
     * @param string $mageAttribute
     * @return \Webkul\MpMassUpload\Api\Data\AttributeInterface
     */
    public function setMageAttribute($mageAttribute)
    {
        return $this->setData(self::MAGE_ATTRIBUTE, $mageAttribute);
    }

    /**
     * Get Mage Attribute
     *
     * @return string
     */
    public function getMageAttribute()
    {
        return parent::getData(self::MAGE_ATTRIBUTE);
    }

    /**
     * Set File Attribute
     *
     * @param string $fileAttribute
     * @return \Webkul\MpMassUpload\Api\Data\AttributeInterface
     */
    public function setFileAttribute($fileAttribute)
    {
        return $this->setData(self::FILE_ATTRIBUTE, $fileAttribute);
    }

    /**
     * Get File Attribute
     *
     * @return string
     */
    public function getFileAttribute()
    {
        return parent::getData(self::FILE_ATTRIBUTE);
    }
}
