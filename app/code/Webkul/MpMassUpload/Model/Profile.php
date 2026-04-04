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
use Webkul\MpMassUpload\Api\Data\ProfileInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Profile extends AbstractModel implements ProfileInterface, IdentityInterface
{
    /**
     * No route page id.
     */
    public const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * MpMassUpload Profile cache tag.
     */
    public const CACHE_TAG = 'marketplace_massupload_profile';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_massupload_profile';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_massupload_profile';

    /**
     * Initialize resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpMassUpload\Model\ResourceModel\Profile::class);
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
            return $this->noRouteProfile();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route Profile.
     *
     * @return \Webkul\MpMassUpload\Model\Profile
     */
    public function noRouteProfile()
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
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
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
    public function getCustomerId()
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
    public function setCustomerId($sellerId)
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
     * Get Product Type.
     *
     * @return string|null
     */
    public function getProductType()
    {
        return parent::getData(self::PRODUCT_TYPE);
    }

    /**
     * Set Product Type.
     *
     * @param string $productType
     *
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     */
    public function setProductType($productType)
    {
        return $this->setData(self::PRODUCT_TYPE, $productType);
    }

    /**
     * Get Attributeset Id.
     *
     * @return int|null
     */
    public function getAttributeSetId()
    {
        return parent::getData(self::ATTRIBUTE_SET_ID);
    }

    /**
     * Set Attributeset Id.
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
     * Get Image File.
     *
     * @return string|null
     */
    public function getImageFile()
    {
        return parent::getData(self::IMAGE_FILE);
    }

    /**
     * Set Image File.
     *
     * @param string $imageFile
     *
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     */
    public function setImageFile($imageFile)
    {
        return $this->setData(self::IMAGE_FILE, $imageFile);
    }

    /**
     * Get Link File.
     *
     * @return string|null
     */
    public function getLinkFile()
    {
        return parent::getData(self::LINK_FILE);
    }

    /**
     * Set Link File.
     *
     * @param string $linkFile
     *
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     */
    public function setLinkFile($linkFile)
    {
        return $this->setData(self::LINK_FILE, $linkFile);
    }

    /**
     * Get Sample File.
     *
     * @return string|null
     */
    public function getSampleFile()
    {
        return parent::getData(self::SAMPLE_FILE);
    }

    /**
     * Set Sample File.
     *
     * @param string $sampleFile
     *
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     */
    public function setSampleFile($sampleFile)
    {
        return $this->setData(self::SAMPLE_FILE, $sampleFile);
    }

    /**
     * Get Data Row.
     *
     * @return string|null
     */
    public function getDataRow()
    {
        return parent::getData(self::DATA_ROW);
    }

    /**
     * Set Data Row.
     *
     * @param string $dataRow
     *
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     */
    public function setDataRow($dataRow)
    {
        return $this->setData(self::DATA_ROW, $dataRow);
    }

    /**
     * Get File Type.
     *
     * @return string|null
     */
    public function getFileType()
    {
        return parent::getData(self::FILE_TYPE);
    }

    /**
     * Set File Type.
     *
     * @param string $fileType
     *
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     */
    public function setFileType($fileType)
    {
        return $this->setData(self::FILE_TYPE, $fileType);
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
