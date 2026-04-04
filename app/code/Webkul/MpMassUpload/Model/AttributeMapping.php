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
namespace Webkul\MpMassUpload\Model;

use Magento\Framework\Model\AbstractModel;
use Webkul\MpMassUpload\Api\Data\AttributeMappingInterface;
use Magento\Framework\DataObject\IdentityInterface;

class AttributeMapping extends AbstractModel implements AttributeMappingInterface, IdentityInterface
{
    /**
     * No route page id.
     */
    public const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * MpMassUpload Attribute Mapping cache tag.
     */
    public const CACHE_TAG = 'marketplace_massupload_attribute_mapping';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_massupload_attribute_mapping';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_massupload_attribute_mapping';

    /**
     * Initialize resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpMassUpload\Model\ResourceModel\AttributeMapping::class);
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
            return $this->noRouteAttributeMapping();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route AttributeMapping.
     *
     * @return \Webkul\MpMassUpload\Model\AttributeMapping
     */
    public function noRouteAttributeMapping()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
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
     * @return \Webkul\MpMassUpload\Api\Data\AttributeMappingInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get Profile ID.
     *
     * @return int
     */
    public function getProfileId()
    {
        return parent::getData(self::PROFILE_ID);
    }

    /**
     * Set Profile ID.
     *
     * @param int $profileId
     *
     * @return \Webkul\MpMassUpload\Api\Data\AttributeMappingInterface
     */
    public function setProfileId($profileId)
    {
        return $this->setData(self::PROFILE_ID, $profileId);
    }

    /**
     * Get File Attribute.
     *
     * @return string
     */
    public function getFileAttribute()
    {
        return parent::getData(self::FILE_ATTRIBUTE);
    }

    /**
     * Set File Attribute.
     *
     * @param string $fileAttribute
     *
     * @return \Webkul\MpMassUpload\Api\Data\AttributeMappingInterface
     */
    public function setFileAttribute($fileAttribute)
    {
        return $this->setData(self::FILE_ATTRIBUTE, $fileAttribute);
    }

    /**
     * Get Magento Attribute.
     *
     * @return string
     */
    public function getMageAttribute()
    {
        return parent::getData(self::MAGE_ATTRIBUTE);
    }

    /**
     * Set Magento Attribute.
     *
     * @param string $mageAttribute
     *
     * @return \Webkul\MpMassUpload\Api\Data\AttributeMappingInterface
     */
    public function setMageAttribute($mageAttribute)
    {
        return $this->setData(self::MAGE_ATTRIBUTE, $mageAttribute);
    }
}
