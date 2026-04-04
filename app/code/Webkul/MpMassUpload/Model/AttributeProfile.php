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
use Webkul\MpMassUpload\Api\Data\AttributeProfileInterface;
use Magento\Framework\DataObject\IdentityInterface;

class AttributeProfile extends AbstractModel implements AttributeProfileInterface, IdentityInterface
{
    /**
     * No route page id.
     */
    public const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * MpMassUpload Profile cache tag.
     */
    public const CACHE_TAG = 'marketplace_massupload_attribute_profile';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_massupload_attribute_profile';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_massupload_attribute_profile';

    /**
     * Initialize resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpMassUpload\Model\ResourceModel\AttributeProfile::class);
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
            return $this->noRouteAttributeProfile();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route AttributeProfile.
     *
     * @return \Webkul\MpMassUpload\Model\AttributeProfile
     */
    public function noRouteAttributeProfile()
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
     * @return \Webkul\MpMassUpload\Api\Data\AttributeProfileInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
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
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     */
    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }

    /**
     * Get Profile Name.
     *
     * @return string|null
     */
    public function getProfileName()
    {
        return parent::getData(self::PROFILE_NAME);
    }

    /**
     * Set Profile Name.
     *
     * @param string $profileName
     *
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     */
    public function setProfileName($profileName)
    {
        return $this->setData(self::PROFILE_NAME, $profileName);
    }

    /**
     * Get Attribute Set Id.
     *
     * @return int|null
     */
    public function getAttributeSetId()
    {
        return parent::getData(self::ATTRIBUTE_SET_ID);
    }

    /**
     * Set Attribute Set Id.
     *
     * @param int $attributeSetId
     *
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     */
    public function setAttributeSetId($attributeSetId)
    {
        return $this->setData(self::ATTRIBUTE_SET_ID, $attributeSetId);
    }

    /**
     * Get Created Date.
     *
     * @return string|null
     */
    public function getCreatedDate()
    {
        return parent::getData(self::CREATED_DATE);
    }

    /**
     * Set Created Date.
     *
     * @param string $createdDate
     *
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     */
    public function setCreatedDate($createdDate)
    {
        return $this->setData(self::CREATED_DATE, $createdDate);
    }
}
